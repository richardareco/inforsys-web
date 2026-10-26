<?php

namespace App\Http\Controllers;

use App\Models\EcomConfig;
use App\Services\Tienda\CatalogoTienda;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/** Tienda online pública (sin sesión): catálogo de productos con categoría. */
class TiendaController extends Controller
{
    private function config(string $slug): ?EcomConfig
    {
        return EcomConfig::with('empresa')->where('slug', $slug)->where('activo', true)->first();
    }

    private function noDisponible(): Response
    {
        return response()->view('tienda.no-disponible', [], 404);
    }

    public function inicio(Request $request, string $slug)
    {
        $t = $this->config($slug);
        if (!$t) {
            return $this->noDisponible();
        }

        $catalogo  = new CatalogoTienda($t);
        $categoria = trim((string) $request->query('categoria', ''));
        $q         = trim((string) $request->query('q', ''));
        $orden     = in_array($request->query('orden'), ['precio_asc', 'precio_desc', 'recientes'], true) ? $request->query('orden') : 'nombre';
        $pagina    = max(1, (int) $request->query('page', 1));

        $categorias = $catalogo->categorias();
        $productos  = $catalogo->productos($categoria ?: null, $q ?: null, $orden, $pagina, $t->urlPublica())
            ->appends(array_filter(['categoria' => $categoria, 'q' => $q, 'orden' => $orden === 'nombre' ? null : $orden]));

        $actual = collect($categorias)->firstWhere('codigo', $categoria);

        return view('tienda.index', [
            't' => $t, 'catalogo' => $catalogo, 'categorias' => $categorias, 'productos' => $productos,
            'categoria' => $categoria, 'categoriaNombre' => $actual['nombre'] ?? null, 'q' => $q, 'orden' => $orden,
            'esInicio' => $categoria === '' && $q === '' && $pagina === 1,
        ]);
    }

    public function producto(string $slug, string $codigo)
    {
        $t = $this->config($slug);
        if (!$t) {
            return $this->noDisponible();
        }

        $catalogo = new CatalogoTienda($t);
        $p = $catalogo->producto($codigo);
        if (!$p) {
            return $this->noDisponible();
        }

        return view('tienda.producto', [
            't' => $t, 'catalogo' => $catalogo, 'p' => $p, 'fotos' => $catalogo->fotoIds($codigo),
            'relacionados' => $catalogo->relacionados($p['grupnr'], $codigo),
        ]);
    }

    public function foto(Request $request, string $slug, string $codigo, ?string $nro = null)
    {
        $t = $this->config($slug);
        $binario = $t ? (new CatalogoTienda($t))->foto($codigo, $nro !== null ? (int) $nro : null) : null;
        abort_if($binario === null, 404);

        $mime = match (true) {
            str_starts_with($binario, "\x89PNG") => 'image/png',
            str_starts_with($binario, 'GIF')     => 'image/gif',
            str_starts_with($binario, 'RIFF')    => 'image/webp',
            default                              => 'image/jpeg',
        };

        $etag = '"' . md5($binario) . '"';
        if ($request->header('If-None-Match') === $etag) {
            return response('', 304)->header('ETag', $etag);
        }

        return response($binario)->header('Content-Type', $mime)->header('ETag', $etag)
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
