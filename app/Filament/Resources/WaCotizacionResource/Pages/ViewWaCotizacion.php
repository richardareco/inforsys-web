<?php

namespace App\Filament\Resources\WaCotizacionResource\Pages;

use App\Filament\Resources\WaCotizacionResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewWaCotizacion extends ViewRecord
{
    protected static string $resource = WaCotizacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')->label('Ver PDF')->icon('heroicon-o-document-arrow-down')->color('primary')
                ->url(fn () => WaCotizacionResource::urlPdf($this->record))->openUrlInNewTab(),
            Action::make('volver')->label('Volver')->icon('heroicon-o-arrow-left')->color('gray')
                ->url(WaCotizacionResource::getUrl('index')),
        ];
    }
}
