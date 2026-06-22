<?php

namespace App\Filament\Resources\HistorialVentasResource\Pages;

use App\Filament\Resources\HistorialVentasResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewHistorialVenta extends ViewRecord
{
    protected static string $resource = HistorialVentasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('volver')
                ->label('Volver al listado')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(HistorialVentasResource::getUrl('index')),
        ];
    }
}
