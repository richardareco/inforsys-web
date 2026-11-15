<?php

namespace App\Services\Productos;

use App\Models\ProductoInfoExtra;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ficha completa de un producto para mostrársela al cliente: datos de `items`, la ficha comercial de `items_ficha`
 * (si la base la tiene) y las fotos de `itemsimage`. Solo se eligen columnas públicas: nunca el costo.
 */
class FichaProducto
{
    /** Campos de texto de la ficha que se muestran como secciones. */
    public const SECCIONES = ['descripcion', 'beneficios', 'modo_uso', 'ingredientes', 'tipo_piel', 'advertencias'];

    private const CONEXION = 'delphi';

    /** La tabla items_ficha es opcional por base: se consulta una vez por request y por base de datos. */
    public static function tablaExiste(): bool
    {
        static $cache = [];

        $base = (string) config('database.connections.' . self::CONEXION . '.database');

        return $cache[$base] ??= Schema::connection(self::CONEXION)->hasTable('items_ficha');
    }

    /**
     * @return array<string, mixed>|null null si el producto no existe
     */
    public function obtener(string $codigo, ?string $deposito = null, ?int $empresaId = null): ?array
    {
        $db = DB::connection(self::CONEXION);

        $p = $db->table('items')->where('item', $codigo)
            ->select('item', 'descr', 'descr1', 'scode', 'scode1', 'grupnr', 'marnr', 'precio1', 'precio3', 'unit')
            ->first();
        if (!$p) {
            return null;
        }

        $ficha = self::tablaExiste() ? $db->table('items_ficha')->where('item', $codigo)->first() : null;

        $marca = $this->texto($ficha->marca ?? null)
            ?? $this->texto($p->marnr !== null && $p->marnr !== '' ? $db->table('marca')->where('marnr', $p->marnr)->value('descr') : null);

        $precio    = (float) $p->precio3;
        $mayorista = (float) $p->precio1;

        return [
            'item'            => (string) $p->item,
            'nombre'          => $this->texto($p->descr) ?? '',
            'codigo_barras'   => $this->texto($p->scode1) ?? $this->texto($p->scode),
            'categoria'       => $this->categoria($p->grupnr),
            'grupnr'          => $p->grupnr,
            'marca'           => $marca,
            'contenido'       => $this->texto($ficha->contenido ?? null),
            // Sin ficha (o con la descripción vacía) se usa la descripción corta del producto
            'descripcion'     => $this->texto($ficha->descripcion ?? null) ?? $this->texto($p->descr1),
            'beneficios'      => $this->texto($ficha->beneficios ?? null),
            'modo_uso'        => $this->texto($ficha->modo_uso ?? null),
            'ingredientes'    => $this->texto($ficha->ingredientes ?? null),
            'tipo_piel'       => $this->texto($ficha->tipo_piel ?? null),
            'advertencias'    => $this->texto($ficha->advertencias ?? null),
            'tonos'           => $this->tonos($ficha->tonos ?? null),
            'extras'          => ProductoInfoExtra::de($codigo, $empresaId),
            'destacado'       => (bool) ($ficha->destacado ?? false),
            'precio'          => $precio,
            'precio_mayorista' => ($mayorista > 0 && $mayorista < $precio) ? $mayorista : null,
            'stock'           => $this->stock($codigo, $deposito),
            'fotos'           => $this->fotoIds($codigo),
        ];
    }

    /**
     * Productos de la misma categoría para "También te puede interesar".
     *
     * @return array<int, array{item: string, nombre: string, marca: ?string, precio: float, stock: float, tiene_foto: bool}>
     */
    public function relacionados(string $codigo, mixed $grupnr, ?string $deposito = null, int $limite = 4): array
    {
        if ($grupnr === null || $grupnr === '') {
            return [];
        }

        $db = DB::connection(self::CONEXION);

        $filas = $db->table('items')
            ->where('items.grupnr', $grupnr)->where('items.item', '<>', $codigo)
            ->whereRaw("TRIM(COALESCE(items.item, '')) <> ''")
            ->select('items.item', 'items.descr', 'items.precio3')
            ->selectRaw('COALESCE((SELECT qty FROM itemsdepo WHERE itemsdepo.item = items.item AND itemsdepo.depo = ?), 0) AS stock_depo', [$deposito])
            ->selectRaw('EXISTS(SELECT 1 FROM itemsimage WHERE itemsimage.item = items.item) AS tiene_foto')
            ->orderByRaw('(stock_depo > 0) DESC')->orderByRaw('tiene_foto DESC')->orderBy('items.descr')
            ->limit(max(1, $limite))
            ->get();

        $marcas = self::tablaExiste()
            ? $db->table('items_ficha')->whereIn('item', $filas->pluck('item'))->pluck('marca', 'item')
            : collect();

        return $filas->map(fn ($r) => [
            'item'       => (string) $r->item,
            'nombre'     => $this->texto($r->descr) ?? '',
            'marca'      => $this->texto($marcas[$r->item] ?? null),
            'precio'     => (float) $r->precio3,
            'stock'      => (float) $r->stock_depo,
            'tiene_foto' => (bool) $r->tiene_foto,
        ])->all();
    }

    /** Identificadores (itemsimage.nro) de las fotos del producto, en orden. */
    public function fotoIds(string $codigo): array
    {
        return DB::connection(self::CONEXION)->table('itemsimage')
            ->where('item', $codigo)->whereNotNull('image')
            ->orderBy('nro')->pluck('nro')->map(fn ($n) => (int) $n)->all();
    }

    private function stock(string $codigo, ?string $deposito): float
    {
        $q = DB::connection(self::CONEXION)->table('itemsdepo')->where('item', $codigo);

        return (float) ($deposito !== null ? $q->where('depo', $deposito)->sum('qty') : $q->sum('qty'));
    }

    private function categoria(mixed $grupnr): ?string
    {
        if ($grupnr === null || $grupnr === '') {
            return null;
        }

        return $this->texto(DB::connection(self::CONEXION)->table('grupo')->where('grupnr', $grupnr)->value('descr'));
    }

    /**
     * Tonos válidos de la ficha: [{nombre, color}] con color hexadecimal. Lo que no cumple se descarta, así el color
     * puede usarse directo en un estilo sin riesgo.
     *
     * @return array<int, array{nombre: string, color: string}>
     */
    private function tonos(mixed $crudo): array
    {
        $lista = is_string($crudo) ? json_decode($crudo, true) : $crudo;
        if (!is_array($lista)) {
            return [];
        }

        $salida = [];
        foreach ($lista as $t) {
            $nombre = is_array($t) ? $this->texto($t['nombre'] ?? null) : null;
            $color  = is_array($t) ? trim((string) ($t['color'] ?? '')) : '';
            if ($nombre !== null && preg_match('/^#[0-9a-fA-F]{3,8}$/', $color)) {
                $salida[] = ['nombre' => mb_substr($nombre, 0, 40), 'color' => $color];
            }
        }

        return array_slice($salida, 0, 40);
    }

    /** Texto limpio y en UTF-8 válido; vacío → null (para ocultar las secciones sin datos). */
    private function texto(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $valor = trim(mb_convert_encoding((string) $valor, 'UTF-8', 'UTF-8'));

        return $valor === '' ? null : $valor;
    }
}
