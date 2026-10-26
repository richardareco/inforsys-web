<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Envío de mensajes de WhatsApp a través del proxy de Meta de Kapso.
 * POST {base}/meta/whatsapp/{version}/{phone_number_id}/messages  (header X-API-Key)
 */
class KapsoClient
{
    public function sendText(string $phoneNumberId, string $to, string $body): ?string
    {
        return $this->send($phoneNumberId, [
            'to'   => $to,
            'type' => 'text',
            'text' => ['body' => $body, 'preview_url' => false],
        ]);
    }

    public function sendImage(string $phoneNumberId, string $to, string $link, ?string $caption = null): ?string
    {
        return $this->send($phoneNumberId, [
            'to'    => $to,
            'type'  => 'image',
            'image' => array_filter(['link' => $link, 'caption' => $caption]),
        ]);
    }

    public function sendDocument(string $phoneNumberId, string $to, string $link, string $filename, ?string $caption = null): ?string
    {
        return $this->send($phoneNumberId, [
            'to'       => $to,
            'type'     => 'document',
            'document' => array_filter(['link' => $link, 'filename' => $filename, 'caption' => $caption]),
        ]);
    }

    private function send(string $phoneNumberId, array $payload): ?string
    {
        $key = config('kapso.api_key');
        if (!$key) {
            throw new RuntimeException('Falta KAPSO_API_KEY en el .env');
        }

        $url = rtrim(config('kapso.api_base_url'), '/')
            . '/meta/whatsapp/' . config('kapso.graph_version')
            . '/' . $phoneNumberId . '/messages';

        $response = Http::withHeaders(['X-API-Key' => $key])
            ->acceptJson()
            ->timeout(15)
            ->post($url, ['messaging_product' => 'whatsapp'] + $payload);

        if (!$response->successful()) {
            throw new RuntimeException('Kapso respondió ' . $response->status() . ': ' . mb_substr($response->body(), 0, 300));
        }

        return $response->json('messages.0.id');
    }
}
