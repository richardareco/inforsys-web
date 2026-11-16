<?php

namespace App\Filament\Widgets\Vendedor;

use Filament\Widgets\Widget;

class TopProductosVendedorWidget extends Widget
{
    use ConVendedor;

    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 1;
    protected static string $view = 'filament.widgets.vendedor.top-productos';

    protected function getViewData(): array
    {
        $v = $this->vendedor();
        [$d, $h, $etiqueta] = $this->rango();
        $productos = $v ? $v->topProductos($d, $h, 8) : [];

        return ['productos' => $productos, 'maximo' => $productos ? max(array_column($productos, 'unidades')) : 0, 'etiqueta' => $etiqueta];
    }
}
