<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\FinancialStatsWidget;
use App\Filament\Widgets\MetodosPagoChartWidget;
use App\Filament\Widgets\TopClientesWidget;
use App\Filament\Widgets\TopProductosWidget;
use App\Filament\Widgets\VelocimetroKpiWidget;
use App\Filament\Widgets\VendedoresWidget;
use App\Filament\Widgets\VentasChartWidget;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'Dashboard';

    public function getHeading(): string
    {
        $user  = Auth::user();
        $hora  = now()->hour;
        $saludo = match(true) {
            $hora >= 6  && $hora < 12 => 'Buenos días',
            $hora >= 12 && $hora < 19 => 'Buenas tardes',
            default                   => 'Buenas noches',
        };

        return "{$saludo}, {$user->name} 👋";
    }

    public function getSubheading(): ?string
    {
        $empresa = Auth::user()->empresa?->nombre;
        $fecha   = now()->isoFormat('dddd D [de] MMMM [de] YYYY');

        return $empresa
            ? "{$empresa} · {$fecha}"
            : "Resumen general del negocio · {$fecha}";
    }

    public function filtersForm(Form $form): Form
    {
        return $form->schema([
            Select::make('period')
                ->label('')
                ->options([
                    'dia'    => 'Hoy',
                    'ayer'   => 'Ayer',
                    'semana' => 'Esta Semana',
                    'mes'    => 'Este Mes',
                    'año'    => 'Este Año',
                ])
                ->default('dia')
                ->selectablePlaceholder(false)
                ->native(false),
        ]);
    }

    public function getWidgets(): array
    {
        return [
            FinancialStatsWidget::class,
            VentasChartWidget::class,
            MetodosPagoChartWidget::class,
            VelocimetroKpiWidget::class,
            TopProductosWidget::class,
            TopClientesWidget::class,
            VendedoresWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn () => route('dashboard.export-pdf', [
                    'period' => $this->filters['period'] ?? 'dia',
                ]))
                ->openUrlInNewTab(),
        ];
    }
}
