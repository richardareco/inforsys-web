<?php

namespace App\Filament\Widgets\Vendedor;

use Filament\Widgets\Widget;

class CategoriasVendedorWidget extends Widget
{
    use ConVendedor;

    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 1;
    protected static string $view = 'filament.widgets.vendedor.categorias';

    protected function getViewData(): array
    {
        $v = $this->vendedor();
        [$d, $h, $etiqueta] = $this->rango();
        $categorias = $v ? $v->porCategoria($d, $h) : [];

        return ['categorias' => $categorias, 'total' => array_sum(array_column($categorias, 'monto')), 'etiqueta' => $etiqueta];
    }
}
