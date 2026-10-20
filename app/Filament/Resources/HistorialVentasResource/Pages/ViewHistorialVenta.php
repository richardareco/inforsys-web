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
            \Filament\Actions\Action::make('imprimir')
                ->label('Imprimir ticket')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(fn () => url('/comprobante/' . $this->record->invnr . '/ticket') . '?reimpresion=1')
                ->openUrlInNewTab(),
            \Filament\Actions\Action::make('volver')
                ->label('Volver al listado')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(HistorialVentasResource::getUrl('index')),
        ];
    }
}
