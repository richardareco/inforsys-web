<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ComprobantePdfController extends Controller
{
    public function cierreCaja(Request $request)
    {
        abort_unless(\App\Filament\Pages\CierreCajaPage::canAccess(), 403);

        $fecha = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->query('fecha'))
            ? $request->query('fecha')
            : today()->toDateString();

        return view('filament.components.cierre-caja-print', [
            'd'        => \App\Filament\Pages\CierreCajaPage::datos($fecha),
            'fecha'    => $fecha,
            'empresa'  => Auth::user()->empresa,
            'usuario'  => Auth::user(),
            'generado' => now()->locale('es')->isoFormat('DD/MM/YYYY [a las] HH:mm'),
        ]);
    }

    public function ticket(Request $request, $invnr)
    {
        $factura = DB::connection('delphi')->table('invo1')->where('invnr', $invnr)->first();
        abort_if(!$factura, 404, 'Factura no encontrada');

        $detalles = DB::connection('delphi')->table('invo2')->where('invnr', $invnr)->get();
        $cliente  = DB::connection('delphi')->table('cliente')->where('custnr', $factura->custnr)->first();
        $cajero   = DB::connection('delphi')->table('personal')->where('pernr', $factura->pernr)->first();

        $medio = $request->query('medio');
        if (!in_array($medio, ['efectivo', 'transferencia', 'tarjeta', 'qr', 'credito'], true)) {
            $medio = $this->medioDePago($factura);
        }
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
            'reimpresion' => $request->boolean('reimpresion'),
        ]);
    }

    /** Deduce la forma de pago de una venta a partir de su registro en caja. */
    private function medioDePago(object $factura): string
    {
        if ((float) ($factura->acredito ?? 0) > 0) {
            return 'credito';
        }

        $caja = $factura->cajanr
            ? DB::connection('delphi')->table('cajareg')->where('cajanr', $factura->cajanr)->first()
            : null;

        if ($caja) {
            $caja = (object) array_change_key_case((array) $caja, CASE_LOWER);
            if ((float) $caja->paytr > 0) return 'transferencia';
            if ((float) $caja->paytj > 0) return 'tarjeta';
        }

        return 'efectivo';
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
