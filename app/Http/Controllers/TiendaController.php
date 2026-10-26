<?php

namespace App\Http\Controllers;

use App\Models\EcomConfig;
use App\Services\Tienda\CatalogoTienda;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

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

    /** Cualquier falla se registra internamente y el visitante solo ve un aviso genérico (nunca detalles técnicos). */
    private function protegido(callable $accion)
    {
        try {
            return $accion();
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Tienda pública: ' . $e->getMessage());

            return response()->view('tienda.no-disponible', [], 503);
        }
    }

    public function inicio(Request $request, string $slug)
    {
        return $this->protegido(fn () => $this->inicioInterno($request, $slug));
    }

    private function inicioInterno(Request $request, string $slug)
    {
        $t = $this->config($slug);
        if (!$t) {
            return $this->noDisponible();
        }

        $catalogo  = new CatalogoTienda($t);
        $categoria = mb_substr(trim((string) $request->query('categoria', '')), 0, 20);
        $q         = mb_substr(trim((string) $request->query('q', '')), 0, 80);
        $orden     = in_array($request->query('orden'), ['precio_asc', 'precio_desc', 'recientes'], true) ? $request->query('orden') : 'nombre';
        $pagina    = min(5000, max(1, (int) $request->query('page', 1)));

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
        return $this->protegido(fn () => $this->productoInterno($slug, $codigo));
    }

    private function productoInterno(string $slug, string $codigo)
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

    /** Precios y disponibilidad actuales de los productos del carrito (JSON público, sin datos internos). */
    public function carrito(Request $request, string $slug)
    {
        return $this->protegido(function () use ($request, $slug) {
            $t = $this->config($slug);
            if (!$t || !$t->carritoDisponible()) {
                return response()->json(['items' => (object) []], 404);
            }

            $codigos = array_slice(explode(',', mb_substr((string) $request->query('c', ''), 0, 1500)), 0, 30);

            return response()->json(['items' => (object) (new CatalogoTienda($t))->porCodigos($codigos)])
                ->header('Cache-Control', 'private, max-age=15');
        });
    }

    public function foto(Request $request, string $slug, string $codigo, ?string $nro = null)
    {
        return $this->protegido(fn () => $this->fotoInterna($request, $slug, $codigo, $nro));
    }

    private function fotoInterna(Request $request, string $slug, string $codigo, ?string $nro = null)
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
