<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Models\ItemImage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

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
        // Guardar ruta de imagen temporalmente
        if (!empty($data['image'])) {
            session(['pending_item_image' => $data['image']]);
        }

        // Remover image del data para que no intente guardarse en items table
        unset($data['image']);

        return $data;
    }

    protected function afterSave(): void
    {
        $imagePath = session('pending_item_image');

        if (!empty($imagePath)) {
            if (Storage::disk('local')->exists($imagePath)) {
                $imageContent = Storage::disk('local')->get($imagePath);

                ItemImage::updateOrCreate(
                    ['item' => $this->record->item],
                    ['image' => $imageContent]
                );
            }
            session()->forget('pending_item_image');
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
