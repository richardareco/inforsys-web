<?php

use App\Http\Controllers\DashboardPdfController;
use App\Http\Controllers\PresupuestoPdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/admin'));

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/export-pdf', [DashboardPdfController::class, 'export'])
        ->name('dashboard.export-pdf');
    Route::get('/presupuesto/{presnr}/pdf', [PresupuestoPdfController::class, 'export'])
        ->name('presupuesto.pdf');
});
