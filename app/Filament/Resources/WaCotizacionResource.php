<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WaCotizacionResource\Pages;
use App\Models\WaCotizacion;
use App\Traits\CheckUserAcceso;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class WaCotizacionResource extends Resource
{
    use CheckUserAcceso;

    protected static ?string $model = WaCotizacion::class;

    protected static ?string $navigationIcon   = 'heroicon-o-document-text';
    protected static ?string $navigationLabel  = 'Cotizaciones WhatsApp';
    protected static ?string $modelLabel       = 'cotización';
    protected static ?string $pluralModelLabel = 'Cotizaciones de WhatsApp';
    protected static ?string $navigationGroup  = 'Ventas';
    protected static ?int    $navigationSort   = 10;
    protected static ?string $slug             = 'cotizaciones-whatsapp';

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

    public static function getEloquentQuery(): Builder
    {
        $user  = Auth::user();
        $query = parent::getEloquentQuery();

        if ($user?->empresa_id) {
            return $query->where('empresa_id', $user->empresa_id);
        }

        return $user?->isSuperAdmin() ? $query : $query->whereRaw('1 = 0');
    }

    public static function urlPdf(WaCotizacion $cot): string
    {
        return URL::temporarySignedRoute('wa.cotizacion.pdf', now()->addHour(), ['cotizacion' => $cot->id]);
    }

    private static function gs($v): string
    {
        return 'Gs. ' . number_format((float) $v, 0, ',', '.');
    }

    private static function color(string $estado): string
    {
        return match ($estado) {
            'aceptada' => 'success',
            'cancelada' => 'danger',
            default => 'info',
        };
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('Cotización')->formatStateUsing(fn ($state) => 'COT-' . str_pad((string) $state, 6, '0', STR_PAD_LEFT))->weight('bold'),
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('contacto.nombre')->label('Cliente')->placeholder('Cliente')
                    ->description(fn (WaCotizacion $r) => '+' . $r->contacto->telefono)
                    ->searchable(query: fn (Builder $q, string $search) => $q->whereHas('contacto', fn ($c) => $c->where('nombre', 'like', "%{$search}%")->orWhere('telefono', 'like', "%{$search}%"))),
                Tables\Columns\TextColumn::make('items_count')->label('Productos')->counts('items')->alignCenter(),
                Tables\Columns\TextColumn::make('total')->label('Total')->formatStateUsing(fn ($state) => self::gs($state))->alignEnd()->sortable(),
                Tables\Columns\TextColumn::make('estado')->label('Estado')->badge()->color(fn ($state) => self::color((string) $state))
                    ->formatStateUsing(fn ($state) => ['enviada' => 'Enviada', 'aceptada' => 'Convertida en pedido', 'cancelada' => 'Cancelada'][$state] ?? $state),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')->options(['enviada' => 'Enviada', 'aceptada' => 'Convertida en pedido', 'cancelada' => 'Cancelada']),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Ver'),
                Tables\Actions\Action::make('pdf')->label('PDF')->icon('heroicon-o-document-arrow-down')->color('gray')
                    ->url(fn (WaCotizacion $r) => self::urlPdf($r))->openUrlInNewTab(),
            ])
            ->emptyStateHeading('Todavía no hay cotizaciones')
            ->poll('30s');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Cotización')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('id')->label('Número')->formatStateUsing(fn ($state) => 'COT-' . str_pad((string) $state, 6, '0', STR_PAD_LEFT))->weight('bold'),
                    TextEntry::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i'),
                    TextEntry::make('total')->label('Total')->formatStateUsing(fn ($state) => self::gs($state))->weight('bold'),
                    TextEntry::make('contacto.nombre')->label('Cliente')->placeholder('Cliente'),
                    TextEntry::make('contacto.telefono')->label('Teléfono')->formatStateUsing(fn ($state) => '+' . $state),
                    TextEntry::make('estado')->label('Estado')->badge()->color(fn ($state) => self::color((string) $state))
                        ->formatStateUsing(fn ($state) => ['enviada' => 'Enviada', 'aceptada' => 'Convertida en pedido', 'cancelada' => 'Cancelada'][$state] ?? $state),
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
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWaCotizaciones::route('/'),
            'view'  => Pages\ViewWaCotizacion::route('/{record}'),
        ];
    }
}
