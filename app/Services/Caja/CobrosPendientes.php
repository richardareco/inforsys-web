<?php

namespace App\Services\Caja;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Cobro en caja de las ventas que el POS dejó "pendientes de cobro" (empresas sin módulo de caja en el POS).
 *
 * Una venta está pendiente cuando es completada (flag 4), no es a crédito y no tiene cajanr. Cobrarla es registrar el
 * movimiento en cajareg (con las formas de pago) y enlazarlo en invo1.cajanr, igual que hace el POS con el módulo de caja.
 */
class CobrosPendientes
{
    public const PERIODOS = ['hoy' => 'Hoy', '7d' => 'Últimos 7 días', '30d' => 'Últimos 30 días'];

    private const LIMITE = 100;

    private function db()
    {
        return DB::connection('delphi');
    }

    /** Ventas pendientes de cobro (condición compartida con el Historial de ventas). */
    private function pendientes()
    {
        return $this->db()->table('invo1')
            ->where('invo1.flag', '4')
            ->where(fn ($q) => $q->whereNull('invo1.acredito')->orWhere('invo1.acredito', 0))
            ->where(fn ($q) => $q->whereNull('invo1.cajanr')->orWhere('invo1.cajanr', ''))
            ->where('invo1.total', '>', 0);
    }

