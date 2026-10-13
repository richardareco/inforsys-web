<x-filament-panels::page>
<style>
#pos-minimal {
    display:flex; flex-direction:column;
    height:calc(100vh - 4rem);
    background:#ffffff;
}

/* ── Fila superior: cliente / cajero / depósito ── */
#pos-header {
    display:flex; align-items:flex-end; gap:1rem; padding:.75rem 1rem .6rem;
    background:#ffffff; border-bottom:1px solid #e2e8f0; flex-shrink:0; flex-wrap:wrap;
}
.ctx-group { display:flex; flex-direction:column; gap:.2rem; }
.ctx-label { font-size:.65rem; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.7px; }
.ctx-select {
    appearance:none !important; -webkit-appearance:none !important;
    background-image:none !important;
    background:#f8fafc; color:#1e293b;
    border:1px solid #e2e8f0; border-radius:.4rem;
    padding:.35rem .65rem; font-size:.85rem; outline:none; cursor:pointer;
    transition:border-color .15s;
}
.ctx-select:focus { border-color:#0047AB; }
.ctx-hint {
    margin-left:auto; display:flex; align-items:center; gap:.5rem;
    font-size:.7rem; color:#cbd5e1;
}
.ctx-hint kbd {
    background:#f1f5f9; border:1px solid #e2e8f0; border-radius:.25rem;
    padding:.1rem .35rem; color:#64748b; font-size:.68rem;
}

/* ── Fila de búsqueda + total ── */
#pos-search-bar {
    display:flex; align-items:center; gap:.75rem; padding:.6rem 1rem;
    background:#f8fafc; border-bottom:2px solid #e2e8f0; flex-shrink:0;
}
#pos-search-wrap { flex:1; position:relative; }
#pos-search-input {
    width:100%; padding:.7rem 1rem .7rem 2.6rem; font-size:1.1rem;
    border:2px solid #e2e8f0; border-radius:.5rem;
    background:#ffffff; color:#1e293b;
    outline:none; transition:border-color .15s;
    box-shadow:0 1px 3px rgba(0,0,0,.06);
}
#pos-search-input:focus { border-color:#0047AB; box-shadow:0 0 0 3px rgba(0,71,171,.1); }
#pos-search-input::placeholder { color:#cbd5e1; }
.pos-search-icon {
    position:absolute; left:.8rem; top:50%; transform:translateY(-50%);
    color:#94a3b8; pointer-events:none;
}

/* ── Total box ── */
#pos-total-box {
    display:flex; flex-direction:column; align-items:flex-end;
    background:#ffffff; border:2px solid #0047AB; border-radius:.5rem; padding:.45rem 1rem;
    min-width:13rem; line-height:1.15; flex-shrink:0;
}
#pos-total-label  { font-size:.62rem; font-weight:700; color:#0047AB; text-transform:uppercase; letter-spacing:.8px; }
#pos-total-amount { font-size:2.3rem; font-weight:800; color:#0047AB; font-variant-numeric:tabular-nums; }
#pos-total-meta   { font-size:.68rem; color:#94a3b8; }

