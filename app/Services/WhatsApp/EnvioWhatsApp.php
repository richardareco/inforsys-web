<?php

namespace App\Services\WhatsApp;

use App\Models\WaConversacion;
use App\Models\WaLinea;
use App\Models\WaMensaje;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Envía por Kapso y deja registrado cada mensaje saliente de la conversación. */
class EnvioWhatsApp
{
    public function __construct(private KapsoClient $kapso) {}

    public function texto(WaConversacion $conversacion, WaLinea $linea, string $telefono, string $texto, string $autor = 'agente'): void
    {
        $this->registrar($conversacion, 'text', $texto, null, $autor,
            fn () => $this->kapso->sendText($linea->phone_number_id, $telefono, $texto));
    }

    public function imagen(WaConversacion $conversacion, WaLinea $linea, string $telefono, string $url, string $caption, string $autor = 'agente'): void
    {
        $this->registrar($conversacion, 'image', $caption, $url, $autor,
            fn () => $this->kapso->sendImage($linea->phone_number_id, $telefono, $url, $caption));
    }

    public function documento(WaConversacion $conversacion, WaLinea $linea, string $telefono, string $url, string $filename, string $caption, string $autor = 'agente'): void
    {
        $this->registrar($conversacion, 'document', $caption, $url, $autor,
            fn () => $this->kapso->sendDocument($linea->phone_number_id, $telefono, $url, $filename, $caption));
    }

    private function registrar(WaConversacion $conversacion, string $tipo, string $texto, ?string $mediaUrl, string $autor, callable $envio): void
    {
        $msg = WaMensaje::create([
            'conversacion_id' => $conversacion->id,
            'direccion'       => 'out',
            'autor'           => $autor,
            'tipo'            => $tipo,
            'texto'           => $texto,
            'media_url'       => $mediaUrl,
            'estado'          => 'enviado',
        ]);

        try {
            $id = $envio();
        } catch (Throwable $e) {
            Log::warning('WhatsApp: no se pudo enviar el mensaje: ' . $e->getMessage());
            WaMensaje::whereKey($msg->id)->update(['estado' => 'error', 'error' => mb_substr($e->getMessage(), 0, 500)]);

            return;
        }

        // El mensaje ya salió: guardar su id es opcional y no debe tumbar el flujo
        try {
            if ($id) {
                WaMensaje::whereKey($msg->id)->update(['kapso_message_id' => $id]);
            }
        } catch (Throwable $e) {
            Log::notice('WhatsApp: no se pudo guardar el id del mensaje enviado: ' . $e->getMessage());
        }
    }
}
