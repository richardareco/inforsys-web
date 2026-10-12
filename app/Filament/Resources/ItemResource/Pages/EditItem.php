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

    protected function afterSave(): void
    {
        if (!empty($this->form->getRawState()['image'])) {
            $imagePath = $this->form->getRawState()['image'];

            if (Storage::disk('local')->exists($imagePath)) {
                $imageContent = Storage::disk('local')->get($imagePath);

                ItemImage::updateOrCreate(
                    ['item' => $this->record->item],
                    ['image' => $imageContent]
                );
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