/* ── Resultados búsqueda ── */
#pos-search-results {
    position:absolute; top:calc(100% + .25rem); left:0; right:0; z-index:50;
    background:#ffffff; border:1px solid #e2e8f0; border-radius:.5rem;
    box-shadow:0 8px 24px rgba(0,0,0,.12); overflow:hidden; max-height:22rem; overflow-y:auto;
}
.pos-result-row {
    display:flex; align-items:center; gap:.75rem;
    padding:.55rem .9rem; cursor:pointer; transition:background .1s;
    border-bottom:1px solid #f1f5f9;
}
.pos-result-row:last-child { border-bottom:none; }
.pos-result-row:hover { background:#eff6ff; }
.pos-result-descr { flex:1; font-size:.85rem; color:#1e293b; font-weight:600; }
.pos-result-code  { font-size:.72rem; color:#94a3b8; margin-top:.1rem; }
.pos-result-price { font-size:.9rem; color:#0047AB; font-weight:700; text-align:right; min-width:5.5rem; font-variant-numeric:tabular-nums; }
.pos-result-stock { font-size:.7rem; color:#94a3b8; text-align:right; margin-top:.1rem; }
.pos-result-stock.low { color:#ef4444; font-weight:600; }

/* ── Body ── */
#pos-body { display:flex; flex:1; overflow:hidden; }

/* ── Tabla ítems ── */
#pos-items-panel { flex:1; overflow-y:auto; background:#ffffff; }
#pos-items-empty { padding:3rem; text-align:center; color:#cbd5e1; }
#pos-items-empty .ico { font-size:2.5rem; margin-bottom:.5rem; }
#pos-items-empty .msg { font-size:.9rem; font-weight:600; color:#94a3b8; }
#pos-items-empty .sub { font-size:.78rem; color:#cbd5e1; margin-top:.25rem; }

#pos-items-table { width:100%; border-collapse:collapse; }
#pos-items-table thead th {
    position:sticky; top:0; z-index:1;
    background:#f8fafc; color:#64748b;
    font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.6px;
    padding:.5rem .75rem; border-bottom:2px solid #e2e8f0; text-align:left;
}
#pos-items-table thead th.r { text-align:right; }
#pos-items-table tbody tr:hover { background:#f0f9ff; }
#pos-items-table tbody tr:nth-child(even) td { background:#fafafa; }
#pos-items-table tbody tr:nth-child(even):hover td { background:#f0f9ff; }
#pos-items-table tbody td { padding:.55rem .75rem; font-size:.84rem; border-bottom:1px solid #f1f5f9; color:#1e293b; }
.td-num { text-align:right; font-variant-numeric:tabular-nums; }
.td-descr { font-weight:600; }
.td-code-val { font-size:.72rem; color:#94a3b8; }
.td-sub { font-weight:700; color:#0047AB; }
.qty-input {
    width:3.5rem; text-align:center; padding:.25rem .3rem;
    border:1px solid #e2e8f0; border-radius:.375rem;
    background:#f8fafc; font-size:.83rem; outline:none;
}
.qty-input:focus { border-color:#0047AB; }
.btn-remove {
    background:none; border:none; color:#e2e8f0; cursor:pointer;
    font-size:1.1rem; line-height:1; padding:.2rem .4rem; border-radius:.25rem; transition:color .1s;
}
.btn-remove:hover { color:#ef4444; }

/* ── Barra inferior con total y cobrar ── */
#pos-bottom-bar {
    display:flex; align-items:center; justify-content:flex-end; gap:.75rem;
    padding:.6rem 1rem; background:#ffffff; border-top:1px solid #e2e8f0; flex-shrink:0;
}
#pos-cobrar-btn {
    padding:.65rem 2rem; font-size:1rem; font-weight:700; letter-spacing:.4px;
    background:#0047AB; color:#fff; border:none; border-radius:.5rem;
    cursor:pointer; transition:background .15s; white-space:nowrap;
}
#pos-cobrar-btn:hover:not(:disabled) { background:#003d99; }
#pos-cobrar-btn:disabled { background:#cbd5e1; cursor:not-allowed; }
#pos-clear-btn {
    padding:.6rem 1.1rem;
    background:none; border:1px solid #e2e8f0; border-radius:.5rem;
    font-size:.78rem; color:#94a3b8; cursor:pointer; transition:color .1s, border-color .1s;
}
#pos-clear-btn:hover { color:#ef4444; border-color:#fecaca; }

/* ── Modal pago ── */
#pos-pay-backdrop {
    position:fixed; inset:0; background:rgba(15,23,42,.5);
    display:flex; align-items:center; justify-content:center; z-index:9999;
}
#pos-pay-modal {
    background:#fff; border-radius:.75rem; width:27rem; max-width:95vw;
    box-shadow:0 24px 60px rgba(0,0,0,.2); overflow:hidden;
}
#pos-pay-modal-hdr {
    background:#0047AB; padding:.9rem 1.1rem;
    display:flex; justify-content:space-between; align-items:center;
}
#pos-pay-modal-hdr .ttl { font-size:1rem; font-weight:700; color:#fff; }
#pos-pay-modal-hdr .cls { background:none; border:none; color:rgba(255,255,255,.7); cursor:pointer; font-size:1.4rem; line-height:1; }
#pos-pay-modal-body { padding:1.1rem; }
.pay-total {
    text-align:center; background:#f8fafc; border-radius:.5rem;
    padding:.65rem; margin-bottom:1rem;
}
.pay-total .lbl    { font-size:.65rem; color:#94a3b8; text-transform:uppercase; letter-spacing:.6px; }
.pay-total .amount { font-size:1.9rem; font-weight:800; color:#0047AB; font-variant-numeric:tabular-nums; }
.pay-methods { display:grid; grid-template-columns:1fr 1fr; gap:.45rem; margin-bottom:.9rem; }
.pay-btn {
    padding:.55rem .4rem; border:2px solid #e2e8f0; border-radius:.5rem;
    background:#f8fafc; color:#475569; font-size:.78rem; font-weight:600;
    cursor:pointer; transition:all .15s; display:flex; flex-direction:column; align-items:center; gap:.2rem;
}
.pay-btn:hover  { border-color:#0047AB; color:#0047AB; background:#eff6ff; }
.pay-btn.active { border-color:#0047AB; background:#0047AB; color:#fff; }
.pay-btn .ico   { font-size:1.3rem; }
.pay-field { margin-bottom:.7rem; }
.pay-field label { display:block; font-size:.73rem; color:#64748b; margin-bottom:.3rem; }
.pay-input-n {
    width:100%; padding:.6rem .75rem; border:1px solid #e2e8f0; border-radius:.5rem;
    font-size:1rem; font-weight:600; color:#1e293b; outline:none; transition:border-color .15s;
}
.pay-input-n:focus { border-color:#0047AB; }
.pay-change {
    display:flex; justify-content:space-between; align-items:center;
    border-radius:.375rem; padding:.45rem .75rem; font-size:.83rem; font-weight:600; margin-bottom:.7rem;
}
.pay-change.ok  { background:#f0fdf4; color:#166534; }
.pay-change.neg { background:#fef2f2; color:#991b1b; }
#pos-confirm-btn {
    width:100%; padding:.75rem; font-size:.95rem; font-weight:700; letter-spacing:.4px;
    background:#16a34a; color:#fff; border:none; border-radius:.5rem;
    cursor:pointer; transition:background .15s;
}
#pos-confirm-btn:hover:not(:disabled) { background:#15803d; }
#pos-confirm-btn:disabled { background:#cbd5e1; cursor:not-allowed; }
</style>

<div
    id="pos-minimal"
    x-data="posMinimal()"
    x-init="init()"
    @keydown.f10.window.prevent="$wire.openPaymentModal()"
    @keydown.escape.window="handleEscape()"
    @focus-search.window="focusSearch()"
    @focus-amount.window="focusAmount()"
>

    {{-- ══ FILA 1: Cliente / Cajero / Depósito ══ --}}
    <div id="pos-header">
        <div class="ctx-group" style="flex:0 1 20rem;">
            <span class="ctx-label">Cliente</span>
            <div x-data="posClienteCombo(@js($clientes), @js($clienteId))" @click.outside="close()" style="position:relative;display:flex;gap:.3rem;align-items:center;">
                <input type="text"
                    class="ctx-select"
                    :placeholder="selectedName || 'Cliente...'"
                    x-model="query"
                    @focus="open = true"
                    @input="open = true; hl = 0"
                    @keydown.arrow-down.prevent="moveDown()"
                    @keydown.arrow-up.prevent="moveUp()"
                    @keydown.enter.prevent="pickHighlighted()"
                    @keydown.escape.prevent="close()"
                    autocomplete="off"
                    style="flex:1;padding:.35rem .65rem;border:1px solid #e2e8f0;border-radius:.4rem;font-size:.85rem;background:white;color:#1e293b;"
                />
                <button onclick="window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id')).call('openNewClienteModal')" style="padding:.35rem .5rem;background:#0047AB;color:white;border:none;border-radius:.4rem;cursor:pointer;font-size:.7rem;font-weight:700;white-space:nowrap;transition:background .15s;" onmouseover="this.style.background='#003d99'" onmouseout="this.style.background='#0047AB'">
                    +
                </button>
                <div x-show="open && filtered.length > 0" style="display:none;position:absolute;top:calc(100% + 2px);left:0;right:0;z-index:60;background:white;border:1px solid #0047AB;border-radius:.4rem;max-height:150px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,.1);">
                    <template x-for="(c, i) in filtered" :key="c.custnr">
                        <div :class="i === hl ? 'bg-blue-100' : ''" style="display:flex;align-items:center;justify-content:space-between;gap:.4rem;padding:.3rem .6rem;border-bottom:1px solid #f1f5f9;cursor:pointer;font-size:.75rem;transition:background .08s;" @mouseenter="hl = i" @click="pick(c)">
                            <span style="font-weight:600;color:#1e293b;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" x-text="c.custname"></span>
                            <span style="color:#94a3b8;flex-shrink:0;" x-text="c.custnr"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <div class="ctx-group">
            <span class="ctx-label">Cajero</span>
            <select wire:model.live="cajeroId" class="ctx-select" style="min-width:10rem;">
                @foreach($cajeros as $p)
                    <option value="{{ $p->pernr }}">{{ $p->pername }}</option>
                @endforeach
            </select>
        </div>
        <div class="ctx-group">
            <span class="ctx-label">Depósito</span>
            <select wire:model.live="depositoId" class="ctx-select" style="min-width:10rem;">
                @foreach($depositos as $d)
                    <option value="{{ $d->deponr }}">{{ $d->deponr }} – {{ $d->depo_nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="ctx-hint">
            <kbd>F10</kbd> Cobrar &nbsp; <kbd>Esc</kbd> Cerrar modal
        </div>
    </div>

    {{-- ══ FILA 2: Búsqueda + Total ══ --}}
    <div id="pos-search-bar">
        <div id="pos-search-wrap">
            <svg class="pos-search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input
                id="pos-search-input"
                type="text"
                wire:model.live.debounce.200ms="search"
                placeholder="Escanee código de barras o escriba descripción…"
                autocomplete="off"
                @keydown.enter.prevent="selectFirstResult()"
            >
            @if(count($searchResults) > 0)
            <div id="pos-search-results">
                @foreach($searchResults as $r)
                <div class="pos-result-row" wire:click="addToCart('{{ $r->item }}')">
                    <div style="flex:1;">
                        <div class="pos-result-descr">{{ $r->descr }}</div>
                        <div class="pos-result-code">{{ $r->item }}{{ $r->scode ? ' · '.$r->scode : '' }}</div>
                    </div>
                    <div>
                        <div class="pos-result-price">Gs. {{ number_format($r->precio, 0, ',', '.') }}</div>
                        <div class="pos-result-stock {{ $r->stock_depo < 5 ? 'low' : '' }}">
                            Stock: {{ $r->stock_depo }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div id="pos-total-box">
            <div id="pos-total-label">Total a cobrar</div>
            <div id="pos-total-amount">Gs. {{ number_format($this->getCartTotal(), 0, ',', '.') }}</div>
            <div id="pos-total-meta">
                {{ count($cart) }} {{ count($cart) === 1 ? 'ítem' : 'ítems' }} &nbsp;·&nbsp; F10 para cobrar
            </div>
        </div>
    </div>

    {{-- ══ BODY: Tabla ══ --}}
    <div id="pos-body">
        <div id="pos-items-panel">
            @if(empty($cart))
            <div id="pos-items-empty">
                <div class="ico">🛒</div>
                <div class="msg">Escanee o busque un producto</div>
                <div class="sub">Los ítems aparecerán aquí</div>
            </div>
            @else
            <table id="pos-items-table">
                <thead>
                    <tr>
                        <th style="width:2rem;">#</th>
                        <th style="width:7rem;">Código</th>
                        <th>Descripción</th>
                        <th class="r" style="width:5rem;">Cant.</th>
                        <th class="r" style="width:8rem;">Precio</th>
                        <th class="r" style="width:9rem;">Subtotal</th>
                        <th style="width:2.5rem;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cart as $i => $item)
                    <tr>
                        <td class="td-code-val" style="color:#cbd5e1;">{{ $i + 1 }}</td>
                        <td class="td-code-val">{{ $item['item'] }}</td>
                        <td class="td-descr">{{ $item['descr'] }}</td>
                        <td class="td-num">
                            <input
                                type="number" min="1"
                                value="{{ $item['qty'] }}"
                                class="qty-input"
                                @change="$wire.updateQty({{ $i }}, $event.target.value)"
                            >
                        </td>
                        <td class="td-num" style="color:#64748b;">Gs. {{ number_format($item['precio'], 0, ',', '.') }}</td>
                        <td class="td-num td-sub">Gs. {{ number_format($item['precio'] * $item['qty'], 0, ',', '.') }}</td>
                        <td style="text-align:center;">
                            <button class="btn-remove" wire:click="removeFromCart({{ $i }})">×</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    {{-- ══ BARRA INFERIOR: limpiar + cobrar ══ --}}
    <div id="pos-bottom-bar">
        @if(!empty($cart))
        <button id="pos-clear-btn" wire:click="$set('cart', [])">Limpiar carrito</button>
        @endif
        <button
            id="pos-cobrar-btn"
            wire:click="openPaymentModal()"
            {{ empty($cart) ? 'disabled' : '' }}
        >
            COBRAR &nbsp; Gs. {{ number_format($this->getCartTotal(), 0, ',', '.') }}
        </button>
    </div>

    {{-- ══ MODAL PAGO ══ --}}
    @if($showPaymentModal)
    <div id="pos-pay-backdrop" x-data x-init="$nextTick(() => { const el = document.getElementById('pay-amount-input'); if(el){el.focus();el.select();} })">
        <div id="pos-pay-modal">
            <div id="pos-pay-modal-hdr">
                <span class="ttl">Cobrar venta</span>
                <button class="cls" wire:click="closePaymentModal()">×</button>
            </div>
            <div id="pos-pay-modal-body">
                <div class="pay-total">
                    <div class="lbl">Total a cobrar</div>
                    <div class="amount">Gs. {{ number_format($this->getCartTotal(), 0, ',', '.') }}</div>
                </div>

                <div class="pay-methods">
                    <button class="pay-btn {{ $paymentMethod === 'efectivo' ? 'active' : '' }}" wire:click="$set('paymentMethod','efectivo')">
                        <span class="ico">💵</span> Efectivo
                    </button>
                    <button class="pay-btn {{ $paymentMethod === 'tarjeta' ? 'active' : '' }}" wire:click="$set('paymentMethod','tarjeta')">
                        <span class="ico">💳</span> Tarjeta
                    </button>
                    <button class="pay-btn {{ $paymentMethod === 'transferencia' ? 'active' : '' }}" wire:click="$set('paymentMethod','transferencia')">
                        <span class="ico">🏦</span> Transferencia
                    </button>
                    <button class="pay-btn {{ $paymentMethod === 'qr' ? 'active' : '' }}" wire:click="$set('paymentMethod','qr')">
                        <span class="ico">📱</span> QR
                    </button>
                </div>

                @if($paymentMethod === 'efectivo')
                <div class="pay-field">
                    <label>Monto recibido (Gs.)</label>
                    <input id="pay-amount-input" type="number" class="pay-input-n" wire:model.live="amountReceived" min="0" step="1000">
                </div>
                @php $vuelto = $amountReceived - $this->getCartTotal(); @endphp
                <div class="pay-change {{ $vuelto >= 0 ? 'ok' : 'neg' }}">
                    <span>{{ $vuelto >= 0 ? 'Vuelto:' : 'Falta:' }}</span>
                    <span>Gs. {{ number_format(abs($vuelto), 0, ',', '.') }}</span>
                </div>
                @endif

                <button
                    id="pos-confirm-btn"
                    wire:click="confirmSale()"
                    {{ ($paymentMethod === 'efectivo' && $amountReceived < $this->getCartTotal()) ? 'disabled' : '' }}
                >
                    ✓ &nbsp; CONFIRMAR VENTA
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
function posMinimal() {
    return {
        init() { this.$nextTick(() => this.focusSearch()); },
        focusSearch() {
            this.$nextTick(() => { const el = document.getElementById('pos-search-input'); if(el) el.focus(); });
        },
        focusAmount() {
            this.$nextTick(() => { const el = document.getElementById('pay-amount-input'); if(el){el.focus();el.select();} });
        },
        selectFirstResult() {
            const first = document.querySelector('.pos-result-row');
            if(first) first.click(); else this.focusSearch();
        },
        handleEscape() {
            if (@js($showPaymentModal)) @this.closePaymentModal();
        },
    };
}
document.addEventListener('livewire:navigated', () => {
    const el = document.getElementById('pos-search-input');
    if(el) el.focus();
});

function posClienteCombo(clientes, initialId) {
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
        },
        pickHighlighted() { const item = this.filtered[this.hl]; if (item) this.pick(item); },
        moveDown() {
            if (!this.open) { this.open = true; return; }
            this.hl = Math.min(this.hl + 1, this.filtered.length - 1);
        },
        moveUp() {
            this.hl = Math.max(this.hl - 1, 0);
        },
        close() { this.open = false; this.query = ''; },
    };
}
</script>

{{-- ══════════════ MODAL: NUEVO CLIENTE ══════════════ --}}
@if($showNewClienteModal)
<div style="position:fixed;inset:0;z-index:50;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);display:flex;align-items:flex-end;justify-content:center;" @keydown.escape.window="$wire.call('closeNewClienteModal')" @click.self="$wire.call('closeNewClienteModal')">
    <div style="background:white;width:100%;max-width:400px;border-radius:1.25rem 1.25rem 0 0;padding:1.4rem;box-shadow:0 -8px 30px rgba(0,0,0,.2);" @click.stop>
        <div style="background:linear-gradient(135deg,#0047AB,#1e3a8a);margin:-1.4rem -1.4rem 1.1rem;padding:1.1rem 1.35rem;border-radius:.85rem .85rem 0 0;">
            <p style="font-size:1rem;font-weight:900;color:white;">Nuevo Cliente</p>
            <p style="font-size:.75rem;color:rgba(255,255,255,.65);margin-top:.1rem;">Registro rápido</p>
        </div>

        <div style="display:flex;flex-direction:column;gap:.6rem;max-height:60vh;overflow-y:auto;">
            <div>
                <label style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;display:block;margin-bottom:.25rem;">Nombre *</label>
                <input wire:model="newClienteName" type="text" placeholder="Nombre completo" style="width:100%;padding:.5rem;border:1.5px solid #d1d5db;border-radius:.5rem;font-size:.8rem;outline:none;transition:border-color .2s;" @focus="this.style.borderColor='#0047AB'" @blur="this.style.borderColor='#d1d5db'"/>
            </div>

            <div>
                <label style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;display:block;margin-bottom:.25rem;">Nacionalidad</label>
                <select wire:model="newClienteNacionalidad" style="width:100%;padding:.5rem;border:1.5px solid #d1d5db;border-radius:.5rem;font-size:.8rem;outline:none;background:white;" @focus="this.style.borderColor='#0047AB'" @blur="this.style.borderColor='#d1d5db'">
                    <option value="">Seleccionar país...</option>
                    <option value="PY">Paraguay</option>
                    <option value="AR">Argentina</option>
                    <option value="BR">Brasil</option>
                    <option value="CL">Chile</option>
                    <option value="UY">Uruguay</option>
                    <option value="BO">Bolivia</option>
                    <option value="CO">Colombia</option>
                    <option value="PE">Perú</option>
                    <option value="EC">Ecuador</option>
                    <option value="VE">Venezuela</option>
                    <option value="GY">Guyana</option>
                    <option value="SR">Surinam</option>
                </select>
            </div>

            <div>
                <label style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;display:block;margin-bottom:.25rem;">Dirección</label>
                <input wire:model="newClienteDireccion" type="text" placeholder="Dirección..." style="width:100%;padding:.5rem;border:1.5px solid #d1d5db;border-radius:.5rem;font-size:.8rem;outline:none;transition:border-color .2s;" @focus="this.style.borderColor='#0047AB'" @blur="this.style.borderColor='#d1d5db'"/>
            </div>

            <div>
                <label style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;display:block;margin-bottom:.25rem;">Teléfono</label>
                <input wire:model="newClienteTelefono" type="tel" placeholder="Teléfono..." style="width:100%;padding:.5rem;border:1.5px solid #d1d5db;border-radius:.5rem;font-size:.8rem;outline:none;transition:border-color .2s;" @focus="this.style.borderColor='#0047AB'" @blur="this.style.borderColor='#d1d5db'"/>
            </div>

            <div>
                <label style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;display:block;margin-bottom:.25rem;">RUC</label>
                <input wire:model="newClienteRuc" type="text" placeholder="RUC..." style="width:100%;padding:.5rem;border:1.5px solid #d1d5db;border-radius:.5rem;font-size:.8rem;outline:none;transition:border-color .2s;" @focus="this.style.borderColor='#0047AB'" @blur="this.style.borderColor='#d1d5db'"/>
            </div>

            <div>
                <label style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;display:block;margin-bottom:.25rem;">Identidad/Cédula</label>
                <input wire:model="newClienteIdentidad" type="text" placeholder="Identidad..." style="width:100%;padding:.5rem;border:1.5px solid #d1d5db;border-radius:.5rem;font-size:.8rem;outline:none;transition:border-color .2s;" @focus="this.style.borderColor='#0047AB'" @blur="this.style.borderColor='#d1d5db'"/>
            </div>
        </div>

        <div style="display:flex;gap:.65rem;margin-top:1.2rem;">
            <button wire:click="closeNewClienteModal" style="flex:1;padding:.7rem;border-radius:.8rem;border:1.5px solid #e5e7eb;background:transparent;font-weight:700;color:#6b7280;cursor:pointer;font-size:.9rem;transition:all .2s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                Cancelar
            </button>
            <button wire:click="saveNewCliente" style="flex:1.5;padding:.7rem;border-radius:.8rem;background:#0047AB;color:white;font-weight:900;font-size:.95rem;border:none;cursor:pointer;box-shadow:0 3px 10px rgba(0,71,171,.3);transition:all .2s;" onmouseover="this.style.background='#003d99'" onmouseout="this.style.background='#0047AB'">
                Registrar
            </button>
        </div>
    </div>
</div>
@endif

</x-filament-panels::page>
