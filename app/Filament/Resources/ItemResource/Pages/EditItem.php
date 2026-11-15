<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Services\Productos\FotosProducto;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditItem extends EditRecord
{
    protected static string $resource = ItemResource::class;

    /** Fotos nuevas elegidas en el formulario: se guardan después de grabar el producto. */
    protected array $fotosNuevas = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Las fotos no son columnas de items: van a itemsimage (una fila por foto)
        $this->fotosNuevas = array_values($data['fotos_nuevas'] ?? []);
        unset($data['fotos_nuevas'], $data['image'], $data['fotos_actuales']);

        return $data;
    }

    protected function afterSave(): void
    {
        if (!$this->fotosNuevas) {
            return;
        }

        $r = app(FotosProducto::class)->agregar($this->record->item, $this->fotosNuevas);
        $this->fotosNuevas = [];

        $this->avisarFotos($r);
    }

    /** Elimina una foto del producto en el momento (no hace falta guardar el formulario). */
    public function eliminarFoto(int $nro): void
    {
        $ok = app(FotosProducto::class)->eliminar($this->record->item, $nro);

        Notification::make()
            ->title($ok ? 'Foto eliminada' : 'No se encontró la foto')
            ->{$ok ? 'success' : 'warning'}()
            ->send();
    }

    private function avisarFotos(array $r): void
    {
        if ($r['agregadas'] > 0) {
            Notification::make()->title($r['agregadas'] === 1 ? 'Foto agregada' : "{$r['agregadas']} fotos agregadas")->success()->send();
        }
        if ($r['omitidas'] > 0) {
            Notification::make()->title('Algunas fotos no se agregaron')
                ->body('Cada producto admite hasta ' . FotosProducto::MAXIMO . ' fotos. Eliminá alguna para subir otras.')->warning()->send();
        }
        if ($r['invalidas'] > 0) {
            Notification::make()->title('Hay archivos que no son imágenes válidas')->warning()->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
