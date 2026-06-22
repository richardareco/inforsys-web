<?php

namespace App\Filament\Resources\PresupuestoResource\Pages;

use App\Filament\Pages\PresupuestoCrearPage;
use App\Filament\Resources\PresupuestoResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListPresupuestos extends ListRecords
{
    protected static string $resource = PresupuestoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('nuevo')
                ->label('Nuevo Presupuesto')
                ->icon('heroicon-o-plus')
                ->url(PresupuestoCrearPage::getUrl()),
        ];
    }
}
