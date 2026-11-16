<?php

namespace App\Filament\Resources\MetaResource\Pages;

use App\Filament\Resources\MetaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListMetas extends ListRecords
{
    protected static string $resource = MetaResource::class;

    public function getTabs(): array
    {
        return [
            'todas'    => Tab::make('Todas'),
            'empresa'  => Tab::make('De la empresa')->icon('heroicon-m-building-office-2')->modifyQueryUsing(fn (Builder $query) => $query->where('personal_id', '')),
            'vendedor' => Tab::make('Por vendedor')->icon('heroicon-m-user')->modifyQueryUsing(fn (Builder $query) => $query->where('personal_id', '!=', '')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
