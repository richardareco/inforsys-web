<?php

namespace App\Filament\Resources\MetaResource\Pages;

use App\Filament\Resources\MetaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMeta extends CreateRecord
{
    protected static string $resource = MetaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['empresa_id'] = Auth::user()->empresa_id;
        $data['mes'] = \Carbon\Carbon::parse($data['mes'])->startOfMonth()->format('Y-m-d');

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
