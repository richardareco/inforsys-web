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
        // Generar siguiente item (último ID + 1)
        $lastItem = DB::connection('delphi')
            ->table('items')
            ->orderByRaw('CAST(item AS UNSIGNED) DESC')
            ->select('item')
            ->first();

        $nextId = $lastItem ? ((int)$lastItem->item) + 1 : 1;
        $data['item'] = str_pad($nextId, 7, '0', STR_PAD_LEFT);

        // Guardar la imagen path en sesión SOLO SI es string (ya procesado por Filament)
        if (!empty($data['image']) && is_string($data['image'])) {
            session(['pending_image_file' => $data['image']]);
            Log::info("📸 Imagen path guardado en sesión: {$data['image']}");
        } else {
            Log::warning("⚠️ Image no es string o está vacío: " . gettype($data['image'] ?? null));
        }

        // Remover image del data (no pertenece a items table)
        unset($data['image']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Obtener la imagen de la sesión
        $imageFile = session('pending_image_file');

        Log::info("⏳ afterCreate - Buscando imagen: " . ($imageFile ?? 'null'));

        if (!empty($imageFile)) {
            try {
                $fullPath = 'products/' . basename($imageFile);

                Log::info("📁 Intentando leer: {$fullPath}");

                if (Storage::disk('local')->exists($fullPath)) {
                    $content = Storage::disk('local')->get($fullPath);
                    $size = strlen($content);

                    Log::info("✅ Archivo leído: {$size} bytes");

                    ItemImage::create([
                        'item' => $this->record->item,
                        'image' => $content,
                    ]);

                    Log::info("🎉 Foto guardada en itemsimage para item: {$this->record->item}");
                } else {
                    Log::error("❌ Archivo no encontrado en: {$fullPath}");
                }
            } catch (\Exception $e) {
                Log::error("💥 Error al guardar foto: " . $e->getMessage());
            }

            session()->forget('pending_image_file');
        }
    }
}
