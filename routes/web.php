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

    // Ruta para servir imágenes de productos
    Route::get('/api/item-image/{item}', [ItemImageController::class, 'show'])
        ->name('item-image.show');

    // Rutas para Productos - manejadas mediante un helper para evitar conflicto Filament
    Route::get('/admin/items/create', fn() => app(\App\Filament\Resources\ItemResource\Pages\CreateItem::class)->render())
        ->name('filament.admin.resources.items.create');
    Route::get('/admin/items/{record}/edit', fn($record) => app(\App\Filament\Resources\ItemResource\Pages\EditItem::class)->render())
        ->name('filament.admin.resources.items.edit');
});
