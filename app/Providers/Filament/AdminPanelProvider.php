<?php

namespace App\Providers\Filament;

use Filament\Enums\ThemeMode;
use Filament\FontProviders\GoogleFontProvider;
use App\Filament\Pages\Auth\Login;
use App\Http\Middleware\SetDelphiConnection;
use App\Http\Middleware\RedirectVendorsToPos;
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
            ->spa()
            ->darkMode(true)
            ->defaultThemeMode(ThemeMode::Light)
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): string => auth()->check() ? view('filament.components.fullscreen-button')->render() : ''
            )
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
                function (): HtmlString {
                    $user = auth()->user();
                    if (!$user) {
                        return new HtmlString('');
                    }

                    $accesos = json_encode($user->accesos ?? []);
                    $isSuperAdmin = $user->isSuperAdmin() ? 'true' : 'false';

                    return new HtmlString("
<script>
(function() {
if (window.__accesosMenuInit) return;
window.__accesosMenuInit = true;
function aplicarAccesosMenu() {
    const accesos = $accesos;
    const isSuperAdmin = $isSuperAdmin;

    if (isSuperAdmin) return; // Super admin ve todo

    // Mapeo de módulos a palabras clave en el menú
    const moduloTextos = {
        'dashboard': 'Dashboard',
        'historial_ventas': 'Historial de Ventas',
        'cierre_caja': 'Cierre de Caja',
        'cuentas_cobrar': 'Cuentas por Cobrar',
        'proveedores': 'Proveedores',
        'compras': 'Compras',
        'gastos': 'Gastos',
        'reportes': 'Reportes',
        'configuraciones': 'Configuraciones',
        'usuarios': 'Usuarios',
        'presupuestos': 'Presupuestos',
    };

    // Buscar y ocultar items sin acceso
    const navItems = document.querySelectorAll('[class*=\"sidebar\"] a, [class*=\"nav\"] a');
    navItems.forEach(item => {
        const texto = item.textContent.trim();

        for (const [modulo, keywords] of Object.entries(moduloTextos)) {
            if (texto.includes(keywords)) {
                if (!accesos[modulo] && modulo !== 'ventas' && modulo !== 'clientes' && modulo !== 'productos') {
                    item.parentElement.parentElement.style.display = 'none';
                }
                break;
            }
        }
    });
}
document.addEventListener('DOMContentLoaded', aplicarAccesosMenu);
document.addEventListener('livewire:navigated', aplicarAccesosMenu);
})();
</script>
                    ");
                }
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
    min-height: auto !important;
    padding: 1rem !important;
}
/* nombre de empresa / brand */
.fi-sidebar-header *,
.fi-sidebar-header a,
.fi-brand-name {
    color: #ffffff !important;
}
.fi-brand {
    width: 100% !important;
    max-width: 100% !important;
}
.fi-brand-name {
    white-space: normal !important;
    word-wrap: break-word !important;
    word-break: break-word !important;
    max-width: 100% !important;
    padding: 0.5rem 0 !important;
    line-height: 1.4 !important;
    font-size: 1rem !important;
    font-weight: 600 !important;
    overflow: visible !important;
    text-overflow: unset !important;
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

/* ══ BOTÓN SIDEBAR VISIBLE EN MÓVIL ══ */
.fi-topbar-open-sidebar-btn,
.fi-topbar-close-sidebar-btn {
    background-color: #f3f4f6 !important;
    color: #1f2937 !important;
    border-radius: 0.5rem !important;
    padding: 0.5rem !important;
}

.fi-topbar-open-sidebar-btn:hover,
.fi-topbar-close-sidebar-btn:hover {
    background-color: #e5e7eb !important;
    color: #111827 !important;
}

.fi-topbar-open-sidebar-btn svg path,
.fi-topbar-close-sidebar-btn svg path {
    stroke: #1f2937 !important;
    fill: #1f2937 !important;
}

@media (max-width: 768px) {
    .fi-topbar-open-sidebar-btn,
    .fi-topbar-close-sidebar-btn {
        display: flex !important;
    }
}
</style>')
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString('
<style>
/* ══ MENÚS: TOQUE MODERNO (sidebar + topbar) ══ */
.fi-sidebar,
.fi-sidebar nav,
.fi-sidebar-nav {
    background: linear-gradient(180deg, #1e2b3f 0%, #253347 55%, #1b2638 100%) !important;
}
.fi-sidebar {
    box-shadow: 4px 0 24px -8px rgba(15, 23, 42, .35) !important;
    border-inline-end: 1px solid rgba(255,255,255,.04) !important;
}
.fi-sidebar-header,
.fi-sidebar > div:first-child {
    background: transparent !important;
    position: relative;
}
.fi-sidebar-header::after {
    content: "";
    position: absolute;
    left: 1rem; right: 1rem; bottom: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.16), transparent);
}
.fi-brand-name {
    letter-spacing: -.01em !important;
    font-weight: 800 !important;
    font-size: 1.15rem !important;
}

/* Items */
.fi-sidebar-nav { padding-top: 1rem !important; }
.fi-sidebar-item-button {
    border-radius: .8rem !important;
    padding: .62rem .8rem !important;
    margin-inline: .15rem;
    font-weight: 600 !important;
    transition: background-color .18s ease, transform .18s ease, box-shadow .18s ease !important;
}
.fi-sidebar-item-button:hover {
    background-color: rgba(255,255,255,.09) !important;
    transform: translateX(3px);
}
.fi-sidebar-item-icon,
.fi-sidebar-item-button svg {
    transition: color .18s ease, transform .18s ease !important;
}
.fi-sidebar-item-button:hover svg { transform: scale(1.1); }

/* Item activo */
.fi-sidebar-item-active .fi-sidebar-item-button,
.fi-sidebar-item-button[aria-current="page"],
.fi-sidebar-item-button.fi-active {
    background: linear-gradient(135deg, #0047AB 0%, #0a6cdc 100%) !important;
    box-shadow: 0 6px 16px -4px rgba(10, 108, 220, .55), inset 0 1px 0 rgba(255,255,255,.18) !important;
    transform: none;
}
.fi-sidebar-item-active .fi-sidebar-item-button::before,
.fi-sidebar-item-button[aria-current="page"]::before {
    content: "";
    position: absolute;
    left: -.45rem; top: 22%; bottom: 22%;
    width: 4px;
    border-radius: 9999px;
    background: #7dd3fc;
    box-shadow: 0 0 10px rgba(125, 211, 252, .8);
}

/* Etiquetas de grupo */
.fi-sidebar-group-label {
    text-transform: uppercase !important;
    letter-spacing: .09em !important;
    font-size: .68rem !important;
    font-weight: 700 !important;
    color: rgba(255,255,255,.38) !important;
}
.fi-sidebar-group { margin-top: .4rem; }

/* Scroll fino */
.fi-sidebar-nav::-webkit-scrollbar { width: 6px; }
.fi-sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.14); border-radius: 9999px; }
.fi-sidebar-nav::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,.26); }
.fi-sidebar-nav::-webkit-scrollbar-track { background: transparent; }

