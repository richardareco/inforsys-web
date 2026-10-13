<?php

namespace App\Http\Controllers;

use App\Models\ItemImage;
use Illuminate\Http\Response;

class ItemImageController extends Controller
{
    public function show($item)
    {
        $image = ItemImage::where('item', $item)->first();

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
        }

        return response($image->image)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
