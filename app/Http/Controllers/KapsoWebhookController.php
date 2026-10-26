<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\WaEvento;
use App\Services\WhatsApp\CatalogoService;
use App\Services\WhatsApp\ProcesadorEntrante;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class KapsoWebhookController extends Controller
{
    /**
     * Receptor de webhooks de Kapso.
     * - Verifica X-Webhook-Signature = HMAC-SHA256(secreto, cuerpo crudo) en hexadecimal.
     * - Evita duplicados con X-Idempotency-Key.
     * - Responde 200 de inmediato y procesa después de enviar la respuesta (Kapso exige < 10 s).
     */
    public function recibir(Request $request, ProcesadorEntrante $procesador)
    {
        $secreto = config('kapso.webhook_secret');
        if (!$secreto) {
            return response()->json(['error' => 'webhook no configurado'], 503);
        }

        $crudo = $request->getContent();
        $firma = (string) $request->header('X-Webhook-Signature', '');

        if ($firma === '' || !hash_equals(hash_hmac('sha256', $crudo, $secreto), strtolower($firma))) {
            return response()->json(['error' => 'firma inválida'], 401);
        }

        $payload = json_decode($crudo, true);
        if (!is_array($payload)) {
            return response()->json(['error' => 'cuerpo inválido'], 400);
        }

        $clave  = (string) ($request->header('X-Idempotency-Key') ?: hash('sha256', $crudo));
        $nombre = (string) $request->header('X-Webhook-Event', '');

        try {
            $evento = WaEvento::create([
                'idempotency_key' => mb_substr($clave, 0, 120),
                'evento'          => $nombre ?: null,
                'phone_number_id' => $payload['phone_number_id'] ?? null,
                'estado'          => 'recibido',
                'payload'         => $payload,
            ]);
        } catch (QueryException $e) {
            return response()->json(['ok' => true, 'duplicado' => true]);
        }

        if ($nombre !== 'whatsapp.message.received') {
            $evento->update(['estado' => 'ignorado']);

            return response()->json(['ok' => true]);
        }

        dispatch(fn () => $procesador->procesar($payload, $evento))->afterResponse();

        return response()->json(['ok' => true]);
    }

    /** Foto de producto con enlace firmado (para que WhatsApp pueda descargarla sin sesión). */
    public function foto(Request $request, int $empresa)
    {
        $item = (string) $request->query('item', '');
        $emp  = Empresa::find($empresa);
        abort_if($item === '' || !$emp || !$emp->db_name, 404);

        $binario = (new CatalogoService($emp))->fotoBinaria($item);
        abort_if($binario === null, 404);

        $mime  = 'image/jpeg';
        $inicio = substr($binario, 0, 4);
        if (str_starts_with($inicio, "\x89PNG")) {
            $mime = 'image/png';
        }

        return response($binario)
            ->header('Content-Type', $mime)
            ->header('Cache-Control', 'private, max-age=3600');
    }
}
