<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EcomConfig extends Model
{
    protected $table = 'ecom_config';

    protected $fillable = [
        'empresa_id', 'activo', 'slug', 'nombre', 'eslogan', 'texto_inicio', 'logo', 'portada', 'color',
        'whatsapp', 'instagram', 'facebook', 'direccion', 'horarios',
        'mostrar_precios', 'mostrar_mayorista', 'mayorista_min_unidades', 'mostrar_stock', 'mostrar_sin_stock',
        'categorias_ocultas', 'depositos', 'por_pagina', 'mensaje_whatsapp', 'seo_titulo', 'seo_descripcion',
    ];

    protected $casts = [
        'activo'             => 'boolean',
        'mostrar_precios'    => 'boolean',
        'mostrar_mayorista'  => 'boolean',
        'mostrar_stock'      => 'boolean',
        'mostrar_sin_stock'  => 'boolean',
        'categorias_ocultas' => 'array',
        'depositos'          => 'array',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function nombreTienda(): string
    {
        return trim((string) $this->nombre) ?: (string) $this->empresa?->nombre;
    }

    public function urlPublica(): string
    {
        return url('/tienda/' . $this->slug);
    }

    public function urlArchivo(?string $ruta): ?string
    {
        return $ruta ? Storage::disk('public')->url($ruta) : null;
    }

    /** Número de WhatsApp solo con dígitos, listo para wa.me. */
    public function whatsappDigitos(): string
    {
        return preg_replace('/\D+/', '', (string) $this->whatsapp);
    }

    public function enlaceWhatsapp(?string $texto = null): ?string
    {
        $n = $this->whatsappDigitos();

        return $n === '' ? null : 'https://wa.me/' . $n . ($texto ? '?text=' . rawurlencode($texto) : '');
    }

    /** Color de texto legible sobre el color de la marca. */
    public function colorTexto(): string
    {
        $hex = ltrim((string) $this->color, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return '#ffffff';
        }
        [$r, $g, $b] = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];

        return (0.299 * $r + 0.587 * $g + 0.114 * $b) > 160 ? '#111827' : '#ffffff';
    }

    public static function sugerirSlug(string $texto): string
    {
        $base = Str::slug($texto) ?: 'tienda';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
