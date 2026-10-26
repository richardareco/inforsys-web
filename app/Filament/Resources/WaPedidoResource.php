<?php

namespace App\Filament\Resources;

use App\Filament\Pages\ConversacionesWhatsAppPage;
use App\Filament\Resources\WaPedidoResource\Pages;
use App\Models\Empresa;
use App\Models\WaMensaje;
use App\Models\WaPedido;
use App\Services\WhatsApp\EnvioWhatsApp;
use App\Services\WhatsApp\ReglaDeNegocio;
use App\Services\WhatsApp\VentaDelphiService;
use App\Traits\CheckUserAcceso;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class WaPedidoResource extends Resource
{
    use CheckUserAcceso;

    protected static ?string $model = WaPedido::class;

    protected static ?string $navigationIcon   = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel  = 'Pedidos WhatsApp';
    protected static ?string $modelLabel       = 'pedido';
    protected static ?string $pluralModelLabel = 'Pedidos de WhatsApp';
    protected static ?string $navigationGroup  = 'Ventas';
    protected static ?int    $navigationSort   = 9;
    protected static ?string $slug             = 'pedidos-whatsapp';

    public static function canAccess(): bool
    {
        return self::userHasAcceso('whatsapp');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canAccess();
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function getNavigationBadge(): ?string
    {
        if (!self::canAccess()) {
            return null;
        }
        $n = static::getEloquentQuery()->where('estado', 'pendiente')->count();

        return $n > 0 ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /** Solo los pedidos de la empresa del usuario (incluye los que el cliente dejó sin confirmar). */
    public static function getEloquentQuery(): Builder
    {
        $user  = Auth::user();
        $query = parent::getEloquentQuery();

        if ($user?->empresa_id) {
            return $query->where('empresa_id', $user->empresa_id);
        }

        return $user?->isSuperAdmin() ? $query : $query->whereRaw('1 = 0');
    }

    /** Estados del panel: pendiente → pagado | anulado (borrador = el cliente no llegó a confirmar). */
    private static function colorEstado(string $estado): string
    {
        return match ($estado) {
            'pendiente', 'procesando' => 'warning',
            'pagado' => 'success',
            'anulado' => 'danger',
            default => 'gray',
        };
    }

    public static function nombreEstado(string $estado): string
    {
        return match ($estado) {
            'borrador' => 'Sin confirmar',
            'procesando' => 'Procesando',
            default => ucfirst($estado),
        };
    }

    public static function gs($v): string
    {
        return 'Gs. ' . number_format((float) $v, 0, ',', '.');
    }

    /** Avisa al cliente por WhatsApp si todavía está dentro de las 24 h. Devuelve si se envió. */
    public static function avisarCliente(WaPedido $pedido, string $texto): bool
    {
        $conv = $pedido->conversacion()->with(['linea', 'contacto'])->first();
        if (!$conv || !$conv->linea) {
            return false;
        }

        $ventana = WaMensaje::where('conversacion_id', $conv->id)->where('direccion', 'in')
            ->where('created_at', '>=', now()->subHours(24))->exists();
        if (!$ventana) {
            return false;
        }

        app(EnvioWhatsApp::class)->texto($conv, $conv->linea, $conv->contacto->telefono, $texto, 'humano');

        return true;
    }

    /** Marca el pedido como pagado y genera la venta en el sistema. */
    public static function pagar(WaPedido $pedido): void
    {
        try {
            $empresa = Empresa::findOrFail($pedido->empresa_id);
            $invnr   = (new VentaDelphiService($empresa))->pagar($pedido, Auth::user());
        } catch (ReglaDeNegocio $e) {
            Notification::make()->title('No se pudo generar la venta')->body($e->getMessage())->danger()->send();

            return;
        } catch (\Throwable $e) {
            report($e);
            Notification::make()->title('Error al generar la venta')->body($e->getMessage())->danger()->send();

            return;
        }

        $pedido->refresh();
        $enviado = self::avisarCliente($pedido, "¡Recibimos tu pago! Tu pedido {$pedido->numero} por " . self::gs($pedido->total) . ' está confirmado. Gracias por tu compra.');

        Notification::make()->title("Pedido pagado · venta {$invnr} registrada")
            ->body(($enviado ? 'Se le avisó al cliente por WhatsApp. ' : 'No se pudo avisar al cliente por WhatsApp (pasaron más de 24 horas). ') . 'Se descontó el stock.')
            ->success()->send();
    }

    public static function anular(WaPedido $pedido, string $motivo): void
    {
        try {
            $empresa = Empresa::findOrFail($pedido->empresa_id);
            (new VentaDelphiService($empresa))->anular($pedido, Auth::user(), $motivo);
        } catch (ReglaDeNegocio $e) {
            Notification::make()->title('No se pudo anular')->body($e->getMessage())->danger()->send();

            return;
        }

        $pedido->refresh();
        $enviado = $pedido->confirmado_at
            ? self::avisarCliente($pedido, "Lamentablemente no pudimos confirmar tu pedido {$pedido->numero}: {$motivo}. Si querés, escribinos y vemos otra alternativa.")
            : false;

        Notification::make()->title('Pedido anulado')
            ->body($enviado ? 'Se le avisó al cliente por WhatsApp.' : 'No se envió aviso al cliente.')->warning()->send();
    }

    private static function textoPagar(WaPedido $p): string
    {
        $caja = ($p->empresa?->modulo_caja_enabled ?? true)
            ? ' y se registrará el cobro de ' . self::gs($p->total) . ' (' . $p->forma_pago . ') en caja'
            : '';

        return 'Se generará la venta en el sistema, se descontará el stock' . $caja . '. El cliente recibirá un aviso por WhatsApp. Esta acción no se puede deshacer desde acá.';
    }

    private static function accionPagar(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('pagar')
            ->label('Marcar como pagado')->icon('heroicon-o-banknotes')->color('success')
            ->requiresConfirmation()
            ->modalHeading(fn (WaPedido $record) => "¿Marcar {$record->numero} como pagado?")
            ->modalDescription(fn (WaPedido $record) => self::textoPagar($record))
            ->modalSubmitActionLabel('Sí, generar la venta')
            ->visible(fn (WaPedido $record) => $record->estado === 'pendiente')
            ->action(fn (WaPedido $record) => self::pagar($record));
    }

    private static function accionAnular(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('anular')
            ->label('Anular')->icon('heroicon-o-x-circle')->color('danger')
            ->form([Textarea::make('motivo')->label('Motivo (se le envía al cliente)')->required()->maxLength(200)])
            ->visible(fn (WaPedido $record) => in_array($record->estado, ['pendiente', 'borrador'], true))
            ->action(fn (WaPedido $record, array $data) => self::anular($record, $data['motivo']));
    }

    public static function accionesCabecera(): array
    {
        return [self::accionPagar(), self::accionAnular()];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('Pedido')->formatStateUsing(fn ($state) => 'PED-' . str_pad((string) $state, 6, '0', STR_PAD_LEFT))->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('nombre_cliente')->label('Cliente')->searchable()->description(fn (WaPedido $r) => '+' . $r->telefono),
                Tables\Columns\TextColumn::make('origen')->label('Origen')->badge()->color(fn ($state) => $state === 'tienda_web' ? 'info' : 'gray')
                    ->formatStateUsing(fn ($state) => $state === 'tienda_web' ? 'Tienda web' : 'Chat'),
                Tables\Columns\TextColumn::make('entrega_tipo')->label('Entrega')->formatStateUsing(fn ($state) => $state === 'envio' ? 'Envío' : 'Retiro')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('forma_pago')->label('Pago')->formatStateUsing(fn ($state) => ucfirst((string) $state)),
                Tables\Columns\TextColumn::make('total')->label('Total')->formatStateUsing(fn ($state) => self::gs($state))->alignEnd()->sortable(),
                Tables\Columns\TextColumn::make('estado')->label('Estado')->badge()->color(fn ($state) => self::colorEstado((string) $state))->formatStateUsing(fn ($state) => self::nombreEstado((string) $state))
                    ->description(fn (WaPedido $r) => $r->venta_invnr ? 'Venta ' . $r->venta_invnr : null),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Ver'),
                Tables\Actions\Action::make('pagar')->label('Pagado')->icon('heroicon-o-banknotes')->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (WaPedido $record) => "¿Marcar {$record->numero} como pagado?")
                    ->modalDescription(fn (WaPedido $record) => self::textoPagar($record))
                    ->modalSubmitActionLabel('Sí, generar la venta')
                    ->visible(fn (WaPedido $record) => $record->estado === 'pendiente')
                    ->action(fn (WaPedido $record) => self::pagar($record)),
                Tables\Actions\Action::make('anular')->label('Anular')->icon('heroicon-o-x-circle')->color('danger')
                    ->form([Textarea::make('motivo')->label('Motivo (se le envía al cliente)')->required()->maxLength(200)])
                    ->visible(fn (WaPedido $record) => in_array($record->estado, ['pendiente', 'borrador'], true))
                    ->action(fn (WaPedido $record, array $data) => self::anular($record, $data['motivo'])),
            ])
            ->emptyStateHeading('No hay pedidos en este período')
            ->emptyStateDescription('Cambiá el período o el estado para ver otros pedidos.')
            ->poll('15s');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Pedido')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('id')->label('Número')->formatStateUsing(fn ($state) => 'PED-' . str_pad((string) $state, 6, '0', STR_PAD_LEFT))->weight('bold'),
                    TextEntry::make('estado')->label('Estado')->badge()->color(fn ($state) => self::colorEstado((string) $state))->formatStateUsing(fn ($state) => self::nombreEstado((string) $state)),
                    TextEntry::make('total')->label('Total')->formatStateUsing(fn ($state) => self::gs($state))->weight('bold'),
                    TextEntry::make('nombre_cliente')->label('Cliente'),
                    TextEntry::make('origen')->label('Origen')->formatStateUsing(fn ($state) => $state === 'tienda_web' ? 'Carrito de la tienda web' : 'Chat con el agente'),
                    TextEntry::make('telefono')->label('Teléfono')->formatStateUsing(fn ($state) => '+' . $state),
                    TextEntry::make('confirmado_at')->label('Confirmado por el cliente')->dateTime('d/m/Y H:i')->placeholder('—'),
                    TextEntry::make('entrega_tipo')->label('Entrega')->formatStateUsing(fn ($state) => $state === 'envio' ? 'Envío a domicilio' : 'Retiro en el local'),
                    TextEntry::make('direccion_entrega')->label('Dirección')->placeholder('—'),
                    TextEntry::make('forma_pago')->label('Forma de pago')->formatStateUsing(fn ($state) => ucfirst((string) $state)),
                    TextEntry::make('requiere_factura')->label('Factura')->formatStateUsing(fn ($state) => $state ? 'Sí' : 'No'),
                    TextEntry::make('ruc')->label('RUC / CI')->placeholder('—'),
                    TextEntry::make('observacion')->label('Aclaración del cliente')->placeholder('—'),
                ]),
            ]),
            Section::make('Productos')->schema([
                RepeatableEntry::make('items')->label('')->schema([
                    Grid::make(4)->schema([
                        TextEntry::make('descr')->label('Producto')->columnSpan(2),
                        TextEntry::make('cantidad')->label('Cantidad')->formatStateUsing(fn ($state) => rtrim(rtrim(number_format((float) $state, 3, ',', '.'), '0'), ',')),
                        TextEntry::make('subtotal')->label('Subtotal')->formatStateUsing(fn ($state) => self::gs($state)),
                    ]),
                ]),
            ]),
            Section::make('Gestión')->schema([
                TextEntry::make('venta_invnr')->label('Venta generada')->placeholder('—'),
                TextEntry::make('pagado_at')->label('Pagado el')->dateTime('d/m/Y H:i')->placeholder('—'),
                TextEntry::make('anulado_at')->label('Anulado el')->dateTime('d/m/Y H:i')->placeholder('—'),
                TextEntry::make('gestor.name')->label('Gestionado por')->placeholder('—'),
                TextEntry::make('nota_interna')->label('Motivo de anulación')->placeholder('—'),
            ])->columns(3),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWaPedidos::route('/'),
            'view'  => Pages\ViewWaPedido::route('/{record}'),
        ];
    }

    public static function urlConversacion(WaPedido $pedido): ?string
    {
        return $pedido->conversacion_id ? ConversacionesWhatsAppPage::getUrl(['c' => $pedido->conversacion_id]) : null;
    }
}
