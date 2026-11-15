<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListItems extends ListRecords
{
    protected static string $resource = ItemResource::class;

    /** La tabla es ancha (precios, costo, stock): usa todo el ancho para que no se esconda la columna de acciones. */
    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->label('Crear Producto'),
        ];
    }
}