/* Barra superior */
.fi-topbar nav {
    backdrop-filter: blur(10px);
    box-shadow: 0 1px 0 rgba(15, 23, 42, .06), 0 8px 24px -16px rgba(15, 23, 42, .25) !important;
}
html:not(.dark) .fi-topbar nav {
    background-color: rgba(255,255,255,.88) !important;
}
.fi-topbar .fi-icon-btn { border-radius: .7rem !important; transition: background-color .15s ease !important; }
.fi-user-menu .fi-avatar {
    box-shadow: 0 0 0 2px #fff, 0 0 0 4px rgba(0, 71, 171, .35);
    transition: box-shadow .18s ease;
}
.fi-user-menu button:hover .fi-avatar { box-shadow: 0 0 0 2px #fff, 0 0 0 4px #0047AB; }

/* Botón colapsar sidebar */
.fi-sidebar-header .fi-icon-btn {
    border-radius: .65rem !important;
    background: rgba(255,255,255,.07) !important;
}
.fi-sidebar-header .fi-icon-btn:hover { background: rgba(255,255,255,.15) !important; }
</style>')
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString('
<style>
    /* ══════════ PULIDO VISUAL DEL DASHBOARD (solo apariencia) ══════════ */
    html:not(.dark) .fi-dashboard-page {
        background:
            radial-gradient(900px 380px at 100% -5%, rgba(99, 102, 241, .08), transparent 60%),
            radial-gradient(700px 360px at -5% 25%, rgba(14, 165, 233, .06), transparent 60%),
            #ffffff;
    }

    /* Encabezado */
    .fi-dashboard-page .fi-header { padding-bottom: .9rem; border-bottom: 1px solid rgba(148, 163, 184, .25); }
    .fi-dashboard-page .fi-header-heading { letter-spacing: -.025em; color: #0b2a5b; }
    .dark .fi-dashboard-page .fi-header-heading { color: #f1f5f9; }
    .fi-dashboard-page .fi-header-subheading { font-size: .95rem; margin-top: .35rem; color: #64748b; }

    /* Botón Exportar PDF */
    .fi-dashboard-page .fi-header .fi-btn {
        border-radius: .8rem;
        font-weight: 700;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: #fff;
        border: none;
        box-shadow: 0 8px 18px -8px rgba(79, 70, 229, .6), inset 0 1px 0 rgba(255,255,255,.22);
        transition: transform .18s ease, filter .18s ease, box-shadow .18s ease;
    }
    .fi-dashboard-page .fi-header .fi-btn svg { color: #fff; }
    .fi-dashboard-page .fi-header .fi-btn:hover { filter: brightness(1.08); transform: translateY(-1px); }

    /* Filtro de período */
    .fi-dashboard-page .fi-fo-select .fi-input-wrp,
    .fi-dashboard-page .fi-input-wrp {
        border-radius: .85rem;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .05), inset 0 0 0 1px rgba(148, 163, 184, .35);
        transition: box-shadow .18s ease;
    }
    .fi-dashboard-page .fi-input-wrp:focus-within {
        box-shadow: 0 0 0 4px rgba(99, 102, 241, .14), inset 0 0 0 1.5px #6366f1;
    }

    /* Secciones (gráficos, rankings, metas) */
    .fi-dashboard-page .fi-section {
        border-radius: 1.2rem;
        border: 1px solid rgba(148, 163, 184, .22);
        box-shadow: 0 12px 28px -20px rgba(15, 23, 42, .35), 0 1px 3px rgba(15, 23, 42, .04);
        --tw-ring-color: transparent;
        transition: box-shadow .2s ease, transform .2s ease;
    }
    .fi-dashboard-page .fi-section:hover {
        box-shadow: 0 18px 36px -20px rgba(15, 23, 42, .4), 0 1px 3px rgba(15, 23, 42, .05);
    }
    .fi-dashboard-page .fi-section-header { border-bottom: 1px solid rgba(148, 163, 184, .18); }
    .fi-dashboard-page .fi-section-header-heading {
        font-weight: 800;
        letter-spacing: -.01em;
        position: relative;
        padding-left: .75rem;
    }
    .fi-dashboard-page .fi-section-header-heading::before {
        content: "";
        position: absolute;
        left: 0; top: 12%; bottom: 12%;
        width: 4px;
        border-radius: 9999px;
        background: linear-gradient(180deg, #6366f1, #0ea5e9);
    }

    /* Tablas de rankings */
    .fi-dashboard-page .fi-section table thead th {
        font-size: .68rem;
        letter-spacing: .08em;
        color: #94a3b8;
    }
    .fi-dashboard-page .fi-section table tbody tr { transition: background-color .15s ease; }
    .fi-dashboard-page .fi-section table tbody tr:hover { background-color: rgba(99, 102, 241, .05); }
    .fi-dashboard-page .fi-section table tbody tr td:first-child { font-weight: 800; }
    .fi-dashboard-page .fi-section table tbody tr:nth-child(1) td:first-child { color: #d97706; }
    .fi-dashboard-page .fi-section table tbody tr:nth-child(2) td:first-child { color: #64748b; }
    .fi-dashboard-page .fi-section table tbody tr:nth-child(3) td:first-child { color: #b45309; }

    /* Espaciado entre bloques */
    .fi-dashboard-page .fi-wi { gap: 1.25rem; }
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
                RedirectVendorsToPos::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                SetDelphiConnection::class,
            ]);
    }
}
