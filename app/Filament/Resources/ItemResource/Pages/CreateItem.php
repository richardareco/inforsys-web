<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Models\Item;
use App\Models\ItemImage;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CreateItem extends CreateRecord
{
    protected static string $resource = ItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generar siguiente item (último ID + 1)
        $lastItem = DB::connection('delphi')
            ->table('items')
            ->orderByRaw('CAST(item AS UNSIGNED) DESC')
            ->select('item')
            ->first();

        $nextId = $lastItem ? ((int)$lastItem->item) + 1 : 1;
        $data['item'] = str_pad($nextId, 7, '0', STR_PAD_LEFT);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (!empty($this->form->getRawState()['image'])) {
            $imagePath = $this->form->getRawState()['image'];

            if (Storage::disk('local')->exists($imagePath)) {
                $imageContent = Storage::disk('local')->get($imagePath);

                ItemImage::create([
                    'item' => $this->record->item,
                    'image' => $imageContent,
                ]);
            }
        }
    }
}
