<x-filament-panels::page>
<style>
/* ── Reset & base ── */
#pos-minimal { display:flex; flex-direction:column; height:calc(100vh - 4rem); gap:0; background:#f1f5f9; }

/* ── Top bar ── */
#pos-top-bar {
    display:flex; align-items:center; gap:1rem; padding:.6rem 1rem;
    background:#1e293b; border-bottom:2px solid #0047AB;
    flex-shrink:0;
}
#pos-search-wrap { flex:1; position:relative; }
#pos-search-input {
    width:100%; padding:.65rem 1rem .65rem 2.6rem; font-size:1.1rem;
    border:2px solid #334155; border-radius:.5rem; background:#0f172a; color:#f8fafc;
    outline:none; transition:border-color .15s;
}
#pos-search-input:focus { border-color:#0047AB; }
#pos-search-input::placeholder { color:#64748b; }
.pos-search-icon {
    position:absolute; left:.75rem; top:50%; transform:translateY(-50%);
    color:#64748b; pointer-events:none;
}

/* ── Total box ── */
#pos-total-box {
    display:flex; flex-direction:column; align-items:flex-end;
    background:#0047AB; border-radius:.5rem; padding:.4rem .9rem;
    min-width:14rem; line-height:1.1;
}
#pos-total-label { font-size:.65rem; font-weight:600; color:rgba(255,255,255,.7); text-transform:uppercase; letter-spacing:.8px; }
#pos-total-amount { font-size:2rem; font-weight:800; color:#ffffff; letter-spacing:.5px; font-variant-numeric:tabular-nums; }
#pos-total-items  { font-size:.7rem; color:rgba(255,255,255,.6); }

/* ── Context row ── */
#pos-ctx-row {
    display:flex; align-items:center; gap:.75rem; padding:.4rem 1rem;
    background:#0f172a; border-bottom:1px solid #1e293b; flex-shrink:0; flex-wrap:wrap;
}
.ctx-label { font-size:.65rem; color:#64748b; text-transform:uppercase; letter-spacing:.6px; }
.ctx-select {
    background:#1e293b; color:#f1f5f9; border:1px solid #334155;
    border-radius:.375rem; padding:.3rem .6rem; font-size:.8rem; outline:none; cursor:pointer;
}
.ctx-select:focus { border-color:#0047AB; }

/* ── Body: search results + cart ── */
#pos-body { display:flex; flex:1; overflow:hidden; }

/* ── Items table (left) ── */
#pos-items-panel {
    flex:1; overflow-y:auto; background:#f8fafc;
}
#pos-items-table { width:100%; border-collapse:collapse; }
#pos-items-table thead th {
    position:sticky; top:0; z-index:1;
    background:#e2e8f0; color:#475569; font-size:.7rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.6px; padding:.5rem .75rem;
    border-bottom:1px solid #cbd5e1; text-align:left;
}
#pos-items-table thead th.num { text-align:right; }
#pos-items-table tbody tr { cursor:pointer; transition:background .1s; }
#pos-items-table tbody tr:hover { background:#dbeafe; }
#pos-items-table tbody tr:nth-child(even) { background:#f1f5f9; }
#pos-items-table tbody tr:nth-child(even):hover { background:#dbeafe; }
#pos-items-table tbody td { padding:.55rem .75rem; font-size:.85rem; border-bottom:1px solid #e2e8f0; }
#pos-items-table .td-code { font-size:.72rem; color:#94a3b8; }
#pos-items-table .td-descr { font-weight:600; color:#1e293b; }
#pos-items-table .td-num { text-align:right; font-variant-numeric:tabular-nums; }
#pos-items-table .td-stock-low { color:#dc2626; font-weight:700; }

