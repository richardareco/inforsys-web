<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class VendedoresWidget extends Widget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 8;
    protected int | string | array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.vendedores';

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

    protected function getViewData(): array
    {
        [$start, $end] = $this->getDateRange();

        $vendedores = DB::connection('delphi')
            ->table('invo1')
            ->join('personal', 'invo1.pernr', '=', 'personal.pernr')
            ->selectRaw('
                personal.pernr,
                personal.pername,
                personal.porc_comision,
                COUNT(invo1.nro)       AS ventas_count,
                SUM(invo1.total)       AS total_ventas
            ')
            ->where('invo1.flag', '4')
            ->whereNotNull('invo1.pernr')
            ->where('invo1.pernr', '!=', '')
            ->whereBetween('invo1.fecha', [$start, $end])
            ->groupBy('personal.pernr', 'personal.pername', 'personal.porc_comision')
            ->orderByDesc('total_ventas')
            ->limit(5)
            ->get();

        return compact('vendedores');
    }
}
