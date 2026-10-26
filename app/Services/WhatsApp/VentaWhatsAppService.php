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

    private const VIGENCIA_LISTA_HORAS = 48;

    /** @return array<string, float> código => cantidad de los productos que el cliente va pidiendo */
    private function lista(WaConversacion $conv): array
    {
        if (!$conv->carrito || !$conv->carrito_at || $conv->carrito_at->lt(now()->subHours(self::VIGENCIA_LISTA_HORAS))) {
            return [];
        }

        return array_map('floatval', $conv->carrito);
    }

    private function guardarLista(WaConversacion $conv, array $lista): void
    {
        $conv->update(['carrito' => $lista ?: null, 'carrito_at' => $lista ? now() : null]);
    }

    private function lineasDeLista(array $lista): array
    {
        $out = [];
        foreach ($lista as $codigo => $cantidad) {
            $out[] = ['codigo' => (string) $codigo, 'cantidad' => $cantidad];
        }

        return $out;
    }

    /** Detalle y total de lo que lleva pedido el cliente hasta ahora (precios y stock de este momento). */
    public function resumenLista(WaConversacion $conv): array
    {
        $lista = $this->lista($conv);
        if (!$lista) {
            return ['productos' => [], 'total_gs' => 0, 'total' => 'Gs. 0', 'cantidad_de_productos' => 0];
        }

        $lineas = $this->validarLineas($this->lineasDeLista($lista));

        return [
            'cantidad_de_productos' => count($lineas),
            'productos' => array_map(fn ($l) => [
                'codigo'   => $l['item'],
                'nombre'   => $l['descr'],
                'cantidad' => $l['cantidad'],
                'precio'   => 'Gs. ' . number_format($l['precio'], 0, ',', '.'),
                'subtotal' => 'Gs. ' . number_format($l['subtotal'], 0, ',', '.'),
            ], $lineas),
            'total_gs' => (int) array_sum(array_column($lineas, 'subtotal')),
            'total'    => 'Gs. ' . number_format(array_sum(array_column($lineas, 'subtotal')), 0, ',', '.'),
        ];
    }

    /** Suma (o reemplaza) la cantidad de un producto en la lista, validando stock y precio. */
    public function agregarALista(WaConversacion $conv, string $codigo, float $cantidad, bool $reemplazar = false): array
    {
        $codigo = trim($codigo);
        $lista  = $this->lista($conv);
        $nueva  = $lista;
        $nueva[$codigo] = $reemplazar ? $cantidad : ($lista[$codigo] ?? 0) + $cantidad;

        $this->validarLineas($this->lineasDeLista($nueva)); // lanza ReglaDeNegocio si no corresponde
        $this->guardarLista($conv, $nueva);

        return $this->resumenLista($conv->fresh());
    }

    public function quitarDeLista(WaConversacion $conv, string $codigo): array
    {
        $lista = $this->lista($conv);
        if (!array_key_exists(trim($codigo), $lista)) {
            throw new ReglaDeNegocio('Ese producto no está en la lista del cliente.');
        }
        unset($lista[trim($codigo)]);
        $this->guardarLista($conv, $lista);

        return $this->resumenLista($conv->fresh());
    }

    /** Crea la cotización con todo lo que el cliente fue pidiendo y vacía la lista. */
    public function generarCotizacion(WaConversacion $conv): WaCotizacion
    {
        $lista = $this->lista($conv);
        if (!$lista) {
            throw new ReglaDeNegocio('Todavía no hay productos en la lista del cliente. Agregá los que pida con agregar_producto.');
        }

        $cot = $this->crearCotizacion($conv, $this->lineasDeLista($lista));
        $this->guardarLista($conv, []);

        return $cot;
    }

    /**
     * Arma el resumen del pedido (estado borrador). No es definitivo: el cliente debe confirmar
     * con un mensaje posterior y recién ahí se llama a confirmarPedido().
     */
    public function prepararPedido(WaConversacion $conv, ?int $cotizacionId, array $datos): WaPedido
    {
        $cot = $this->cotizacionParaPedido($conv, $cotizacionId);

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

    /**
     * Cotización que respalda el pedido: la indicada, o los productos que el cliente fue pidiendo
     * (se arma una cotización interna, sin enviar PDF), o la última cotización generada.
     */
    private function cotizacionParaPedido(WaConversacion $conv, ?int $cotizacionId): WaCotizacion
    {
        if ($cotizacionId) {
            $cot = WaCotizacion::with('items')->where('empresa_id', $this->empresa->id)
                ->where('conversacion_id', $conv->id)->find($cotizacionId);

            if ($cot) {
                return $cot;
            }
        }

        // Pedidos todavía sin confirmar: se descartan para no dejar restos si el cliente cambió algo
        $descartar = fn () => WaPedido::where('conversacion_id', $conv->id)->where('estado', 'borrador')->get()->each(function (WaPedido $p) {
            $p->cotizacion?->update(['estado' => 'cancelada']);
            $p->delete();
        });

        if ($this->lista($conv)) {
            $descartar();

            return $this->crearCotizacion($conv, $this->lineasDeLista($this->lista($conv)));
        }

        $ultima = WaCotizacion::with('items')->where('empresa_id', $this->empresa->id)->where('conversacion_id', $conv->id)
            ->where('estado', 'enviada')->where('created_at', '>=', now()->subHours(self::VIGENCIA_LISTA_HORAS))
            ->latest('id')->first();

        if ($ultima) {
            return $ultima;
        }

        throw new ReglaDeNegocio('Todavía no hay productos para el pedido. Agregá lo que pida el cliente con agregar_producto.');
    }

    /** Confirma el pedido: exige que el cliente haya escrito DESPUÉS de ver el resumen. */
    public function confirmarPedido(WaConversacion $conv, ?int $pedidoId = null): WaPedido
    {
        $consulta = WaPedido::with('items')->where('empresa_id', $this->empresa->id)->where('conversacion_id', $conv->id);

        // Sin número: el sistema usa el pedido que se estaba preparando en esta conversación
        $pedido = $pedidoId
            ? (clone $consulta)->find($pedidoId)
            : ((clone $consulta)->where('estado', 'borrador')->latest('id')->first()
                ?? (clone $consulta)->where('confirmado_at', '>=', now()->subHours(2))->latest('id')->first());

        if (!$pedido) {
            throw new ReglaDeNegocio('No hay un pedido en preparación en esta conversación. Armalo primero con preparar_pedido.');
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
            $this->guardarLista($conv, []); // el pedido ya incluye lo que el cliente fue pidiendo

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

            $precio = $this->catalogo->precioPara($p, $cantidad);
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
