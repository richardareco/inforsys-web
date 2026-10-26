<?php

namespace App\Services\WhatsApp;

use App\Models\Empresa;
use App\Models\User;
use App\Models\WaAgenteConfig;
use App\Models\WaPedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Pasa un pedido de WhatsApp a venta en la base Delphi cuando el vendedor lo marca como pagado.
 * Replica lo que hace el POS (invo1 flag 4, invo2, descuento de stock en items e itemsdepo y, si el
 * módulo de caja está activo, el cobro en cajareg), todo en una sola transacción.
 */
class VentaDelphiService
{
    public function __construct(private Empresa $empresa) {}

    /** @return string número de comprobante (invnr) generado */
    public function pagar(WaPedido $pedido, ?User $user): string
    {
        // Se "reclama" el pedido de forma atómica para que dos clics no generen dos ventas
        $reclamado = WaPedido::whereKey($pedido->id)->where('estado', 'pendiente')->update(['estado' => 'procesando']);
        if (!$reclamado) {
            throw new ReglaDeNegocio('Este pedido ya fue procesado o no está pendiente.');
        }

        try {
            [$invnr, $custnr] = $this->registrarVenta($pedido->fresh('items'), $user);
        } catch (Throwable $e) {
            WaPedido::whereKey($pedido->id)->update(['estado' => 'pendiente']);
            throw $e;
        }

        try {
            WaPedido::whereKey($pedido->id)->update([
                'estado' => 'pagado', 'venta_invnr' => $invnr, 'cliente_delphi' => $custnr,
                'pagado_at' => now(), 'gestionado_por' => $user?->id, 'gestionado_at' => now(),
            ]);
        } catch (Throwable $e) {
            // La venta ya está en el sistema: dejar rastro para corregirlo a mano
            Log::critical("WhatsApp: venta {$invnr} creada para el pedido {$pedido->id} pero no se pudo marcar como pagado: " . $e->getMessage());
            throw $e;
        }

        return $invnr;
    }

    public function anular(WaPedido $pedido, ?User $user, string $motivo): void
    {
        $ok = WaPedido::whereKey($pedido->id)->whereIn('estado', ['pendiente', 'borrador'])->update([
            'estado' => 'anulado', 'nota_interna' => $motivo, 'anulado_at' => now(),
            'gestionado_por' => $user?->id, 'gestionado_at' => now(),
        ]);

        if (!$ok) {
            throw new ReglaDeNegocio('Solo se pueden anular pedidos pendientes o sin confirmar.');
        }
    }

