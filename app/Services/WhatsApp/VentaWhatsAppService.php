<?php

namespace App\Services\WhatsApp;

use App\Models\Empresa;
use App\Models\WaAgenteConfig;
use App\Models\WaConversacion;
use App\Models\WaCotizacion;
use App\Models\WaMensaje;
use App\Models\WaPedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

/**
 * Cotizaciones y pedidos del agente. Todo vive en tablas de Laravel; no se escribe en Delphi.
 * Los precios (items.precio3) y los totales los calcula siempre este servicio, nunca el modelo.
 */
class VentaWhatsAppService
{
    private const MAX_LINEAS = 20;
    private const MAX_CANTIDAD = 10000;

    public function __construct(
        private Empresa $empresa,
        private WaAgenteConfig $config,
        private CatalogoService $catalogo,
    ) {}

    /** @param array<int, array{codigo?: string, cantidad?: mixed}> $lineas */
    public function crearCotizacion(WaConversacion $conv, array $lineas): WaCotizacion
    {
        $validadas = $this->validarLineas($lineas);

        return DB::transaction(function () use ($conv, $validadas) {
            $cot = WaCotizacion::create([
                'empresa_id'      => $this->empresa->id,
                'conversacion_id' => $conv->id,
                'contacto_id'     => $conv->contacto_id,
                'estado'          => 'enviada',
                'total'           => array_sum(array_column($validadas, 'subtotal')),
            ]);
            $cot->items()->createMany($validadas);

            return $cot->load('items');
        });
    }

    /**
     * Arma el resumen del pedido (estado borrador). No es definitivo: el cliente debe confirmar
     * con un mensaje posterior y recién ahí se llama a confirmarPedido().
     */
    public function prepararPedido(WaConversacion $conv, int $cotizacionId, array $datos): WaPedido
    {
        $cot = WaCotizacion::with('items')->where('empresa_id', $this->empresa->id)
            ->where('conversacion_id', $conv->id)->find($cotizacionId);

        if (!$cot) {
            throw new ReglaDeNegocio('No encuentro esa cotización en esta conversación.');
        }

        $existente = WaPedido::where('cotizacion_id', $cot->id)->first();
        if ($existente && $existente->estado !== 'borrador') {
            return $existente; // ya fue confirmado: no se duplica
        }

        $this->validarVigente($cot);

        $entrega = $datos['entrega'] ?? null;
        if (!in_array($entrega, ['retiro', 'envio'], true)) {
            throw new ReglaDeNegocio('Falta definir si es retiro en el local o envío.');
        }
        $direccion = trim((string) ($datos['direccion_entrega'] ?? ''));
        if ($entrega === 'envio' && $direccion === '') {
            throw new ReglaDeNegocio('Para un envío necesito la dirección de entrega.');
        }

        $pago = $datos['forma_pago'] ?? null;
        $permitidos = array_values(array_filter([
            $this->config->pago_efectivo ? 'efectivo' : null,
            $this->config->pago_transferencia ? 'transferencia' : null,
        ]));
        if (!in_array($pago, $permitidos, true)) {
            throw new ReglaDeNegocio('La forma de pago debe ser una de: ' . implode(', ', $permitidos) . '.');
        }

        $factura = (bool) ($datos['factura'] ?? false);
        $ruc     = trim((string) ($datos['ruc'] ?? ''));
        if ($factura && $ruc === '') {
            throw new ReglaDeNegocio('Para emitir factura necesito el RUC o cédula del cliente.');
        }

        $nombre = trim((string) ($datos['nombre'] ?? '')) ?: trim((string) $conv->contacto->nombre);
        if ($nombre === '') {
            throw new ReglaDeNegocio('Necesito el nombre del cliente para registrar el pedido.');
        }

        $ultimoCliente = (int) WaMensaje::where('conversacion_id', $conv->id)->where('direccion', 'in')->max('id');

        return DB::transaction(function () use ($conv, $cot, $existente, $entrega, $direccion, $pago, $factura, $ruc, $nombre, $datos, $ultimoCliente) {
            $pedido = $existente ?? new WaPedido(['empresa_id' => $this->empresa->id, 'cotizacion_id' => $cot->id]);
            $pedido->fill([
                'conversacion_id'      => $conv->id,
                'contacto_id'          => $conv->contacto_id,
                'estado'               => 'borrador',
                'nombre_cliente'       => $nombre,
                'telefono'             => $conv->contacto->telefono,
                'entrega_tipo'         => $entrega,
                'direccion_entrega'    => $entrega === 'envio' ? $direccion : null,
                'forma_pago'           => $pago,
                'requiere_factura'     => $factura,
                'ruc'                  => $factura ? $ruc : null,
                'observacion'          => mb_substr(trim((string) ($datos['nota'] ?? '')), 0, 500) ?: null,
                'total'                => $cot->total,
                'preparado_mensaje_id' => $ultimoCliente,
            ])->save();

            $pedido->items()->delete();
            $pedido->items()->createMany($cot->items->map(fn ($i) => [
                'item' => $i->item, 'descr' => $i->descr, 'cantidad' => $i->cantidad,
                'precio' => $i->precio, 'subtotal' => $i->subtotal,
            ])->all());

            return $pedido->load('items');
        });
    }

