<?php

namespace App\Filament\Widgets\Vendedor;

use Filament\Widgets\Widget;

class MetaVendedorWidget extends Widget
{
    use ConVendedor;

    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;
    protected static string $view = 'filament.widgets.vendedor.meta';

    protected function getViewData(): array
    {
        $v = $this->vendedor();
        [, $h] = $this->rango();

        return [
            'meta' => $v?->meta($h),
            // mes al que se refiere la meta aunque todavía no tenga una cargada
            'mes'  => ucfirst($h->copy()->locale('es')->isoFormat('MMMM YYYY')),
        ];
    }
}
