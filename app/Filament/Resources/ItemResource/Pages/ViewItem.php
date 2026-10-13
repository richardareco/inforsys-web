<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewItem extends ViewRecord
{
    protected static string $resource = ItemResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información Básica')
                    ->schema([
                        Infolists\Components\TextEntry::make('item')
                            ->label('Código Producto'),
                        Infolists\Components\TextEntry::make('scode')
                            ->label('Referencia'),
                        Infolists\Components\TextEntry::make('scode1')
                            ->label('Código de Barra'),
                        Infolists\Components\TextEntry::make('descr')
                            ->label('Descripción'),
                    ])->columns(2),

                Infolists\Components\Section::make('Precios')
                    ->schema([
                        Infolists\Components\TextEntry::make('precio1')
                            ->label('Precio 1')
                            ->formatStateUsing(fn($state) => 'Gs. ' . number_format($state, 0, ',', '.')),
                        Infolists\Components\TextEntry::make('precio2')
                            ->label('Precio 2')
                            ->formatStateUsing(fn($state) => 'Gs. ' . number_format($state, 0, ',', '.')),
                        Infolists\Components\TextEntry::make('precio3')
                            ->label('Precio 3')
                            ->formatStateUsing(fn($state) => 'Gs. ' . number_format($state, 0, ',', '.')),
                    ])->columns(3),

                Infolists\Components\Section::make('Stock por Depósito')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('depos')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('depo')
                                    ->label('Depósito')
                                    ->getStateUsing(fn($record) => strtoupper($record->depo ?? '')),
                                Infolists\Components\TextEntry::make('xqty')
                                    ->label('Cantidad')
                                    ->getStateUsing(fn($record) => number_format($record->xqty, 0, ',', '.')),
                            ])->columns(2),
                    ]),
            ]);
    }
}
