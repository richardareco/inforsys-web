<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Models\Item;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListItems extends ListRecords
{
    protected static string $resource = ItemResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->query(Item::query())
            ->columns([
                TextColumn::make('item')
                    ->label('Código')
                    ->sortable()
                    ->url(fn (Item $record): string => "http://localhost:8000/admin/items/{$record->item}/edit")
                    ->color('info'),

                TextColumn::make('descr')
                    ->label('Descripción')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('precio1')
                    ->label('Precio 1'),

                TextColumn::make('precio2')
                    ->label('Precio 2'),
            ])
            ->paginated([25, 50, 100]);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('create')
                ->label('New Producto')
                ->button()
                ->color('primary')
                ->url('http://localhost:8000/admin/items/create'),
        ];
    }
}
