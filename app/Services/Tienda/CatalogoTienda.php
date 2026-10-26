<?php

namespace App\Services\Tienda;

use App\Models\EcomConfig;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Catálogo de la tienda online. Solo lectura sobre la base Delphi de la empresa:
 * se publican los productos que tienen una categoría (grupo) registrada.
 * Nombre = items.descr, descripción = items.descr1, precio = precio3, mayorista = precio1.
 */
class CatalogoTienda
{
    public function __construct(private EcomConfig $config) {}

    private function db()
    {
        $base = $this->config->empresa->db_name;

        if (config('database.connections.delphi.database') !== $base) {
            DB::purge('delphi');
            config(['database.connections.delphi.database' => $base]);
        }

        return DB::connection('delphi');
    }

    private function ocultas(): array
    {
        return array_values(array_filter((array) $this->config->categorias_ocultas, fn ($c) => $c !== null && $c !== ''));
    }

    /** Productos publicables: con código, con una categoría que exista y que no esté oculta. */
    private function base()
    {
        $depositos = array_values(array_filter((array) $this->config->depositos));
        $stock = 'COALESCE((SELECT SUM(d.qty) FROM itemsdepo d WHERE d.item = items.item';
        $bind  = [];
        if ($depositos) {
            $stock .= ' AND d.depo IN (' . implode(',', array_fill(0, count($depositos), '?')) . ')';
            $bind   = $depositos;
        }
        $stock .= '), 0)';

        $q = $this->db()->table('items')
            ->join('grupo', 'grupo.grupnr', '=', 'items.grupnr')
            ->selectRaw(
                "items.item, items.descr, items.descr1, items.grupnr, grupo.descr AS categoria, items.precio1, items.precio3, {$stock} AS stock,"
                . ' (SELECT COUNT(*) FROM itemsimage i WHERE i.item = items.item AND i.image IS NOT NULL) AS n_fotos,'
                . ' EXISTS(SELECT 1 FROM itemsimage i2 WHERE i2.item = items.item AND i2.image IS NOT NULL) AS tiene_foto',
                $bind
            )
            ->whereRaw("TRIM(COALESCE(items.item, '')) <> ''")
            ->whereRaw("TRIM(COALESCE(items.descr, '')) <> ''");

        if ($this->ocultas()) {
            $q->whereNotIn('items.grupnr', $this->ocultas());
        }
        if (!$this->config->mostrar_sin_stock) {
            $q->havingRaw('stock > 0');
        }

        return $q;
    }

    /** @return array<int, array{codigo: string, nombre: string, total: int}> */
    public function categorias(): array
    {
        $filas = $this->db()->query()->fromSub($this->base(), 'p')
            ->selectRaw('p.grupnr, p.categoria, COUNT(*) AS total')
            ->groupBy('p.grupnr', 'p.categoria')->orderBy('p.categoria')->get();

        return $filas->map(fn ($f) => ['codigo' => (string) $f->grupnr, 'nombre' => $this->limpio($f->categoria), 'total' => (int) $f->total])->all();
    }

    public function productos(?string $categoria, ?string $busqueda, string $orden, int $pagina, string $rutaBase): LengthAwarePaginator
    {
        $q = $this->filtrar($this->base(), $categoria, $busqueda);
        $porPagina = max(6, min((int) $this->config->por_pagina ?: 24, 60));

        $total = $this->db()->query()->fromSub(clone $q, 'c')->count();

        match ($orden) {
            'precio_asc'  => $q->orderByRaw('items.precio3 = 0')->orderBy('items.precio3'),
            'precio_desc' => $q->orderByDesc('items.precio3'),
            'recientes'   => $q->orderByDesc('items.nro'),
            default       => $q->orderBy('items.descr'),
        };

        $filas = $q->offset(($pagina - 1) * $porPagina)->limit($porPagina)->get()->map(fn ($r) => $this->formatear($r))->all();

        return new LengthAwarePaginator($filas, $total, $porPagina, $pagina, ['path' => $rutaBase]);
    }

    public function producto(string $codigo): ?array
    {
        $r = $this->base()->where('items.item', $codigo)->first();

        return $r ? $this->formatear($r) : null;
    }

