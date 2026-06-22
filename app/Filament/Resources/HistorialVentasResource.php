<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HistorialVentasResource\Pages;
use App\Models\Venta;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\DatePicker;

class HistorialVentasResource extends Resource
{
    protected static ?string $model           = Venta::class;
    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Historial de Ventas';
    protected static ?string $modelLabel      = 'Venta';
    protected static ?string $pluralModelLabel = 'Historial de Ventas';
    protected static ?string $navigationGroup = 'Ventas';
    protected static ?int    $navigationSort  = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select(
                'nro',
                'invnr',
                'fecha',
                'nro_factura',
                'custnr',
                'cajanr',
                'total',
                'saldo',
                'acredito',
                'pernr',
                DB::connection('delphi')->raw(
                    '(SELECT custname FROM cliente WHERE cliente.custnr = invo1.custnr LIMIT 1) as custname'
                )
            )
            ->where('flag', '4')
            ->orderByDesc('fecha')
            ->orderByDesc('invnr');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invnr')
                    ->label('Nro. Venta')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('nro_factura')
                    ->label('Factura')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('custnr')
                    ->label('Cod.')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('custname')
                    ->label('Cliente')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Gs. ' . number_format($state, 0, ',', '.'))
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('saldo')
                    ->label('Saldo')
                    ->formatStateUsing(fn ($state) => $state > 0 ? 'Gs. ' . number_format($state, 0, ',', '.') : '—')
                    ->alignRight()
                    ->color(fn ($state) => $state > 0 ? 'danger' : null),

                TextColumn::make('condicion')
                    ->label('Condición')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->acredito > 0 ? 'credito' : 'contado')
                    ->formatStateUsing(fn ($state) => $state === 'credito' ? 'Crédito' : 'Contado')
                    ->color(fn ($state) => $state === 'credito' ? 'warning' : 'info'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if ($record->acredito > 0) {
                            return $record->saldo == 0 ? 'pagado' : 'pendiente';
                        }
                        return $record->cajanr ? 'pagado' : 'pendiente';
                    })
                    ->formatStateUsing(fn ($state) => $state === 'pagado' ? 'Pagado' : 'Pendiente')
                    ->color(fn ($state) => $state === 'pagado' ? 'success' : 'danger'),

                TextColumn::make('cajanr')
                    ->label('Caja Nro.')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('fecha')
                    ->form([
                        DatePicker::make('desde')->label('Desde')->native(false),
                        DatePicker::make('hasta')->label('Hasta')->native(false),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['desde']) $query->whereDate('fecha', '>=', $data['desde']);
                        if ($data['hasta']) $query->whereDate('fecha', '<=', $data['hasta']);
                    }),

                SelectFilter::make('condicion')
                    ->label('Condición')
                    ->options([
                        'contado' => 'Contado',
                        'credito' => 'Crédito',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!$data['value']) return;
                        if ($data['value'] === 'contado') {
                            $query->where(fn ($q) => $q->whereNull('acredito')->orWhere('acredito', 0));
                        }
                        if ($data['value'] === 'credito') {
                            $query->where('acredito', '>', 0);
                        }
                    }),

                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'pagado'    => 'Pagado',
                        'pendiente' => 'Pendiente',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!$data['value']) return;
                        if ($data['value'] === 'pagado') {
                            $query->where(function ($q) {
                                // Crédito pagado: acredito > 0 y saldo = 0
                                $q->where(fn ($s) => $s->where('acredito', '>', 0)->where('saldo', 0))
                                // Contado pagado: acredito = 0 y tiene cajanr
                                  ->orWhere(fn ($s) => $s->where(fn ($t) => $t->whereNull('acredito')->orWhere('acredito', 0))
                                                         ->whereNotNull('cajanr')->where('cajanr', '!=', ''));
                            });
                        }
                        if ($data['value'] === 'pendiente') {
                            $query->where(function ($q) {
                                // Crédito pendiente: acredito > 0 y saldo > 0
                                $q->where(fn ($s) => $s->where('acredito', '>', 0)->where('saldo', '>', 0))
                                // Contado pendiente: acredito = 0 y sin cajanr
                                  ->orWhere(fn ($s) => $s->where(fn ($t) => $t->whereNull('acredito')->orWhere('acredito', 0))
                                                         ->where(fn ($t) => $t->whereNull('cajanr')->orWhere('cajanr', '')));
                            });
                        }
                    }),
            ])
            ->actions([
                ViewAction::make()->label('Ver detalle'),
            ])
            ->defaultSort('fecha', 'desc')
            ->striped();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Datos de la Venta')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('invnr')->label('Nro. Venta')->weight('bold'),
                        TextEntry::make('fecha')->label('Fecha')->date('d/m/Y'),
                        TextEntry::make('nro_factura')->label('Nro. Factura')->placeholder('—'),
                        TextEntry::make('cajanr')->label('Caja Nro.')->placeholder('—'),
                        TextEntry::make('custname')->label('Cliente'),
                        TextEntry::make('custnr')->label('Cód. Cliente'),
                        TextEntry::make('total')
                            ->label('Total')
                            ->formatStateUsing(fn ($state) => 'Gs. ' . number_format($state, 0, ',', '.')),
                        TextEntry::make('saldo')
                            ->label('Saldo Pendiente')
                            ->formatStateUsing(fn ($state) => $state > 0 ? 'Gs. ' . number_format($state, 0, ',', '.') : 'Sin saldo')
                            ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                    ]),

                Section::make('Ítems de la Venta')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('item')->label('Código'),
                                TextEntry::make('descr')->label('Descripción'),
                                TextEntry::make('qty')
                                    ->label('Cantidad')
                                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.')),
                                TextEntry::make('precio')
                                    ->label('Precio Unit.')
                                    ->formatStateUsing(fn ($state) => 'Gs. ' . number_format($state, 0, ',', '.')),
                                TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->weight('bold')
                                    ->formatStateUsing(fn ($state) => 'Gs. ' . number_format($state, 0, ',', '.')),
                            ])
                            ->columns(5)
                            ->getStateUsing(function ($record) {
                                return DB::connection('delphi')
                                    ->table('invo2')
                                    ->where('invnr', $record->invnr)
                                    ->select('item', 'descr', 'qty', 'precio',
                                        DB::raw('qty * precio as subtotal'))
                                    ->get()
                                    ->map(fn ($r) => (array) $r)
                                    ->toArray();
                            }),
                    ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHistorialVentas::route('/'),
            'view'  => Pages\ViewHistorialVenta::route('/{record}'),
        ];
    }
}
