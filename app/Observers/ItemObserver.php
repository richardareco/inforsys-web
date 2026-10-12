<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\ItemImage;
use Illuminate\Support\Facades\Storage;

class ItemObserver
{
    public function saved(Item $item): void
    {
        // Si hay 'image' en el request, guardar en itemsimage
        if (request()->has('data.image') && !empty(request()->input('data.image'))) {
            $imagePath = request()->input('data.image');

            if (Storage::disk('local')->exists($imagePath)) {
                $imageContent = Storage::disk('local')->get($imagePath);

                ItemImage::updateOrCreate(
                    ['item' => $item->item],
                    ['image' => $imageContent]
                );
            }
        }
    }

    public function deleting(Item $item): void
    {
        // Eliminar foto asociada
        ItemImage::where('item', $item->item)->delete();
    }
}
