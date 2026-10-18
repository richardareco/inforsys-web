<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ConfiguracionesVentasPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Configuraciones';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?int $navigationSort = 15;
    protected static string $view = 'filament.pages.configuraciones-ventas-page';

    public ?array $data = [];

    public function mount(): void
    {
        // Cargar configuración de la empresa del usuario autenticado
        $empresa = Auth::user()->empresa;

        if ($empresa) {
            $this->form->fill([
                'modulo_caja_enabled' => $empresa->modulo_caja_enabled ?? true,
                'permitir_venta_sin_stock' => $empresa->permitir_venta_sin_stock ?? false,
                'mostrar_productos_sin_stock' => $empresa->mostrar_productos_sin_stock ?? true,
                'nombre_empresa' => $empresa->nombre ?? '',
                'empresa_id' => $empresa->id ?? '',
            ]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Configuraciones')
                    ->tabs([
                        Tab::make('Ventas')
                            ->schema([
                                Section::make('Módulo de Caja')
                                    ->description('Controla cómo se procesan las ventas y el registro de pagos.')
                                    ->schema([
                                        Toggle::make('modulo_caja_enabled')
                                            ->label('Módulo de Caja')
                                            ->helperText('Cuando está ACTIVO: El POS registra inmediatamente el pago en caja. Cuando está INACTIVO: Las ventas quedan pendientes de cobro.')
                                            ->live(),
                                    ]),

                                Section::make('Validación de Stock')
                                    ->description('Controla si se permite vender productos sin stock disponible.')
                                    ->schema([
                                        Toggle::make('permitir_venta_sin_stock')
                                            ->label('Permitir venta sin stock')
                                            ->helperText('Cuando está ACTIVO: Se permite vender aunque no haya stock. Cuando está INACTIVO: Solo se puede vender si hay stock disponible.')
                                            ->live(),
                                    ]),

                                Section::make('Visibilidad de Productos')
                                    ->description('Controla qué productos se muestran en el filtro del POS.')
                                    ->schema([
                                        Toggle::make('mostrar_productos_sin_stock')
                                            ->label('Mostrar productos sin stock')
                                            ->helperText('Cuando está ACTIVO: Muestra todos los productos incluso los sin stock. Cuando está INACTIVO: Solo muestra productos con stock disponible.')
                                            ->live(),
                                    ]),
                            ]),

                        Tab::make('Productos')
                            ->schema([
                                Section::make('Configuración de Productos')
                                    ->description('Opciones relacionadas con productos e inventario.')
                                    ->schema([
                                        TextInput::make('placeholder')
                                            ->disabled()
                                            ->label('Próximas configuraciones')
                                            ->default('En desarrollo...'),
                                    ]),
                            ]),

                        Tab::make('Reportes')
                            ->schema([
                                Section::make('Configuración de Reportes')
                                    ->description('Opciones para exportación y generación de reportes.')
                                    ->schema([
                                        TextInput::make('placeholder2')
                                            ->disabled()
                                            ->label('Próximas configuraciones')
                                            ->default('En desarrollo...'),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data')
            ->model(Auth::user()->empresa)
            ->inlineLabel();
    }

    public function save(): void
    {
        $empresa = Auth::user()->empresa;

        if (!$empresa) {
            Notification::make()
                ->title('Error')
                ->body('No se encontró la empresa asociada.')
                ->danger()
                ->send();
            return;
        }

        $data = $this->form->getState();

        $empresa->update([
            'modulo_caja_enabled' => $data['modulo_caja_enabled'] ?? true,
            'permitir_venta_sin_stock' => $data['permitir_venta_sin_stock'] ?? false,
            'mostrar_productos_sin_stock' => $data['mostrar_productos_sin_stock'] ?? true,
        ]);

        Notification::make()
            ->title('Configuraciones guardadas')
            ->body('Los cambios se han guardado correctamente.')
            ->success()
            ->send();
    }
}
