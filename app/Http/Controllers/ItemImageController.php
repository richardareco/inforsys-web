<?php

namespace App\Http\Controllers;

use App\Models\ItemImage;

class ItemImageController extends Controller
{
    /** Foto de un producto: la indicada por su nro (itemsimage.nro) o, sin nro, la primera. */
    public function show($item, $nro = null)
    {
        if ($nro !== null && !ctype_digit((string) $nro)) {
            return response('', 404);
        }

        $consulta = ItemImage::where('item', $item);
        $image = $nro !== null
            ? $consulta->where('nro', (int) $nro)->first()
            : $consulta->orderBy('nro')->first();

        if (!$image || empty($image->image)) {
            return response('', 404);
        }

        // Detectar tipo MIME
        $mimeType = 'image/jpeg';
        $firstBytes = substr($image->image, 0, 4);

        if (str_starts_with($firstBytes, "\x89PNG")) {
            $mimeType = 'image/png';
        } elseif (str_starts_with($firstBytes, "GIF")) {
            $mimeType = 'image/gif';
        } elseif ($firstBytes === 'RIFF' && substr($image->image, 8, 4) === 'WEBP') {
            $mimeType = 'image/webp';
        }

        return response($image->image)
            ->header('Content-Type', $mimeType)
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Cache-Control', 'private, max-age=86400');
    }
}
