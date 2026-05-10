<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class VentasChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Comparación de Ventas';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        return match($this->filters['period'] ?? 'dia') {
            'semana' => $this->getWeeklyData(),
            'mes'    => $this->getMonthlyData(),
            'año'    => $this->getYearlyData(),
            'ayer'   => $this->getHourlyData(today()->subDay(), today()->subDays(2)),
            default  => $this->getHourlyData(today(), today()->subDay()),
        };
    }

    private function getHourlyData($current, $previous): array
    {
        $curr = DB::connection('delphi')
            ->table('invo1')
            ->selectRaw('HOUR(hora) as h, SUM(total) as total')
            ->where('flag', '4')
            ->whereDate('fecha', $current)
            ->whereNotNull('hora')
            ->groupBy('h')
            ->pluck('total', 'h');

        $prev = DB::connection('delphi')
            ->table('invo1')
            ->selectRaw('HOUR(hora) as h, SUM(total) as total')
            ->where('flag', '4')
            ->whereDate('fecha', $previous)
            ->whereNotNull('hora')
            ->groupBy('h')
            ->pluck('total', 'h');

        $labels = $currValues = $prevValues = [];
        for ($h = 0; $h < 24; $h++) {
            $labels[]     = sprintf('%02d:00', $h);
            $currValues[] = (float) ($curr[$h] ?? 0);
            $prevValues[] = (float) ($prev[$h] ?? 0);
        }

        $currLabel = $current->isToday() ? 'Período Actual' : $current->isoFormat('D MMM');
        $prevLabel = 'Período Anterior';

        return $this->buildDatasets($currLabel, $prevLabel, $labels, $currValues, $prevValues);
    }

    private function getWeeklyData(): array
    {
        $startCurr = now()->startOfWeek();
        $startPrev = now()->subWeek()->startOfWeek();

        $curr = DB::connection('delphi')->table('invo1')
            ->selectRaw('DATE(fecha) as d, SUM(total) as total')
            ->where('flag', '4')
            ->whereBetween('fecha', [$startCurr, now()->endOfWeek()])
            ->groupBy('d')->pluck('total', 'd');

        $prev = DB::connection('delphi')->table('invo1')
            ->selectRaw('DATE(fecha) as d, SUM(total) as total')
            ->where('flag', '4')
            ->whereBetween('fecha', [$startPrev, now()->subWeek()->endOfWeek()])
            ->groupBy('d')->pluck('total', 'd');

        $labels = $currValues = $prevValues = [];
        for ($i = 0; $i < 7; $i++) {
            $currDay = $startCurr->copy()->addDays($i);
            $prevDay = $startPrev->copy()->addDays($i);
            $labels[]     = $currDay->isoFormat('ddd D/M');
            $currValues[] = (float) ($curr[$currDay->toDateString()] ?? 0);
            $prevValues[] = (float) ($prev[$prevDay->toDateString()] ?? 0);
        }

        return $this->buildDatasets('Período Actual', 'Período Anterior', $labels, $currValues, $prevValues);
    }

    private function getMonthlyData(): array
    {
        $curr = DB::connection('delphi')->table('invo1')
            ->selectRaw('DAY(fecha) as d, SUM(total) as total')
            ->where('flag', '4')
            ->whereBetween('fecha', [now()->startOfMonth(), now()->endOfMonth()])
            ->groupBy('d')->pluck('total', 'd');

        $prev = DB::connection('delphi')->table('invo1')
            ->selectRaw('DAY(fecha) as d, SUM(total) as total')
            ->where('flag', '4')
            ->whereBetween('fecha', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->groupBy('d')->pluck('total', 'd');

        $daysInMonth = now()->daysInMonth;
        $labels = $currValues = $prevValues = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $labels[]     = (string) $d;
            $currValues[] = (float) ($curr[$d] ?? 0);
            $prevValues[] = (float) ($prev[$d] ?? 0);
        }

        return $this->buildDatasets('Este Mes', 'Mes Anterior', $labels, $currValues, $prevValues);
    }

    private function getYearlyData(): array
    {
        $currRows = DB::connection('delphi')->table('invo1')
            ->selectRaw('MONTH(fecha) as m, SUM(total) as total')
            ->where('flag', '4')
            ->whereYear('fecha', now()->year)
            ->groupBy('m')->pluck('total', 'm');

        $prevRows = DB::connection('delphi')->table('invo1')
            ->selectRaw('MONTH(fecha) as m, SUM(total) as total')
            ->where('flag', '4')
            ->whereYear('fecha', now()->year - 1)
            ->groupBy('m')->pluck('total', 'm');

        $labels = $currValues = $prevValues = [];
        for ($m = 1; $m <= 12; $m++) {
            $labels[]     = now()->startOfYear()->addMonths($m - 1)->isoFormat('MMM');
            $currValues[] = (float) ($currRows[$m] ?? 0);
            $prevValues[] = (float) ($prevRows[$m] ?? 0);
        }

        return $this->buildDatasets((string) now()->year, (string) (now()->year - 1), $labels, $currValues, $prevValues);
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
