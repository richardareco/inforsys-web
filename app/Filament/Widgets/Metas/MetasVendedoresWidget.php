<?php

namespace App\Filament\Widgets\Metas;

use Filament\Widgets\Widget;

class MetasVendedoresWidget extends Widget
{
    use ConMetas;

    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.metas.vendedores';

    protected function getViewData(): array
    {
        return ['d' => $this->datos()];
    }
}
