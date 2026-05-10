<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetDelphiConnection
{
    public function handle(Request $request, Closure $next)
    {
        $dbName = null;

        // Usuario autenticado → usar db de su empresa (o db_name legacy) y guardarlo en sesión
        if ($request->user() && $request->user()->getEffectiveDbName()) {
            $dbName = $request->user()->getEffectiveDbName();
            session(['delphi_db' => $dbName]);
        }
        // Livewire AJAX → el usuario puede no resolverse, leer de sesión
        elseif (session()->has('delphi_db')) {
            $dbName = session('delphi_db');
        }

        if ($dbName) {
            DB::purge('delphi');
            config(['database.connections.delphi.database' => $dbName]);
        }

        return $next($request);
    }
}
