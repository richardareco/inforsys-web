<?php

namespace App\Http\Middleware;

use App\Support\Modulos;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Al entrar a /admin lleva al usuario a su pantalla de inicio: el POS para vendedores y cajeros,
 * el dashboard si tiene acceso, o el primer módulo que tenga permitido.
 *
 * Solo al ENTRAR (sin venir de otra pantalla del panel, o recién iniciada la sesión). Si el usuario llegó desde el menú
 * se respeta lo que pidió: así un vendedor puede abrir su Dashboard, que vive en /admin, sin que lo devuelvan al POS.
 */
class RedirectVendorsToPos
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && trim($request->path(), '/') === 'admin' && $request->isMethod('GET') && !$this->vieneDelPanel($request)) {
            $destino = Modulos::inicio($user);

            if ($destino && rtrim($destino, '/') !== rtrim($request->url(), '/')) {
                return redirect($destino);
            }
        }

        return $next($request);
    }

    /** ¿La visita viene de otra pantalla del panel (que no sea el inicio de sesión)? */
    private function vieneDelPanel(Request $request): bool
    {
        $referer = $request->headers->get('referer');
        if (!$referer) {
            return false;
        }

        $partes = parse_url($referer);
        $ruta   = '/' . trim((string) ($partes['path'] ?? ''), '/');

        return ($partes['host'] ?? null) === $request->getHost()
            && str_starts_with($ruta, '/admin')
            && !str_starts_with($ruta, '/admin/login');
    }
}
