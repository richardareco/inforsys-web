<?php

namespace App\Filament\Widgets\Vendedor;

use Filament\Widgets\ChartWidget;

class VentasDiariasVendedorWidget extends ChartWidget
{
    use ConVendedor;

    protected static ?int $sort = 3;
    protected static ?string $heading = 'Mis ventas en el tiempo';
    protected static ?string $pollingInterval = null;
    protected static ?string $maxHeight = '280px';
    protected int | string | array $columnSpan = 1;

    protected function getType(): string
    {
        return 'line';
    }

    public function getDescription(): ?string
    {
        return $this->rango()[2];
    }

    protected function getData(): array
    {
        $v = $this->vendedor();
        [$d, $h] = $this->rango();
        $serie = $v ? $v->porDia($d, $h) : ['labels' => [], 'totales' => []];

        return [
            'datasets' => [[
                'label'           => 'Ventas (Gs.)',
                'data'            => $serie['totales'],
                'fill'            => true,
                'tension'         => 0.35,
                'borderColor'     => '#0047AB',
                'backgroundColor' => 'rgba(0, 71, 171, 0.12)',
                'pointRadius'     => count($serie['totales']) > 40 ? 0 : 3,
                'pointBackgroundColor' => '#0047AB',
            ]],
            'labels' => $serie['labels'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales'  => ['y' => ['beginAtZero' => true, 'grid' => ['color' => 'rgba(148,163,184,0.15)']], 'x' => ['grid' => ['display' => false]]],
        ];
    }
}
