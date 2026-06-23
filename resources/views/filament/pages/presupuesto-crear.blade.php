<x-filament-panels::page>
<style>
.pres-wrap {
    display: flex; flex-direction: column;
    min-height: calc(100vh - 185px); gap: 0;
}

/* ─── Barra superior ─── */
.pres-topbar {
    display: flex; flex-direction: column; gap: .55rem;
    padding: .65rem .85rem; background: white;
    border: 1px solid #e5e7eb; border-radius: 1rem 1rem 0 0; border-bottom: none;
}
.dark .pres-topbar { background: #1f2937; border-color: #374151; }

.sel-lbl { font-size: .58rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; display: block; margin-bottom: .18rem; white-space: nowrap; }

/* ─── Combobox de cliente ─── */
.cliente-combo { position: relative; }
.cliente-combo-input {
    width: 100%; padding: .38rem 1.8rem .38rem .65rem;
    border: 1px solid #d1d5db; border-radius: .55rem;
    font-size: .8rem; background: #f9fafb; color: #111827; outline: none; transition: border-color .12s;
}
.dark .cliente-combo-input { background: #374151; border-color: #4b5563; color: #f9fafb; }
.cliente-combo-input:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,.2); background: white; }
.dark .cliente-combo-input:focus { background: #1f2937; }
.cliente-combo-chevron { position: absolute; right: .5rem; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; }
.cliente-dropdown {
    position: absolute; top: calc(100% + 2px); left: 0; right: 0; z-index: 60;
    background: white; border: 1.5px solid #6366f1; border-radius: .65rem;
    max-height: 220px; overflow-y: auto;
    box-shadow: 0 8px 24px rgba(0,0,0,.15);
}
.dark .cliente-dropdown { background: #1f2937; border-color: #4f46e5; }
.cliente-option {
    display: flex; align-items: center; justify-content: space-between; gap: .5rem;
    padding: .5rem .85rem; border-bottom: 1px solid #f3f4f6; cursor: pointer; transition: background .08s;
}
.dark .cliente-option { border-color: #374151; }
.cliente-option:last-child { border-bottom: none; }
.cliente-option:hover, .cliente-option.hl { background: #f5f3ff; }
.dark .cliente-option:hover, .dark .cliente-option.hl { background: rgba(99,102,241,.12); }

/* ─── Fila de búsqueda de ítem ─── */
.search-row { display: flex; gap: .5rem; align-items: flex-end; }
.search-row-item { flex: 1; position: relative; }

.pres-search-wrap { position: relative; }
.pres-search {
    width: 100%; padding: .42rem .75rem .42rem 2.3rem;
    border: 2px solid #6366f1; border-radius: .7rem;
    font-size: .88rem; font-weight: 500; background: white; color: #111827;
    outline: none; box-shadow: 0 0 0 3px rgba(99,102,241,.1); transition: border-color .12s;
}
.dark .pres-search { background: #1f2937; color: #f9fafb; }
.pres-search.item-selected { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.1); background: #f0fdf4; }
.dark .pres-search.item-selected { background: rgba(22,163,74,.08); }
.pres-search-icon { position: absolute; left: .7rem; top: 50%; transform: translateY(-50%); pointer-events: none; }

/* ─── Dropdown de ítems ─── */
.pres-results {
    position: absolute; top: calc(100% + 2px); left: 0; right: 0; z-index: 50;
    background: white; border: 1.5px solid #6366f1; border-top: none;
    border-radius: 0 0 .85rem .85rem; max-height: 260px; overflow-y: auto;
    box-shadow: 0 8px 30px rgba(0,0,0,.18);
}
.dark .pres-results { background: #1f2937; border-color: #4f46e5; }
.result-item {
    display: flex; align-items: center; gap: .65rem;
    padding: .6rem .9rem; border-bottom: 1px solid #f3f4f6; cursor: pointer; transition: background .1s;
}
.dark .result-item { border-color: #374151; }
.result-item:last-child { border-bottom: none; }
.result-item:hover, .result-item.active { background: #f5f3ff; }
.dark .result-item:hover, .dark .result-item.active { background: rgba(99,102,241,.1); }

/* ─── Inputs de cantidad y precio ─── */
.row-input {
    padding: .42rem .6rem; border: 2px solid #d1d5db; border-radius: .7rem;
    font-size: .88rem; font-weight: 700; text-align: right; outline: none;
    background: white; color: #111827; width: 100%; transition: all .12s;
}
.dark .row-input { background: #1f2937; border-color: #374151; color: #f9fafb; }
.row-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
.row-input:disabled { opacity: .4; cursor: not-allowed; background: #f3f4f6; }
.dark .row-input:disabled { background: #111827; }

/* ─── Tabla de ítems ─── */
.pres-body {
    flex: 1; background: white;
    border: 1px solid #e5e7eb; border-top: none; border-bottom: none; overflow-x: auto;
}
.dark .pres-body { background: #1f2937; border-color: #374151; }
.pres-table { width: 100%; border-collapse: collapse; }
.pres-table th {
    padding: .55rem .85rem; font-size: .7rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: #6b7280; background: #f9fafb;
    border-bottom: 2px solid #e5e7eb; text-align: left; white-space: nowrap;
}
.dark .pres-table th { background: #111827; border-color: #374151; color: #9ca3af; }
.pres-table td { padding: .5rem .85rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.dark .pres-table td { border-color: #374151; color: #e5e7eb; }
.pres-table tr:last-child td { border-bottom: none; }
.pres-table tr:hover td { background: #f9fafb; }
.dark .pres-table tr:hover td { background: rgba(99,102,241,.04); }

.inline-input {
    padding: .3rem .5rem; border: 1.5px solid #d1d5db; border-radius: .45rem;
    font-size: .85rem; font-weight: 700; text-align: right; outline: none;
    background: white; color: #111827; transition: border-color .12s;
}
.dark .inline-input { background: #374151; border-color: #4b5563; color: #f9fafb; }
.inline-input:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,.15); }
.inline-qty   { width: 68px; }
.inline-price { width: 120px; }

.del-btn {
    width: 1.7rem; height: 1.7rem; border-radius: .4rem;
    border: 1px solid #fecaca; background: #fff5f5; color: #ef4444;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; padding: 0; transition: all .12s;
}
.del-btn:hover { background: #fee2e2; }
.empty-state {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 3rem; text-align: center; color: #d1d5db;
}

/* ─── Barra inferior ─── */
.pres-footer {
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    padding: .85rem 1.25rem; background: white;
    border: 1px solid #e5e7eb; border-radius: 0 0 1rem 1rem; border-top: 2px solid #e5e7eb; flex-shrink: 0;
}
.dark .pres-footer { background: #1f2937; border-color: #374151; }
.guardar-btn {
    display: flex; align-items: center; gap: .5rem; padding: .8rem 1.75rem;
    background: #16a34a; color: white; font-weight: 900; font-size: .95rem;
    border: none; border-radius: .85rem; cursor: pointer;
    box-shadow: 0 3px 12px rgba(22,163,74,.35); transition: all .15s; white-space: nowrap;
}
.guardar-btn:hover { background: #15803d; transform: translateY(-1px); }
.guardar-btn:active { transform: scale(.97); }
.guardar-btn:disabled { background: #e5e7eb; color: #9ca3af; cursor: not-allowed; box-shadow: none; transform: none; }

@media (max-width: 640px) {
    .qty-precio-col { display: none !important; }
    .hint-desktop   { display: none !important; }
}
@media (min-width: 641px) {
    .hint-mobile { display: none !important; }
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<div x-data="presupApp()" x-init="init()" @focus-search.window="$nextTick(() => { $refs.search?.focus(); $refs.search?.select(); })">
<div class="pres-wrap">

    {{-- ══════ BARRA SUPERIOR ══════ --}}
    <div class="pres-topbar">

        {{-- Fila 1: cliente, deposito, obs --}}
        <div style="display:flex;gap:.6rem;flex-wrap:wrap;">

            {{-- Combobox cliente --}}
            <div style="flex:2;min-width:180px;"
                 x-data="clienteCombo(@js($clientes), @js($clienteId))"
                 @click.outside="close()"
                 wire:ignore>
                <label class="sel-lbl">Cliente</label>
                <div class="cliente-combo">
                    <input type="text" class="cliente-combo-input" x-ref="clienteInput"
                        :placeholder="selectedName || 'Buscar cliente...'"
                        x-model="query"
                        @focus="open = true"
                        @input="open = true; hl = 0"
                        @keydown.arrow-down.prevent="moveDown()"
                        @keydown.arrow-up.prevent="moveUp()"
                        @keydown.enter.prevent="pickHighlighted()"
                        @keydown.escape.prevent="close()"
                        autocomplete="off"
                    />
                    <span class="cliente-combo-chevron">
                        <svg style="width:.8rem;height:.8rem" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </span>
                    <div class="cliente-dropdown" x-show="open && filtered.length > 0" style="display:none;">
                        <template x-for="(c, i) in filtered" :key="c.custnr">
                            <div class="cliente-option" :class="i === hl ? 'hl' : ''"
                                :data-ci="i" @mouseenter="hl = i" @click="pick(c)">
                                <span style="font-size:.8rem;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:80%;" class="dark:text-gray-100" x-text="c.custname"></span>
                                <span style="font-size:.68rem;color:#9ca3af;flex-shrink:0;" x-text="c.custnr"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>


{{-- Observaciones --}}
            <div style="flex:2;min-width:160px;">
                <label class="sel-lbl">Observaciones</label>
                <input type="text" wire:model.lazy="obs" placeholder="Opcional..."
                    style="width:100%;padding:.38rem .65rem;border:1px solid #d1d5db;border-radius:.55rem;font-size:.8rem;background:#f9fafb;color:#111827;outline:none;"
                    class="dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
                />
            </div>
        </div>

        {{-- Fila 2: búsqueda + cantidad + precio en línea --}}
        <div class="search-row">

            {{-- Búsqueda de ítem --}}
            <div class="search-row-item" style="flex:3;">
                <label class="sel-lbl" style="display:flex;align-items:center;gap:.35rem;min-height:1rem;">
                    <span x-show="!localItem" style="display:block;">Código / Descripción del ítem</span>
                    <span x-show="localItem" style="display:flex;align-items:center;gap:.3rem;color:#16a34a;overflow:hidden;max-width:100%;">
                        <svg style="width:.65rem;height:.65rem;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.62rem;" x-text="localItem ? localItem.item + ' · ' + localItem.descr : ''"></span>
                        <button @click.prevent="clearLocalItem()" title="Limpiar" style="flex-shrink:0;background:none;border:none;color:#9ca3af;cursor:pointer;padding:0;line-height:1;font-size:.7rem;">✕</button>
                    </span>
                </label>
                <div class="pres-search-wrap">
                    <div class="pres-search-icon">
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
                        :placeholder="localItem ? 'Buscar otro ítem...' : 'Buscar por código o descripción...'"
                        :class="localItem ? 'pres-search item-selected' : 'pres-search'"
                        @input="if (localItem) { localItem = null; }"
                        @keydown.arrow-down.prevent="activeIdx = Math.min(activeIdx + 1, results.length - 1)"
                        @keydown.arrow-up.prevent="activeIdx = Math.max(activeIdx - 1, 0)"
                        @keydown.enter.prevent="selectActive()"
                        @keydown.escape.prevent="$wire.set('search', ''); activeIdx = -1; localItem = null;"
                        @keydown.tab.prevent="if (localItem) { $refs.qty?.focus(); $refs.qty?.select(); }"
                    />

                    {{-- Dropdown de ítems --}}
                    @if(count($searchResults) > 0)
                    <div class="pres-results">
                        @foreach($searchResults as $i => $product)
                        <div class="result-item" :class="activeIdx === {{ $i }} ? 'active' : ''"
                            @click="setLocalItemByIdx({{ $i }})"
                            @mouseenter="activeIdx = {{ $i }}"
                        >
                            <div style="flex:1;min-width:0;">
                                <p style="font-size:.85rem;font-weight:700;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" class="dark:text-white">{{ $product->descr }}</p>
                                <p style="font-size:.7rem;color:#9ca3af;">{{ $product->item }}@if($product->scode) · {{ $product->scode }}@endif</p>
                            </div>
                            <p style="font-size:.88rem;font-weight:900;color:#4f46e5;white-space:nowrap;flex-shrink:0;">
                                Gs.&nbsp;{{ number_format($product->precio, 0, ',', '.') }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Cantidad --}}
            <div class="qty-precio-col" style="flex-shrink:0;width:88px;">
                <label class="sel-lbl">Cant.</label>
                <input type="number" min="1" x-ref="qty"
                    x-model.number="localQty"
                    :disabled="!localItem"
                    class="row-input"
                    @focus="$event.target.select()"
                    @keydown.enter.prevent="$nextTick(() => { $refs.precio?.focus(); $refs.precio?.select(); })"
                    @keydown.tab.prevent="$nextTick(() => { $refs.precio?.focus(); $refs.precio?.select(); })"
                />
            </div>

            {{-- Precio --}}
            <div class="qty-precio-col" style="flex-shrink:0;width:145px;">
                <label class="sel-lbl">Precio Unit. (Gs.)</label>
                <input type="number" min="0" x-ref="precio"
                    x-model.number="localPrecio"
                    :disabled="!localItem"
                    class="row-input"
                    @focus="$event.target.select()"
                    @keydown.enter.prevent="confirmarItem()"
                    @keydown.shift.tab.prevent="$nextTick(() => { $refs.qty?.focus(); $refs.qty?.select(); })"
                />
            </div>

        </div>{{-- /search-row --}}
    </div>

    {{-- ══════ TABLA DE ÍTEMS ══════ --}}
    <div class="pres-body">
        @if(empty($cart))
        <div class="empty-state">
            <svg style="width:3.5rem;height:3.5rem;margin-bottom:1rem" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
            </svg>
            <p style="font-size:.95rem;color:#9ca3af;font-weight:600;">Sin ítems</p>
            <p class="hint-desktop" style="font-size:.78rem;color:#d1d5db;margin-top:.3rem;">Buscá un ítem, ingresá cantidad y precio, luego Enter</p>
            <p class="hint-mobile"  style="font-size:.78rem;color:#d1d5db;margin-top:.3rem;">Buscá un ítem y tocalo para agregarlo a la grilla</p>
        </div>
        @else
        <table class="pres-table">
            <thead>
                <tr>
                    <th style="width:2.5rem;">#</th>
                    <th>Código</th>
                    <th style="width:100%;">Descripción</th>
                    <th style="text-align:right;">Cantidad</th>
                    <th style="text-align:right;">Precio Unit.</th>
                    <th style="text-align:right;">Subtotal</th>
                    <th style="width:2.5rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($cart as $i => $item)
                <tr x-data="cartRow({{ $i }}, {{ $item['qty'] }}, {{ $item['precio'] }})" x-init="init()">
                    <td style="color:#9ca3af;font-size:.8rem;text-align:center;">{{ $i + 1 }}</td>
                    <td style="font-size:.78rem;color:#6b7280;white-space:nowrap;">{{ $item['item'] }}</td>
                    <td style="font-size:.85rem;font-weight:600;color:#111827;" class="dark:text-gray-100">{{ $item['descr'] }}</td>
                    <td style="text-align:right;">
                        <input id="qty-{{ $i }}" type="number" min="1"
                            x-model.number="qty" class="inline-input inline-qty"
                            @focus="$event.target.select()"
                            @keydown.tab.prevent="$nextTick(() => document.getElementById('precio-{{ $i }}')?.focus())"
                            @keydown.enter.prevent="$nextTick(() => document.getElementById('precio-{{ $i }}')?.focus())"
                            @blur="save()"
                        />
                    </td>
                    <td style="text-align:right;">
                        <input id="precio-{{ $i }}" type="number" min="0"
                            x-model.number="precio" class="inline-input inline-price"
                            @focus="$event.target.select()"
                            @keydown.enter.prevent="save(); $dispatch('focus-search')"
                            @keydown.shift.tab.prevent="$nextTick(() => document.getElementById('qty-{{ $i }}')?.focus())"
                            @blur="save()"
                        />
                    </td>
                    <td style="text-align:right;font-size:.88rem;font-weight:800;color:#4f46e5;white-space:nowrap;">
                        Gs.&nbsp;<span x-text="(qty * precio).toLocaleString('es-PY')"></span>
                    </td>
                    <td style="text-align:center;">
                        <button class="del-btn" wire:click="removeFromCart({{ $i }})" title="Eliminar">
                            <svg style="width:.8rem;height:.8rem" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- ══════ BARRA INFERIOR ══════ --}}
    <div class="pres-footer">
        <div>
            <p style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#9ca3af;margin-bottom:.15rem;">Total del Presupuesto</p>
            <p style="font-size:2.25rem;font-weight:900;color:#4f46e5;line-height:1;letter-spacing:-.02em;">
                Gs.&nbsp;{{ number_format($this->getTotal(), 0, ',', '.') }}
            </p>
            @if(count($cart) > 0)
            <p style="font-size:.7rem;color:#9ca3af;margin-top:.2rem;">{{ count($cart) }} ítem(s)</p>
            @endif
        </div>

        <div style="display:flex;gap:.65rem;flex-wrap:wrap;justify-content:flex-end;">

            <button wire:click="guardar" wire:loading.attr="disabled" wire:target="guardar,guardarWhatsapp" class="guardar-btn" {{ empty($cart) ? 'disabled' : '' }}>
                <svg style="width:1.1rem;height:1.1rem" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span wire:loading.remove wire:target="guardar,guardarWhatsapp">GUARDAR Y PDF</span>
                <span wire:loading wire:target="guardar,guardarWhatsapp">Guardando...</span>
            </button>

            @if(!empty($clienteCelular))
            <button wire:click="guardarWhatsapp" wire:loading.attr="disabled" wire:target="guardar,guardarWhatsapp"
                style="display:flex;align-items:center;gap:.5rem;padding:.8rem 1.5rem;background:#22c55e;color:white;font-weight:900;font-size:.92rem;border:none;border-radius:.85rem;cursor:pointer;box-shadow:0 3px 12px rgba(34,197,94,.35);transition:all .15s;white-space:nowrap;"
                {{ empty($cart) ? 'disabled' : '' }}>
                <svg style="width:1.1rem;height:1.1rem" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                <span>GUARDAR Y WHATSAPP</span>
            </button>
            @endif

        </div>
    </div>

</div>{{-- /pres-wrap --}}
</div>{{-- /x-data --}}

<script>
function presupApp() {
    return {
        activeIdx:    -1,
        results:      [],
        localItem:    null,
        localQty:     1,
        localPrecio:  0,

        init() {
            this.$watch('$wire.searchResults', (v) => {
                this.results   = v ?? [];
                this.activeIdx = -1;
            });
            window.addEventListener('abrir-pdf',       e => window.open(e.detail.url, '_blank'));
            window.addEventListener('abrir-whatsapp',  e => window.open(e.detail.url, '_blank'));
            // Coincidencia exacta por código: seleccionar automáticamente
            window.addEventListener('item-exacto', e => this.setLocalItem(e.detail.item));
        },

        /* Selecciona ítem desde el dropdown */
        setLocalItem(item) {
            this.localItem   = item;
            this.localQty    = 1;
            this.localPrecio = parseFloat(item.precio) || 0;
            this.results   = [];
            this.activeIdx = -1;
            this.$wire.set('search', '');

            // Mobile: agregar directo a la grilla sin pasar por qty/precio
            if (window.innerWidth < 641) {
                this.$nextTick(() => this.confirmarItem());
                return;
            }

            // Desktop: foco en cantidad
            this.$nextTick(() => {
                this.$refs.qty?.focus();
                this.$refs.qty?.select();
            });
        },

        setLocalItemByIdx(i) {
            const item = this.results[i];
            if (item) this.setLocalItem(item);
        },

        clearLocalItem() {
            this.localItem = null;
            this.$nextTick(() => this.$refs.search?.focus());
        },

        /* Enter en el precio → agrega a la grilla */
        confirmarItem() {
            if (!this.localItem) return;
            this.$wire.addItemWithParams(
                this.localItem.item,
                this.localItem.descr,
                this.localQty,
                this.localPrecio,
                parseFloat(this.localItem.costo) || 0
            );
            this.localItem  = null;
            this.localQty   = 1;
            this.localPrecio = 0;
        },

        /* Enter en el buscador */
        selectActive() {
            if (this.results.length === 0) return;
            if (this.activeIdx >= 0 && this.activeIdx < this.results.length) {
                this.setLocalItem(this.results[this.activeIdx]);
            } else if (this.results.length === 1) {
                this.setLocalItem(this.results[0]);
            } else {
                this.activeIdx = 0;
            }
        },
    };
}

function clienteCombo(clientes, initialId) {
    return {
        clientes, query: '', open: false, hl: 0, selectedName: '', selectedId: initialId,
        init() {
            const found = this.clientes.find(c => String(c.custnr) === String(this.selectedId));
            if (found) this.selectedName = found.custname;
        },
        get filtered() {
            const q = this.query.toLowerCase().trim();
            if (!q) return this.clientes.slice(0, 60);
            return this.clientes.filter(c =>
                (c.custname || '').toLowerCase().includes(q) ||
                String(c.custnr || '').includes(q)
            ).slice(0, 60);
        },
        pick(c) {
            this.selectedName = c.custname; this.selectedId = c.custnr;
            this.query = ''; this.open = false;
            this.$wire.set('clienteId', c.custnr);
            this.$nextTick(() => window.dispatchEvent(new CustomEvent('focus-search')));
        },
        pickHighlighted() { const item = this.filtered[this.hl]; if (item) this.pick(item); },
        moveDown() {
            if (!this.open) { this.open = true; return; }
            this.hl = Math.min(this.hl + 1, this.filtered.length - 1); this.scrollHl();
        },
        moveUp() { this.hl = Math.max(this.hl - 1, 0); this.scrollHl(); },
        scrollHl() {
            this.$nextTick(() => this.$el.querySelector(`[data-ci="${this.hl}"]`)?.scrollIntoView({ block: 'nearest' }));
        },
        close() { this.open = false; this.query = ''; },
    };
}

function cartRow(idx, initQty, initPrecio) {
    return {
        idx, qty: initQty, precio: initPrecio,
        init() {
            this.$watch('$wire.cart', (cart) => {
                if (cart[this.idx]) { this.qty = cart[this.idx].qty; this.precio = cart[this.idx].precio; }
            });
        },
        save() { this.$wire.updateCartItem(this.idx, this.qty, this.precio); },
    };
}
</script>

</x-filament-panels::page>
