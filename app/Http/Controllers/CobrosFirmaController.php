<?php

namespace App\Http\Controllers;

use App\Filament\Pages\CobrosPage;
use App\Services\Caja\CobrosPendientes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Huella de las ventas pendientes de cobro. Solo para quien tiene el módulo de cobros; no devuelve datos de ventas. */
class CobrosFirmaController extends Controller
{
    public function firma(Request $request): JsonResponse
    {
        abort_unless(CobrosPage::canAccess(), 403);

        $periodo = array_key_exists((string) $request->query('periodo'), CobrosPendientes::PERIODOS) ? (string) $request->query('periodo') : 'hoy';

        return response()->json(['firma' => (new CobrosPendientes)->firma($periodo)])->header('Cache-Control', 'no-store, private');
    }
}
