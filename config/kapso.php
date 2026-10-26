<?php

return [
    // Host de la API de Kapso (sin /platform/v1 ni /meta)
    'api_base_url'   => env('KAPSO_API_BASE_URL', 'https://api.kapso.ai'),
    'api_key'        => env('KAPSO_API_KEY'),
    'graph_version'  => env('KAPSO_GRAPH_VERSION', 'v24.0'),

    // Secreto con el que Kapso firma los webhooks (HMAC-SHA256 del cuerpo)
    'webhook_secret' => env('KAPSO_WEBHOOK_SECRET'),

    // Vigencia de los enlaces firmados de fotos que se envían por WhatsApp
    'foto_url_minutos' => env('KAPSO_FOTO_URL_MINUTOS', 60 * 24 * 7),
];
