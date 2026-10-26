<?php

namespace App\Services\WhatsApp;

use App\Models\WaAgenteConfig;
use App\Models\WaConversacion;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Herramientas que el agente puede usar. Todas son de solo lectura sobre la base Delphi,
 * salvo derivar_a_humano, que cambia el estado de la conversación en Laravel.
 * El agente nunca recibe costos ni ejecuta SQL propio.
 */
class HerramientasAgente
{
    /** @var array<int, string> códigos cuyas fotos se enviarán después del texto */
    public array $fotosPendientes = [];

    public bool $derivado = false;

    /** @var array<int, array{url: string, filename: string, caption: string}> PDFs a enviar después del texto */
    public array $documentosPendientes = [];

    public function __construct(
        private CatalogoService $catalogo,
        private WaAgenteConfig $config,
        private WaConversacion $conversacion,
        private VentaWhatsAppService $ventas,
    ) {}

    public static function definiciones(): array
    {
        return [
            [
                'name'        => 'buscar_productos',
                'description' => 'Busca productos por nombre, marca, código interno o código de barras. Devuelve nombre, precio en guaraníes, stock y si tiene foto. Usala siempre antes de dar precios o disponibilidad.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'consulta' => ['type' => 'string', 'description' => 'Texto a buscar, por ejemplo "jean azul" o "carbon vegetal".'],
                        'limite'   => ['type' => 'integer', 'description' => 'Máximo de productos (1 a 8). Opcional.'],
                    ],
                    'required' => ['consulta'],
                ],
            ],
            [
                'name'        => 'detalle_producto',
                'description' => 'Devuelve los datos actuales de un producto a partir de su código exacto (el campo "codigo" que devolvió buscar_productos).',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'codigo' => ['type' => 'string'],
                    ],
                    'required' => ['codigo'],
                ],
            ],
            [
                'name'        => 'enviar_fotos',
                'description' => 'Envía al cliente la foto de uno o más productos (solo los que tengan foto). Las fotos se mandan después de tu mensaje de texto. Usala al presentar productos o cuando el cliente pida verlos.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'codigos' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Códigos de producto devueltos por buscar_productos.'],
                    ],
                    'required' => ['codigos'],
                ],
            ],
            [
                'name'        => 'info_negocio',
                'description' => 'Devuelve la información configurada del negocio: horarios, ubicación, entregas y formas de pago. Usala para responder esas preguntas; no inventes datos que no estén acá.',
                'inputSchema' => ['type' => 'object'],
            ],
            [
                'name'        => 'agregar_producto',
                'description' => 'Agrega un producto a la lista de la cotización en armado del cliente (no genera el PDF). Usala cada vez que el cliente pide un producto con su cantidad. El sistema valida stock, aplica el precio que corresponde por cantidad y devuelve la lista completa con el total parcial. Si el producto ya estaba, suma la cantidad (o la reemplaza con reemplazar=true).',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'codigo'     => ['type' => 'string', 'description' => 'Código devuelto por buscar_productos.'],
                        'cantidad'   => ['type' => 'number', 'description' => 'Cantidad pedida (1 si no la aclara).'],
                        'reemplazar' => ['type' => 'boolean', 'description' => 'true para fijar esa cantidad en vez de sumarla.'],
                    ],
                    'required' => ['codigo', 'cantidad'],
                ],
            ],
            [
                'name'        => 'quitar_producto',
                'description' => 'Quita un producto de la lista de la cotización en armado.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => ['codigo' => ['type' => 'string']],
                    'required'   => ['codigo'],
                ],
            ],
            [
                'name'        => 'ver_lista',
                'description' => 'Muestra la lista actual de la cotización en armado, con cantidades, precios y total parcial.',
                'inputSchema' => ['type' => 'object'],
            ],
            [
                'name'        => 'generar_cotizacion',
                'description' => 'Genera la cotización con TODOS los productos de la lista y le envía el PDF al cliente. Usala solo cuando el cliente ya terminó de pedir (le preguntaste si necesita algo más y dijo que no). Vacía la lista. Devuelve el detalle y el total.',
                'inputSchema' => ['type' => 'object'],
            ],
            [
                'name'        => 'preparar_pedido',
                'description' => 'Arma el resumen del pedido con TODOS los productos de la lista del cliente (o con la última cotización si la lista está vacía), más los datos de entrega y pago. No hace falta pasar ningún número. NO registra el pedido todavía: después tenés que mostrarle el resumen completo al cliente y pedirle que confirme. Cuando el cliente responda que sí en un mensaje nuevo, recién ahí usá confirmar_pedido.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'nombre'            => ['type' => 'string', 'description' => 'Nombre completo del cliente.'],
                        'entrega'           => ['type' => 'string', 'enum' => ['retiro', 'envio'], 'description' => 'retiro en el local o envio a domicilio.'],
                        'direccion_entrega' => ['type' => 'string', 'description' => 'Obligatoria si es envio.'],
                        'forma_pago'        => ['type' => 'string', 'enum' => ['efectivo', 'transferencia']],
                        'factura'           => ['type' => 'boolean', 'description' => 'true si el cliente quiere factura.'],
                        'ruc'               => ['type' => 'string', 'description' => 'RUC o cédula, obligatorio si quiere factura.'],
                        'nota'              => ['type' => 'string', 'description' => 'Aclaración del cliente, opcional.'],
                    ],
                    'required' => ['nombre', 'entrega', 'forma_pago'],
                ],
            ],
            [
                'name'        => 'confirmar_pedido',
                'description' => 'Registra definitivamente el pedido que se estaba preparando en esta conversación (no necesita parámetros). Usala SOLO cuando el cliente, en un mensaje posterior al resumen que le mostraste, confirmó de forma explícita (por ejemplo "sí, confirmo"). Si no confirmó, el sistema lo rechaza. Solo el número PED-xxxxxx que devuelve esta herramienta es válido: nunca inventes uno.',
                'inputSchema' => ['type' => 'object'],
            ],
            [
                'name'        => 'derivar_a_humano',
                'description' => 'Pasa la conversación a un vendedor y pausa al agente. Usala cuando el cliente pida hablar con una persona, tenga un reclamo, envíe un comprobante de pago, pida algo que no podés resolver (descuentos, condiciones especiales, productos sin precio) o cuando las herramientas fallen.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'motivo' => ['type' => 'string', 'description' => 'Resumen breve para el vendedor: qué quiere el cliente y qué productos mencionó.'],
                    ],
                    'required' => ['motivo'],
                ],
            ],
        ];
    }

    /** @return array{content: string, is_error: bool} */
    public function ejecutar(string $nombre, array $input): array
    {
        $r = $this->ejecutarSinLog($nombre, $input);
        Log::info('WhatsApp herramienta', ['conversacion' => $this->conversacion->id, 'tool' => $nombre, 'error' => $r['is_error'], 'resultado' => mb_substr($r['content'], 0, 160)]);

        return $r;
    }

    private function ejecutarSinLog(string $nombre, array $input): array
    {
        try {
            $resultado = match ($nombre) {
                'buscar_productos' => $this->buscar($input),
                'detalle_producto' => $this->detalle($input),
                'enviar_fotos'     => $this->fotos($input),
                'info_negocio'     => $this->infoNegocio(),
                'agregar_producto'   => $this->agregar($input),
                'quitar_producto'    => $this->quitar($input),
                'ver_lista'          => $this->ventas->resumenLista($this->conversacion->fresh()),
                'generar_cotizacion' => $this->cotizar(),
                'preparar_pedido'  => $this->prepararPedido($input),
                'confirmar_pedido' => $this->confirmarPedido($input),
                'derivar_a_humano' => $this->derivar($input),
                default            => throw new \InvalidArgumentException("Herramienta desconocida: {$nombre}"),
            };

            return ['content' => json_encode($resultado, JSON_UNESCAPED_UNICODE), 'is_error' => false];
        } catch (ReglaDeNegocio $e) {
            return ['content' => json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE), 'is_error' => true];
        } catch (Throwable $e) {
            report($e);

            return ['content' => json_encode(['error' => 'No pude consultar el sistema en este momento.'], JSON_UNESCAPED_UNICODE), 'is_error' => true];
        }
    }

    private function buscar(array $input): array
    {
        $consulta = trim((string) ($input['consulta'] ?? ''));
        $limite   = max(1, min((int) ($input['limite'] ?? $this->config->max_productos ?: 5), 8));

        if ($consulta === '') {
            return ['error' => 'La consulta está vacía.'];
        }

        $productos = array_map(fn ($p) => $this->publico($p), $this->catalogo->buscar($consulta, $limite));

        return ['cantidad' => count($productos), 'productos' => $productos];
    }

    private function detalle(array $input): array
    {
        $p = $this->catalogo->detalle(trim((string) ($input['codigo'] ?? '')));

        return $p ? $this->publico($p) : ['error' => 'No existe un producto con ese código.'];
    }

    private function fotos(array $input): array
    {
        $enviadas = [];
        $sinFoto  = [];

        foreach (array_slice((array) ($input['codigos'] ?? []), 0, 5) as $codigo) {
            $p = $this->catalogo->detalle((string) $codigo);
            if (!$p || !$p['tiene_foto']) {
                $sinFoto[] = (string) $codigo;
                continue;
            }
            if (!in_array($p['codigo'], $this->fotosPendientes, true)) {
                $this->fotosPendientes[] = $p['codigo'];
            }
            $enviadas[] = $p['codigo'];
        }

        $this->fotosPendientes = array_slice($this->fotosPendientes, 0, max(1, (int) $this->config->max_fotos));

        return ['se_enviaran' => $enviadas, 'sin_foto' => $sinFoto];
    }

    private function infoNegocio(): array
    {
        $c = $this->config;
        $pagos = array_values(array_filter([
            $c->pago_efectivo ? 'Efectivo' : null,
            $c->pago_transferencia ? 'Transferencia' : null,
        ]));

        return [
            'horarios'            => $c->horarios ?: null,
            'ubicacion'           => $c->ubicacion ?: null,
            'entrega'             => $c->entrega ?: null,
            'formas_de_pago'      => $pagos,
            'datos_transferencia' => $c->pago_transferencia ? ($c->datos_transferencia ?: null) : null,
            'nota'                => 'Los datos en null no están configurados: decile al cliente que lo consulte con un vendedor.',
        ];
    }

    private function agregar(array $input): array
    {
        $cantidad = (float) ($input['cantidad'] ?? 1);
        $lista = $this->ventas->agregarALista(
            $this->conversacion, (string) ($input['codigo'] ?? ''), $cantidad > 0 ? $cantidad : 1, (bool) ($input['reemplazar'] ?? false)
        );

        return $lista + ['nota' => 'Todavía NO se generó el PDF. Seguí agregando lo que pida y, cuando termine, preguntale si necesita algo más antes de generar la cotización.'];
    }

    private function quitar(array $input): array
    {
        return $this->ventas->quitarDeLista($this->conversacion, (string) ($input['codigo'] ?? ''));
    }

    private function cotizar(): array
    {
        $cot = $this->ventas->generarCotizacion($this->conversacion);

        $this->documentosPendientes[] = [
            'url'      => $this->ventas->urlPdfCotizacion($cot),
            'filename' => $cot->numero . '.pdf',
            'caption'  => 'Cotización ' . $cot->numero,
        ];

        return VentaWhatsAppService::formato($cot) + [
            'cotizacion_id' => $cot->id,
            'nota' => 'El PDF se enviará al cliente automáticamente después de tu mensaje. Resumile los productos y el total y preguntale si quiere avanzar con el pedido.',
        ];
    }

    private function prepararPedido(array $input): array
    {
        $pedido = $this->ventas->prepararPedido($this->conversacion, ((int) ($input['cotizacion_id'] ?? 0)) ?: null, [
            'nombre'            => $input['nombre'] ?? null,
            'entrega'           => $input['entrega'] ?? null,
            'direccion_entrega' => $input['direccion_entrega'] ?? null,
            'forma_pago'        => $input['forma_pago'] ?? null,
            'factura'           => $input['factura'] ?? false,
            'ruc'               => $input['ruc'] ?? null,
            'nota'              => $input['nota'] ?? null,
        ]);

        $resumen = VentaWhatsAppService::formato($pedido) + [
            'pedido_id' => $pedido->id,
            'estado'    => $pedido->estado,
            'cliente'   => $pedido->nombre_cliente,
            'entrega'   => $pedido->entrega_tipo === 'envio' ? 'Envío a: ' . $pedido->direccion_entrega : 'Retiro en el local',
            'forma_pago' => $pedido->forma_pago,
            'factura'   => $pedido->requiere_factura ? 'Sí, a nombre de RUC/CI ' . $pedido->ruc : 'No',
        ];

        if ($pedido->estado === 'borrador') {
            $resumen['instruccion'] = 'Mostrale al cliente este resumen completo (productos, total, entrega, pago, factura) y pedile que confirme. NO llames a confirmar_pedido hasta que el cliente responda con un mensaje nuevo.';
        } else {
            $resumen['instruccion'] = 'Este pedido ya está registrado; no hace falta confirmarlo de nuevo.';
        }

        return $resumen;
    }

    private function confirmarPedido(array $input): array
    {
        $pedido = $this->ventas->confirmarPedido($this->conversacion, ((int) ($input['pedido_id'] ?? 0)) ?: null);

        $datos = [
            'ok'       => true,
            'pedido'   => $pedido->numero,
            'estado'   => 'registrado, pendiente de aprobación de un vendedor',
            'total'    => 'Gs. ' . number_format($pedido->total, 0, ',', '.'),
            'instruccion' => 'Decile al cliente que su pedido quedó registrado con ese número y que un vendedor lo va a confirmar enseguida.',
        ];

        if ($pedido->forma_pago === 'transferencia') {
            $datos['datos_transferencia'] = $this->config->datos_transferencia ?: null;
            $datos['instruccion'] .= ' Pasale los datos de transferencia (si hay) y aclarale que el vendedor verificará el pago; no des el pago por confirmado.';
        }

        return $datos;
    }

    private function derivar(array $input): array
    {
        $this->conversacion->update(['estado' => 'humano']);
        $this->derivado = true;

        return ['ok' => true, 'motivo' => mb_substr((string) ($input['motivo'] ?? ''), 0, 300)];
    }

    /** Solo lo que el cliente puede ver: nada de costos ni precio mayorista. */
    private function publico(array $p): array
    {
        $stock = (float) $p['stock'];
        $may   = $this->catalogo->mayorista($p);

        return ($may ? [
            'precio_mayorista'            => 'Gs. ' . number_format($may['precio'], 0, ',', '.'),
            'mayorista_desde_unidades'    => $may['desde'],
            'condicion_mayorista'         => 'Precio mayorista comprando desde ' . $may['desde'] . ' unidades del mismo producto',
        ] : []) + [
            'codigo'             => $p['codigo'],
            'nombre'             => $p['nombre'],
            'precio_gs'          => $p['precio'] > 0 ? (int) round($p['precio']) : null,
            'precio_formateado'  => $p['precio'] > 0 ? 'Gs. ' . number_format($p['precio'], 0, ',', '.') : null,
            'disponible'         => $stock > 0,
            'stock'              => $stock > 0 ? (float) rtrim(rtrim(number_format($stock, 3, '.', ''), '0'), '.') : 0,
            'tiene_foto'         => $p['tiene_foto'],
        ];
    }
}
