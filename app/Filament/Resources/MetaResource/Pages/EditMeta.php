<?php

namespace App\Filament\Resources\MetaResource\Pages;

use App\Filament\Resources\MetaResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditMeta extends EditRecord
{
    protected static string $resource = MetaResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['empresa_id']  = Auth::user()->empresa_id;
        $data['mes']         = \Carbon\Carbon::parse($data['mes'])->startOfMonth()->format('Y-m-d');
        $data['personal_id'] = (string) ($data['personal_id'] ?? '');

        if (MetaResource::existeMeta($data['empresa_id'], $data['mes'], $data['personal_id'], $this->record->id)) {
            Notification::make()->title('Ya hay una meta para ese mes y ese vendedor')->danger()->send();
            $this->halt();
        }

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
