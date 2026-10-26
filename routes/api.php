<?php

use App\Http\Controllers\KapsoWebhookController;
use Illuminate\Support\Facades\Route;

// Sin sesión ni CSRF: el acceso se protege con firma HMAC (webhook) o URL firmada (foto).
Route::post('/webhooks/kapso', [KapsoWebhookController::class, 'recibir'])->name('webhooks.kapso');

Route::get('/wa/foto/{empresa}', [KapsoWebhookController::class, 'foto'])
    ->middleware('signed')
    ->name('wa.foto');

Route::get('/wa/cotizacion/{cotizacion}', [\App\Http\Controllers\WaDocumentosController::class, 'cotizacion'])
    ->middleware('signed')
    ->name('wa.cotizacion.pdf');
