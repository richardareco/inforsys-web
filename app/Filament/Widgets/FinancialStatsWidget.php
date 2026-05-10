<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class FinancialStatsWidget extends Widget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.financial-stats';

    private function getDateRange(): array
    {
        return match($this->filters['period'] ?? 'dia') {
            'ayer'   => [today()->subDay(), today()->subDay()],
            'semana' => [now()->startOfWeek(), now()->endOfWeek()],
            'mes'    => [now()->startOfMonth(), now()->endOfMonth()],
            'año'    => [now()->startOfYear(), now()->endOfYear()],
            default  => [today(), today()],
        };
    }

    private function getPreviousRange(): array
    {
        return match($this->filters['period'] ?? 'dia') {
            'ayer'   => [today()->subDays(2), today()->subDays(2)],
            'semana' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'mes'    => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'año'    => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
            default  => [today()->subDay(), today()->subDay()],
        };
    }

    private function pctChange(float $prev, float $curr): ?float
    {
        if ($prev == 0) return null;
        return (($curr - $prev) / $prev) * 100;
    }

    protected function getViewData(): array
    {
        [$start, $end]         = $this->getDateRange();
        [$prevStart, $prevEnd] = $this->getPreviousRange();

        $invoActual = DB::connection('delphi')->table('invo1')
            ->where('flag', '4')->whereBetween('fecha', [$start, $end])
            ->selectRaw('SUM(total) as ventas, SUM(ctotal) as costo')->first();

        $invoAnterior = DB::connection('delphi')->table('invo1')
            ->where('flag', '4')->whereBetween('fecha', [$prevStart, $prevEnd])
            ->selectRaw('SUM(total) as ventas, SUM(ctotal) as costo')->first();

        $ventas    = (float) $invoActual->ventas;
        $costo     = (float) $invoActual->costo;
        $prevVentas = (float) $invoAnterior->ventas;
        $prevCosto  = (float) $invoAnterior->costo;

        $gastos = (float) DB::connection('delphi')->table('gastos')
            ->where('estado', 'REG')->whereBetween('fecha', [$start, $end])->sum('monto');

        $prevGastos = (float) DB::connection('delphi')->table('gastos')
            ->where('estado', 'REG')->whereBetween('fecha', [$prevStart, $prevEnd])->sum('monto');

        $devoluciones = (float) DB::connection('delphi')->table('dev_cliente1')
            ->where('anulado', 0)->whereBetween('fecha', [$start, $end])->sum('total');

        $prevDevoluciones = (float) DB::connection('delphi')->table('dev_cliente1')
            ->where('anulado', 0)->whereBetween('fecha', [$prevStart, $prevEnd])->sum('total');

        $ganancia     = $ventas - $costo - $gastos;
        $prevGanancia = $prevVentas - $prevCosto - $prevGastos;

        $cobrarData = DB::connection('delphi')->table('invo1')
            ->where('flag', '4')->where('saldo', '>', 0)
            ->selectRaw('SUM(saldo) as total, COUNT(*) as cnt')->first();
        $cobrar      = (float) $cobrarData->total;
        $cobrarCount = (int)   $cobrarData->cnt;

        $pagarData = DB::connection('delphi')->table('compra1')
            ->where('anulado', 0)->where('saldo', '>', 0)
            ->selectRaw('SUM(saldo) as total, COUNT(*) as cnt')->first();
        $pagar      = (float) $pagarData->total;
        $pagarCount = (int)   $pagarData->cnt;

        $creditosCobrados = (float) DB::connection('delphi')->table('cuentas_acobrar')
            ->whereBetween('fecha_pago', [$start, $end])->sum('pago');

        return [
            'ventas'           => $ventas,
            'ventasChange'     => $this->pctChange($prevVentas, $ventas),
            'costo'            => $costo,
            'costoChange'      => $this->pctChange($prevCosto, $costo),
            'gastos'           => $gastos,
            'gastosChange'     => $this->pctChange($prevGastos, $gastos),
            'devoluciones'     => $devoluciones,
            'devolucionesChange' => $this->pctChange($prevDevoluciones, $devoluciones),
            'ganancia'         => $ganancia,
            'gananciaChange'   => $this->pctChange($prevGanancia, $ganancia),
            'cobrar'           => $cobrar,
            'cobrarCount'      => $cobrarCount,
            'pagar'            => $pagar,
            'pagarCount'       => $pagarCount,
            'creditosCobrados' => $creditosCobrados,
        ];
    }
}
