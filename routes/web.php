<?php

use App\Http\Controllers\DashboardPdfController;
use App\Http\Controllers\PresupuestoPdfController;
use App\Http\Controllers\ComprobantePdfController;
use App\Http\Controllers\ItemImageController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/admin'));

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/export-pdf', [DashboardPdfController::class, 'export'])
        ->name('dashboard.export-pdf');
    Route::get('/presupuesto/{presnr}/pdf', [PresupuestoPdfController::class, 'export'])
        ->name('presupuesto.pdf');
    Route::get('/comprobante/{invnr}/pdf', [ComprobantePdfController::class, 'generarComprobante'])
        ->name('comprobante.pdf');

    Route::get('/cierre-caja/imprimir', [ComprobantePdfController::class, 'cierreCaja'])
        ->name('cierre-caja.imprimir');
    Route::get('/comprobante/{invnr}/ticket', [ComprobantePdfController::class, 'ticket'])
        ->name('comprobante.ticket');

    // Ruta para servir imágenes de productos
    Route::get('/api/item-image/{item}', [ItemImageController::class, 'show'])
        ->name('item-image.show');

    // Rutas para Productos - manejadas mediante un helper para evitar conflicto Filament
    Route::get('/admin/items/create', function () {
        abort_unless(\App\Filament\Resources\ItemResource::canAccess(), 403);

        return app(\App\Filament\Resources\ItemResource\Pages\CreateItem::class)->render();
    })
        ->name('filament.admin.resources.items.create');
    Route::get('/admin/items/{record}/edit', function ($record) {
        abort_unless(\App\Filament\Resources\ItemResource::canAccess(), 403);

        return app(\App\Filament\Resources\ItemResource\Pages\EditItem::class)->render();
    })
        ->name('filament.admin.resources.items.edit');
});

// ── Tienda online pública: sin sesión ni cookies (no genera una sesión por cada visita) ──
Route::prefix('tienda/{slug}')
    ->withoutMiddleware([
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        \App\Http\Middleware\SetDelphiConnection::class,
    ])
    ->where(['slug' => '[a-z0-9]+(?:-[a-z0-9]+)*'])
    ->middleware(\App\Http\Middleware\SeguridadTienda::class)
    ->group(function () {
        Route::middleware('throttle:tienda')->group(function () {
            Route::get('/', [\App\Http\Controllers\TiendaController::class, 'inicio'])->name('tienda.inicio');
            Route::get('/carrito', [\App\Http\Controllers\TiendaController::class, 'carrito'])->name('tienda.carrito');
            Route::get('/producto/{codigo}', [\App\Http\Controllers\TiendaController::class, 'producto'])->where('codigo', '[^/]{1,60}')->name('tienda.producto');
        });
        Route::get('/foto/{codigo}/{nro?}', [\App\Http\Controllers\TiendaController::class, 'foto'])->middleware('throttle:tienda-fotos')
            ->where('codigo', '[^/]{1,60}')->where('nro', '[0-9]{1,10}')->name('tienda.foto');
    });
