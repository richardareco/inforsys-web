<?php

namespace App\Filament\Pages\Informes;

class InformeGananciasPage extends InformeBasePage
{
    protected static ?string $navigationIcon  = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Informes de ganancias';
    protected static ?string $title           = 'Informes de ganancias';
    protected static ?string $slug            = 'informes/ganancias';
    protected static ?int    $navigationSort  = 3;
    protected static string  $acento          = '#d97706';

    public function getDescripcion(): string
    {
        return 'Conocé cuánto ganás realmente: margen por producto, categoría y período.';
    }

    public function getContenidos(): array
    {
        return [
            'Ganancia bruta y neta por período',
            'Margen por producto',
            'Margen por categoría',
            'Productos más y menos rentables',
            'Evolución de la ganancia mes a mes',
        ];
    }
}
