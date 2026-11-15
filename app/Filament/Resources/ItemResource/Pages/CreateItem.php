<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Services\Productos\FotosProducto;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateItem extends CreateRecord
{
    protected static string $resource = ItemResource::class;

    /** Fotos elegidas en el formulario: se guardan cuando el producto ya tiene su código. */
    protected array $fotosNuevas = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generar siguiente item (último ID + 1)
        $lastItem = DB::connection('delphi')
            ->table('items')
            ->orderByRaw('CAST(item AS UNSIGNED) DESC')
            ->select('item')
            ->first();

        $nextId = $lastItem ? ((int) $lastItem->item) + 1 : 1;
        $data['item'] = str_pad($nextId, 7, '0', STR_PAD_LEFT);

        // Las fotos no son columnas de items: van a itemsimage (una fila por foto)
        $this->fotosNuevas = array_values($data['fotos_nuevas'] ?? []);
        unset($data['fotos_nuevas'], $data['image'], $data['fotos_actuales']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (!$this->fotosNuevas) {
            return;
        }

        $r = app(FotosProducto::class)->agregar($this->record->item, $this->fotosNuevas);
        $this->fotosNuevas = [];

        if ($r['agregadas'] > 0) {
            Notification::make()->title($r['agregadas'] === 1 ? 'Foto agregada' : "{$r['agregadas']} fotos agregadas")->success()->send();
        }
        if ($r['invalidas'] > 0) {
            Notification::make()->title('Hay archivos que no son imágenes válidas')->warning()->send();
        }
    }
}
