<?php

namespace App\Services\WhatsApp;

use App\Models\WaAgenteConfig;
use App\Models\WaContacto;
use App\Models\WaConversacion;
use App\Models\WaEvento;
use App\Models\WaLinea;
use App\Models\WaMensaje;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Procesa un whatsapp.message.received: registra la conversación y, si el agente está
 * activo y la conversación no fue tomada por un vendedor, responde.
 *
 * Etapa 1: respuesta "básica" (búsqueda de productos sin IA). En la etapa del agente
 * esta respuesta se reemplaza por el modelo con herramientas.
 */
class ProcesadorEntrante
{
    public function __construct(private KapsoClient $kapso) {}

    public function procesar(array $payload, WaEvento $evento): void
    {
        try {
            $resultado = $this->manejar($payload);
            $evento->update(['estado' => $resultado]);
        } catch (Throwable $e) {
            Log::error('WhatsApp: error procesando evento ' . $evento->id . ': ' . $e->getMessage());
            $evento->update(['estado' => 'error', 'error' => mb_substr($e->getMessage(), 0, 500)]);
        }
    }

    /** @return string estado final del evento: procesado | ignorado */
    private function manejar(array $payload): string
    {
        $phoneNumberId = (string) ($payload['phone_number_id'] ?? '');
        $mensaje       = $payload['message'] ?? [];

        if (($mensaje['kapso']['direction'] ?? 'inbound') !== 'inbound') {
            return 'ignorado';
        }

        $linea = WaLinea::with('empresa')->where('phone_number_id', $phoneNumberId)->where('activo', true)->first();
        if (!$linea || !$linea->empresa) {
            return 'ignorado';
        }

        $empresa  = $linea->empresa;
        $telefono = preg_replace('/\D+/', '', (string) ($payload['conversation']['phone_number'] ?? ''));
        if ($telefono === '') {
            return 'ignorado';
        }

        $contacto = WaContacto::firstOrCreate(
            ['empresa_id' => $empresa->id, 'telefono' => $telefono],
            ['nombre' => $payload['conversation']['kapso']['contact_name'] ?? null]
        );

        $conversacion = WaConversacion::firstOrCreate(
            ['linea_id' => $linea->id, 'contacto_id' => $contacto->id],
            ['empresa_id' => $empresa->id, 'estado' => 'agente']
        );
        $conversacion->update([
            'kapso_conversation_id' => $payload['conversation']['id'] ?? $conversacion->kapso_conversation_id,
            'ultimo_mensaje_at'     => now(),
        ]);

        $tipo  = (string) ($mensaje['type'] ?? 'text');
        $texto = $tipo === 'text'
            ? (string) ($mensaje['text']['body'] ?? '')
            : (string) ($mensaje['kapso']['content'] ?? '');

        try {
            WaMensaje::create([
                'conversacion_id'  => $conversacion->id,
                'direccion'        => 'in',
                'autor'            => 'cliente',
                'tipo'             => $tipo,
                'texto'            => $texto,
                'media_url'        => $mensaje['kapso']['media_url'] ?? null,
                'kapso_message_id' => $mensaje['id'] ?? null,
                'estado'           => 'recibido',
            ]);
        } catch (QueryException $e) {
            // El mismo mensaje ya estaba registrado (reintento de Kapso)
            return 'ignorado';
        }

        $config = WaAgenteConfig::where('empresa_id', $empresa->id)->first();
        if (!$config || !$config->activo || $conversacion->estado !== 'agente') {
            return 'procesado';
        }

        $this->responder($conversacion, $linea, $telefono, $empresa, $config, $tipo, $texto);

        return 'procesado';
    }

    private function responder($conversacion, WaLinea $linea, string $telefono, $empresa, WaAgenteConfig $config, string $tipo, string $texto): void
    {
        if ($tipo !== 'text' || trim($texto) === '') {
            $this->enviarTexto($conversacion, $linea, $telefono, 'Por ahora solo puedo leer mensajes de texto. Escribime qué producto buscás.');
            return;
        }

        if (preg_match('/^\s*(hola|holaa+|buenas|buen(os)? d[ií]as?|buenas (tardes|noches)|hey|ola)\b[\s!.,¡]*$/iu', $texto)) {
            $bienvenida = $config->mensaje_bienvenida
                ?: "¡Hola! Soy el asistente de {$empresa->nombre}. Escribime el producto que buscás y te paso precio y disponibilidad.";
            $this->enviarTexto($conversacion, $linea, $telefono, $bienvenida);
            return;
        }

        $catalogo  = new CatalogoService($empresa, $config);
        $productos = $catalogo->buscar($texto, (int) $config->max_productos ?: 5);

        if (!$productos) {
            $this->enviarTexto($conversacion, $linea, $telefono, 'No encontré productos con esa búsqueda. ¿Podés escribirlo de otra forma o decirme el código?');
            return;
        }

        $lineas = ['Encontré esto:', ''];
        foreach ($productos as $i => $p) {
            $precio = $p['precio'] > 0 ? 'Gs. ' . number_format($p['precio'], 0, ',', '.') : 'Precio a consultar';
            $stock  = $p['stock'] > 0 ? 'Disponible (' . rtrim(rtrim(number_format($p['stock'], 3, ',', '.'), '0'), ',') . ' u.)' : 'Sin stock';
            $lineas[] = ($i + 1) . ') ' . $p['nombre'] . "\n   " . $precio . ' · ' . $stock;
        }
        $this->enviarTexto($conversacion, $linea, $telefono, implode("\n", $lineas));

        $fotos = 0;
        foreach ($productos as $p) {
            if ($fotos >= (int) $config->max_fotos) {
                break;
            }
            if (!$p['tiene_foto']) {
                continue;
            }
            $caption = $p['nombre'] . ($p['precio'] > 0 ? ' - Gs. ' . number_format($p['precio'], 0, ',', '.') : '');
            $this->enviarImagen($conversacion, $linea, $telefono, $catalogo->fotoUrl($p['codigo']), $caption);
            $fotos++;
        }
    }

    private function enviarTexto($conversacion, WaLinea $linea, string $telefono, string $texto): void
    {
        $this->registrarSalida($conversacion, 'text', $texto, null, fn () => $this->kapso->sendText($linea->phone_number_id, $telefono, $texto));
    }

    private function enviarImagen($conversacion, WaLinea $linea, string $telefono, string $url, string $caption): void
    {
        $this->registrarSalida($conversacion, 'image', $caption, $url, fn () => $this->kapso->sendImage($linea->phone_number_id, $telefono, $url, $caption));
    }

    private function registrarSalida($conversacion, string $tipo, string $texto, ?string $mediaUrl, callable $envio): void
    {
        $msg = WaMensaje::create([
            'conversacion_id' => $conversacion->id,
            'direccion'       => 'out',
            'autor'           => 'agente',
            'tipo'            => $tipo,
            'texto'           => $texto,
            'media_url'       => $mediaUrl,
            'estado'          => 'enviado',
        ]);

        try {
            $id = $envio();
            if ($id) {
                $msg->update(['kapso_message_id' => $id]);
            }
        } catch (Throwable $e) {
            Log::warning('WhatsApp: no se pudo enviar el mensaje: ' . $e->getMessage());
            $msg->update(['estado' => 'error', 'error' => mb_substr($e->getMessage(), 0, 500)]);
        }
    }
}
