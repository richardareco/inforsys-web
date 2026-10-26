<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_contains(config('app.url'), 'ngrok')) {
            URL::forceScheme('https');
        }

        // Tienda pública: límite de pedidos por IP (protege la base de datos de abusos y bots)
        RateLimiter::for('tienda', fn (Request $r) => Limit::perMinute(120)->by($r->ip()));
        RateLimiter::for('tienda-fotos', fn (Request $r) => Limit::perMinute(600)->by($r->ip()));

        \App\Models\Item::observe(\App\Observers\ItemObserver::class);
    }
}
