<?php

namespace App\Filament\Pages\Informes;

class InformeFinancierosPage extends InformeBasePage
{
    protected static ?string $navigationIcon  = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Informes financieros';
    protected static ?string $title           = 'Informes financieros';
    protected static ?string $slug            = 'informes/financieros';
    protected static ?int    $navigationSort  = 6;
    protected static string  $acento          = '#0f766e';

    public function getDescripcion(): string
    {
        return 'La visión global del negocio: deudas, cobros, gastos y resultado del período.';
    }

    public function getContenidos(): array
    {
        return [
            'Cuentas por cobrar y antigüedad de la deuda',
            'Cuentas por pagar',
            'Gastos por categoría',
            'Estado de resultados del período',
            'Flujo de ingresos y egresos',
        ];
    }
}
