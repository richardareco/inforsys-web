<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class MetodosPagoChartWidget extends Widget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 1;
    protected static string $view = 'filament.widgets.metodos-pago-chart';

    protected function getViewData(): array
    {
        [$start, $end] = $this->getDateRange();

        $row = DB::connection('delphi')
            ->table('cajareg')
            ->whereBetween('fecha', [$start, $end])
            ->where('flag', '!=', 'c')
            ->selectRaw('SUM(paygs) as efectivo, SUM(paytr) as transferencia, SUM(paytj) as tarjeta, SUM(paycr) as credito')
            ->first();

        $efectivo      = (float) ($row->efectivo      ?? 0);
        $transferencia = (float) ($row->transferencia ?? 0);
        $tarjeta       = (float) ($row->tarjeta       ?? 0);
        $credito       = (float) ($row->credito       ?? 0);
        $total         = $efectivo + $transferencia + $tarjeta + $credito;

        return [
            'items' => [
                ['label' => 'Efectivo',      'amount' => $efectivo,      'color' => '#3b82f6', 'pct' => $total > 0 ? $efectivo / $total * 100 : 0],
                ['label' => 'Transferencia', 'amount' => $transferencia, 'color' => '#10b981', 'pct' => $total > 0 ? $transferencia / $total * 100 : 0],
                ['label' => 'Tarjeta',       'amount' => $tarjeta,       'color' => '#f59e0b', 'pct' => $total > 0 ? $tarjeta / $total * 100 : 0],
                ['label' => 'Credito',       'amount' => $credito,       'color' => '#a855f7', 'pct' => $total > 0 ? $credito / $total * 100 : 0],
            ],
            'total' => $total,
        ];
    }

    private function getDateRange(): array
    {
        $period = $this->filters['period'] ?? 'dia';

        if ($period === 'ayer') {
            return [today()->subDay(), today()->subDay()];
        }
        if ($period === 'semana') {
            return [now()->startOfWeek(), now()->endOfWeek()];
        }
        if ($period === 'mes') {
            return [now()->startOfMonth(), now()->endOfMonth()];
        }
        if ($period === 'año') {
            return [now()->startOfYear(), now()->endOfYear()];
        }

        return [today(), today()];
    }
}
