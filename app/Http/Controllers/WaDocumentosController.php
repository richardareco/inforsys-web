<?php

namespace App\Http\Controllers;

use App\Models\WaAgenteConfig;
use App\Models\WaCotizacion;
use App\Models\WaPedido;
use Barryvdh\DomPDF\Facade\Pdf;

class WaDocumentosController extends Controller
{
    /** PDF de una cotización, accesible solo con enlace firmado (el cliente no tiene sesión). */
    public function cotizacion(int $cotizacion)
    {
        $cot = WaCotizacion::with(['items', 'contacto'])->findOrFail($cotizacion);
        $empresa = $cot->contacto->empresa_id ? \App\Models\Empresa::find($cot->empresa_id) : null;
        abort_if(!$empresa, 404);

        $logoPath = null;
        if ($empresa->logo) {
            $path = storage_path('app/public/' . $empresa->logo);
            $logoPath = file_exists($path) ? $path : null;
        }

        $pie = WaAgenteConfig::where('empresa_id', $empresa->id)->value('cotizacion_pie');

        return Pdf::loadView('pdf.wa-cotizacion', [
            'cotizacion' => $cot, 'empresa' => $empresa, 'logoPath' => $logoPath, 'pie' => $pie,
        ])->setPaper('a4', 'portrait')->stream($cot->numero . '.pdf');
    }

    /** Comprobante de un pedido pagado, accesible solo con enlace firmado (el cliente no tiene sesión). */
    public function pedido(int $pedido)
    {
        $ped = WaPedido::with('items')->findOrFail($pedido);
        abort_if($ped->estado !== 'pagado', 404);
        $empresa = \App\Models\Empresa::find($ped->empresa_id);
        abort_if(!$empresa, 404);

        $logoPath = null;
        if ($empresa->logo) {
            $path = storage_path('app/public/' . $empresa->logo);
            $logoPath = file_exists($path) ? $path : null;
        }

        return Pdf::loadView('pdf.wa-pedido', ['pedido' => $ped, 'empresa' => $empresa, 'logoPath' => $logoPath])
            ->setPaper('a4', 'portrait')->stream($ped->numero . '.pdf');
    }
}
