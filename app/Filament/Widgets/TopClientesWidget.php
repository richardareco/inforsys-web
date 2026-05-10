<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class TopClientesWidget extends Widget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 1;
    protected static string $view = 'filament.widgets.top-clientes';

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

    protected function getViewData(): array
    {
        [$start, $end]         = $this->getDateRange();
        [$prevStart, $prevEnd] = $this->getPreviousRange();

        $clientes = DB::connection('delphi')
            ->table('invo1')
            ->join('cliente', 'invo1.custnr', '=', 'cliente.custnr')
            ->selectRaw('
                cliente.custnr,
                MAX(cliente.custname) AS custname,
                COUNT(invo1.nro)      AS pedidos,
                SUM(invo1.total)      AS ingresos
            ')
            ->where('invo1.flag', '4')
            ->whereBetween('invo1.fecha', [$start, $end])
            ->groupBy('cliente.custnr')
            ->orderByDesc('ingresos')
            ->limit(10)
            ->get();

        $prevData = DB::connection('delphi')
            ->table('invo1')
            ->selectRaw('custnr, SUM(total) as ingresos')
            ->where('flag', '4')
            ->whereBetween('fecha', [$prevStart, $prevEnd])
            ->groupBy('custnr')
            ->pluck('ingresos', 'custnr');

        return compact('clientes', 'prevData');
    }
}
