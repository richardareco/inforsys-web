<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CuentasVencidasWidget;
use App\Filament\Widgets\FinancialStatsWidget;
use App\Filament\Widgets\ProductosSinMovimientoWidget;
use App\Filament\Widgets\StockBajoWidget;
use App\Filament\Widgets\VentasCategoriaWidget;
use App\Filament\Widgets\MetodosPagoChartWidget;
use App\Filament\Widgets\TopClientesWidget;
use App\Filament\Widgets\TopProductosWidget;
use App\Filament\Widgets\VelocimetroKpiWidget;
use App\Filament\Widgets\VendedoresWidget;
use App\Filament\Widgets\VentasChartWidget;
use App\Filament\Widgets\Vendedor\CategoriasVendedorWidget;
use App\Filament\Widgets\Vendedor\MetaVendedorWidget;
use App\Filament\Widgets\Vendedor\ResumenVendedorWidget;
use App\Filament\Widgets\Vendedor\SinVendedorWidget;
use App\Filament\Widgets\Vendedor\TopProductosVendedorWidget;
use App\Filament\Widgets\Vendedor\VentasDiariasVendedorWidget;
use App\Services\Dashboard\VentasVendedor;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'Dashboard';

    public static function canAccess(): bool
    {
        return \App\Support\Modulos::permite(Auth::user(), 'dashboard');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    /** Un usuario con rol de vendedor ve su propio panel (solo sus ventas), nunca el de la empresa. */
    private function esVendedor(): bool
    {
        return (bool) Auth::user()?->isVendedor();
    }

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
        if ($this->esVendedor()) {
            return 'Tu panel de ventas · ' . VentasVendedor::rango($this->filters ?? [])[2];
        }

        $empresa = Auth::user()->empresa?->nombre;
        $fecha   = ucfirst(now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY'));

        return $empresa
            ? "{$empresa} · {$fecha}"
            : "Resumen general del negocio · {$fecha}";
    }

    public function filtersForm(Form $form): Form
    {
        if ($this->esVendedor()) {
            return $form->schema([
                Section::make()->schema([
                    Select::make('periodo')
                        ->label('Período')
                        ->options(VentasVendedor::PERIODOS)
                        ->default('mes')
                        ->selectablePlaceholder(false)
                        ->native(false)
                        ->live(),
                    DatePicker::make('desde')
                        ->label('Desde')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->maxDate(today())
                        ->default(now()->startOfMonth())
                        ->visible(fn ($get) => $get('periodo') === 'personalizado'),
                    DatePicker::make('hasta')
                        ->label('Hasta')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->maxDate(today())
                        ->default(today())
                        ->visible(fn ($get) => $get('periodo') === 'personalizado'),
                ])->columns(['default' => 1, 'md' => 3]),
            ]);
        }

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
        if ($this->esVendedor()) {
            return VentasVendedor::paraUsuario(Auth::user())
                ? [
                    ResumenVendedorWidget::class,
                    MetaVendedorWidget::class,
                    VentasDiariasVendedorWidget::class,
                    CategoriasVendedorWidget::class,
                    TopProductosVendedorWidget::class,
                ]
                : [SinVendedorWidget::class];
        }

        return [
            FinancialStatsWidget::class,
            VentasChartWidget::class,
            MetodosPagoChartWidget::class,
            VelocimetroKpiWidget::class,
            TopProductosWidget::class,
            TopClientesWidget::class,
            VendedoresWidget::class,
            StockBajoWidget::class,
            CuentasVencidasWidget::class,
            VentasCategoriaWidget::class,
            ProductosSinMovimientoWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }

    protected function getHeaderActions(): array
    {
        // El informe en PDF es de toda la empresa
        if ($this->esVendedor()) {
            return [];
        }

        return [
            Action::make('exportPdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-chart-bar')
                ->color('gray')
                ->url(fn () => route('dashboard.export-pdf', [
                    'period' => $this->filters['period'] ?? 'dia',
                ]))
                ->openUrlInNewTab(),
        ];
    }
}
