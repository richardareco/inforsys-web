<?php

namespace App\Filament\Pages;

use App\Models\WaAgenteConfig;
use App\Models\WaLinea;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgenteWhatsAppPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Agente de WhatsApp';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?int    $navigationSort  = 20;
    protected static ?string $title           = 'Agente de WhatsApp';
    protected static ?string $slug            = 'agente-whatsapp';
    protected static string  $view            = 'filament.pages.agente-whatsapp-page';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user && ($user->isSuperAdmin() || $user->isAdministrador()) && $user->empresa_id;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canAccess();
    }

    public function mount(): void
    {
        $empresaId = Auth::user()->empresa_id;
        $config    = WaAgenteConfig::firstOrNew(['empresa_id' => $empresaId]);
        $linea     = WaLinea::where('empresa_id', $empresaId)->first();

        $this->form->fill(array_merge($config->attributesToArray(), [
            'phone_number_id' => $linea?->phone_number_id,
            'depositos'       => $config->depositos ?? [],
            'modo'            => $config->modo ?: 'basico',
            'max_productos'   => $config->max_productos ?: 5,
            'max_fotos'       => $config->max_fotos ?? 3,
            'pago_efectivo'      => $config->pago_efectivo ?? true,
            'pago_transferencia' => $config->pago_transferencia ?? true,
        ]));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Conexión')
                    ->description('Número de WhatsApp conectado en Kapso. Lo encontrás en Kapso → Phone numbers (columna "Phone number ID").')
                    ->schema([
                        Toggle::make('activo')->label('Agente activo')
                            ->helperText('Si está apagado, los mensajes se guardan pero no se responden.'),
                        TextInput::make('phone_number_id')->label('Phone number ID de Kapso')->maxLength(40),
                        Select::make('modo')->label('Modo de respuesta')->native(false)->options([
                            'basico' => 'Básico: busca productos y responde con precio, stock y fotos',
                        ])->helperText('El modo con inteligencia artificial se habilita en la próxima etapa.'),
                    ]),

                Section::make('Productos')
                    ->schema([
                        CheckboxList::make('depositos')->label('Depósitos que cuentan para el stock')
                            ->options(fn () => $this->opcionesDepositos())
                            ->columns(3)
                            ->helperText('Sin seleccionar ninguno se suma el stock de todos los depósitos.'),
                        TextInput::make('max_productos')->label('Máximo de productos por respuesta')->numeric()->minValue(1)->maxValue(10)->default(5),
                        TextInput::make('max_fotos')->label('Máximo de fotos por respuesta')->numeric()->minValue(0)->maxValue(5)->default(3),
                        Textarea::make('regla_mayorista')->label('Regla de precio mayorista')->rows(2)
                            ->helperText('Se define más adelante. Por ahora el agente informa solo el precio normal.'),
                    ]),

                Section::make('Información del negocio')
                    ->schema([
                        Textarea::make('mensaje_bienvenida')->label('Mensaje de bienvenida')->rows(2),
                        Textarea::make('horarios')->label('Horarios de atención')->rows(2),
                        Textarea::make('ubicacion')->label('Ubicación')->rows(2),
                        Textarea::make('entrega')->label('Entregas')->rows(2),
                        Toggle::make('pago_efectivo')->label('Acepta efectivo'),
                        Toggle::make('pago_transferencia')->label('Acepta transferencia'),
                        Textarea::make('datos_transferencia')->label('Datos para transferencia')->rows(3),
                        Textarea::make('tono')->label('Tono del agente')->rows(2),
                        Textarea::make('instrucciones')->label('Instrucciones adicionales')->rows(3),
                    ]),
            ])
            ->statePath('data');
    }

    private function opcionesDepositos(): array
    {
        try {
            return DB::connection('delphi')->table('deposito')->orderBy('deponr')
                ->get(['deponr', 'depo_nombre'])
                ->mapWithKeys(fn ($d) => [$d->deponr => trim($d->deponr . ' - ' . $d->depo_nombre, ' -')])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    public function save(): void
    {
        $empresaId = Auth::user()->empresa_id;
        $data      = $this->form->getState();
        $phoneId   = trim((string) ($data['phone_number_id'] ?? ''));
        unset($data['phone_number_id']);

        if ($phoneId !== '') {
            $otra = WaLinea::where('phone_number_id', $phoneId)->where('empresa_id', '!=', $empresaId)->exists();
            if ($otra) {
                Notification::make()->title('Ese número ya está asignado a otra empresa')->danger()->send();
                return;
            }
        }

        $data['depositos'] = array_values($data['depositos'] ?? []);
        WaAgenteConfig::updateOrCreate(['empresa_id' => $empresaId], $data);

        $linea = WaLinea::where('empresa_id', $empresaId)->first();
        if ($phoneId === '') {
            $linea?->update(['activo' => false]);
        } else {
            WaLinea::updateOrCreate(['empresa_id' => $empresaId], ['phone_number_id' => $phoneId, 'activo' => true]);
        }

        Notification::make()->title('Configuración guardada')->success()->send();
    }
}
