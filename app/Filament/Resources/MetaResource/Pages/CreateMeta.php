<?php

namespace App\Filament\Resources\MetaResource\Pages;

use App\Filament\Resources\MetaResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CreateMeta extends CreateRecord
{
    protected static string $resource = MetaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['empresa_id']  = Auth::user()->empresa_id;
        $data['mes']         = \Carbon\Carbon::parse($data['mes'])->startOfMonth()->format('Y-m-d');
        $data['personal_id'] = (string) ($data['personal_id'] ?? '');

        if (MetaResource::existeMeta($data['empresa_id'], $data['mes'], $data['personal_id'])) {
            Notification::make()->title('Ya hay una meta para ese mes y ese vendedor')->body('Editá la existente en lugar de crear otra.')->danger()->send();
            $this->halt();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
