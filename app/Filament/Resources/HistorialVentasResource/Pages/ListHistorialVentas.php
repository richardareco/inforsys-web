<?php

namespace App\Filament\Resources\HistorialVentasResource\Pages;

use App\Filament\Resources\HistorialVentasResource;
use Filament\Resources\Pages\ListRecords;

class ListHistorialVentas extends ListRecords
{
    protected static string $resource = HistorialVentasResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
