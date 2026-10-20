<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ComprobantePdfController extends Controller
{
    public function ticket(Request $request, $invnr)
    {
        $factura = DB::connection('delphi')->table('invo1')->where('invnr', $invnr)->first();
        abort_if(!$factura, 404, 'Factura no encontrada');

        $detalles = DB::connection('delphi')->table('invo2')->where('invnr', $invnr)->get();
        $cliente  = DB::connection('delphi')->table('cliente')->where('custnr', $factura->custnr)->first();
        $cajero   = DB::connection('delphi')->table('personal')->where('pernr', $factura->pernr)->first();

        $medio = in_array($request->query('medio'), ['efectivo', 'transferencia', 'tarjeta', 'qr'], true)
            ? $request->query('medio')
            : 'efectivo';
        $pago = is_numeric($request->query('pago')) ? (float) $request->query('pago') : null;

        return view('filament.components.ticket-print', [
            'empresa'  => Auth::user()->empresa,
            'factura'  => $factura,
            'detalles' => $detalles,
            'cliente'  => $cliente,
            'cajero'   => $cajero,
            'invnr'    => $invnr,
            'medio'    => $medio,
            'pago'     => $pago,
        ]);
    }

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
