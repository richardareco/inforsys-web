<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\ItemImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ItemObserver
{
    public function saved(Item $item): void
    {
        // Intentar obtener la imagen de la sesión (guardada por Create/EditItem)
        $imagePath = session('pending_item_image');

        if (!empty($imagePath)) {
            try {
                if (Storage::disk('local')->exists($imagePath)) {
                    $imageContent = Storage::disk('local')->get($imagePath);
                    $imageSize = strlen($imageContent);

                    // Solo guardar si el archivo tiene contenido
                    if ($imageSize > 0) {
                        ItemImage::updateOrCreate(
                            ['item' => $item->item],
                            ['image' => $imageContent]
                        );

                        Log::info("✅ Foto guardada para item {$item->item}: {$imageSize} bytes");
                    }
                } else {
                    Log::warning("⚠️ Archivo no existe: {$imagePath}");
                }
            } catch (\Exception $e) {
                Log::error("❌ Error guardando foto para item {$item->item}: " . $e->getMessage());
            }

            session()->forget('pending_item_image');
        }
    }

    public function deleting(Item $item): void
    {
        // Eliminar foto asociada
        ItemImage::where('item', $item->item)->delete();
    }
}
