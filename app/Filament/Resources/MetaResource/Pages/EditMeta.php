<?php

namespace App\Filament\Resources\MetaResource\Pages;

use App\Filament\Resources\MetaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditMeta extends EditRecord
{
    protected static string $resource = MetaResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['empresa_id'] = Auth::user()->empresa_id;
        $data['mes'] = \Carbon\Carbon::parse($data['mes'])->startOfMonth()->format('Y-m-d');

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
