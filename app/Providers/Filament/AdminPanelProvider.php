<?php

namespace App\Providers\Filament;

use Filament\FontProviders\GoogleFontProvider;
use App\Filament\Pages\Auth\Login;
use App\Http\Middleware\SetDelphiConnection;
use Illuminate\Support\Facades\Storage;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->colors([
                'primary' => Color::hex('#0047AB'),
            ])
            ->font('Inter', provider: GoogleFontProvider::class)
            ->brandName(fn () => auth()->user()?->empresa?->nombre ?? 'Inforsys')
            ->brandLogo(fn () => auth()->user()?->empresa?->logo
                ? Storage::disk('public')->url(auth()->user()->empresa->logo)
                : null)
            ->brandLogoHeight('6rem')
            ->darkMode(true)
            ->renderHook(
                PanelsRenderHook::HEAD_START,
                fn (): HtmlString => new HtmlString(
                    '<script>if(!localStorage.getItem("theme")&&!localStorage.getItem("appearance")){localStorage.setItem("theme","dark");document.documentElement.classList.add("dark");}</script>' .
                    '<link rel="manifest" href="/manifest.json">' .
                    '<meta name="theme-color" content="#0047AB">' .
                    '<meta name="mobile-web-app-capable" content="yes">' .
                    '<meta name="apple-mobile-web-app-capable" content="yes">' .
                    '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">' .
                    '<meta name="apple-mobile-web-app-title" content="Inforsys">' .
                    '<script>if("serviceWorker" in navigator){navigator.serviceWorker.register("/sw.js");}</script>'
                )
            )
            ->navigationGroups([
                'Configuración',
                'Administración',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                SetDelphiConnection::class,
            ]);
    }
}
