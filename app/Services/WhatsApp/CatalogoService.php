<?php

namespace App\Services\WhatsApp;

use App\Models\Empresa;
use App\Models\WaAgenteConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

/**
 * Consulta de productos para el agente. Solo lectura sobre la base Delphi de la empresa.
 * Precio normal = items.precio3, mayorista = items.precio1, stock = SUM(itemsdepo.qty).
 * Nunca devuelve costos.
 */
class CatalogoService
{
    public function __construct(
        private Empresa $empresa,
        private ?WaAgenteConfig $config = null,
    ) {}

    private function db()
    {
        DB::purge('delphi');
        config(['database.connections.delphi.database' => $this->empresa->db_name]);

        return DB::connection('delphi');
    }

    private function consultaBase()
    {
        $depositos = array_values(array_filter((array) ($this->config?->depositos ?? [])));
        $stockSql  = 'COALESCE((SELECT SUM(d.qty) FROM itemsdepo d WHERE d.item = items.item';
        $bind      = [];

        if ($depositos) {
            $stockSql .= ' AND d.depo IN (' . implode(',', array_fill(0, count($depositos), '?')) . ')';
            $bind      = $depositos;
        }
        $stockSql .= '), 0) AS stock';

        $query = $this->db()->table('items')
            ->selectRaw(
                'items.item, items.descr, items.grupnr, items.precio1, items.precio3, ' . $stockSql
                . ', EXISTS(SELECT 1 FROM itemsimage i WHERE i.item = items.item AND i.image IS NOT NULL) AS tiene_foto',
                $bind
            )
            ->whereRaw("TRIM(COALESCE(items.item, '')) <> ''");

        if (!($this->empresa->mostrar_productos_sin_stock ?? true)) {
            $query->havingRaw('stock > 0');
        }

        return $query;
    }

    /** @return array<int, array<string, mixed>> */
    public function buscar(string $texto, int $limite = 5): array
    {
        $palabras = preg_split('/\s+/u', mb_strtolower(trim($texto)), -1, PREG_SPLIT_NO_EMPTY);
        $palabras = array_slice($palabras, 0, 6);

        if (!$palabras) {
            return [];
        }

        $query = $this->consultaBase();

        foreach ($palabras as $palabra) {
            $like = '%' . addcslashes($palabra, '%_\\') . '%';
            $query->where(function ($q) use ($palabra, $like) {
                $q->where('items.descr', 'like', $like)
                  ->orWhere('items.item', $palabra)
                  ->orWhere('items.scode', $palabra)
                  ->orWhere('items.scode1', $palabra);
            });
        }

        return $query->orderByRaw('stock > 0 DESC')
            ->orderBy('items.descr')
            ->limit(max(1, min($limite, 10)))
            ->get()
            ->map(fn ($r) => $this->formatear($r))
            ->all();
    }

    public function detalle(string $codigo): ?array
    {
        $row = $this->consultaBase()->where('items.item', $codigo)->first();

        return $row ? $this->formatear($row) : null;
    }

    private function formatear(object $r): array
    {
        return [
            'codigo'            => (string) $r->item,
            'nombre'            => trim((string) $r->descr),
            'categoria'         => $r->grupnr,
            'precio'            => (float) $r->precio3,
            'precio_mayorista'  => (float) $r->precio1,
            'stock'             => (float) $r->stock,
            'tiene_foto'        => (bool) $r->tiene_foto,
        ];
    }

    /**
     * Precio mayorista (items.precio1) si el negocio lo tiene activado, el producto lo tiene cargado
     * y es menor al normal. Devuelve null cuando no corresponde mostrarlo ni aplicarlo.
     *
     * @return array{precio: float, desde: int}|null
     */
    public function mayorista(array $p): ?array
    {
        $min = (int) ($this->config?->mayorista_min_unidades ?? 0);

        if (!($this->config?->mayorista_activo ?? false) || $min < 2) {
            return null;
        }

        $mayor = (float) $p['precio_mayorista'];

        return ($mayor > 0 && $mayor < (float) $p['precio']) ? ['precio' => round($mayor), 'desde' => $min] : null;
    }

    /** Precio unitario que corresponde según la cantidad pedida. */
    public function precioPara(array $p, float $cantidad): float
    {
        $m = $this->mayorista($p);

        return round($m && $cantidad >= $m['desde'] ? $m['precio'] : $p['precio']);
    }

    /** Texto de la ficha de un producto: código, descripción y precios (también para el pie de la foto). */
    public function ficha(array $p): string
    {
        $gs = fn ($v) => 'Gs. ' . number_format($v, 0, ',', '.');

        $lineas = ['Cód. ' . $p['codigo'], $p['nombre'], $p['precio'] > 0 ? 'Precio: ' . $gs($p['precio']) : 'Precio a consultar'];

        if ($m = $this->mayorista($p)) {
            $lineas[] = 'Mayorista: ' . $gs($m['precio']) . ' c/u comprando desde ' . $m['desde'] . ' unidades';
        }

        return implode("\n", $lineas);
    }

    public function fotoBinaria(string $codigo): ?string
    {
        $img = $this->db()->table('itemsimage')->where('item', $codigo)->value('image');

        return $img !== null && $img !== '' ? $img : null;
    }

    /** Enlace público y firmado a la foto, para enviarla por WhatsApp. */
    public function fotoUrl(string $codigo): string
    {
        return URL::temporarySignedRoute(
            'wa.foto',
            now()->addMinutes((int) config('kapso.foto_url_minutos')),
            ['empresa' => $this->empresa->id, 'item' => $codigo]
        );
    }
}
