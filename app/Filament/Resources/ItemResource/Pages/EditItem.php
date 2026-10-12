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
        // Guardar la imagen en sesión antes de removerla
        if (!empty($data['image'])) {
            session(['pending_image_file' => $data['image']]);
            Log::info("📸 Imagen guardada en sesión para edición: {$data['image']}");
        }

        // Remover image del data (no pertenece a items table)
        unset($data['image']);

        return $data;
    }

    protected function afterSave(): void
    {
        // Obtener la imagen de la sesión
        $imageFile = session('pending_image_file');

        Log::info("⏳ afterSave - Buscando imagen: " . ($imageFile ?? 'null'));

        if (!empty($imageFile)) {
            try {
                $fullPath = 'products/' . basename($imageFile);

                Log::info("📁 Intentando leer: {$fullPath}");

                if (Storage::disk('local')->exists($fullPath)) {
                    $content = Storage::disk('local')->get($fullPath);
                    $size = strlen($content);

                    Log::info("✅ Archivo leído: {$size} bytes");

                    ItemImage::updateOrCreate(
                        ['item' => $this->record->item],
                        ['image' => $content]
                    );

                    Log::info("🎉 Foto actualizada en itemsimage para item: {$this->record->item}");
                } else {
                    Log::error("❌ Archivo no encontrado en: {$fullPath}");
                }
            } catch (\Exception $e) {
                Log::error("💥 Error al guardar foto: " . $e->getMessage());
            }

            session()->forget('pending_image_file');
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
