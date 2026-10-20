<?php

namespace App\Filament\Pages\Informes;

class InformeCajaPage extends InformeBasePage
{
    protected static ?string $navigationIcon  = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = 'Informes de caja';
    protected static ?string $title           = 'Informes de caja';
    protected static ?string $slug            = 'informes/caja';
    protected static ?int    $navigationSort  = 5;
    protected static string  $acento          = '#0891b2';

    public function getDescripcion(): string
    {
        return 'Revisá el movimiento de caja a lo largo del tiempo: cobros, formas de pago y diferencias.';
    }

    public function getContenidos(): array
    {
        return [
            'Cierres de caja por rango de fechas',
            'Cobros por forma de pago',
            'Diferencias entre caja y ventas',
            'Movimientos por cajero',
            'Devoluciones y salidas de caja',
        ];
    }
}
