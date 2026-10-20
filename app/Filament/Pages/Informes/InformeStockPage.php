<?php

namespace App\Filament\Pages\Informes;

class InformeStockPage extends InformeBasePage
{
    protected static ?string $navigationIcon  = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Informes de stock';
    protected static ?string $title           = 'Informes de stock';
    protected static ?string $slug            = 'informes/stock';
    protected static ?int    $navigationSort  = 4;
    protected static string  $acento          = '#7c3aed';

    public function getDescripcion(): string
    {
        return 'Mantené el inventario bajo control: existencias, reposición y productos sin rotación.';
    }

    public function getContenidos(): array
    {
        return [
            'Stock valorizado por depósito',
            'Productos con stock bajo y agotados',
            'Productos sin movimiento',
            'Stock negativo y diferencias',
            'Rotación de inventario',
        ];
    }
}