    /** Confirma el pedido: exige que el cliente haya escrito DESPUÉS de ver el resumen. */
    public function confirmarPedido(WaConversacion $conv, int $pedidoId): WaPedido
    {
        $pedido = WaPedido::with('items')->where('empresa_id', $this->empresa->id)
            ->where('conversacion_id', $conv->id)->find($pedidoId);

        if (!$pedido) {
            throw new ReglaDeNegocio('No encuentro ese pedido en esta conversación.');
        }

        if ($pedido->estado !== 'borrador') {
            return $pedido; // ya registrado (mensaje repetido): no se duplica
        }

        $respondio = WaMensaje::where('conversacion_id', $conv->id)->where('direccion', 'in')
            ->where('id', '>', (int) $pedido->preparado_mensaje_id)->exists();

        if (!$respondio) {
            throw new ReglaDeNegocio('El cliente todavía no confirmó el resumen. Mostráselo, pedile que confirme y esperá su respuesta antes de confirmar el pedido.');
        }

        $this->validarVigente($pedido->cotizacion()->with('items')->first());

        return DB::transaction(function () use ($pedido, $conv) {
            $pedido->update(['estado' => 'pendiente', 'confirmado_at' => now()]);
            $pedido->cotizacion?->update(['estado' => 'aceptada']);

            // Los datos del cliente quedan guardados para próximas compras
            $conv->contacto->update(array_filter([
                'nombre'    => $pedido->nombre_cliente,
                'direccion' => $pedido->direccion_entrega,
                'ruc'       => $pedido->ruc,
            ]));

            return $pedido->fresh('items');
        });
    }

    public function urlPdfCotizacion(WaCotizacion $cot): string
    {
        return URL::temporarySignedRoute('wa.cotizacion.pdf', now()->addMinutes((int) config('kapso.foto_url_minutos')), ['cotizacion' => $cot->id]);
    }

    /** Vuelve a comprobar stock y precio contra el sistema (puede haber cambiado desde la cotización). */
    private function validarVigente(WaCotizacion $cot): void
    {
        $actual = $this->validarLineas($cot->items->map(fn ($i) => ['codigo' => $i->item, 'cantidad' => $i->cantidad])->all());

        foreach ($actual as $i => $linea) {
            $original = $cot->items[$i] ?? null;
            if ($original && abs($original->precio - $linea['precio']) > 0.5) {
                throw new ReglaDeNegocio("El precio de {$linea['descr']} cambió desde la cotización. Armá una cotización nueva con los precios actuales.");
            }
        }
    }

    /**
     * @param array<int, array{codigo?: string, cantidad?: mixed}> $lineas
     * @return array<int, array{item: string, descr: string, cantidad: float, precio: float, subtotal: float}>
     */
    private function validarLineas(array $lineas): array
    {
        if (!$lineas) {
            throw new ReglaDeNegocio('La cotización no tiene productos.');
        }
        if (count($lineas) > self::MAX_LINEAS) {
            throw new ReglaDeNegocio('Como máximo se cotizan ' . self::MAX_LINEAS . ' productos por vez.');
        }

        // Si repiten un código, se suman las cantidades
        $porCodigo = [];
        foreach ($lineas as $l) {
            $codigo   = trim((string) ($l['codigo'] ?? ''));
            $cantidad = round((float) ($l['cantidad'] ?? 0), 3);
            if ($codigo === '' || $cantidad <= 0 || $cantidad > self::MAX_CANTIDAD) {
                throw new ReglaDeNegocio('Cada producto necesita un código válido y una cantidad mayor a cero.');
            }
            $porCodigo[$codigo] = ($porCodigo[$codigo] ?? 0) + $cantidad;
        }

        $resultado = [];
        foreach ($porCodigo as $codigo => $cantidad) {
            $p = $this->catalogo->detalle((string) $codigo);

            if (!$p) {
                throw new ReglaDeNegocio("El producto {$codigo} no está disponible en el catálogo.");
            }
            if ($p['precio'] <= 0) {
                throw new ReglaDeNegocio("{$p['nombre']} no tiene precio publicado: hay que consultarlo con un vendedor.");
            }
            if (!($this->empresa->permitir_venta_sin_stock ?? false) && $cantidad > $p['stock']) {
                $disp = rtrim(rtrim(number_format($p['stock'], 3, '.', ''), '0'), '.') ?: '0';
                throw new ReglaDeNegocio("No hay stock suficiente de {$p['nombre']}: disponible {$disp}, pedido {$cantidad}.");
            }

            $precio = round($p['precio']);
            $resultado[] = [
                'item'     => (string) $p['codigo'],
                'descr'    => $p['nombre'],
                'cantidad' => $cantidad,
                'precio'   => $precio,
                'subtotal' => round($precio * $cantidad),
            ];
        }

        return $resultado;
    }

    public static function formato(WaCotizacion|WaPedido $doc): array
    {
        return [
            'numero'    => $doc->numero,
            'id'        => $doc->id,
            'total_gs'  => (int) $doc->total,
            'total'     => 'Gs. ' . number_format($doc->total, 0, ',', '.'),
            'productos' => $doc->items->map(fn ($i) => [
                'codigo'   => $i->item,
                'nombre'   => $i->descr,
                'cantidad' => (float) $i->cantidad,
                'precio'   => 'Gs. ' . number_format($i->precio, 0, ',', '.'),
                'subtotal' => 'Gs. ' . number_format($i->subtotal, 0, ',', '.'),
            ])->all(),
        ];
    }
}
