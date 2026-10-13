<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Models\ItemImage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EditItem extends EditRecord
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remover image del data (no pertenece a items table, se maneja en callback)
        unset($data['image']);
        return $data;
    }

    protected function afterSave(): void
    {
        $cacheKey = session('pending_image_key');

        if (!empty($cacheKey)) {
            Log::info("⏳ afterSave - Limpiando cache de imagen: {$cacheKey}");
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
            session()->forget('pending_image_key');
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