    /** @return array<int, array<string, mixed>> */
    public function relacionados(string $grupnr, string $excluir, int $n = 4): array
    {
        return $this->base()->where('items.grupnr', $grupnr)->where('items.item', '!=', $excluir)
            ->orderByRaw('tiene_foto DESC')->orderBy('items.descr')->limit($n)->get()
            ->map(fn ($r) => $this->formatear($r))->all();
    }

    /** Identificadores (itemsimage.nro) de todas las fotos del producto, en orden. */
    public function fotoIds(string $codigo): array
    {
        return $this->db()->table('itemsimage')->where('item', $codigo)->whereNotNull('image')
            ->orderBy('nro')->pluck('nro')->map(fn ($n) => (int) $n)->all();
    }

    /** Foto del producto: la indicada (por su nro) o la primera. Solo de productos publicables. */
    public function foto(string $codigo, ?int $nro = null): ?string
    {
        if (!$this->producto($codigo)) {
            return null;
        }

        $q = $this->db()->table('itemsimage')->where('item', $codigo)->whereNotNull('image');
        $img = $nro ? $q->where('nro', $nro)->value('image') : $q->orderBy('nro')->value('image');

        return $img !== null && $img !== '' ? $img : null;
    }

    /** Totales para el panel de administración. */
    public function resumen(): array
    {
        $b = $this->base();
        $filas = $this->db()->query()->fromSub($b, 'p')->selectRaw('COUNT(*) AS total, SUM(p.tiene_foto) AS con_foto, SUM(p.stock > 0) AS con_stock, SUM(p.precio3 > 0) AS con_precio')->first();

        return [
            'productos'  => (int) ($filas->total ?? 0),
            'con_foto'   => (int) ($filas->con_foto ?? 0),
            'con_stock'  => (int) ($filas->con_stock ?? 0),
            'con_precio' => (int) ($filas->con_precio ?? 0),
            'categorias' => count($this->categorias()),
        ];
    }

    /** Todas las categorías de la base, tengan o no productos (para elegir cuáles ocultar). */
    public function todasLasCategorias(): array
    {
        return $this->db()->table('grupo')->orderBy('descr')->get(['grupnr', 'descr'])
            ->mapWithKeys(fn ($g) => [(string) $g->grupnr => $this->limpio($g->descr)])->all();
    }

    public function mayorista(array $p): ?array
    {
        $min = (int) $this->config->mayorista_min_unidades;
        if (!$this->config->mostrar_mayorista || $min < 2) {
            return null;
        }

        return ($p['precio_mayorista'] > 0 && $p['precio_mayorista'] < $p['precio']) ? ['precio' => $p['precio_mayorista'], 'desde' => $min] : null;
    }

    private function filtrar($q, ?string $categoria, ?string $busqueda)
    {
        if ($categoria !== null && $categoria !== '') {
            $q->where('items.grupnr', $categoria);
        }

        foreach (array_slice(preg_split('/\s+/u', mb_strtolower(trim((string) $busqueda)), -1, PREG_SPLIT_NO_EMPTY), 0, 5) as $palabra) {
            $like = '%' . addcslashes($palabra, '%_\\') . '%';
            $q->where(fn ($w) => $w->where('items.descr', 'like', $like)->orWhere('items.descr1', 'like', $like)
                ->orWhere('items.item', $palabra)->orWhere('items.scode1', $palabra)->orWhere('items.scode', $palabra));
        }

        return $q;
    }

    private function formatear(object $r): array
    {
        return [
            'codigo'           => (string) $r->item,
            'nombre'           => $this->limpio($r->descr),
            'descripcion'      => $this->limpio($r->descr1),
            'grupnr'           => (string) $r->grupnr,
            'categoria'        => $this->limpio($r->categoria),
            'precio'           => round((float) $r->precio3),
            'precio_mayorista' => round((float) $r->precio1),
            'stock'            => (float) $r->stock,
            'tiene_foto'       => (bool) $r->tiene_foto,
            'fotos'            => (int) $r->n_fotos,
        ];
    }

    private function limpio($texto): string
    {
        return trim((string) mb_convert_encoding((string) $texto, 'UTF-8', 'UTF-8'));
    }
}
