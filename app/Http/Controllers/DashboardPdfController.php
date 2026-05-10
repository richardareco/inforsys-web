<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardPdfController extends Controller
{
    public function export(Request $request)
    {
        $period  = str_replace("\u{00F1}", 'n', $request->get('period', 'dia'));
        $user    = Auth::user();
        $empresa = $user->empresa;

        [$start, $end]         = $this->getDateRange($period);
        [$prevStart, $prevEnd] = $this->getPreviousRange($period);

        $invoActual = DB::connection('delphi')->table('invo1')
            ->where('flag', '4')->whereBetween('fecha', [$start, $end])
            ->selectRaw('SUM(total) as ventas, SUM(ctotal) as costo')->first();

        $invoAnterior = DB::connection('delphi')->table('invo1')
            ->where('flag', '4')->whereBetween('fecha', [$prevStart, $prevEnd])
            ->selectRaw('SUM(total) as ventas, SUM(ctotal) as costo')->first();

        $ventas     = (float) $invoActual->ventas;
        $costo      = (float) $invoActual->costo;
        $prevVentas = (float) $invoAnterior->ventas;

        $gastos = (float) DB::connection('delphi')->table('gastos')
            ->where('estado', 'REG')->whereBetween('fecha', [$start, $end])->sum('monto');

        $devoluciones = (float) DB::connection('delphi')->table('dev_cliente1')
            ->where('anulado', 0)->whereBetween('fecha', [$start, $end])->sum('total');

        $ganancia = $ventas - $costo - $gastos;

        $cobrarData = DB::connection('delphi')->table('invo1')
            ->where('flag', '4')->where('saldo', '>', 0)
            ->selectRaw('SUM(saldo) as total, COUNT(*) as cnt')->first();

        $pagarData = DB::connection('delphi')->table('compra1')
            ->where('anulado', 0)->where('saldo', '>', 0)
            ->selectRaw('SUM(saldo) as total, COUNT(*) as cnt')->first();

        $creditosCobrados = (float) DB::connection('delphi')->table('cuentas_acobrar')
            ->whereBetween('fecha_pago', [$start, $end])->sum('pago');

        $pagoRow = DB::connection('delphi')->table('cajareg')
            ->whereBetween('fecha', [$start, $end])
            ->where('flag', '!=', 'c')
            ->selectRaw('SUM(paygs) as efectivo, SUM(paytr) as transferencia, SUM(paytj) as tarjeta, SUM(paycr) as credito')
            ->first();

        $efectivo      = (float) ($pagoRow->efectivo      ?? 0);
        $transferencia = (float) ($pagoRow->transferencia ?? 0);
        $tarjeta       = (float) ($pagoRow->tarjeta       ?? 0);
        $credito       = (float) ($pagoRow->credito       ?? 0);
        $totalPagos    = $efectivo + $transferencia + $tarjeta + $credito;

        $metodos = [
            ['label' => 'Efectivo',      'amount' => $efectivo,      'color' => '#3b82f6', 'pct' => $totalPagos > 0 ? round($efectivo / $totalPagos * 100) : 0],
            ['label' => 'Transferencia', 'amount' => $transferencia, 'color' => '#10b981', 'pct' => $totalPagos > 0 ? round($transferencia / $totalPagos * 100) : 0],
            ['label' => 'Tarjeta',       'amount' => $tarjeta,       'color' => '#f59e0b', 'pct' => $totalPagos > 0 ? round($tarjeta / $totalPagos * 100) : 0],
            ['label' => 'Credito',       'amount' => $credito,       'color' => '#a855f7', 'pct' => $totalPagos > 0 ? round($credito / $totalPagos * 100) : 0],
        ];

        $currYear = now()->year;
        $prevYear = $currYear - 1;

        $ventasCurr = DB::connection('delphi')->table('invo1')
            ->selectRaw('MONTH(fecha) as m, SUM(total) as total')
            ->where('flag', '4')->whereYear('fecha', $currYear)
            ->groupBy('m')->pluck('total', 'm');

        $ventasPrev = DB::connection('delphi')->table('invo1')
            ->selectRaw('MONTH(fecha) as m, SUM(total) as total')
            ->where('flag', '4')->whereYear('fecha', $prevYear)
            ->groupBy('m')->pluck('total', 'm');

        $meses    = [];
        $maxVenta = 0;
        for ($m = 1; $m <= 12; $m++) {
            $curr     = (float) ($ventasCurr[$m] ?? 0);
            $prev     = (float) ($ventasPrev[$m] ?? 0);
            $maxVenta = max($maxVenta, $curr, $prev);
            $meses[]  = [
                'label' => now()->startOfYear()->addMonths($m - 1)->isoFormat('MMM'),
                'curr'  => $curr,
                'prev'  => $prev,
            ];
        }

        $metaMes = (float) (DB::table('metas')
            ->where('empresa_id', $user->empresa_id)
            ->where('mes', now()->startOfMonth()->format('Y-m-d'))
            ->value('monto') ?? 0);

        $ventasMes = (float) DB::connection('delphi')->table('invo1')
            ->where('flag', '4')
            ->whereBetween('fecha', [now()->startOfMonth(), now()])
            ->sum('total');

        $metaPct = $metaMes > 0 ? min(100, round(($ventasMes / $metaMes) * 100, 1)) : null;

        $topProductos = DB::connection('delphi')
            ->table('invo2')
            ->selectRaw('item, MAX(descr) as descr, SUM(qty) as ventas, SUM(qty * precio) as ingresos')
            ->where('flag', '3')->whereBetween('fecha', [$start, $end])
            ->groupBy('item')->orderByDesc('ingresos')->limit(10)->get();

        $topClientes = DB::connection('delphi')
            ->table('invo1')
            ->join('cliente', 'invo1.custnr', '=', 'cliente.custnr')
            ->selectRaw('cliente.custnr, MAX(cliente.custname) AS custname, COUNT(invo1.nro) AS pedidos, SUM(invo1.total) AS ingresos')
            ->where('invo1.flag', '4')->whereBetween('invo1.fecha', [$start, $end])
            ->groupBy('cliente.custnr')->orderByDesc('ingresos')->limit(10)->get();

        $vendedores = DB::connection('delphi')
            ->table('invo1')
            ->join('personal', 'invo1.pernr', '=', 'personal.pernr')
            ->selectRaw('personal.pernr, personal.pername, personal.porc_comision, COUNT(invo1.nro) AS ventas_count, SUM(invo1.total) AS total_ventas')
            ->where('invo1.flag', '4')
            ->whereNotNull('invo1.pernr')->where('invo1.pernr', '!=', '')
            ->whereBetween('invo1.fecha', [$start, $end])
            ->groupBy('personal.pernr', 'personal.pername', 'personal.porc_comision')
            ->orderByDesc('total_ventas')->limit(5)->get();

        $logoPath = null;
        if ($empresa?->logo) {
            $path = storage_path('app/public/' . $empresa->logo);
            if (file_exists($path)) {
                $logoPath = $path;
            }
        }

        if ($period === 'ayer')        $periodLabel = 'Ayer (' . today()->subDay()->isoFormat('D [de] MMMM') . ')';
        elseif ($period === 'semana')  $periodLabel = 'Esta Semana';
        elseif ($period === 'mes')     $periodLabel = 'Este Mes (' . now()->isoFormat('MMMM YYYY') . ')';
        elseif ($period === 'ano')     $periodLabel = 'Este Anio (' . now()->year . ')';
        else                           $periodLabel = 'Hoy (' . today()->isoFormat('D [de] MMMM [de] YYYY') . ')';

        $pdf = Pdf::loadView('pdf.dashboard', compact(
            'empresa', 'logoPath', 'periodLabel', 'period',
            'ventas', 'costo', 'gastos', 'devoluciones', 'ganancia',
            'prevVentas', 'cobrarData', 'pagarData', 'creditosCobrados',
            'metodos', 'totalPagos',
            'meses', 'maxVenta', 'currYear', 'prevYear',
            'metaMes', 'ventasMes', 'metaPct',
            'topProductos', 'topClientes', 'vendedores'
        ))->setPaper('a4', 'portrait');

        $filename = 'dashboard-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    private function getDateRange(string $period): array
    {
        if ($period === 'ayer')   return [today()->subDay(), today()->subDay()];
        if ($period === 'semana') return [now()->startOfWeek(), now()->endOfWeek()];
        if ($period === 'mes')    return [now()->startOfMonth(), now()->endOfMonth()];
        if ($period === 'ano')    return [now()->startOfYear(), now()->endOfYear()];
        return [today(), today()];
    }

    private function getPreviousRange(string $period): array
    {
        if ($period === 'ayer')   return [today()->subDays(2), today()->subDays(2)];
        if ($period === 'semana') return [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()];
        if ($period === 'mes')    return [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()];
        if ($period === 'ano')    return [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()];
        return [today()->subDay(), today()->subDay()];
    }
}