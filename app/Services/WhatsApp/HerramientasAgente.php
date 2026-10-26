<?php

namespace App\Services\WhatsApp;

use App\Models\WaAgenteConfig;
use App\Models\WaConversacion;
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

    public function __construct(
        private CatalogoService $catalogo,
        private WaAgenteConfig $config,
        private WaConversacion $conversacion,
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
                'name'        => 'derivar_a_humano',
                'description' => 'Pasa la conversación a un vendedor y pausa al agente. Usala cuando el cliente quiera comprar o pedir una cotización, pida hablar con una persona, tenga un reclamo, envíe un comprobante de pago, o cuando no puedas resolver su consulta con las herramientas.',
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
        try {
            $resultado = match ($nombre) {
                'buscar_productos' => $this->buscar($input),
                'detalle_producto' => $this->detalle($input),
                'enviar_fotos'     => $this->fotos($input),
                'info_negocio'     => $this->infoNegocio(),
                'derivar_a_humano' => $this->derivar($input),
                default            => throw new \InvalidArgumentException("Herramienta desconocida: {$nombre}"),
            };

            return ['content' => json_encode($resultado, JSON_UNESCAPED_UNICODE), 'is_error' => false];
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

        return [
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
