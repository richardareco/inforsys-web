<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Models\Item;
use App\Models\ItemImage;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CreateItem extends CreateRecord
{
    protected static string $resource = ItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::info("🔵 mutateFormDataBeforeCreate - Data recibida:", $data);

        // Generar siguiente item (último ID + 1)
        $lastItem = DB::connection('delphi')
            ->table('items')
            ->orderByRaw('CAST(item AS UNSIGNED) DESC')
            ->select('item')
            ->first();

        $nextId = $lastItem ? ((int)$lastItem->item) + 1 : 1;
        $data['item'] = str_pad($nextId, 7, '0', STR_PAD_LEFT);
        Log::info("🔵 Item ID generado: {$data['item']}");

        // Manejar cache key para imagen si existe
        $cacheKey = session('pending_image_key');
        if (!empty($cacheKey)) {
            Log::info("🔵 Cache key encontrada: {$cacheKey}");
            session(['pending_image_key' => $cacheKey]);
        }

        // Remover image del data (no pertenece a items table, se maneja en callback)
        unset($data['image']);

        Log::info("🔵 Data antes de crear:", $data);
        return $data;
    }

    protected function afterCreate(): void
    {
        $cacheKey = session('pending_image_key');

        Log::info("⏳ afterCreate - Buscando imagen en cache: {$cacheKey}");

        if (!empty($cacheKey)) {
            try {
                $imageData = \Illuminate\Support\Facades\Cache::get($cacheKey);

                if ($imageData && isset($imageData['content'])) {
                    $content = $imageData['content'];
                    $size = strlen($content);

                    Log::info("✅ Imagen encontrada: {$size} bytes");

                    ItemImage::create([
                        'item' => $this->record->item,
                        'image' => $content,
                    ]);

                    Log::info("🎉 Foto guardada en itemsimage para item: {$this->record->item}");
                } else {
                    Log::error("❌ Datos no encontrados en cache: {$cacheKey}");
                }
            } catch (\Exception $e) {
                Log::error("💥 Error al guardar foto: " . $e->getMessage());
            }

            // Limpiar cache y sesión
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
            session()->forget('pending_image_key');
        }
    }
}
