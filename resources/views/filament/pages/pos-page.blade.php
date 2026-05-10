<x-filament-panels::page>
<style>
/* ─── Contenedor principal ─── */
.pos-wrap {
    display: flex; flex-direction: column;
    height: calc(100vh - 185px); min-height: 500px;
    gap: 0;
}

/* ─── Barra superior: 2 filas (selects arriba, búsqueda abajo) ─── */
.pos-topbar {
    display: flex; flex-direction: column; gap: .5rem;
    padding: .65rem .85rem; background: white;
    border: 1px solid #e5e7eb; border-radius: 1rem 1rem 0 0;
    border-bottom: none;
}
.dark .pos-topbar { background: #1f2937; border-color: #374151; }

.topbar-selects { display: flex; align-items: flex-end; gap: .6rem; flex-wrap: wrap; }
.topbar-search  { position: relative; }

/* ─── Fix selects (sin flechas duplicadas) ─── */
.pos-sel {
    -webkit-appearance: none; -moz-appearance: none; appearance: none;
    background-color: #f9fafb;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%236b7280'%3E%3Cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' clip-rule='evenodd'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right .55rem center; background-size: 1rem;
    padding: .38rem 1.9rem .38rem .65rem;
    border: 1px solid #d1d5db; border-radius: .55rem;
    font-size: .8rem; color: #111827; cursor: pointer; width: 100%;
}
.dark .pos-sel { background-color: #374151; border-color: #4b5563; color: #f9fafb; }
.pos-sel:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,.2); }
.sel-label { font-size: .58rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: .15rem; white-space: nowrap; }

