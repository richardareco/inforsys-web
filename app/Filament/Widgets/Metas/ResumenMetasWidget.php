<?php

namespace App\Filament\Widgets\Metas;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ResumenMetasWidget extends StatsOverviewWidget
{
    use ConMetas;

    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = null;
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $d  = $this->datos();
        $gs = fn (float $n) => 'Gs. ' . number_format($n, 0, ',', '.');
        $e  = $d['empresa'];
        $r  = $d['resumen'];

        $empresa = $e
            ? Stat::make('Meta de la empresa · ' . $d['mes'], number_format($e['pct'], 1, ',', '.') . '%')
                ->description($gs($e['vendido']) . ' de ' . $gs($e['meta']))
                ->descriptionIcon($e['pct'] >= 100 ? 'heroicon-m-check-badge' : 'heroicon-m-flag')
                ->color($e['pct'] >= 100 ? 'success' : ($e['pct'] >= $d['esperado'] ? 'primary' : 'warning'))
            : Stat::make('Meta de la empresa · ' . $d['mes'], 'Sin meta')
                ->description('Cargala en Metas de Ventas')->descriptionIcon('heroicon-m-flag')->color('gray');

        return [
            $empresa,
            Stat::make('Vendedores con meta', (string) $r['con_meta'])
                ->description($r['con_meta'] === 0 ? 'Ninguno todavía' : ($d['cerrado'] ? 'Mes cerrado' : 'Se espera ' . number_format($d['esperado'], 0, ',', '.') . '% a esta altura del mes'))
                ->descriptionIcon('heroicon-m-user-group')->color('gray'),
            Stat::make('Metas cumplidas', $r['cumplidas'] . ' de ' . $r['con_meta'])
                ->description($r['cumplidas'] > 0 ? 'Llegaron al 100% o más' : 'Ninguna por ahora')
                ->descriptionIcon('heroicon-m-trophy')->color($r['cumplidas'] > 0 ? 'success' : 'gray'),
            Stat::make('Cumplimiento promedio', number_format($r['promedio'], 1, ',', '.') . '%')
                ->description('Promedio de los vendedores con meta')->descriptionIcon('heroicon-m-chart-bar')
                ->color($r['con_meta'] === 0 ? 'gray' : ($r['promedio'] >= $d['esperado'] ? 'success' : 'warning')),
        ];
    }
}
