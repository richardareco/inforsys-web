<?php

namespace App\Filament\Pages\Informes;

class InformeComprasPage extends InformeBasePage
{
    protected static ?string $navigationIcon  = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Informes de compras';
    protected static ?string $title           = 'Informes de compras';
    protected static ?string $slug            = 'informes/compras';
    protected static ?int    $navigationSort  = 2;
    protected static string  $acento          = '#2563eb';

    public function getDescripcion(): string
    {
        return 'Controlá lo que se compra: por proveedor, período y producto, y lo que queda por pagar.';
    }

    public function getContenidos(): array
    {
        return [
            'Compras por período',
            'Compras por proveedor',
            'Productos más comprados',
            'Comparación de costos entre compras',
            'Cuentas por pagar y vencimientos',
        ];
    }
}
