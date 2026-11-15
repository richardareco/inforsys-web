<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Información adicional de la ficha de un producto, con el formato que cada empresa quiera
 * (por ejemplo "Garantía", "Cuidados", "Origen"): una lista de secciones con título y contenido.
 */
class ProductoInfoExtra extends Model
{
    protected $table = 'producto_info_extra';

    protected $fillable = ['empresa_id', 'item', 'secciones'];

    protected $casts = ['secciones' => 'array'];

    public const MAXIMO = 20;
    public const LARGO_TITULO = 60;
    public const LARGO_CONTENIDO = 2000;

    /**
     * Siempre la base de la web. Una relación desde Item (base Delphi) heredaría la conexión del producto y buscaría
     * esta tabla en la base del cliente.
     */
    public function getConnectionName(): ?string
    {
        return config('database.default');
    }

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->empresa_id ??= self::empresaActualId());
        static::saving(fn (self $m) => $m->secciones = self::limpiar($m->secciones));
    }

    /** Empresa del usuario logueado; si no tiene (superadministrador), la que corresponde a la base Delphi en uso. */
    public static function empresaActualId(): ?int
    {
        $id = auth()->user()?->empresa_id
            ?: Empresa::where('db_name', config('database.connections.delphi.database'))->value('id');

        return $id ? (int) $id : null;
    }

    /**
     * Secciones válidas: título y contenido con texto, recortados al largo permitido y hasta el máximo.
     *
     * @return array<int, array{titulo: string, contenido: string}>
     */
    public static function limpiar(mixed $secciones): array
    {
        if (!is_array($secciones)) {
            return [];
        }

        $salida = [];
        foreach ($secciones as $s) {
            $titulo    = is_array($s) ? trim((string) ($s['titulo'] ?? '')) : '';
            $contenido = is_array($s) ? trim((string) ($s['contenido'] ?? '')) : '';
            if ($titulo === '' || $contenido === '') {
                continue;
            }
            $salida[] = ['titulo' => mb_substr($titulo, 0, self::LARGO_TITULO), 'contenido' => mb_substr($contenido, 0, self::LARGO_CONTENIDO)];
        }

        return array_slice($salida, 0, self::MAXIMO);
    }

    /** Secciones extra de un producto; de la empresa indicada o, si no, la del usuario logueado. */
    public static function de(string $item, ?int $empresaId = null): array
    {
        $empresa = $empresaId ?: self::empresaActualId();
        if (!$empresa) {
            return [];
        }

        return self::limpiar(self::where('empresa_id', $empresa)->where('item', $item)->value('secciones'));
    }
}
