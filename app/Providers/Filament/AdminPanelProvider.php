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
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                PanelsRenderHook::HEAD_START,
                fn (): HtmlString => new HtmlString(
                    '<link rel="manifest" href="/manifest.json">' .
                    '<link rel="apple-touch-icon" href="/images/icon-192.png">' .
                    '<meta name="theme-color" content="#0047AB">' .
                    '<meta name="mobile-web-app-capable" content="yes">' .
                    '<meta name="apple-mobile-web-app-capable" content="yes">' .
                    '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">' .
                    '<meta name="apple-mobile-web-app-title" content="InforsysPdv">' .
                    '<script>if("serviceWorker" in navigator){navigator.serviceWorker.register("/sw.js");}</script>'
                )
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString('
<style>
/* ══ SIDEBAR SIEMPRE OSCURA ══ */
:root { --sidebar-width: 16rem; }
.fi-sidebar,
.fi-sidebar nav,
.fi-sidebar-nav {
    background-color: #253347 !important;
}
.fi-sidebar-header,
.fi-sidebar > div:first-child {
    background-color: #253347 !important;
    border-bottom-color: transparent !important;
}
/* nombre de empresa / brand */
.fi-sidebar-header *,
.fi-sidebar-header a,
.fi-brand-name {
    color: #ffffff !important;
}
/* items del menú — texto blanco semitransparente para inactivos */
.fi-sidebar-item-button {
    color: rgba(255,255,255,0.75) !important;
}
.fi-sidebar-item-button span,
.fi-sidebar-item-label {
    color: rgba(255,255,255,0.75) !important;
}
.fi-sidebar-item-icon,
.fi-sidebar-item-button svg {
    color: rgba(255,255,255,0.6) !important;
}
.fi-sidebar-item-button:hover {
    background-color: rgba(255,255,255,0.08) !important;
}
.fi-sidebar-item-button:hover span,
.fi-sidebar-item-button:hover .fi-sidebar-item-label,
.fi-sidebar-item-button:hover .fi-sidebar-item-icon,
.fi-sidebar-item-button:hover svg {
    color: #ffffff !important;
}
/* item activo */
.fi-sidebar-item-active .fi-sidebar-item-button,
.fi-sidebar-item-button[aria-current="page"],
.fi-sidebar-item-button.fi-active {
    background-color: #0047AB !important;
}
.fi-sidebar-item-active .fi-sidebar-item-button span,
.fi-sidebar-item-active .fi-sidebar-item-button svg,
.fi-sidebar-item-button[aria-current="page"] span,
.fi-sidebar-item-button[aria-current="page"] svg {
    color: #ffffff !important;
}
/* grupo label */
.fi-sidebar-group-label {
    color: rgba(255,255,255,0.4) !important;
}
/* botón colapsar */
.fi-sidebar-close-overlay-btn svg,
.fi-icon-btn[title="Collapse sidebar"] svg,
.fi-icon-btn[title="Expand sidebar"] svg {
    color: rgba(255,255,255,0.6) !important;
}

/* ══ FONDO DEL CONTENIDO (modo claro) ══ */
html:not(.dark) .fi-main,
html:not(.dark) .fi-body,
html:not(.dark) .fi-main-ctn {
    background-color: #ffffff !important;
}
html:not(.dark) .fi-topbar nav,
html:not(.dark) .fi-header {
    background-color: #ffffff !important;
    box-shadow: 0 1px 0 #e2e8f0 !important;
}
/* Páginas: header title área */
html:not(.dark) .fi-simple-main {
    background-color: #ffffff !important;
}
/* Topbar toggle sidebar button */
html:not(.dark) .fi-topbar .fi-icon-btn {
    color: #475569 !important;
}
</style>')
            )
            ->navigationGroups([
                'Ventas',
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