    /**
     * Lista para la pantalla de caja: las más recientes primero.
     *
     * @return array{ventas: array<int, array<string, mixed>>, total: float, ultima: string}
     */
    public function listar(string $periodo = 'hoy', string $buscar = '', ?Carbon $ahora = null): array
    {
        $ahora = $ahora ?? now();
        $desde = match ($periodo) {
            '7d'    => $ahora->copy()->subDays(6)->startOfDay(),
            '30d'   => $ahora->copy()->subDays(29)->startOfDay(),
            default => $ahora->copy()->startOfDay(),
        };

        $q = $this->pendientes()
            ->leftJoin('cliente', 'cliente.custnr', '=', 'invo1.custnr')
            ->leftJoin('personal', 'personal.pernr', '=', 'invo1.pernr')
            ->where('invo1.fecha', '>=', $desde->toDateString())
            ->selectRaw("invo1.nro, invo1.invnr, invo1.fecha, invo1.hora, invo1.total, cliente.custname AS cliente, personal.pername AS vendedor,
                (SELECT COUNT(*) FROM invo2 WHERE invo2.invnr = invo1.invnr AND invo2.flag = '3') AS lineas,
                (SELECT COALESCE(SUM(invo2.qty), 0) FROM invo2 WHERE invo2.invnr = invo1.invnr AND invo2.flag = '3') AS unidades");

        $buscar = trim($buscar);
        if ($buscar !== '') {
            $like = '%' . addcslashes($buscar, '%_\\') . '%';
            $q->where(fn ($w) => $w->where('invo1.invnr', 'like', $like)->orWhere('cliente.custname', 'like', $like)->orWhere('personal.pername', 'like', $like));
        }

        $filas = $q->orderByDesc('invo1.nro')->limit(self::LIMITE)->get();

        $ventas = $filas->map(function ($f) use ($ahora) {
            $momento = $this->momento($f->fecha, $f->hora);

            return [
                'invnr'    => (string) $f->invnr,
                'numero'   => str_pad((string) $f->invnr, 6, '0', STR_PAD_LEFT),
                'total'    => (float) $f->total,
                'cliente'  => $this->texto($f->cliente) ?: 'Cliente ocasional',
                'vendedor' => $this->texto($f->vendedor) ?: '—',
                'lineas'   => (int) $f->lineas,
                'unidades' => (float) $f->unidades,
                'hora'     => $momento ? $momento->format('H:i') : '',
                'hace'     => $momento ? $this->hace($momento, $ahora) : '',
                'minutos'  => $momento ? (int) max(0, $momento->diffInMinutes($ahora, false)) : 0,
                'nro'      => (int) $f->nro,
            ];
        })->all();

        return [
            'ventas' => $ventas,
            'total'  => array_sum(array_column($ventas, 'total')),
            'ultima' => $ventas ? (string) max(array_column($ventas, 'nro')) : '',
        ];
    }

    /**
     * Huella mínima de lo que hay pendiente en el período: cambia cuando entra una venta nueva o se cobra una. El
     * navegador la consulta cada 2 segundos y solo recarga el listado cuando cambió (una consulta chica, sin armar la pantalla).
     */
    public function firma(string $periodo = 'hoy', ?Carbon $ahora = null): string
    {
        $ahora = $ahora ?? now();
        $desde = match ($periodo) {
            '7d'    => $ahora->copy()->subDays(6)->startOfDay(),
            '30d'   => $ahora->copy()->subDays(29)->startOfDay(),
            default => $ahora->copy()->startOfDay(),
        };

        $r = $this->pendientes()->where('invo1.fecha', '>=', $desde->toDateString())
            ->selectRaw('COUNT(*) AS n, COALESCE(MAX(invo1.nro), 0) AS ultima, COALESCE(SUM(invo1.total), 0) AS total')->first();

        return (int) $r->n . '-' . (int) $r->ultima . '-' . (int) round((float) $r->total);
    }

    /**
     * Venta pendiente con sus productos, para mostrarla antes de cobrar. null si no existe o ya fue cobrada.
     *
     * @return array<string, mixed>|null
     */
    public function venta(string $invnr): ?array
    {
        $f = $this->pendientes()
            ->leftJoin('cliente', 'cliente.custnr', '=', 'invo1.custnr')
            ->leftJoin('personal', 'personal.pernr', '=', 'invo1.pernr')
            ->where('invo1.invnr', $invnr)
            ->first(['invo1.invnr', 'invo1.fecha', 'invo1.hora', 'invo1.total', 'cliente.custname AS cliente', 'personal.pername AS vendedor']);
        if (!$f) {
            return null;
        }

        $lineas = $this->db()->table('invo2')->where('invnr', $invnr)->where('flag', '3')->orderBy('nro')
            ->get(['item', 'descr', 'qty', 'precio'])
            ->map(fn ($l) => [
                'item'     => (string) $l->item,
                'descr'    => $this->texto($l->descr) ?: (string) $l->item,
                'qty'      => (float) $l->qty,
                'precio'   => (float) $l->precio,
                'subtotal' => (float) $l->qty * (float) $l->precio,
            ])->all();

        $momento = $this->momento($f->fecha, $f->hora);

        return [
            'invnr'    => (string) $f->invnr,
            'numero'   => str_pad((string) $f->invnr, 6, '0', STR_PAD_LEFT),
            'total'    => (float) $f->total,
            'cliente'  => $this->texto($f->cliente) ?: 'Cliente ocasional',
            'vendedor' => $this->texto($f->vendedor) ?: '—',
            'hora'     => $momento ? $momento->format('H:i') : '',
            'lineas'   => $lineas,
        ];
    }

    /**
     * Registra el cobro: inserta el movimiento en cajareg con las formas de pago y lo enlaza en la venta (cajanr).
     *
     * El efectivo es lo que entrega el cliente; el resto (transferencia y tarjeta) debe estar dentro del total. Lo que
     * falte lo cubre el efectivo, y si entrega de más se calcula el vuelto.
     *
     * @return array{cajanr: string, total: float, efectivo: float, transferencia: float, tarjeta: float, recibido: float, vuelto: float, principal: string}
     *
     * @throws CobroInvalido
     */
    public function cobrar(string $invnr, float $efectivo = 0, float $transferencia = 0, float $tarjeta = 0): array
    {
        if ($efectivo < 0 || $transferencia < 0 || $tarjeta < 0) {
            throw new CobroInvalido('Los importes no pueden ser negativos.');
        }

        return $this->db()->transaction(function () use ($invnr, $efectivo, $transferencia, $tarjeta) {
            // La fila queda bloqueada: si dos cajeros cobran la misma venta, el segundo la encuentra ya cobrada
            $venta = $this->db()->table('invo1')->where('invnr', $invnr)->lockForUpdate()->first();
            if (!$venta) {
                throw new CobroInvalido('No se encontró la venta.');
            }
            if (trim((string) $venta->cajanr) !== '' || (float) ($venta->acredito ?? 0) > 0 || (string) $venta->flag !== '4') {
                throw new CobroInvalido('Esta venta ya no está pendiente de cobro (otro cajero pudo haberla cobrado).');
            }

            $total = round((float) $venta->total, 2);
            if ($total <= 0) {
                throw new CobroInvalido('La venta no tiene importe para cobrar.');
            }

            $efectivo      = round($efectivo, 2);
            $transferencia = round($transferencia, 2);
            $tarjeta       = round($tarjeta, 2);

            if ($transferencia + $tarjeta > $total + 0.005) {
                throw new CobroInvalido('La transferencia y la tarjeta superan el total de la venta.');
            }
            $efectivoNecesario = round($total - $transferencia - $tarjeta, 2);
            if ($efectivo + 0.005 < $efectivoNecesario) {
                throw new CobroInvalido('Los importes no cubren el total: faltan Gs. ' . number_format($efectivoNecesario - $efectivo, 0, ',', '.') . '.');
            }

            $this->db()->table('cajareg')->insert([
                'fecha'       => today()->toDateString(),
                'totalus'     => $total,
                'total'       => $total,
                'paygs'       => $efectivoNecesario,
                'paytr'       => $transferencia,
                'paytj'       => $tarjeta,
                'ctotal'      => (float) $venta->ctotal,
                'sta_num'     => '01',
                'op'          => 'PC',
                'flag'        => 1,
                'tipo_moneda' => 'GS',
            ]);

            $nro    = $this->db()->getPdo()->lastInsertId();
            $cajanr = (string) $this->db()->table('cajareg')->where('nro', $nro)->value('cajanr');
            if ($cajanr === '') {
                throw new CobroInvalido('No se pudo generar el número de caja del cobro.');
            }

            $this->db()->table('invo1')->where('invnr', $invnr)->update(['cajanr' => $cajanr]);

            $principal = collect(['efectivo' => $efectivoNecesario, 'transferencia' => $transferencia, 'tarjeta' => $tarjeta])->sortDesc()->keys()->first();

            return [
                'cajanr'        => $cajanr,
                'total'         => $total,
                'efectivo'      => $efectivoNecesario,
                'transferencia' => $transferencia,
                'tarjeta'       => $tarjeta,
                'recibido'      => $efectivoNecesario > 0 ? $efectivo : 0.0,
                'vuelto'        => $efectivoNecesario > 0 ? max(0.0, round($efectivo - $efectivoNecesario, 2)) : 0.0,
                'principal'     => $principal,
            ];
        });
    }

    private function momento(mixed $fecha, mixed $hora): ?Carbon
    {
        try {
            return $fecha ? Carbon::parse(substr((string) $fecha, 0, 10) . ' ' . ($hora ?: '00:00:00')) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function hace(Carbon $momento, Carbon $ahora): string
    {
        $min = (int) max(0, $momento->diffInMinutes($ahora, false));

        return match (true) {
            $min < 1    => 'ahora mismo',
            $min < 60   => "hace {$min} min",
            $min < 1440 => 'hace ' . intdiv($min, 60) . ' h',
            default     => 'hace ' . intdiv($min, 1440) . ' d',
        };
    }

    private function texto(mixed $v): string
    {
        return trim(mb_convert_encoding((string) $v, 'UTF-8', 'UTF-8'));
    }
}
