<?php

namespace App\Filament\Resources;

use App\Filament\Pages\ConversacionesWhatsAppPage;
use App\Filament\Resources\WaPedidoResource\Pages;
use App\Models\WaMensaje;
use App\Models\WaPedido;
use App\Services\WhatsApp\EnvioWhatsApp;
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

    /** Solo los pedidos de la empresa del usuario; los borradores (sin confirmar) no se muestran. */
    public static function getEloquentQuery(): Builder
    {
        $user  = Auth::user();
        $query = parent::getEloquentQuery()->where('estado', '!=', 'borrador');

        if ($user?->empresa_id) {
            return $query->where('empresa_id', $user->empresa_id);
        }

        return $user?->isSuperAdmin() ? $query : $query->whereRaw('1 = 0');
    }

    private static function colorEstado(string $estado): string
    {
        return match ($estado) {
            'pendiente' => 'warning',
            'aprobado', 'convertido' => 'success',
            'rechazado', 'cancelado' => 'danger',
            default => 'gray',
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

    public static function aprobar(WaPedido $pedido): void
    {
        if ($pedido->estado !== 'pendiente') {
            return;
        }
        $pedido->update(['estado' => 'aprobado', 'gestionado_por' => Auth::id(), 'gestionado_at' => now()]);

        $enviado = self::avisarCliente($pedido, "¡Tu pedido {$pedido->numero} fue confirmado por nuestro equipo! Total: " . self::gs($pedido->total) . '. Gracias por tu compra.');
        Notification::make()->title('Pedido aprobado')
            ->body($enviado ? 'Se le avisó al cliente por WhatsApp.' : 'No se pudo avisar al cliente (pasaron más de 24 horas): escribile desde otro medio.')
            ->success()->send();
    }

    public static function rechazar(WaPedido $pedido, string $motivo): void
    {
        if ($pedido->estado !== 'pendiente') {
            return;
        }
        $pedido->update([
            'estado' => 'rechazado', 'gestionado_por' => Auth::id(), 'gestionado_at' => now(), 'nota_interna' => $motivo,
        ]);

        $enviado = self::avisarCliente($pedido, "Lamentablemente no pudimos confirmar tu pedido {$pedido->numero}: {$motivo}. Si querés, escribinos y vemos otra alternativa.");
        Notification::make()->title('Pedido rechazado')
            ->body($enviado ? 'Se le avisó al cliente por WhatsApp.' : 'No se pudo avisar al cliente (pasaron más de 24 horas).')
            ->warning()->send();
    }

    private static function accionAprobar(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('aprobar')
            ->label('Aprobar')->icon('heroicon-o-check-circle')->color('success')
            ->requiresConfirmation()->modalDescription('Se le avisará al cliente que su pedido fue confirmado.')
            ->visible(fn (WaPedido $record) => $record->estado === 'pendiente')
            ->action(fn (WaPedido $record) => self::aprobar($record));
    }

    private static function accionRechazar(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('rechazar')
            ->label('Rechazar')->icon('heroicon-o-x-circle')->color('danger')
            ->form([Textarea::make('motivo')->label('Motivo (se le envía al cliente)')->required()->maxLength(200)])
            ->visible(fn (WaPedido $record) => $record->estado === 'pendiente')
            ->action(fn (WaPedido $record, array $data) => self::rechazar($record, $data['motivo']));
    }

    public static function accionesCabecera(): array
    {
        return [self::accionAprobar(), self::accionRechazar()];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('Pedido')->formatStateUsing(fn ($state) => 'PED-' . str_pad((string) $state, 6, '0', STR_PAD_LEFT))->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('nombre_cliente')->label('Cliente')->searchable()->description(fn (WaPedido $r) => '+' . $r->telefono),
                Tables\Columns\TextColumn::make('entrega_tipo')->label('Entrega')->formatStateUsing(fn ($state) => $state === 'envio' ? 'Envío' : 'Retiro')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('forma_pago')->label('Pago')->formatStateUsing(fn ($state) => ucfirst((string) $state)),
                Tables\Columns\TextColumn::make('total')->label('Total')->formatStateUsing(fn ($state) => self::gs($state))->alignEnd()->sortable(),
                Tables\Columns\TextColumn::make('estado')->label('Estado')->badge()->color(fn ($state) => self::colorEstado((string) $state))->formatStateUsing(fn ($state) => ucfirst((string) $state)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')->options([
                    'pendiente' => 'Pendiente', 'aprobado' => 'Aprobado', 'rechazado' => 'Rechazado', 'cancelado' => 'Cancelado', 'convertido' => 'Convertido en venta',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Ver'),
                Tables\Actions\Action::make('aprobar')->label('Aprobar')->icon('heroicon-o-check-circle')->color('success')
                    ->requiresConfirmation()->modalDescription('Se le avisará al cliente que su pedido fue confirmado.')
                    ->visible(fn (WaPedido $record) => $record->estado === 'pendiente')
                    ->action(fn (WaPedido $record) => self::aprobar($record)),
                Tables\Actions\Action::make('rechazar')->label('Rechazar')->icon('heroicon-o-x-circle')->color('danger')
                    ->form([Textarea::make('motivo')->label('Motivo (se le envía al cliente)')->required()->maxLength(200)])
                    ->visible(fn (WaPedido $record) => $record->estado === 'pendiente')
                    ->action(fn (WaPedido $record, array $data) => self::rechazar($record, $data['motivo'])),
            ])
            ->emptyStateHeading('Todavía no hay pedidos')
            ->emptyStateDescription('Cuando un cliente confirme un pedido por WhatsApp va a aparecer acá.')
            ->poll('15s');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Pedido')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('id')->label('Número')->formatStateUsing(fn ($state) => 'PED-' . str_pad((string) $state, 6, '0', STR_PAD_LEFT))->weight('bold'),
                    TextEntry::make('estado')->label('Estado')->badge()->color(fn ($state) => self::colorEstado((string) $state))->formatStateUsing(fn ($state) => ucfirst((string) $state)),
                    TextEntry::make('total')->label('Total')->formatStateUsing(fn ($state) => self::gs($state))->weight('bold'),
                    TextEntry::make('nombre_cliente')->label('Cliente'),
                    TextEntry::make('telefono')->label('Teléfono')->formatStateUsing(fn ($state) => '+' . $state),
                    TextEntry::make('confirmado_at')->label('Confirmado por el cliente')->dateTime('d/m/Y H:i'),
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
                TextEntry::make('gestor.name')->label('Gestionado por')->placeholder('—'),
                TextEntry::make('gestionado_at')->label('Fecha')->dateTime('d/m/Y H:i')->placeholder('—'),
                TextEntry::make('nota_interna')->label('Motivo / nota interna')->placeholder('—'),
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
