<?php

namespace App\Services\Productos;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Fotos de un producto (tabla Delphi itemsimage: varias filas por producto, la de menor nro es la principal).
 * Cada foto subida se achica y comprime antes de guardarse en la base.
 */
class FotosProducto
{
    /** Máximo de fotos por producto (cada una se guarda dentro de la base). */
    public const MAXIMO = 10;

    private const LADO = 600;

    private const CONEXION = 'delphi';

    /** @return array<int, int> nro de cada foto, la principal primero */
    public function listar(string $item): array
    {
        return DB::connection(self::CONEXION)->table('itemsimage')
            ->where('item', $item)->whereNotNull('image')
            ->orderBy('nro')->pluck('nro')->map(fn ($n) => (int) $n)->all();
    }

    /**
     * Agrega fotos nuevas hasta completar el máximo.
     *
     * @param  array<int, UploadedFile|\Livewire\Features\SupportFileUploads\TemporaryUploadedFile>  $archivos
     * @return array{agregadas: int, omitidas: int, invalidas: int}
     */
    public function agregar(string $item, array $archivos): array
    {
        $lugar = max(0, self::MAXIMO - count($this->listar($item)));
        $agregadas = $omitidas = $invalidas = 0;

        foreach ($archivos as $archivo) {
            if (!$archivo instanceof UploadedFile) {
                continue;
            }
            if ($agregadas >= $lugar) {
                $omitidas++;
                continue;
            }

            $contenido = @file_get_contents($archivo->getRealPath());
            $procesada = $contenido === false ? null : $this->procesar($contenido);
            if ($procesada === null) {
                $invalidas++;
                continue;
            }

            DB::connection(self::CONEXION)->table('itemsimage')->insert(['item' => $item, 'image' => $procesada]);
            $agregadas++;
        }

        return ['agregadas' => $agregadas, 'omitidas' => $omitidas, 'invalidas' => $invalidas];
    }

    /** Elimina una foto del producto (solo si es de ese producto). */
    public function eliminar(string $item, int $nro): bool
    {
        return DB::connection(self::CONEXION)->table('itemsimage')->where('item', $item)->where('nro', $nro)->delete() > 0;
    }

    /**
     * Achica la imagen a un cuadrado de 600 px (fondo blanco, proporción conservada) y la guarda como JPEG.
     * Devuelve null si el contenido no es una imagen.
     */
    public function procesar(string $contenido): ?string
    {
        if (!function_exists('imagecreatefromstring')) {
            return $this->pareceImagen($contenido) ? $contenido : null;
        }

        $imagen = @imagecreatefromstring($contenido);
        if (!$imagen) {
            return null;
        }

        try {
            $ancho = imagesx($imagen);
            $alto  = imagesy($imagen);
            $lienzo = imagecreatetruecolor(self::LADO, self::LADO);
            imagefill($lienzo, 0, 0, imagecolorallocate($lienzo, 255, 255, 255));

            $razon = min(self::LADO / $ancho, self::LADO / $alto);
            $nuevoAncho = max(1, (int) ($ancho * $razon));
            $nuevoAlto  = max(1, (int) ($alto * $razon));
            imagecopyresampled(
                $lienzo, $imagen, (int) ((self::LADO - $nuevoAncho) / 2), (int) ((self::LADO - $nuevoAlto) / 2), 0, 0,
                $nuevoAncho, $nuevoAlto, $ancho, $alto
            );

            ob_start();
            imagejpeg($lienzo, null, 78);
            $jpeg = ob_get_clean();
            imagedestroy($lienzo);

            return $jpeg ?: null;
        } catch (\Throwable $e) {
            Log::warning('No se pudo procesar la foto del producto: ' . $e->getMessage());

            return null;
        } finally {
            imagedestroy($imagen);
        }
    }

    private function pareceImagen(string $contenido): bool
    {
        return str_starts_with($contenido, "\xFF\xD8\xFF") || str_starts_with($contenido, "\x89PNG") || str_starts_with($contenido, 'GIF8')
            || (str_starts_with($contenido, 'RIFF') && substr($contenido, 8, 4) === 'WEBP');
    }
}
