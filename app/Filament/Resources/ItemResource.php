<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Models\Item;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ItemResource extends Resource
{
    protected static ?string $model           = Item::class;
    protected static ?string $navigationIcon  = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Productos';
    protected static ?string $modelLabel      = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';
    protected static ?int    $navigationSort  = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('scode')
                    ->label('Referencia')
                    ->searchable()
                    ->sortable()
                    ->color('gray'),

                TextColumn::make('descr')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(45)
                    ->tooltip(fn ($record) => $record->descr),

                TextColumn::make('stock_total')
                    ->label('Stock')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state <= 0 => 'danger',
                        $state <= 5 => 'warning',
                        default     => 'success',
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.')),

                TextColumn::make('precio1')
                    ->label('Precio 1')
                    ->alignRight()
                    ->formatStateUsing(fn ($state) => 'Gs. ' . number_format($state, 0, ',', '.')),

                TextColumn::make('precio2')
                    ->label('Precio 2')
                    ->alignRight()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => 'Gs. ' . number_format($state, 0, ',', '.')),

                TextColumn::make('precio3')
                    ->label('Precio 3')
                    ->alignRight()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => 'Gs. ' . number_format($state, 0, ',', '.'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('obs')
                    ->label('Observación')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->obs)
                    ->color('gray')
                    ->placeholder('—'),
            ])
            ->defaultSort('descr')
            ->searchPlaceholder('Buscar por descripción o referencia...')
            ->striped()
            ->actions([
                Action::make('ver_detalle')
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (Item $record) => $record->descr)
                    ->modalDescription(fn (Item $record) => 'Código: ' . $record->item . '  ·  Ref: ' . ($record->scode ?: '—'))
                    ->modalContent(fn (Item $record) => view(
                        'filament.resources.item.detail-modal',
                        [
                            'item'      => $record,
                            'depositos' => DB::connection('delphi')
                                ->table('itemsdepo')
                                ->join('deposito', 'itemsdepo.depo', '=', 'deposito.deponr')
                                ->where('itemsdepo.item', $record->item)
                                ->where('itemsdepo.qty', '>', 0)
                                ->select('deposito.depo_nombre', 'itemsdepo.depo', 'itemsdepo.qty')
                                ->orderByDesc('itemsdepo.qty')
                                ->get(),
                            'proveedor' => DB::connection('delphi')
                                ->table('proveedor')
                                ->where('suppnr', $record->suppnr)
                                ->select('suppname', 'telef1', 'celular1', 'email')
                                ->first(),
                        ]
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalWidth('lg'),
            ])
            ->recordAction('ver_detalle')
            ->paginated([25, 50, 100]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select('items.*')
            ->selectRaw('COALESCE((SELECT SUM(qty) FROM itemsdepo WHERE itemsdepo.item = items.item), 0) AS stock_total');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItems::route('/'),
        ];
    }
}