/* ── Cart panel (right) ── */
#pos-cart-panel {
    width:22rem; display:flex; flex-direction:column;
    background:#ffffff; border-left:1px solid #e2e8f0;
    overflow:hidden; flex-shrink:0;
}
#pos-cart-title {
    padding:.5rem .75rem; font-size:.7rem; font-weight:700; color:#64748b;
    text-transform:uppercase; letter-spacing:.6px; border-bottom:1px solid #e2e8f0;
    background:#f8fafc; flex-shrink:0;
}
#pos-cart-items { flex:1; overflow-y:auto; }
.cart-row {
    display:grid; grid-template-columns:1fr auto auto;
    gap:.4rem; align-items:center;
    padding:.5rem .75rem; border-bottom:1px solid #f1f5f9;
    font-size:.8rem;
}
.cart-row-descr { color:#1e293b; font-weight:500; font-size:.78rem; line-height:1.3; }
.cart-row-code  { font-size:.68rem; color:#94a3b8; }
.cart-row-qty   {
    background:#f1f5f9; border:1px solid #e2e8f0; border-radius:.375rem;
    width:3rem; text-align:center; padding:.25rem; font-size:.8rem;
    color:#1e293b; outline:none;
}
.cart-row-qty:focus { border-color:#0047AB; }
.cart-row-subtotal { text-align:right; font-weight:600; color:#0047AB; min-width:5rem; font-size:.8rem; font-variant-numeric:tabular-nums; }
.cart-row-remove {
    background:none; border:none; color:#cbd5e1; cursor:pointer; padding:.25rem;
    border-radius:.25rem; font-size:1rem; line-height:1; transition:color .1s;
}
.cart-row-remove:hover { color:#ef4444; }
#pos-cart-empty { padding:2rem; text-align:center; color:#94a3b8; font-size:.85rem; }

/* ── Cart footer ── */
#pos-cart-footer { padding:.75rem; border-top:1px solid #e2e8f0; background:#f8fafc; flex-shrink:0; }
#pos-cobrar-btn {
    width:100%; padding:.75rem; font-size:1rem; font-weight:700;
    background:#0047AB; color:#ffffff; border:none; border-radius:.5rem;
    cursor:pointer; transition:background .15s; letter-spacing:.5px;
}
#pos-cobrar-btn:hover { background:#0037891; }
#pos-cobrar-btn:disabled { background:#94a3b8; cursor:not-allowed; }
#pos-cart-clear {
    width:100%; margin-top:.4rem; padding:.4rem; font-size:.75rem;
    background:none; border:1px solid #e2e8f0; border-radius:.375rem;
    color:#94a3b8; cursor:pointer; transition:color .1s, border-color .1s;
}
#pos-cart-clear:hover { color:#ef4444; border-color:#ef4444; }

/* ── Payment modal ── */
#pos-pay-modal-backdrop {
    position:fixed; inset:0; background:rgba(0,0,0,.6);
    display:flex; align-items:center; justify-content:center;
    z-index:9999;
}
#pos-pay-modal {
    background:#ffffff; border-radius:.75rem; width:28rem; max-width:95vw;
    box-shadow:0 20px 60px rgba(0,0,0,.3); overflow:hidden;
}
#pos-pay-modal-header {
    background:#0047AB; padding:1rem 1.25rem;
    display:flex; justify-content:space-between; align-items:center;
}
#pos-pay-modal-header span { font-size:1.1rem; font-weight:700; color:#ffffff; }
#pos-pay-modal-header button { background:none; border:none; color:rgba(255,255,255,.7); cursor:pointer; font-size:1.4rem; line-height:1; }
#pos-pay-modal-body { padding:1.25rem; }
.pay-total-display {
    text-align:center; margin-bottom:1rem;
    background:#f8fafc; border-radius:.5rem; padding:.75rem;
}
.pay-total-display .lbl { font-size:.7rem; color:#64748b; text-transform:uppercase; letter-spacing:.6px; }
.pay-total-display .amount { font-size:2rem; font-weight:800; color:#0047AB; font-variant-numeric:tabular-nums; }
.pay-methods { display:grid; grid-template-columns:1fr 1fr; gap:.5rem; margin-bottom:1rem; }
.pay-method-btn {
    padding:.6rem; border:2px solid #e2e8f0; border-radius:.5rem;
    background:#f8fafc; color:#475569; font-size:.8rem; font-weight:600;
    cursor:pointer; transition:all .15s; display:flex; flex-direction:column; align-items:center; gap:.25rem;
}
.pay-method-btn:hover { border-color:#0047AB; color:#0047AB; background:#eff6ff; }
.pay-method-btn.active { border-color:#0047AB; background:#0047AB; color:#ffffff; }
.pay-method-icon { font-size:1.4rem; }
.pay-input-group { margin-bottom:.75rem; }
.pay-input-label { font-size:.75rem; color:#64748b; margin-bottom:.3rem; }
.pay-input {
    width:100%; padding:.6rem .75rem; border:1px solid #e2e8f0; border-radius:.5rem;
    font-size:1rem; font-weight:600; color:#1e293b; outline:none;
    transition:border-color .15s;
}
.pay-input:focus { border-color:#0047AB; }
.pay-change-row {
    display:flex; justify-content:space-between; align-items:center;
    background:#f0fdf4; border-radius:.375rem; padding:.5rem .75rem;
    font-size:.85rem; color:#166534; font-weight:600; margin-bottom:.75rem;
}
.pay-change-row.negative { background:#fef2f2; color:#991b1b; }
#pos-pay-confirm-btn {
    width:100%; padding:.8rem; font-size:1rem; font-weight:700;
    background:#16a34a; color:#ffffff; border:none; border-radius:.5rem;
    cursor:pointer; transition:background .15s; letter-spacing:.5px;
}
#pos-pay-confirm-btn:hover { background:#15803d; }
#pos-pay-confirm-btn:disabled { background:#94a3b8; cursor:not-allowed; }

/* ── Search results dropdown ── */
#pos-search-results {
    position:absolute; top:100%; left:0; right:0; z-index:50;
    background:#1e293b; border:1px solid #334155; border-radius:.5rem;
    box-shadow:0 8px 24px rgba(0,0,0,.4); margin-top:.25rem; overflow:hidden;
    max-height:20rem; overflow-y:auto;
}
.pos-search-result-row {
    display:flex; align-items:center; gap:.75rem;
    padding:.55rem .9rem; cursor:pointer; transition:background .1s;
    border-bottom:1px solid #334155;
}
.pos-search-result-row:last-child { border-bottom:none; }
.pos-search-result-row:hover { background:#334155; }
.pos-result-descr { flex:1; font-size:.85rem; color:#f1f5f9; font-weight:500; }
.pos-result-code  { font-size:.72rem; color:#64748b; }
.pos-result-price { font-size:.85rem; color:#38bdf8; font-weight:600; text-align:right; min-width:5rem; font-variant-numeric:tabular-nums; }
.pos-result-stock { font-size:.72rem; color:#64748b; text-align:right; }
.pos-result-stock.low { color:#f87171; }
</style>

<div
    id="pos-minimal"
    x-data="posMinimal()"
    x-init="init()"
    @keydown.f10.window.prevent="$wire.openPaymentModal()"
    @keydown.escape.window="handleEscape()"
    @focus-search.window="focusSearch()"
    @focus-amount.window="focusAmount()"
    @cart-updated.window="onCartUpdated()"
>

    {{-- ══ TOP BAR: search + total ══ --}}
    <div id="pos-top-bar">
        {{-- Search --}}
        <div id="pos-search-wrap">
            <svg class="pos-search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input
                id="pos-search-input"
                type="text"
                wire:model.live.debounce.200ms="search"
                placeholder="Código de barras, scode, descripción…"
                autocomplete="off"
                @keydown.enter.prevent="selectFirstResult()"
                @focus="searchFocused = true"
                @blur="searchFocused = false"
            >
            {{-- Dropdown de resultados --}}
            @if(count($searchResults) > 0)
            <div id="pos-search-results">
                @foreach($searchResults as $r)
                <div
                    class="pos-search-result-row"
                    wire:click="addToCart('{{ $r->item }}')"
                >
                    <div>
                        <div class="pos-result-descr">{{ $r->descr }}</div>
                        <div class="pos-result-code">{{ $r->item }}{{ $r->scode ? ' · '.$r->scode : '' }}</div>
                    </div>
                    <div style="text-align:right">
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

        {{-- Total prominente --}}
        <div id="pos-total-box">
            <div id="pos-total-label">Total a cobrar</div>
            <div id="pos-total-amount">
                Gs. {{ number_format($this->getCartTotal(), 0, ',', '.') }}
            </div>
            <div id="pos-total-items">{{ count($cart) }} {{ count($cart) === 1 ? 'ítem' : 'ítems' }} &nbsp;·&nbsp; F10 para cobrar</div>
        </div>
    </div>

    {{-- ══ CONTEXT ROW: cliente, cajero, depósito ══ --}}
    <div id="pos-ctx-row">
        <div>
            <div class="ctx-label">Cliente</div>
            <select wire:model.live="clienteId" class="ctx-select">
                @foreach($clientes as $c)
                    <option value="{{ $c->custnr }}">{{ $c->custname }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <div class="ctx-label">Cajero</div>
            <select wire:model.live="cajeroId" class="ctx-select">
                @foreach($cajeros as $p)
                    <option value="{{ $p->pernr }}">{{ $p->pername }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <div class="ctx-label">Depósito</div>
            <select wire:model.live="depositoId" class="ctx-select">
                @foreach($depositos as $d)
                    <option value="{{ $d->deponr }}">{{ $d->deponr }} – {{ $d->depo_nombre }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-left:auto; font-size:.7rem; color:#334155;">
            <kbd style="background:#1e293b;border:1px solid #334155;border-radius:.25rem;padding:.15rem .35rem;color:#94a3b8;">F10</kbd> Cobrar &nbsp;
            <kbd style="background:#1e293b;border:1px solid #334155;border-radius:.25rem;padding:.15rem .35rem;color:#94a3b8;">Esc</kbd> Cancelar modal
        </div>
    </div>

    {{-- ══ BODY ══ --}}
    <div id="pos-body">

        {{-- Items panel (tabla del carrito visible full-screen) --}}
        <div id="pos-items-panel">
            @if(empty($cart))
                <div style="padding:3rem; text-align:center; color:#94a3b8;">
                    <div style="font-size:3rem; margin-bottom:.5rem;">🛒</div>
                    <div style="font-size:.95rem; font-weight:600;">Escanee o busque un producto</div>
                    <div style="font-size:.8rem; margin-top:.35rem;">Los ítems aparecerán aquí</div>
                </div>
            @else
            <table id="pos-items-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th class="num">Cant.</th>
                        <th class="num">Precio</th>
                        <th class="num">Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cart as $i => $item)
                    <tr>
                        <td class="td-code" style="color:#94a3b8;">{{ $i + 1 }}</td>
                        <td class="td-code">{{ $item['item'] }}</td>
                        <td class="td-descr">{{ $item['descr'] }}</td>
                        <td class="td-num">
                            <input
                                type="number" min="1"
                                value="{{ $item['qty'] }}"
                                class="cart-row-qty"
                                @change="$wire.updateQty({{ $i }}, $event.target.value)"
                                style="width:3.5rem;"
                            >
                        </td>
                        <td class="td-num" style="color:#64748b;">Gs. {{ number_format($item['precio'], 0, ',', '.') }}</td>
                        <td class="td-num" style="font-weight:700; color:#0047AB;">
                            Gs. {{ number_format($item['precio'] * $item['qty'], 0, ',', '.') }}
                        </td>
                        <td style="text-align:center;">
                            <button
                                class="cart-row-remove"
                                wire:click="removeFromCart({{ $i }})"
                                title="Eliminar"
                            >×</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

        {{-- Cart panel (right) ── mini-resumen + botón cobrar --}}
        <div id="pos-cart-panel">
            <div id="pos-cart-title">Resumen ({{ count($cart) }})</div>
            <div id="pos-cart-items">
                @if(empty($cart))
                    <div id="pos-cart-empty">Carrito vacío</div>
                @else
                    @foreach($cart as $i => $item)
                    <div class="cart-row">
                        <div>
                            <div class="cart-row-descr">{{ Str::limit($item['descr'], 30) }}</div>
                            <div class="cart-row-code">{{ $item['item'] }} · ×{{ $item['qty'] }}</div>
                        </div>
                        <div class="cart-row-subtotal">
                            Gs.&nbsp;{{ number_format($item['precio'] * $item['qty'], 0, ',', '.') }}
                        </div>
                        <button class="cart-row-remove" wire:click="removeFromCart({{ $i }})">×</button>
                    </div>
                    @endforeach
                @endif
            </div>
            <div id="pos-cart-footer">
                <button
                    id="pos-cobrar-btn"
                    wire:click="openPaymentModal()"
                    @click.prevent
                    {{ empty($cart) ? 'disabled' : '' }}
                >
                    COBRAR — Gs. {{ number_format($this->getCartTotal(), 0, ',', '.') }}
                </button>
                @if(!empty($cart))
                <button id="pos-cart-clear" wire:click="$set('cart', [])">Limpiar carrito</button>
                @endif
            </div>
        </div>
    </div>

    {{-- ══ MODAL DE PAGO ══ --}}
    @if($showPaymentModal)
    <div id="pos-pay-modal-backdrop" x-data="posPayModal()" x-init="init()">
        <div id="pos-pay-modal">
            <div id="pos-pay-modal-header">
                <span>Cobrar venta</span>
                <button wire:click="closePaymentModal()">×</button>
            </div>
            <div id="pos-pay-modal-body">
                {{-- Total --}}
                <div class="pay-total-display">
                    <div class="lbl">Total a cobrar</div>
                    <div class="amount">Gs. {{ number_format($this->getCartTotal(), 0, ',', '.') }}</div>
                </div>

                {{-- Métodos de pago --}}
                <div class="pay-methods">
                    <button
                        class="pay-method-btn {{ $paymentMethod === 'efectivo' ? 'active' : '' }}"
                        wire:click="$set('paymentMethod', 'efectivo')"
                    >
                        <span class="pay-method-icon">💵</span>
                        Efectivo
                    </button>
                    <button
                        class="pay-method-btn {{ $paymentMethod === 'tarjeta' ? 'active' : '' }}"
                        wire:click="$set('paymentMethod', 'tarjeta')"
                    >
                        <span class="pay-method-icon">💳</span>
                        Tarjeta
                    </button>
                    <button
                        class="pay-method-btn {{ $paymentMethod === 'transferencia' ? 'active' : '' }}"
                        wire:click="$set('paymentMethod', 'transferencia')"
                    >
                        <span class="pay-method-icon">🏦</span>
                        Transferencia
                    </button>
                    <button
                        class="pay-method-btn {{ $paymentMethod === 'qr' ? 'active' : '' }}"
                        wire:click="$set('paymentMethod', 'qr')"
                    >
                        <span class="pay-method-icon">📱</span>
                        QR
                    </button>
                </div>

                {{-- Monto recibido (solo efectivo) --}}
                @if($paymentMethod === 'efectivo')
                <div class="pay-input-group">
                    <div class="pay-input-label">Monto recibido (Gs.)</div>
                    <input
                        id="pay-amount-input"
                        type="number"
                        class="pay-input"
                        wire:model.live="amountReceived"
                        min="0"
                        step="1000"
                    >
                </div>
                @php $vuelto = $amountReceived - $this->getCartTotal(); @endphp
                <div class="pay-change-row {{ $vuelto < 0 ? 'negative' : '' }}">
                    <span>{{ $vuelto >= 0 ? 'Vuelto:' : 'Falta:' }}</span>
                    <span>Gs. {{ number_format(abs($vuelto), 0, ',', '.') }}</span>
                </div>
                @endif

                {{-- Confirmar --}}
                <button
                    id="pos-pay-confirm-btn"
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
        searchFocused: false,

        init() {
            this.$nextTick(() => this.focusSearch());
        },

        focusSearch() {
            this.$nextTick(() => {
                const el = document.getElementById('pos-search-input');
                if (el) el.focus();
            });
        },

        focusAmount() {
            this.$nextTick(() => {
                const el = document.getElementById('pay-amount-input');
                if (el) { el.focus(); el.select(); }
            });
        },

        selectFirstResult() {
            // Click the first result row
            const first = document.querySelector('.pos-search-result-row');
            if (first) first.click();
            else this.focusSearch();
        },

        handleEscape() {
            if (@js($showPaymentModal)) {
                @this.closePaymentModal();
            }
        },

        onCartUpdated() {
            this.$nextTick(() => this.focusSearch());
        },
    };
}

function posPayModal() {
    return {
        init() {
            this.$nextTick(() => {
                const el = document.getElementById('pay-amount-input');
                if (el) { el.focus(); el.select(); }
            });
        },
    };
}

// Re-focus search after any Livewire navigation / cart update
document.addEventListener('livewire:navigated', () => {
    const el = document.getElementById('pos-search-input');
    if (el) el.focus();
});
</script>
</x-filament-panels::page>
