@php
    $user = auth()->user();
    $accesos = $user?->accesos ?? [];
    $esSuper = $user?->isSuperAdmin() ?? false;
    $items = [];

    // Inicio = Dashboard (vendedores y cajeros no lo usan: entran directo al POS)
    if ($esSuper || (!in_array($user->role, ['vendedor', 'cajero']) && ($accesos['dashboard'] ?? false))) {
        $items[] = ['Inicio', route('filament.admin.pages.dashboard'), request()->routeIs('filament.admin.pages.dashboard'), 'home'];
    }
    if (\App\Filament\Pages\PosPage::canAccess()) {
        $items[] = ['Vender', route('filament.admin.pages.pos-page'), request()->routeIs('filament.admin.pages.pos-page'), 'cart'];
    }
    if (\App\Filament\Resources\HistorialVentasResource::canAccess()) {
        $items[] = ['Ventas', route('filament.admin.resources.historial-ventas.index'), request()->routeIs('filament.admin.resources.historial-ventas.*'), 'receipt'];
    }
    if (\App\Filament\Pages\CierreCajaPage::canAccess()) {
        $items[] = ['Caja', route('filament.admin.pages.cierre-caja'), request()->routeIs('filament.admin.pages.cierre-caja'), 'cash'];
    }
@endphp

<style>
.mnav { display: none; }
@media (max-width: 767px) {
    :root { --mnav-h: calc(3.9rem + env(safe-area-inset-bottom)); }
    .mnav {
        display: flex; position: fixed; left: 0; right: 0; bottom: 0; z-index: 20;
        background: rgba(255, 255, 255, .96); -webkit-backdrop-filter: blur(14px); backdrop-filter: blur(14px);
        border-top: 1px solid #eef0f6; border-radius: 1.4rem 1.4rem 0 0;
        padding: .25rem .4rem env(safe-area-inset-bottom);
        box-shadow: 0 -12px 28px -14px rgba(30, 41, 99, .28);
    }
    .mnav-btn {
        flex: 1 1 0; min-width: 0; min-height: 44px; height: 3.4rem;
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .12rem;
        background: none; border: none; cursor: pointer; text-decoration: none;
        color: #8b93a7; font-size: .66rem; font-weight: 700; letter-spacing: .01em;
        -webkit-tap-highlight-color: transparent;
    }
    .mnav-ico {
        display: flex; align-items: center; justify-content: center;
        width: 3.1rem; height: 1.9rem; border-radius: 999px;
        transition: background .2s ease, color .2s ease, transform .2s ease, box-shadow .2s ease;
    }
    .mnav-ico svg { width: 1.45rem; height: 1.45rem; }
    .mnav-btn.active { color: #4f46e5; }
    .mnav-btn.active .mnav-ico {
        background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff;
        box-shadow: 0 8px 16px -8px rgba(79, 70, 229, .8);
    }
    .mnav-btn:active .mnav-ico { transform: scale(.92); background: #eef0ff; }
    .mnav-btn.active:active .mnav-ico { background: linear-gradient(135deg, #4f46e5, #7c3aed); }
    /* Espacio para que la barra no tape contenido */
    body:not(.pos-app) .fi-main { padding-bottom: calc(var(--mnav-h) + 1rem) !important; }
    /* Punto de venta: la barra "Ver venta" queda justo encima de la navegación */
    body.pos-app .pos-wrap { height: calc(100dvh - 4rem - var(--mnav-h)) !important; }
    /* El panel de venta a pantalla completa termina justo encima de la navegación (bottom: var(--mnav-h)) */
}
</style>

<nav class="mnav" aria-label="Navegación principal">
    @foreach($items as [$label, $url, $active, $icon])
        <a href="{{ $url }}" wire:navigate class="mnav-btn {{ $active ? 'active' : '' }}" @if($active) aria-current="page" @endif>
            <span class="mnav-ico">@include('filament.components.mnav-icon', ['icon' => $icon])</span>
            <span>{{ $label }}</span>
        </a>
    @endforeach
    <button type="button" class="mnav-btn" x-data @click="$store.sidebar.open()">
        <span class="mnav-ico">@include('filament.components.mnav-icon', ['icon' => 'menu'])</span>
        <span>Menú</span>
    </button>
</nav>
