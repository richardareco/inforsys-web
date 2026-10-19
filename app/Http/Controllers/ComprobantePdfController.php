<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ComprobantePdfController extends Controller
{
    public function generarComprobante($invnr)
    {
        $user = Auth::user();
        $empresa = $user->empresa;

        // Obtener cabecera de factura
        $factura = DB::connection('delphi')
            ->table('invo1')
            ->where('invnr', $invnr)
            ->first();

        if (!$factura) {
            return response()->json(['error' => 'Factura no encontrada'], 404);
        }

        // Obtener detalles de factura
        $detalles = DB::connection('delphi')
            ->table('invo2')
            ->where('invnr', $invnr)
            ->get();

        // Obtener datos del cliente
        $cliente = DB::connection('delphi')
            ->table('cliente')
            ->where('custnr', $factura->custnr)
            ->first();

        // Obtener datos del cajero
        $cajero = DB::table('users')
            ->where('id', $factura->pernr)
            ->first();

        $data = [
            'empresa' => $empresa,
            'factura' => $factura,
            'detalles' => $detalles,
            'cliente' => $cliente,
            'cajero' => $cajero,
            'invnr' => $invnr,
        ];

        $pdf = Pdf::loadView('filament.components.comprobante-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 5)
            ->setOption('margin-bottom', 5)
            ->setOption('margin-left', 5)
            ->setOption('margin-right', 5);

        return $pdf->stream("comprobante-{$invnr}.pdf");
    }
}
