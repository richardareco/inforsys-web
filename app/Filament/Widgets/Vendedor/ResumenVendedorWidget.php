<?php

namespace App\Filament\Widgets\Vendedor;

use App\Services\Dashboard\VentasVendedor;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ResumenVendedorWidget extends StatsOverviewWidget
{
    use ConVendedor;

    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = null;
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $v = $this->vendedor();
        if (!$v) {
            return [];
        }

        [$d, $h] = $this->rango();
        [$pd, $ph] = VentasVendedor::anterior($d, $h);

        $ahora = $v->resumen($d, $h);
        $antes = $v->resumen($pd, $ph);
        $serie = $v->porDia($d, $h);

        $gs = fn (float $n) => 'Gs. ' . number_format($n, 0, ',', '.');

        return [
            $this->tarjeta('Total vendido', $gs($ahora['total']), $ahora['total'], $antes['total'], $serie['totales'], 'heroicon-m-banknotes'),
            $this->tarjeta('Ventas realizadas', number_format($ahora['cantidad'], 0, ',', '.'), $ahora['cantidad'], $antes['cantidad'], $serie['cantidades'], 'heroicon-m-receipt-percent'),
            $this->tarjeta('Ticket promedio', $gs($ahora['ticket']), $ahora['ticket'], $antes['ticket'], [], 'heroicon-m-calculator'),
            $this->tarjeta('Unidades vendidas', rtrim(rtrim(number_format($ahora['unidades'], 2, ',', '.'), '0'), ','), $ahora['unidades'], $antes['unidades'], [], 'heroicon-m-cube'),
        ];
    }

    /** Tarjeta con la variación respecto del tramo anterior (sube en verde, baja en rojo). */
    private function tarjeta(string $titulo, string $valor, float|int $actual, float|int $anterior, array $serie, string $icono): Stat
    {
        $stat = Stat::make($titulo, $valor);

        if ($anterior > 0) {
            $pct = ($actual - $anterior) / $anterior * 100;
            $sube = $pct >= 0;
            $stat->description(($sube ? '+' : '') . number_format($pct, 1, ',', '.') . '% vs. período anterior')
                ->descriptionIcon($sube ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($sube ? 'success' : 'danger');
        } elseif ($actual > 0) {
            $stat->description('Sin ventas en el período anterior')->descriptionIcon('heroicon-m-sparkles')->color('success');
        } else {
            $stat->description('Todavía sin ventas')->descriptionIcon($icono)->color('gray');
        }

        if (count($serie) > 1 && array_sum($serie) > 0) {
            $stat->chart(array_map('floatval', $serie));
        }

        return $stat;
    }
}
