<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class TopProductosWidget extends Widget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 1;
    protected static string $view = 'filament.widgets.top-productos';

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

        $products = DB::connection('delphi')
            ->table('invo2')
            ->selectRaw('item, MAX(descr) as descr, SUM(qty) as ventas, SUM(qty * precio) as ingresos')
            ->where('flag', '3')
            ->whereBetween('fecha', [$start, $end])
            ->groupBy('item')
            ->orderByDesc('ingresos')
            ->limit(10)
            ->get();

        $prevData = DB::connection('delphi')
            ->table('invo2')
            ->selectRaw('item, SUM(qty * precio) as ingresos')
            ->where('flag', '3')
            ->whereBetween('fecha', [$prevStart, $prevEnd])
            ->groupBy('item')
            ->pluck('ingresos', 'item');

        return compact('products', 'prevData');
    }
}
