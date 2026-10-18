<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectVendorsToPos
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Si es vendedor o cajero, redirigir a pos-page en lugar de dashboard
        if ($user && in_array($user->role, ['vendedor', 'cajero'])) {
            // Si está en dashboard, redirigir a pos-page
            if ($request->path() === 'admin' || $request->path() === 'admin/') {
                return redirect('/admin/pos-page');
            }
        }

        return $next($request);
    }
}
