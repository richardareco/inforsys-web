<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class VentasChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Ventas durante el Período';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $currYear = now()->year;
        $prevYear = $currYear - 1;

        $curr = DB::connection('delphi')->table('invo1')
            ->selectRaw('MONTH(fecha) as m, SUM(total) as total')
            ->where('flag', '4')
            ->whereYear('fecha', $currYear)
            ->groupBy('m')->pluck('total', 'm');

        $prev = DB::connection('delphi')->table('invo1')
            ->selectRaw('MONTH(fecha) as m, SUM(total) as total')
            ->where('flag', '4')
            ->whereYear('fecha', $prevYear)
            ->groupBy('m')->pluck('total', 'm');

        $labels = $currValues = $prevValues = [];
        for ($m = 1; $m <= 12; $m++) {
            $labels[]     = now()->startOfYear()->addMonths($m - 1)->isoFormat('MMM');
            $currValues[] = (float) ($curr[$m] ?? 0);
            $prevValues[] = (float) ($prev[$m] ?? 0);
        }

        return $this->buildDatasets((string) $currYear, (string) $prevYear, $labels, $currValues, $prevValues);
    }

    private function buildDatasets(string $currLabel, string $prevLabel, array $labels, array $curr, array $prev): array
    {
        return [
            'datasets' => [
                [
                    'label'                => $currLabel,
                    'data'                 => $curr,
                    'borderColor'          => '#3b82f6',
                    'backgroundColor'      => 'rgba(59,130,246,0.08)',
                    'fill'                 => true,
                    'tension'              => 0.4,
                    'pointBackgroundColor' => '#3b82f6',
                    'pointBorderColor'     => '#ffffff',
                    'pointBorderWidth'     => 2,
                    'pointRadius'          => 4,
                    'pointHoverRadius'     => 6,
                ],
                [
                    'label'                => $prevLabel,
                    'data'                 => $prev,
                    'borderColor'          => 'rgba(148,163,184,0.6)',
                    'backgroundColor'      => 'transparent',
                    'fill'                 => false,
                    'tension'              => 0.4,
                    'borderDash'           => [5, 4],
                    'pointBackgroundColor' => 'rgba(148,163,184,0.5)',
                    'pointBorderColor'     => '#ffffff',
                    'pointBorderWidth'     => 1,
                    'pointRadius'          => 3,
                    'pointHoverRadius'     => 5,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display'  => true,
                    'position' => 'top',
                    'align'    => 'end',
                    'labels'   => [
                        'color'    => 'rgb(148,163,184)',
                        'usePointStyle' => true,
                        'pointStyleWidth' => 8,
                        'padding' => 16,
                        'font'    => ['size' => 11],
                    ],
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(15,23,42,0.9)',
                    'titleColor'      => '#94a3b8',
                    'bodyColor'       => '#f8fafc',
                    'borderColor'     => 'rgba(59,130,246,0.3)',
                    'borderWidth'     => 1,
                    'padding'         => 12,
                    'cornerRadius'    => 8,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid'  => ['color' => 'rgba(148,163,184,0.08)'],
                    'ticks' => ['color' => 'rgb(148,163,184)', 'maxTicksLimit' => 6],
                    'border' => ['display' => false],
                ],
                'x' => [
                    'grid'  => ['display' => false],
                    'ticks' => ['color' => 'rgb(148,163,184)', 'maxTicksLimit' => 12],
                    'border' => ['display' => false],
                ],
            ],
            'interaction' => [
                'mode'         => 'index',
                'intersect'    => false,
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
