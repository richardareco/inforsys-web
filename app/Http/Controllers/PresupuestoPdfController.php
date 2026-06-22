<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PresupuestoPdfController extends Controller
{
    public function export(string $presnr)
    {
        $presupuesto = DB::connection('delphi')
            ->table('presupuesto1')
            ->where('presnr', $presnr)
            ->first();

        abort_if(!$presupuesto, 404);

        $cliente = DB::connection('delphi')
            ->table('cliente')
            ->where('custnr', $presupuesto->custnr)
            ->first();

        $items = DB::connection('delphi')
            ->table('presupuesto2')
            ->where('presnr', $presnr)
            ->where('anulado', 0)
            ->get();

        $empresa  = Auth::user()->empresa;
        $logoPath = null;

        if ($empresa?->logo) {
            $path = storage_path('app/public/' . $empresa->logo);
            if (file_exists($path)) {
                $logoPath = $path;
            }
        }

        $pdf = Pdf::loadView('pdf.presupuesto', compact(
            'presupuesto', 'cliente', 'items', 'empresa', 'logoPath'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("presupuesto-{$presnr}.pdf");
    }
}