    /** @return array{0: string, 1: string} [invnr, custnr] */
    private function registrarVenta(WaPedido $pedido, ?User $user): array
    {
        // Solo se reconecta si la conexión apunta a otra base (el middleware ya la deja lista en el panel)
        if (config('database.connections.delphi.database') !== $this->empresa->db_name) {
            DB::purge('delphi');
            config(['database.connections.delphi.database' => $this->empresa->db_name]);
        }
        $db = DB::connection('delphi');

        $config   = WaAgenteConfig::where('empresa_id', $this->empresa->id)->first();
        // Mismos depósitos que usa el catálogo para mostrar stock (sin configurar = todos, en orden)
        $depos = array_values(array_filter((array) ($config?->depositos ?? [])))
            ?: $db->table('deposito')->orderBy('deponr')->pluck('deponr')->map(fn ($d) => (string) $d)->all()
            ?: ['D01'];
        $sinStock = (bool) ($this->empresa->permitir_venta_sin_stock ?? false);
        $conCaja  = (bool) ($this->empresa->modulo_caja_enabled ?? true);

        $personal = (string) $user?->personal_id;
        $pernr = $personal !== '' && $db->table('personal')->where('pernr', $personal)->exists()
            ? $personal
            : (string) $db->table('personal')->orderBy('pernr')->value('pernr');

        $invnr = null;
        $custnr = null;

        $db->transaction(function () use ($pedido, $db, $depos, $sinStock, $conCaja, $pernr, &$invnr, &$custnr) {
            $custnr = $this->clienteDelphi($db, $pedido);
            $hoy    = today()->toDateString();
            $hora   = now()->format('H:i:s');

            // Líneas con el depósito del que sale cada cantidad (bloquea las filas de stock)
            $lineas = [];
            $costoTotal = 0.0;
            foreach ($pedido->items as $it) {
                $costo = (float) $db->table('items')->where('item', $it->item)->value('costo');
                $restante = (float) $it->cantidad;

                foreach ($depos as $i => $depo) {
                    if ($restante <= 0) {
                        break;
                    }
                    $disp  = (float) $db->table('itemsdepo')->where('item', $it->item)->where('depo', $depo)->lockForUpdate()->value('qty');
                    $ultimo = $i === array_key_last($depos);
                    $toma  = $sinStock && $ultimo ? $restante : min($restante, max($disp, 0));
                    if ($toma > 0) {
                        $lineas[] = ['item' => $it->item, 'descr' => $it->descr, 'depo' => $depo, 'qty' => $toma, 'precio' => (float) $it->precio, 'costo' => $costo];
                        $restante -= $toma;
                    }
                }

                if ($restante > 0.0005) {
                    throw new ReglaDeNegocio("No hay stock suficiente de {$it->descr} para generar la venta (faltan " . rtrim(rtrim(number_format($restante, 3, '.', ''), '0'), '.') . ').');
                }
                $costoTotal += $costo * (float) $it->cantidad;
            }

            // 1. Cabecera
            $db->table('invo1')->insert([
                'total'       => $pedido->total,
                'totalv'      => $pedido->total,
                'ctotal'      => $costoTotal,
                'flag'        => '4',
                'fecha'       => $hoy,
                'hora'        => $hora,
                'pernr'       => $pernr,
                'custnr'      => $custnr,
                'depo'        => $depos[0],
                'saldo'       => 0,
                'sta_num'     => '01',
                'sucursal_id' => 1,
            ]);

            $invnr = (string) $db->table('invo1')
                ->where('fecha', $hoy)->where('hora', $hora)->where('pernr', $pernr)->where('custnr', $custnr)->where('total', $pedido->total)
                ->orderByRaw('invnr DESC')->value('invnr');

            // 2. Detalle y 3. stock
            foreach ($lineas as $l) {
                $db->table('invo2')->insert([
                    'invnr' => $invnr, 'item' => $l['item'], 'descr' => $l['descr'], 'depo' => $l['depo'],
                    'qty' => $l['qty'], 'precio' => $l['precio'], 'costo' => $l['costo'], 'flag' => '3', 'fecha' => $hoy,
                ]);
                $db->table('items')->where('item', $l['item'])->decrement('stock', $l['qty']);
                $db->table('itemsdepo')->where('item', $l['item'])->where('depo', $l['depo'])->decrement('qty', $l['qty']);
            }

            // 4. Cobro en caja (si el módulo está activo)
            if ($conCaja) {
                $db->table('cajareg')->insert([
                    'fecha' => $hoy, 'totalus' => $pedido->total, 'total' => $pedido->total,
                    'paygs' => $pedido->forma_pago === 'efectivo' ? $pedido->total : 0,
                    'paytr' => $pedido->forma_pago === 'transferencia' ? $pedido->total : 0,
                    'paytj' => 0, 'ctotal' => $costoTotal, 'sta_num' => '01', 'op' => 'PC', 'flag' => 1, 'tipo_moneda' => 'GS',
                ]);
                $nro    = $db->getPdo()->lastInsertId();
                $cajanr = $db->table('cajareg')->where('nro', $nro)->value('cajanr');
                $db->table('invo1')->where('invnr', $invnr)->update(['cajanr' => $cajanr]);
            }
        });

        return [$invnr, (string) $custnr];
    }

    /** Cliente del sistema: por RUC, luego por teléfono; si no existe se crea con los datos del pedido. */
    private function clienteDelphi($db, WaPedido $pedido): string
    {
        if (trim((string) $pedido->ruc) !== '') {
            $c = $db->table('cliente')->where('ruc', trim($pedido->ruc))->first();
            if ($c) {
                return (string) $c->custnr;
            }
        }

        $tel = preg_replace('/\D+/', '', (string) $pedido->telefono);
        $fin = substr($tel, -9);
        if (strlen($fin) >= 8) {
            foreach (['telef1', 'telef2', 'telef3', 'celular1', 'celular2'] as $col) {
                $c = $db->table('cliente')
                    ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE({$col}, ' ', ''), '-', ''), '+', ''), '(', ''), ')', '') LIKE ?", ['%' . $fin])
                    ->first();
                if ($c) {
                    return (string) $c->custnr;
                }
            }
        }

        $max = (int) $db->table('cliente')->selectRaw('MAX(CAST(custnr AS UNSIGNED)) AS m')->value('m');
        $nuevo = (string) ($max + 1);

        $db->table('cliente')->insert([
            'custnr'   => $nuevo,
            'custname' => mb_substr((string) ($pedido->nombre_cliente ?: 'Cliente WhatsApp'), 0, 50),
            'addr'     => $pedido->direccion_entrega ? mb_substr($pedido->direccion_entrega, 0, 80) : null,
            'telef1'   => $tel !== '' ? '+' . $tel : null,
            'ruc'      => $pedido->ruc ?: null,
        ]);

        return $nuevo;
    }
}
