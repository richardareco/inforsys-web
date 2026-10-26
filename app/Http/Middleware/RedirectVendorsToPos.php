<?php

namespace App\Http\Middleware;

use App\Support\Modulos;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Al entrar a /admin lleva al usuario a su pantalla de inicio: el POS para vendedores y cajeros,
 * el dashboard si tiene acceso, o el primer módulo que tenga permitido.
 */
class RedirectVendorsToPos
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && trim($request->path(), '/') === 'admin' && $request->isMethod('GET')) {
            $destino = Modulos::inicio($user);

            if ($destino && rtrim($destino, '/') !== rtrim($request->url(), '/')) {
                return redirect($destino);
            }
        }

        return $next($request);
    }
}
