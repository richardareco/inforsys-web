<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class ConfiguracionVentasPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Configuraciones';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.configuracion-ventas-page';
    protected static string $title = 'Configuraciones';

    public ?array $data = [];

    public function mount(): void
    {
        $empresa = auth()->user()?->empresa;

        if ($empresa) {
            $this->form->fill([
                'modulo_caja_enabled' => $empresa->modulo_caja_enabled ?? true,
            ]);
        }
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Configuración de Ventas')
                ->description('Configura cómo se registran las ventas en el sistema')
                ->schema([
                    Forms\Components\Toggle::make('modulo_caja_enabled')
                        ->label('Usar módulo de caja')
                        ->helperText('Activa el POS con registro de caja e inmediato del pago. Desactívalo para permitir ventas sin pago (pendiente de cobro)')
                        ->default(true)
                        ->columnSpan('full'),
                ]),
        ];
    }

    public function submit(): void
    {
        $empresa = auth()->user()?->empresa;

        if (!$empresa) {
            Notification::make()->title('Error')->body('No se encontró la empresa')->danger()->send();
            return;
        }

        $data = $this->form->getState();
        $empresa->update([
            'modulo_caja_enabled' => $data['modulo_caja_enabled'],
        ]);

        Notification::make()->title('Configuración guardada')->success()->send();
    }
}
