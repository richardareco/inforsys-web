<?php

namespace App\Filament\Pages;

use App\Models\WaConversacion;
use App\Models\WaMensaje;
use App\Services\WhatsApp\EnvioWhatsApp;
use App\Traits\CheckUserAcceso;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;

class ConversacionesWhatsAppPage extends Page
{
    use CheckUserAcceso;

    protected static ?string $navigationIcon  = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Conversaciones';
    protected static ?string $title           = 'Conversaciones de WhatsApp';
    protected static ?string $navigationGroup = 'Ventas';
    protected static ?int    $navigationSort  = 8;
    protected static ?string $slug            = 'conversaciones-whatsapp';
    protected static string  $view            = 'filament.pages.conversaciones-whatsapp-page';

    public ?int $seleccionada = null;
    public string $filtro = 'todas';   // todas | atencion | agente | humano
    public string $busqueda = '';
    public string $mensaje = '';

    public static function canAccess(): bool
    {
        return self::userHasAcceso('whatsapp');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canAccess();
    }

    /** Cantidad de conversaciones esperando a una persona (el último mensaje es del cliente). */
    public static function getNavigationBadge(): ?string
    {
        if (!self::canAccess()) {
            return null;
        }

        $n = self::consultaBase()->where('estado', '!=', 'agente')
            ->whereRaw("(select direccion from wa_mensajes where conversacion_id = wa_conversaciones.id order by id desc limit 1) = 'in'")
            ->count();

        return $n > 0 ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    /** Solo las conversaciones de la empresa del usuario. */
    private static function consultaBase(): Builder
    {
        $user  = Auth::user();
        $query = WaConversacion::query();

        if ($user?->empresa_id) {
            return $query->where('empresa_id', $user->empresa_id);
        }

        return $user?->isSuperAdmin() ? $query : $query->whereRaw('1 = 0');
    }

    /** Conteos para las tarjetas de arriba (mismo criterio que los filtros de la lista). */
    #[Computed]
    public function resumenEstados(): array
    {
        $ultimaEntrante = "(select direccion from wa_mensajes where conversacion_id = wa_conversaciones.id order by id desc limit 1) = 'in'";

        return [
            'agente' => self::consultaBase()->where('estado', 'agente')->count(),
            'humano' => self::consultaBase()->where('estado', '!=', 'agente')->count(),
            'atencion' => self::consultaBase()->where('estado', '!=', 'agente')->whereRaw($ultimaEntrante)->count(),
            'hoy' => self::consultaBase()->where('ultimo_mensaje_at', '>=', today())->count(),
        ];
    }

    /** Nombre e imagen con que se muestra al agente de IA. */
    #[Computed]
    public function agente(): array
    {
        $config = \App\Models\WaAgenteConfig::where('empresa_id', Auth::user()?->empresa_id)->first();
        $avatar = $config?->agente_avatar;

        return [
            'nombre' => trim((string) $config?->agente_nombre) ?: 'Asistente IA',
            'avatar' => $avatar ? Storage::disk('public')->url($avatar) : '/images/agente-ia.svg',
        ];
    }

    #[Computed]
    public function conversaciones()
    {
        $q = self::consultaBase()->with('contacto')
            ->select('wa_conversaciones.*')
            ->addSelect(['ultimo_texto' => WaMensaje::select('texto')->whereColumn('conversacion_id', 'wa_conversaciones.id')->orderByDesc('id')->limit(1)])
            ->addSelect(['ultima_direccion' => WaMensaje::select('direccion')->whereColumn('conversacion_id', 'wa_conversaciones.id')->orderByDesc('id')->limit(1)])
            ->orderByDesc('ultimo_mensaje_at')->orderByDesc('id');

        match ($this->filtro) {
            'atencion' => $q->where('estado', '!=', 'agente')
                ->whereRaw("(select direccion from wa_mensajes where conversacion_id = wa_conversaciones.id order by id desc limit 1) = 'in'"),
            'agente'   => $q->where('estado', 'agente'),
            'humano'   => $q->where('estado', '!=', 'agente'),
            default    => null,
        };

        if (trim($this->busqueda) !== '') {
            $t = '%' . addcslashes(trim($this->busqueda), '%_\\') . '%';
            $q->whereHas('contacto', fn ($c) => $c->where('nombre', 'like', $t)->orWhere('telefono', 'like', $t));
        }

        return $q->limit(60)->get();
    }

    #[Computed]
    public function actual(): ?WaConversacion
    {
        return $this->seleccionada
            ? self::consultaBase()->with(['contacto', 'linea'])->find($this->seleccionada)
            : null;
    }

    #[Computed]
    public function mensajes()
    {
        if (!$this->actual) {
            return collect();
        }

        return WaMensaje::where('conversacion_id', $this->actual->id)->orderByDesc('id')->limit(80)->get()->reverse()->values();
    }

    /** El cliente solo recibe texto libre dentro de las 24 h posteriores a su último mensaje. */
    #[Computed]
    public function ventanaAbierta(): bool
    {
        if (!$this->actual) {
            return false;
        }

        return WaMensaje::where('conversacion_id', $this->actual->id)
            ->where('direccion', 'in')
            ->where('created_at', '>=', now()->subHours(24))
            ->exists();
    }

    public function seleccionar(int $id): void
    {
        if (self::consultaBase()->whereKey($id)->exists()) {
            $this->seleccionada = $id;
            $this->mensaje = '';
        }
    }

    public function volver(): void
    {
        $this->seleccionada = null;
    }

    private function cambiarEstado(string $estado, string $aviso): void
    {
        if (!$this->actual) {
            return;
        }

        $this->actual->update(['estado' => $estado]);
        Notification::make()->title($aviso)->success()->send();
    }

    public function tomarControl(): void
    {
        $this->cambiarEstado('humano', 'Tomaste la conversación. El agente no responderá.');
    }

    public function pausarAgente(): void
    {
        $this->cambiarEstado('pausado', 'Agente pausado en esta conversación.');
    }

    public function devolverAlAgente(): void
    {
        $this->cambiarEstado('agente', 'La conversación volvió al agente.');
    }

    /**
     * El agente deja de usar los mensajes anteriores (siguen visibles acá), se vacía la lista del cliente,
     * se descartan pedidos sin confirmar y la conversación vuelve al agente.
     */
    public function reiniciarContexto(): void
    {
        $conv = $this->actual;
        if (!$conv) {
            return;
        }

        $ultimo = (int) WaMensaje::where('conversacion_id', $conv->id)->max('id');

        \App\Models\WaPedido::where('conversacion_id', $conv->id)->where('estado', 'borrador')->get()->each(function ($p) {
            $p->cotizacion?->update(['estado' => 'cancelada']);
            $p->delete();
        });

        $conv->update(['contexto_desde_id' => $ultimo ?: null, 'carrito' => null, 'carrito_at' => null, 'estado' => 'agente']);

        Notification::make()->title('Contexto reiniciado')->body('El agente empieza de cero en esta conversación. Los mensajes anteriores siguen visibles.')->success()->send();
    }

    public function enviar(EnvioWhatsApp $envio): void
    {
        $conv  = $this->actual;
        $texto = trim($this->mensaje);

        if (!$conv || $texto === '') {
            return;
        }

        if (!$this->ventanaAbierta) {
            Notification::make()->title('Pasaron más de 24 horas desde el último mensaje del cliente')
                ->body('WhatsApp solo permite responder con texto libre dentro de las 24 horas. Hace falta una plantilla.')
                ->warning()->send();

            return;
        }

        // Al escribir una persona, el agente deja de responder
        if ($conv->estado === 'agente') {
            $conv->update(['estado' => 'humano']);
        }

        $envio->texto($conv, $conv->linea, $conv->contacto->telefono, mb_substr($texto, 0, 3500), 'humano');
        $conv->update(['ultimo_mensaje_at' => now()]);
        $this->mensaje = '';
    }
}
