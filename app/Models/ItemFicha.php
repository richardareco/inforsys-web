<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ficha comercial de un producto (marca, beneficios, modo de uso, tonos...). La tabla items_ficha se creó para la web,
 * también se puede completar desde Delphi y su existencia es opcional por base: usar FichaProducto::tablaExiste().
 */
class ItemFicha extends Model
{
    protected $connection = 'delphi';
    protected $table      = 'items_ficha';
    protected $primaryKey = 'nro';
    public    $timestamps = false;

    protected $fillable = [
        'item', 'marca', 'contenido', 'descripcion', 'beneficios', 'modo_uso', 'ingredientes',
        'tipo_piel', 'tonos', 'advertencias', 'destacado',
    ];

    protected $casts = [
        'tonos'     => 'array',
        'destacado' => 'boolean',
    ];

    private const TEXTOS = ['marca', 'contenido', 'descripcion', 'beneficios', 'modo_uso', 'ingredientes', 'tipo_piel', 'advertencias'];

    protected static function booted(): void
    {
        static::saving(fn (self $ficha) => $ficha->mod_fecha = now());
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item', 'item');
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, self::TEXTOS, true)) {
            $value = self::paraLatin1($value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * La tabla es latin1: lo que no entra en latin1 (comillas tipográficas, rayas, emojis) se guardaría como "?".
     * Se pasa a su equivalente simple y se descarta el resto. Un texto vacío queda en null.
     */
    public static function paraLatin1(mixed $valor): ?string
    {
        if (!is_string($valor)) {
            return $valor === null ? null : (string) $valor;
        }

        $valor = strtr($valor, [
            '“' => '"', '”' => '"', '„' => '"', '‘' => "'", '’' => "'", '‚' => "'",
            '–' => '-', '—' => '-', '…' => '...', '•' => '-', '€' => 'EUR', '™' => '(TM)',
        ]);
        $valor = preg_replace('/[^\x{0000}-\x{00FF}]/u', '', $valor) ?? '';
        $valor = trim($valor);

        return $valor === '' ? null : $valor;
    }
}