/* ─── Buscador ─── */
.pos-search-wrap { position: relative; }
.pos-search {
    width: 100%; padding: .5rem .75rem .5rem 2.4rem;
    border: 2px solid #6366f1; border-radius: .7rem;
    font-size: .88rem; font-weight: 500; background: white; color: #111827;
    outline: none; box-shadow: 0 0 0 3px rgba(99,102,241,.1);
}
.dark .pos-search { background: #1f2937; color: #f9fafb; }
.pos-search-icon { position: absolute; left: .7rem; top: 50%; transform: translateY(-50%); pointer-events: none; }

/* ─── Dropdown de resultados (solo móvil, posicionado bajo el buscador) ─── */
.mobile-dropdown {
    display: none;
    position: absolute; top: calc(100% + 2px); left: 0; right: 0; z-index: 40;
    background: white; border: 1.5px solid #6366f1; border-top: none;
    border-radius: 0 0 .85rem .85rem;
    max-height: 55vh; overflow-y: auto;
    box-shadow: 0 8px 30px rgba(0,0,0,.18);
}
.dark .mobile-dropdown { background: #1f2937; border-color: #4f46e5; }
.mob-item {
    display: flex; align-items: center; gap: .65rem;
    padding: .7rem .9rem; border-bottom: 1px solid #f3f4f6; cursor: pointer;
    transition: background .1s;
}
.dark .mob-item { border-color: #374151; }
.mob-item:last-child { border-bottom: none; }
.mob-item:hover, .mob-item:active { background: #f5f3ff; }
.dark .mob-item:hover { background: rgba(99,102,241,.1); }

/* ─── Botón fullscreen ─── */
.fs-btn {
    display: flex; align-items: center; gap: .3rem; padding: .38rem .7rem;
    border: 1px solid #d1d5db; border-radius: .55rem; background: transparent;
    font-size: .75rem; font-weight: 600; color: #6b7280; cursor: pointer; white-space: nowrap;
    transition: all .12s; flex-shrink: 0; align-self: flex-end;
}
.fs-btn:hover { background: #f3f4f6; color: #374151; }
.dark .fs-btn { border-color: #4b5563; color: #9ca3af; }
.dark .fs-btn:hover { background: #374151; }

/* ─── Área central: carrito + productos ─── */
.pos-body {
    display: flex; flex: 1; overflow: hidden;
    border: 1px solid #e5e7eb; border-top: none; border-bottom: none;
}
.dark .pos-body { border-color: #374151; }

/* ─── Panel derecho: carrito ─── */
.pos-cart {
    width: 420px; flex-shrink: 0; display: flex; flex-direction: column;
    border-left: 1px solid #e5e7eb; background: white;
    order: 2;
}
.pos-products { order: 1; }
.dark .pos-cart { background: #1f2937; border-color: #374151; }
.cart-header {
    padding: .65rem 1rem; font-size: .78rem; font-weight: 700;
    color: #374151; border-bottom: 1px solid #f3f4f6;
    display: flex; align-items: center; gap: .4rem; background: #fafafa; flex-shrink: 0;
}
.dark .cart-header { background: #111827; color: #d1d5db; border-color: #374151; }
.cart-body { flex: 1; overflow-y: auto; }
.cart-row {
    display: flex; align-items: center; gap: .5rem; padding: .6rem .85rem;
    border-bottom: 1px solid #f9fafb; cursor: pointer; transition: background .1s;
}
.dark .cart-row { border-color: #374151; }
.cart-row:hover { background: #f5f3ff; }
.cart-row:focus { outline: none; background: #ede9fe; }
.dark .cart-row:hover { background: rgba(99,102,241,.08); }
.dark .cart-row:focus { background: rgba(99,102,241,.15); }
.del-btn {
    flex-shrink: 0; width: 1.6rem; height: 1.6rem; border-radius: .4rem;
    border: 1px solid #fecaca; background: #fff5f5; color: #ef4444;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; padding: 0; transition: all .12s;
}
.del-btn:hover { background: #fee2e2; }

/* ─── Panel derecho: productos ─── */
.pos-products {
    flex: 1; overflow-y: auto; background: #f9fafb; padding: .75rem;
}
.dark .pos-products { background: #111827; }
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: .6rem;
}
.product-card {
    background: white; border-radius: .85rem; border: 1.5px solid #e5e7eb;
    padding: .75rem; display: flex; flex-direction: column; gap: .35rem;
    cursor: pointer; transition: all .15s; user-select: none;
}
.dark .product-card { background: #1f2937; border-color: #374151; }
.product-card:hover { border-color: #6366f1; box-shadow: 0 2px 12px rgba(99,102,241,.15); transform: translateY(-1px); }
.product-card:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(99,102,241,.25); }
.product-card:active { transform: scale(.97); }
.product-add-btn {
    margin-top: auto; width: 100%; padding: .4rem;
    background: #6366f1; color: white; border: none; border-radius: .55rem;
    font-size: .78rem; font-weight: 700; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: .25rem;
    transition: background .12s;
}
.product-add-btn:hover { background: #4f46e5; }

/* ─── Barra inferior ─── */
.pos-footer {
    display: flex; align-items: center; gap: 1rem;
    padding: .75rem 1.25rem; background: white;
    border: 1px solid #e5e7eb; border-radius: 0 0 1rem 1rem;
    border-top: 2px solid #e5e7eb; flex-shrink: 0;
}
.dark .pos-footer { background: #1f2937; border-color: #374151; }
.cobrar-btn {
    display: flex; align-items: center; gap: .5rem; padding: .75rem 2rem;
    background: #4f46e5; color: white; font-weight: 900; font-size: 1rem;
    border: none; border-radius: .85rem; cursor: pointer;
    box-shadow: 0 3px 12px rgba(79,70,229,.35); transition: all .15s; white-space: nowrap;
}
.cobrar-btn:hover { background: #4338ca; transform: translateY(-1px); }
.cobrar-btn:active { transform: scale(.97); }
.cobrar-btn:disabled { background: #e5e7eb; color: #9ca3af; cursor: not-allowed; box-shadow: none; transform: none; }

/* ─── Modales ─── */
.modal-back {
    position: fixed; inset: 0; z-index: 50;
    background: rgba(0,0,0,.55); backdrop-filter: blur(4px);
    display: flex; align-items: flex-end; justify-content: center;
}
@media(min-width:640px){ .modal-back { align-items: center; } }
.modal-box {
    background: white; width: 100%; max-width: 400px;
    border-radius: 1.25rem 1.25rem 0 0; padding: 1.4rem;
    box-shadow: 0 -8px 30px rgba(0,0,0,.2);
}
@media(min-width:640px){ .modal-box { border-radius: 1.25rem; } }
.dark .modal-box { background: #1f2937; }
.pay-method {
    flex: 1; display: flex; flex-direction: column; align-items: center; gap: .4rem;
    padding: .85rem .4rem; border-radius: .85rem; border: 2px solid #e5e7eb;
    font-size: .78rem; font-weight: 700; cursor: pointer; transition: all .15s;
    background: white; color: #6b7280;
}
.dark .pay-method { background: #1f2937; border-color: #374151; }
.pay-method.sel-green  { border-color: #22c55e; background: #f0fdf4; color: #16a34a; }
.pay-method.sel-blue   { border-color: #3b82f6; background: #eff6ff; color: #1d4ed8; }
.pay-method.sel-purple { border-color: #a855f7; background: #faf5ff; color: #7e22ce; }

/* ─── Controles de cantidad en modal de edición ─── */
.qty-ctrl { display: flex; align-items: center; gap: .5rem; margin-bottom: .85rem; }
.qty-btn {
    width: 2.4rem; height: 2.4rem; border-radius: .55rem;
    border: 1.5px solid #d1d5db; background: #f9fafb; color: #374151;
    font-size: 1.2rem; font-weight: 700; cursor: pointer; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; transition: all .12s;
}
.qty-btn:hover { background: #ede9fe; border-color: #6366f1; color: #6366f1; }
.qty-num {
    flex: 1; text-align: center; padding: .5rem;
    border: 1.5px solid #d1d5db; border-radius: .55rem;
    font-size: 1.2rem; font-weight: 800; color: #111827; outline: none;
    background: white;
}
.qty-num:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,.12); }
.dark .qty-num { background: #374151; color: #f9fafb; border-color: #4b5563; }

/* ─── Fullscreen ─── */
body.pos-fullscreen aside,
body.pos-fullscreen nav.fi-sidebar,
body.pos-fullscreen .fi-sidebar { display: none !important; }
body.pos-fullscreen .fi-main-ctn { margin-left: 0 !important; padding-left: 0 !important; }

/* ─── Responsive: móvil ─── */
@media (max-width: 767px) {
    .pos-wrap { height: auto; min-height: 0; }
    .pos-topbar { border-radius: .85rem .85rem 0 0; padding: .55rem .65rem; }

    /* Selects: 3 columnas */
    .topbar-selects > div { flex: 1; min-width: 0; }
    .pos-sel { font-size: .75rem; }

    /* Cuerpo: apilado */
    .pos-body { flex-direction: column; overflow: visible; height: auto; border: none; }

    /* Ocultar grid de productos — los resultados se muestran en el dropdown */
    .pos-products { display: none; }

    /* Mostrar dropdown de resultados */
    .mobile-dropdown { display: block; }

    /* Carrito: ancho completo */
    .pos-cart { width: 100%; border-right: none; border-top: 1px solid #e5e7eb; max-height: 280px; }
    .dark .pos-cart { border-color: #374151; }

    /* Footer pegado al fondo */
    .pos-footer { position: sticky; bottom: 0; z-index: 10; border-radius: 0; border-left: none; border-right: none; }
    .cobrar-btn { padding: .7rem 1.25rem; }
}

/* Desktop: nunca mostrar dropdown */
@media (min-width: 768px) {
    .mobile-dropdown { display: none !important; }
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>

<div x-data="posApp()" x-init="init()" @focus-search.window="$nextTick(() => $refs.search?.focus())">
<div class="pos-wrap">

    {{-- ══════ BARRA SUPERIOR ══════ --}}
    <div class="pos-topbar">

        {{-- Fila 1: Selects --}}
        <div class="topbar-selects">

            {{-- Cliente --}}
            <div style="flex:2;min-width:100px;">
                <label class="sel-label">Cliente</label>
                <select wire:model.live="clienteId" class="pos-sel">
                    @foreach($clientes as $c)
                        <option value="{{ $c->custnr }}">{{ $c->custname }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Depósito --}}
            <div style="flex:1;min-width:80px;">
                <label class="sel-label">Depósito</label>
                <select wire:model.live="depositoId" class="pos-sel">
                    @foreach($depositos as $d)
                        <option value="{{ $d->deponr }}">{{ $d->depo_nombre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Cajero --}}
            <div style="flex:1;min-width:80px;">
                <label class="sel-label">Cajero</label>
                <select wire:model.live="cajeroId" class="pos-sel">
                    @foreach($cajeros as $c)
                        <option value="{{ $c->pernr }}">{{ $c->pername }}</option>
                    @endforeach
                </select>
            </div>

        </div>

        {{-- Fila 2: Buscador --}}
        <div class="topbar-search">
            <div class="pos-search-wrap">
                <div class="pos-search-icon">
                    <div wire:loading wire:target="search">
                        <svg style="width:1rem;height:1rem;color:#6366f1;animation:spin 1s linear infinite" fill="none" viewBox="0 0 24 24">
                            <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"/>
                        </svg>
                    </div>
                    <div wire:loading.remove wire:target="search">
                        <svg style="width:1rem;height:1rem;color:#9ca3af" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                        </svg>
                    </div>
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.200ms="search"
                    x-ref="search"
                    autofocus
                    placeholder="Buscar producto por descripción, referencia o código..."
                    class="pos-search"
                    @keydown.arrow-down.prevent="arrowDown()"
                    @keydown.arrow-up.prevent="arrowUp()"
                    @keydown.enter.prevent="enter()"
                    @keydown.escape.prevent="clearSearch()"
                />
            </div>

            {{-- Dropdown de resultados (solo móvil) --}}
            @if(count($searchResults) > 0)
            <div class="mobile-dropdown">
                @foreach($searchResults as $i => $product)
                <div class="mob-item" wire:click="addToCart('{{ $product->item }}')">
                    <div style="flex:1;min-width:0;">
                        <p style="font-size:.85rem;font-weight:700;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" class="dark:text-white">{{ $product->descr }}</p>
                        <p style="font-size:.7rem;color:#9ca3af;">{{ $product->item }}@if($product->scode) · {{ $product->scode }}@endif
                            &nbsp;·&nbsp;
                            <span style="{{ $product->stock_depo > 5 ? 'color:#16a34a' : ($product->stock_depo > 0 ? 'color:#92400e' : 'color:#b91c1c') }};font-weight:700;">
                                Stock: {{ $product->stock_depo }}
                            </span>
                        </p>
                    </div>
                    <p style="font-size:.9rem;font-weight:900;color:#4f46e5;white-space:nowrap;flex-shrink:0;">Gs.&nbsp;{{ number_format($product->precio, 0, ',', '.') }}</p>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- ══════ CUERPO: carrito + productos ══════ --}}
    <div class="pos-body">

        {{-- ── CARRITO (izquierda / móvil abajo) ── --}}
        <div class="pos-cart">
            <div class="cart-header">
                <svg style="width:1rem;height:1rem;color:#6b7280" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.847-7.156a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                </svg>
                Carrito
                @if(count($cart) > 0)
                    <span style="margin-left:auto;background:#ede9fe;color:#6d28d9;font-size:.65rem;font-weight:800;padding:.1rem .5rem;border-radius:999px;">{{ count($cart) }}</span>
                    <span style="font-size:.65rem;color:#9ca3af;font-weight:400;">· tocá para editar</span>
                @endif
            </div>

            <div class="cart-body">
                @if(empty($cart))
                    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;padding:2rem;text-align:center;color:#d1d5db;">
                        <svg style="width:3rem;height:3rem;margin-bottom:.75rem" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/>
                        </svg>
                        <p style="font-size:.85rem;color:#9ca3af;">Carrito vacío</p>
                        <p style="font-size:.72rem;color:#d1d5db;margin-top:.25rem;">Buscá o seleccioná productos</p>
                    </div>
                @else
                    @foreach($cart as $i => $item)
                        <div class="cart-row"
                            data-cart-idx="{{ $i }}"
                            tabindex="0"
                            @click="openEdit({{ $i }}, {{ $item['qty'] }}, {{ $item['precio'] }}, '{{ addslashes($item['descr']) }}')"
                            @keydown.enter.prevent="openEdit({{ $i }}, {{ $item['qty'] }}, {{ $item['precio'] }}, '{{ addslashes($item['descr']) }}')"
                            @keydown.delete.prevent="$wire.removeFromCart({{ $i }})"
                            @keydown.arrow-down.prevent="focusCartItem({{ $i + 1 }})"
                            @keydown.arrow-up.prevent="{{ $i === 0 ? '$refs.search.focus()' : 'focusCartItem(' . ($i - 1) . ')' }}"
                        >
                            <div style="flex:1;min-width:0;">
                                <p style="font-size:.82rem;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" class="dark:text-gray-100">{{ $item['descr'] }}</p>
                                <p style="font-size:.68rem;color:#9ca3af;">{{ $item['item'] }} · x{{ $item['qty'] }}</p>
                            </div>
                            <div style="text-align:right;flex-shrink:0;">
                                <p style="font-size:.82rem;font-weight:800;color:#4f46e5;">Gs.&nbsp;{{ number_format($item['precio'] * $item['qty'], 0, ',', '.') }}</p>
                                <p style="font-size:.68rem;color:#9ca3af;">c/u&nbsp;{{ number_format($item['precio'], 0, ',', '.') }}</p>
                            </div>
                            <button class="del-btn" @click.stop="$wire.removeFromCart({{ $i }})" title="Eliminar">
                                <svg style="width:.8rem;height:.8rem" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- ── PRODUCTOS (derecha, solo desktop) ── --}}
        <div class="pos-products">
            @if(count($searchResults) > 0)
                <div class="products-grid">
                    @foreach($searchResults as $i => $product)
                        <div
                            class="product-card"
                            :class="activeIdx === {{ $i }} ? 'border-indigo-400 shadow-md' : ''"
                            data-result="{{ $i }}"
                            tabindex="0"
                            wire:click="addToCart('{{ $product->item }}')"
                            @keydown.enter.prevent="$wire.addToCart('{{ $product->item }}')"
                            @keydown.arrow-down.prevent="focusResult(activeIdx + 1)"
                            @keydown.arrow-up.prevent="activeIdx > 0 ? focusResult(activeIdx - 1) : (activeIdx = -1, $refs.search.focus())"
                            @keydown.escape.prevent="activeIdx = -1; $refs.search.focus()"
                            @focus="activeIdx = {{ $i }}"
                        >
                            <p style="font-size:.65rem;color:#9ca3af;font-weight:600;">{{ $product->item }}@if($product->scode) · {{ $product->scode }}@endif</p>
                            <p style="font-size:.82rem;font-weight:700;color:#111827;line-height:1.3;flex:1;" class="dark:text-gray-100">{{ $product->descr }}</p>
                            <span style="font-size:.68rem;font-weight:700;padding:.15rem .5rem;border-radius:999px;display:inline-block;width:fit-content;
                                {{ $product->stock_depo > 5 ? 'background:#dcfce7;color:#16a34a;' : ($product->stock_depo > 0 ? 'background:#fef9c3;color:#92400e;' : 'background:#fee2e2;color:#b91c1c;') }}">
                                Stock:&nbsp;{{ $product->stock_depo }}
                            </span>
                            <p style="font-size:.9rem;font-weight:900;color:#4f46e5;">Gs.&nbsp;{{ number_format($product->precio, 0, ',', '.') }}</p>
                            <button class="product-add-btn" @click.stop="$wire.addToCart('{{ $product->item }}')">
                                <svg style="width:.9rem;height:.9rem" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                                Agregar
                            </button>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:#d1d5db;text-align:center;">
                    <svg style="width:3.5rem;height:3.5rem;margin-bottom:1rem" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <p style="font-size:.9rem;color:#9ca3af;">
                        @if(strlen(trim($search)) >= 2)
                            Sin resultados para "{{ $search }}"
                        @else
                            Buscá un producto para empezar
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- ══════ BARRA INFERIOR ══════ --}}
    <div class="pos-footer" style="z-index:5;position:relative;">
        {{-- Total --}}
        <div style="flex:1;">
            <p style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#9ca3af;margin-bottom:.1rem;">Total a cobrar</p>
            <p style="font-size:2rem;font-weight:900;color:#4f46e5;line-height:1;letter-spacing:-.02em;">
                Gs.&nbsp;{{ number_format($this->getCartTotal(), 0, ',', '.') }}
            </p>
        </div>
        <button wire:click="openPaymentModal" class="cobrar-btn" {{ empty($cart) ? 'disabled' : '' }}>
            <svg style="width:1.2rem;height:1.2rem" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
            </svg>
            COBRAR
            <span style="font-size:.72rem;font-weight:400;opacity:.65;">F10</span>
        </button>
    </div>

</div>{{-- /pos-wrap --}}

{{-- ══════════════ MODAL: EDITAR CANTIDAD Y PRECIO ══════════════ --}}
<div x-show="edit.open" class="modal-back" @click.self="cancelEdit()" @keydown.escape.window="cancelEdit()" style="display:none;">
    <div class="modal-box">
        <p style="font-size:.95rem;font-weight:800;color:#111827;margin-bottom:1rem;" class="dark:text-white" x-text="edit.descr"></p>

        {{-- Cantidad --}}
        <label style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#6b7280;display:block;margin-bottom:.4rem;">Cantidad</label>
        <div class="qty-ctrl">
            <button class="qty-btn" @click="if(edit.qty > 1) edit.qty--">−</button>
            <input class="qty-num" type="number" min="1" x-model.number="edit.qty" />
            <button class="qty-btn" @click="edit.qty++">+</button>
        </div>

        {{-- Precio --}}
        <label style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#6b7280;display:block;margin-bottom:.4rem;">Precio unitario (Gs.)</label>
        <input
            id="edit-precio"
            type="number" min="0"
            x-model.number="edit.precio"
            style="width:100%;padding:.75rem;border-radius:.8rem;border:2px solid #6366f1;font-size:1.4rem;font-weight:800;text-align:center;color:#111827;background:white;outline:none;box-shadow:0 0 0 3px rgba(99,102,241,.12);margin-bottom:.85rem;"
            @focus="$event.target.select()"
            @keydown.enter.prevent="saveEdit()"
        />

        {{-- Subtotal --}}
        <div style="background:#f5f3ff;border-radius:.7rem;padding:.6rem 1rem;display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <span style="font-size:.78rem;color:#7c3aed;font-weight:600;">Subtotal (x<span x-text="edit.qty"></span>):</span>
            <span style="font-size:1.05rem;font-weight:900;color:#4f46e5;" x-text="'Gs. ' + (edit.qty * edit.precio).toLocaleString('es-PY')"></span>
        </div>

        <div style="display:flex;gap:.65rem;">
            <button @click="cancelEdit()" style="flex:1;padding:.7rem;border-radius:.8rem;border:1.5px solid #e5e7eb;background:transparent;font-weight:700;color:#6b7280;cursor:pointer;font-size:.88rem;">Cancelar</button>
            <button @click="saveEdit()" style="flex:1.5;padding:.7rem;border-radius:.8rem;background:#4f46e5;color:white;font-weight:900;font-size:.95rem;border:none;cursor:pointer;box-shadow:0 3px 10px rgba(79,70,229,.3);">Guardar</button>
        </div>
    </div>
</div>

{{-- ══════════════ MODAL: COBRO ══════════════ --}}
@if($showPaymentModal)
<div class="modal-back" @keydown.escape.window="$wire.closePaymentModal()" @keydown.enter.window="$wire.set('amountReceived', localAmount).then(() => $wire.confirmSale())">
    <div class="modal-box" style="max-width:420px;" @click.stop>
        <div style="background:linear-gradient(135deg,#4f46e5,#7c3aed);margin:-1.4rem -1.4rem 1.1rem;padding:1.1rem 1.35rem;border-radius:.85rem .85rem 0 0;">
            <p style="font-size:1rem;font-weight:900;color:white;">Cobrar Venta</p>
            <p style="font-size:.75rem;color:rgba(255,255,255,.65);margin-top:.1rem;">Seleccioná la forma de pago</p>
        </div>

        <div style="background:#f8fafc;border-radius:.8rem;padding:.85rem;text-align:center;margin-bottom:.9rem;" class="dark:bg-gray-700">
            <p style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;">Total</p>
            <p style="font-size:1.9rem;font-weight:900;color:#111827;" class="dark:text-white">Gs.&nbsp;{{ number_format($this->getCartTotal(), 0, ',', '.') }}</p>
        </div>

        <p style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;margin-bottom:.45rem;">Forma de pago</p>
        <div style="display:flex;gap:.5rem;margin-bottom:.9rem;">
            <button wire:click="$set('paymentMethod','efectivo')" class="pay-method {{ $paymentMethod==='efectivo'?'sel-green':'' }}">
                <svg style="width:1.4rem;height:1.4rem" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75"/></svg>
                Efectivo
            </button>
            <button wire:click="$set('paymentMethod','transferencia')" class="pay-method {{ $paymentMethod==='transferencia'?'sel-blue':'' }}">
                <svg style="width:1.4rem;height:1.4rem" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                Transferencia
            </button>
            <button wire:click="$set('paymentMethod','tarjeta')" class="pay-method {{ $paymentMethod==='tarjeta'?'sel-purple':'' }}">
                <svg style="width:1.4rem;height:1.4rem" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                Tarjeta
            </button>
        </div>

        @if($paymentMethod === 'efectivo')
        <div style="background:#f0fdf4;border-radius:.8rem;padding:.85rem;margin-bottom:.9rem;">
            <label style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#16a34a;display:block;margin-bottom:.35rem;">Monto recibido</label>
            <input id="amount-input" type="number"
                x-model="localAmount"
                style="width:100%;padding:.6rem;border-radius:.7rem;border:1.5px solid #86efac;background:white;font-size:1.25rem;font-weight:800;text-align:center;outline:none;"
                @focus="$event.target.select()" />
            @php $cartTotal = $this->getCartTotal(); @endphp
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:.5rem;">
                <p style="font-size:.8rem;font-weight:600;color:#166534;">Vuelto:</p>
                <p x-text="'Gs. ' + new Intl.NumberFormat('es-PY').format(Math.max(0, localAmount - {{ $cartTotal }}))"
                   :style="localAmount >= {{ $cartTotal }} ? 'font-size:1.35rem;font-weight:900;color:#16a34a;' : 'font-size:1.35rem;font-weight:900;color:#dc2626;'"></p>
            </div>
        </div>
        @endif

        <div style="display:flex;gap:.65rem;">
            <button wire:click="closePaymentModal" style="flex:1;padding:.7rem;border-radius:.8rem;border:1.5px solid #e5e7eb;background:transparent;font-weight:700;color:#6b7280;cursor:pointer;">Cancelar</button>
            <button @click="$wire.set('amountReceived', localAmount).then(() => $wire.confirmSale())"
                wire:loading.attr="disabled" wire:target="confirmSale"
                style="flex:1.5;padding:.7rem;border-radius:.8rem;background:#4f46e5;color:white;font-weight:900;font-size:.95rem;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.4rem;box-shadow:0 4px 14px rgba(79,70,229,.35);">
                <svg style="width:1rem;height:1rem" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span wire:loading.remove wire:target="confirmSale">Confirmar</span>
                <span wire:loading wire:target="confirmSale">Procesando...</span>
            </button>
        </div>
    </div>
</div>
@endif

</div>{{-- /x-data posApp --}}

<script>
function posApp() {
    return {
        activeIdx: -1,
        isFullscreen: false,
        edit: { open: false, idx: null, qty: 1, precio: 0, descr: '' },
        localAmount: 0,

        init() {
            this.$watch('$wire.searchResults', () => { this.activeIdx = -1; });
            window.addEventListener('keydown', (e) => {
                if (e.key === 'F10') { e.preventDefault(); this.$wire.openPaymentModal(); }
                if (e.key === 'F11') { e.preventDefault(); this.toggleFullscreen(); }
            });
            window.addEventListener('toggle-fullscreen', () => this.toggleFullscreen());
            window.addEventListener('focus-amount', () => {
                this.$nextTick(() => {
                    this.localAmount = this.$wire.amountReceived;
                    const el = document.getElementById('amount-input');
                    el?.focus();
                    el?.select();
                });
            });
            window.addEventListener('beforeunload', () => document.body.classList.remove('pos-fullscreen'));
        },

        arrowDown() {
            const results = document.querySelectorAll('[data-result]');
            if (results.length > 0) {
                if (this.activeIdx < results.length - 1) {
                    this.activeIdx++;
                    this.$nextTick(() => results[this.activeIdx]?.scrollIntoView({ block: 'nearest' }));
                }
            } else {
                this.focusCartItem(0);
            }
        },
        arrowUp() {
            if (this.activeIdx > 0) this.activeIdx--;
            else this.activeIdx = -1;
        },
        enter() {
            const results = document.querySelectorAll('[data-result]');
            if (this.activeIdx >= 0 && results[this.activeIdx]) {
                results[this.activeIdx].click();
            } else if (results.length === 1) {
                results[0].click();
            } else if (results.length > 1) {
                this.focusResult(0);
            }
        },
        focusResult(idx) {
            const results = document.querySelectorAll('[data-result]');
            if (results[idx]) {
                this.activeIdx = idx;
                results[idx].focus();
                results[idx].scrollIntoView({ block: 'nearest' });
            }
        },
        focusCartItem(idx) {
            const items = document.querySelectorAll('[data-cart-idx]');
            if (items[idx]) {
                items[idx].focus();
                items[idx].scrollIntoView({ block: 'nearest' });
            }
        },
        clearSearch() {
            this.$wire.set('search', '');
            this.activeIdx = -1;
            this.$refs.search?.focus();
        },

        openEdit(idx, qty, precio, descr) {
            this.edit = { open: true, idx, qty: parseInt(qty), precio: parseFloat(precio), descr };
            this.$nextTick(() => document.getElementById('edit-precio')?.focus());
        },
        saveEdit() {
            this.$wire.updateCartItem(this.edit.idx, this.edit.qty, this.edit.precio);
            this.edit.open = false;
            this.$nextTick(() => this.$refs.search?.focus());
        },
        cancelEdit() {
            this.edit.open = false;
            this.$nextTick(() => this.$refs.search?.focus());
        },

        toggleFullscreen() {
            this.isFullscreen = !this.isFullscreen;
            if (this.isFullscreen) {
                document.body.classList.add('pos-fullscreen');
                document.documentElement.requestFullscreen?.().catch(() => {});
            } else {
                document.body.classList.remove('pos-fullscreen');
                document.exitFullscreen?.().catch(() => {});
            }
            this.$refs.search?.focus();
        },
    };
}
</script>

</x-filament-panels::page>
