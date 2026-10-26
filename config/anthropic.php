<?php

return [
    'api_key'    => env('ANTHROPIC_API_KEY'),
    'model'      => env('ANTHROPIC_MODEL', 'claude-sonnet-5-5'),

    // Profundidad de razonamiento: low | medium | high
    'effort'     => env('ANTHROPIC_EFFORT', 'medium'),

    // Tope de tokens por respuesta del modelo y de vueltas de herramientas por mensaje
    'max_tokens' => (int) env('ANTHROPIC_MAX_TOKENS', 1024),
    'max_vueltas' => (int) env('ANTHROPIC_MAX_VUELTAS', 6),

    // Mensajes previos de la conversación que se envían al modelo
    'historial'  => (int) env('ANTHROPIC_HISTORIAL', 20),

    // Si el cliente vuelve a escribir tras tantas horas de silencio, el agente arranca la conversación de cero
    'horas_para_reiniciar' => (int) env('ANTHROPIC_HORAS_REINICIO', 6),

    // Límite de respuestas del agente por conversación y hora (control de gasto)
    'max_respuestas_hora' => (int) env('ANTHROPIC_MAX_RESPUESTAS_HORA', 40),
];
