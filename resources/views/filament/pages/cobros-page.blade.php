<x-filament-panels::page>
@php
    $gs = fn ($n) => number_format((float) $n, 0, ',', '.');
@endphp

<style>
    .cj-wrap { display: grid; gap: 1.1rem; }
    .cj-top { display: flex; gap: .8rem; flex-wrap: wrap; align-items: stretch; }
    .cj-stat { flex: 1 1 11rem; background: #fff; border: 1px solid #e6ebf2; border-radius: 1.2rem; padding: .9rem 1.1rem; box-shadow: 0 1px 2px rgba(15,23,42,.04); }
    .cj-stat small { display: block; font-size: .68rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #8b97ad; }
    .cj-stat b { display: block; font-size: 1.7rem; font-weight: 800; letter-spacing: -.03em; color: #0b1b3a; line-height: 1.15; margin-top: .15rem; }
    .cj-stat.azul { background: linear-gradient(135deg, #0047AB, #0a6cdc); border: 0; }
    .cj-stat.azul small { color: rgba(255,255,255,.75); } .cj-stat.azul b { color: #fff; }
    .cj-live { display: inline-flex; align-items: center; gap: .45rem; font-size: .76rem; font-weight: 700; color: #166534; background: #dcfce7; padding: .3rem .75rem; border-radius: 999px; }
    .cj-live i { width: .55rem; height: .55rem; border-radius: 999px; background: #16a34a; animation: cj-pulso 1.6s ease-out infinite; }
    @keyframes cj-pulso { 0% { box-shadow: 0 0 0 0 rgba(22,163,74,.55); } 70% { box-shadow: 0 0 0 .55rem rgba(22,163,74,0); } 100% { box-shadow: 0 0 0 0 rgba(22,163,74,0); } }
    .cj-barra { display: flex; gap: .6rem; flex-wrap: wrap; align-items: center; }
    .cj-buscar { flex: 1 1 15rem; display: flex; align-items: center; gap: .5rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 999px; padding: .15rem 1rem; }
    .cj-buscar:focus-within { border-color: #0047AB; box-shadow: 0 0 0 4px rgba(0,71,171,.12); }
    .cj-buscar input { flex: 1; border: 0; outline: 0; background: transparent; padding: .6rem 0; font-size: .92rem; min-width: 0; }
    .cj-buscar svg { width: 1.1rem; height: 1.1rem; color: #94a3b8; flex: 0 0 auto; }
    .cj-sel { border: 1px solid #e2e8f0; background: #fff; border-radius: 999px; padding: .6rem 1rem; font-size: .88rem; font-weight: 600; }
    .cj-btn-son { display: inline-flex; align-items: center; gap: .4rem; border: 1px solid #e2e8f0; background: #fff; border-radius: 999px; padding: .6rem 1rem; font-size: .85rem; font-weight: 700; color: #475569; cursor: pointer; }
    .cj-btn-son.on { background: #eef3fc; color: #0047AB; border-color: #c7d6ef; }
    .cj-aviso { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; border-radius: 1rem; padding: .7rem 1rem; font-size: .85rem; }
    .cj-nuevo { background: #0047AB; color: #fff; border-radius: 999px; padding: .35rem .9rem; font-size: .8rem; font-weight: 800; animation: cj-entra .3s ease; }
    @keyframes cj-entra { from { opacity: 0; transform: translateY(-.4rem); } to { opacity: 1; transform: none; } }

    .cj-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(17.5rem, 1fr)); gap: 1rem; }
    .cj-card { position: relative; text-align: left; background: #fff; border: 1px solid #e6ebf2; border-radius: 1.3rem; padding: 1.1rem 1.2rem 1rem; box-shadow: 0 1px 2px rgba(15,23,42,.04), 0 10px 24px -18px rgba(15,23,42,.25); cursor: pointer; transition: transform .15s, box-shadow .15s, border-color .15s; display: flex; flex-direction: column; gap: .55rem; font: inherit; color: inherit; }
    .cj-card:hover { transform: translateY(-3px); border-color: #c7d6ef; box-shadow: 0 2px 4px rgba(15,23,42,.05), 0 18px 34px -20px rgba(0,71,171,.35); }
    .cj-card.nueva { border-color: #0047AB; animation: cj-glow 1.6s ease-out 2; }
    @keyframes cj-glow { 0% { box-shadow: 0 0 0 0 rgba(0,71,171,.45); } 100% { box-shadow: 0 0 0 .9rem rgba(0,71,171,0); } }
    .cj-card .num { font-size: .72rem; font-weight: 800; letter-spacing: .08em; color: #8b97ad; }
    .cj-card .hace { font-size: .74rem; font-weight: 700; color: #64748b; }
    .cj-card .hace.tarde { color: #b45309; }
    .cj-card .cliente { font-size: 1.02rem; font-weight: 800; color: #0b1b3a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cj-card .meta { font-size: .8rem; color: #64748b; display: flex; gap: .4rem; flex-wrap: wrap; }
    .cj-card .total { font-size: 1.75rem; font-weight: 800; letter-spacing: -.03em; color: #0b1b3a; margin-top: .2rem; }
    .cj-card .total small { font-size: .8rem; font-weight: 700; color: #64748b; margin-right: .25rem; letter-spacing: 0; }
    .cj-card .cobrar { margin-top: .3rem; display: inline-flex; align-items: center; justify-content: center; gap: .45rem; background: #059669; color: #fff; font-weight: 800; border-radius: .9rem; padding: .7rem; font-size: .95rem; box-shadow: 0 10px 20px -12px rgba(5,150,105,.8); }
    .cj-vacio { text-align: center; padding: 3.5rem 1rem; background: #fff; border: 1px dashed #cbd5e1; border-radius: 1.4rem; color: #64748b; }
    .cj-vacio b { display: block; font-size: 1.15rem; color: #0b1b3a; margin: .4rem 0 .2rem; }

    .cj-modal { position: fixed; inset: 0; z-index: 90; background: rgba(15,23,42,.55); backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center; padding: 1rem; }
    .cj-panel { background: #fff; border-radius: 1.6rem; width: min(58rem, 100%); max-height: 100%; overflow: hidden; display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr); box-shadow: 0 40px 90px -30px rgba(15,23,42,.6); }
    .cj-izq { padding: 1.3rem 1.4rem; background: #f6f8fb; overflow-y: auto; max-height: calc(100vh - 2rem); }
    .cj-der { padding: 1.3rem 1.4rem; overflow-y: auto; max-height: calc(100vh - 2rem); display: flex; flex-direction: column; gap: .9rem; }
    .cj-izq h3, .cj-der h3 { margin: 0; font-size: .72rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #8b97ad; }
    .cj-lin { display: flex; justify-content: space-between; gap: .8rem; padding: .55rem 0; border-bottom: 1px solid #e6ebf2; font-size: .88rem; }
    .cj-lin span { color: #64748b; font-size: .76rem; display: block; }
    .cj-total { font-size: 2.3rem; font-weight: 800; letter-spacing: -.04em; color: #0b1b3a; line-height: 1; }
    .cj-metodo { display: grid; gap: .35rem; }
    .cj-metodo label { font-size: .78rem; font-weight: 800; color: #334155; display: flex; align-items: center; justify-content: space-between; }
    .cj-in { width: 100%; border: 1.5px solid #e2e8f0; border-radius: .9rem; padding: .7rem .9rem; font-size: 1.1rem; font-weight: 700; text-align: right; background: #fff; }
    .cj-in:focus { outline: 0; border-color: #0047AB; box-shadow: 0 0 0 4px rgba(0,71,171,.12); }
    .cj-chips { display: flex; gap: .35rem; flex-wrap: wrap; }
    .cj-chip { border: 1px solid #dbe3ee; background: #fff; border-radius: 999px; padding: .25rem .7rem; font-size: .74rem; font-weight: 700; color: #475569; cursor: pointer; }
    .cj-chip:hover { background: #eef3fc; color: #0047AB; border-color: #c7d6ef; }
    .cj-resumen { border-radius: 1rem; padding: .8rem 1rem; font-weight: 700; font-size: .92rem; display: flex; justify-content: space-between; align-items: center; }
    .cj-resumen.ok { background: #dcfce7; color: #166534; } .cj-resumen.falta { background: #fef3c7; color: #92400e; } .cj-resumen.error { background: #fee2e2; color: #991b1b; }
    .cj-acciones { display: flex; gap: .6rem; margin-top: auto; }
    .cj-confirmar { flex: 1.6; background: #059669; color: #fff; border: 0; border-radius: 1rem; padding: .95rem; font-size: 1.05rem; font-weight: 800; cursor: pointer; box-shadow: 0 12px 22px -12px rgba(5,150,105,.85); }
    .cj-confirmar:disabled { background: #e2e8f0; color: #94a3b8; box-shadow: none; cursor: not-allowed; }
    .cj-cancelar { flex: 1; background: #fff; border: 1.5px solid #e2e8f0; border-radius: 1rem; padding: .95rem; font-weight: 700; color: #64748b; cursor: pointer; }
    .cj-ok { width: min(26rem, 100%); background: #fff; border-radius: 1.6rem; padding: 1.8rem 1.6rem; text-align: center; box-shadow: 0 40px 90px -30px rgba(15,23,42,.6); }
    .cj-ok .tilde { width: 4rem; height: 4rem; border-radius: 999px; background: #dcfce7; color: #16a34a; display: grid; place-items: center; margin: 0 auto .8rem; }
    .cj-ok .vuelto { margin: 1rem 0 .3rem; background: #f0fdf4; border-radius: 1rem; padding: .8rem; }
    .cj-ok .vuelto b { display: block; font-size: 2rem; font-weight: 800; color: #16a34a; }
    @media (max-width: 760px) {
        .cj-panel { grid-template-columns: 1fr; max-height: 100%; overflow-y: auto; }
        .cj-izq, .cj-der { max-height: none; overflow: visible; }
        .cj-izq { order: 2; }
        .cj-total { font-size: 2rem; }
    }
</style>

<div
    class="cj-wrap"
    x-data="{
        sonido: (() => { try { return localStorage.getItem('caja:sonido') !== '0'; } catch (e) { return true; } })(),
        aviso: false, previa: null, timer: null,
        firma: null, enLinea: true, consultando: false, ultimaCarga: Date.now(),
        init() {
            this.previa = $wire.ultima;
            // Cada 2 s se pregunta si cambió algo (consulta mínima); el listado solo se recarga si hay una venta nueva o cobrada,
            // y cada 30 s igual para que 'hace X min' siga al día.
            this.vigilar();
            setInterval(() => this.vigilar(), 2000);
            document.addEventListener('visibilitychange', () => { if (!document.hidden) this.vigilar(); });
            this.$watch('$wire.ultima', (nueva) => {
                if (this.previa !== null && this.previa !== '' && Number(nueva) > Number(this.previa)) this.nuevaVenta();
                else if (this.previa === '' && nueva !== '') { /* primera carga con ventas: sin aviso */ }
                this.previa = nueva;
            });
        },
        async vigilar() {
            if (document.hidden || this.consultando) return;
            this.consultando = true;
            try {
                const r = await fetch(@js(route('caja.cobros.firma')) + '?periodo=' + encodeURIComponent($wire.periodo), { headers: { 'Accept': 'application/json' }, cache: 'no-store', credentials: 'same-origin' });
                if (!r.ok) throw new Error('estado ' + r.status);
                const { firma } = await r.json();
                this.enLinea = true;
                const cambio = this.firma !== null && firma !== this.firma;
                this.firma = firma;
                if (cambio || Date.now() - this.ultimaCarga > 30000) { this.ultimaCarga = Date.now(); await $wire.$refresh(); }
            } catch (e) {
                this.enLinea = false;
            } finally {
                this.consultando = false;
            }
        },
        nuevaVenta() {
            this.aviso = true; clearTimeout(this.timer); this.timer = setTimeout(() => this.aviso = false, 6000);
            if (this.sonido) this.pitido();
        },
        alternarSonido() {
            this.sonido = !this.sonido;
            try { localStorage.setItem('caja:sonido', this.sonido ? '1' : '0'); } catch (e) {}
            if (this.sonido) this.pitido();
        },
        pitido() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                [880, 1175].forEach((f, i) => {
                    const o = ctx.createOscillator(), g = ctx.createGain();
                    o.frequency.value = f; o.connect(g); g.connect(ctx.destination);
                    g.gain.setValueAtTime(0.0001, ctx.currentTime + i * 0.16);
                    g.gain.exponentialRampToValueAtTime(0.25, ctx.currentTime + i * 0.16 + 0.02);
                    g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + i * 0.16 + 0.15);
                    o.start(ctx.currentTime + i * 0.16); o.stop(ctx.currentTime + i * 0.16 + 0.16);
                });
                setTimeout(() => ctx.close(), 600);
            } catch (e) {}
        }
    }"
>
    {{-- Resumen en vivo --}}
    <div class="cj-top">
        <div class="cj-stat azul"><small>A cobrar ahora</small><b>Gs. {{ $gs($totalLista) }}</b></div>
        <div class="cj-stat"><small>Ventas pendientes</small><b>{{ count($ventas) }}</b></div>
        <div class="cj-stat" style="display:flex;flex-direction:column;justify-content:center;gap:.5rem;">
            <span class="cj-live" x-show="enLinea"><i></i> En vivo · se actualiza sola</span>
            <span class="cj-live" x-show="!enLinea" x-cloak style="background:#fef3c7;color:#92400e;"><i style="background:#d97706;animation:none;"></i> Sin conexión · reintentando…</span>
            <span x-show="aviso" x-cloak class="cj-nuevo">🔔 ¡Nueva venta para cobrar!</span>
        </div>
    </div>

    @if($cajaEnPos)
        <div class="cj-aviso">Tu empresa cobra directamente en el punto de venta (módulo de caja activo). Acá solo vas a ver ventas que hayan quedado pendientes de antes.</div>
    @endif

    {{-- Búsqueda y filtros --}}
    <div class="cj-barra">
        <label class="cj-buscar">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" wire:model.live.debounce.300ms="buscar" placeholder="Buscar por cliente, vendedor o N.° de venta" aria-label="Buscar venta">
        </label>
        <select wire:model.live="periodo" class="cj-sel" aria-label="Período">
            @foreach($periodos as $k => $etiqueta)<option value="{{ $k }}">{{ $etiqueta }}</option>@endforeach
        </select>
        <button type="button" class="cj-btn-son" :class="{ on: sonido }" @click="alternarSonido()" :aria-pressed="sonido">
            <span x-text="sonido ? '🔔 Aviso sonoro' : '🔕 Sin sonido'"></span>
        </button>
    </div>

    {{-- Ventas pendientes --}}
    @if(count($ventas))
        <div class="cj-grid">
            @foreach($ventas as $v)
                <button type="button" class="cj-card {{ $v['minutos'] < 1 ? 'nueva' : '' }}" wire:key="venta-{{ $v['invnr'] }}" wire:click="abrirCobro({{ \Illuminate\Support\Js::from($v['invnr']) }})">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:.5rem;">
                        <span class="num">VENTA N.° {{ $v['numero'] }}</span>
                        <span class="hace {{ $v['minutos'] >= 15 ? 'tarde' : '' }}">{{ $v['hace'] }}</span>
                    </div>
                    <div class="cliente" title="{{ $v['cliente'] }}">{{ $v['cliente'] }}</div>
                    <div class="meta">
                        <span>👤 {{ $v['vendedor'] }}</span>
                        <span>· {{ $v['lineas'] }} {{ $v['lineas'] === 1 ? 'producto' : 'productos' }}</span>
                        @if($v['hora'] !== '')<span>· {{ $v['hora'] }}</span>@endif
                    </div>
                    <div class="total"><small>Gs.</small>{{ $gs($v['total']) }}</div>
                    <span class="cobrar">Cobrar</span>
                </button>
            @endforeach
        </div>
    @else
        <div class="cj-vacio">
            <div style="font-size:2.4rem;">✅</div>
            <b>No hay ventas pendientes de cobro</b>
            <span>Cuando un vendedor registre una venta aparece acá sola, en unos segundos.</span>
        </div>
    @endif

    {{-- Cobro de una venta --}}
    @if($seleccion)
        <div class="cj-modal" wire:key="cobro-{{ $seleccion['invnr'] }}" @keydown.escape.window="$wire.cerrarCobro()" @click.self="$wire.cerrarCobro()"
             x-data="{
                total: {{ (float) $seleccion['total'] }},
                efectivo: '', transferencia: '', tarjeta: '',
                n(v) { const x = parseFloat(v); return isNaN(x) || x < 0 ? 0 : x; },
                get r() { return this.n(this.transferencia); },
                get c() { return this.n(this.tarjeta); },
                get e() { return this.n(this.efectivo); },
                get exceso() { return this.r + this.c > this.total + 0.005; },
                get necesario() { return Math.round((this.total - this.r - this.c) * 100) / 100; },
                get falta() { return this.exceso ? 0 : (this.necesario > 0 ? Math.max(0, Math.round((this.necesario - this.e) * 100) / 100) : 0); },
                get vuelto() { return this.necesario > 0 ? Math.max(0, Math.round((this.e - this.necesario) * 100) / 100) : 0; },
                get puede() { return !this.exceso && this.falta === 0 && (this.r + this.c + this.e) > 0; },
                fmt(v) { return Math.round(v).toLocaleString('es-PY'); },
                todo(metodo) { this.efectivo = ''; this.transferencia = ''; this.tarjeta = ''; this[metodo] = this.total; },
                confirmar() { if (this.puede) $wire.cobrar({ efectivo: this.e, transferencia: this.r, tarjeta: this.c }); }
             }"
             x-init="$nextTick(() => $refs.efectivo?.focus())"
             @keydown.enter.window="if ($event.target.tagName !== 'BUTTON' && $event.target.tagName !== 'A') { $event.preventDefault(); confirmar(); }">
            <div class="cj-panel" @click.stop role="dialog" aria-modal="true" aria-label="Cobrar venta {{ $seleccion['numero'] }}">
                <div class="cj-izq">
                    <h3>Venta N.° {{ $seleccion['numero'] }}</h3>
                    <div style="font-size:1.1rem;font-weight:800;margin:.3rem 0 .1rem;">{{ $seleccion['cliente'] }}</div>
                    <div style="font-size:.8rem;color:#64748b;margin-bottom:.8rem;">Vendedor: {{ $seleccion['vendedor'] }}@if($seleccion['hora'] !== '') · {{ $seleccion['hora'] }}@endif</div>
                    @foreach($seleccion['lineas'] as $l)
                        <div class="cj-lin">
                            <div>{{ $l['descr'] }}<span>{{ rtrim(rtrim(number_format($l['qty'], 3, ',', '.'), '0'), ',') }} × Gs. {{ $gs($l['precio']) }}</span></div>
                            <strong>Gs. {{ $gs($l['subtotal']) }}</strong>
                        </div>
                    @endforeach
                </div>

                <div class="cj-der">
                    <div>
                        <h3>Total a cobrar</h3>
                        <div class="cj-total">Gs. {{ $gs($seleccion['total']) }}</div>
                    </div>

                    <div class="cj-chips" aria-label="Pago rápido">
                        <button type="button" class="cj-chip" @click="todo('efectivo')">Todo en efectivo</button>
                        <button type="button" class="cj-chip" @click="todo('transferencia')">Todo transferencia</button>
                        <button type="button" class="cj-chip" @click="todo('tarjeta')">Todo tarjeta</button>
                    </div>

                    <div class="cj-metodo">
                        <label>💵 Efectivo recibido <span class="cj-chips"><button type="button" class="cj-chip" @click="efectivo = necesario > 0 ? necesario : ''">Exacto</button><button type="button" class="cj-chip" @click="efectivo = 50000">50.000</button><button type="button" class="cj-chip" @click="efectivo = 100000">100.000</button><button type="button" class="cj-chip" @click="efectivo = 200000">200.000</button></span></label>
                        <input type="number" min="0" inputmode="numeric" class="cj-in" x-ref="efectivo" x-model="efectivo" placeholder="0">
                    </div>
                    <div class="cj-metodo">
                        <label>🏦 Transferencia</label>
                        <input type="number" min="0" inputmode="numeric" class="cj-in" x-model="transferencia" placeholder="0">
                    </div>
                    <div class="cj-metodo">
                        <label>💳 Tarjeta</label>
                        <input type="number" min="0" inputmode="numeric" class="cj-in" x-model="tarjeta" placeholder="0">
                    </div>

                    <div class="cj-resumen error" x-show="exceso" x-cloak><span>La transferencia y la tarjeta superan el total</span></div>
                    <div class="cj-resumen falta" x-show="!exceso && falta > 0" x-cloak><span>Falta cobrar</span><strong x-text="'Gs. ' + fmt(falta)"></strong></div>
                    <div class="cj-resumen ok" x-show="puede" x-cloak>
                        <span x-text="vuelto > 0 ? 'Vuelto para el cliente' : 'Listo para cobrar'"></span>
                        <strong x-show="vuelto > 0" x-text="'Gs. ' + fmt(vuelto)"></strong>
                    </div>

                    <div class="cj-acciones">
                        <button type="button" class="cj-cancelar" wire:click="cerrarCobro">Cancelar</button>
                        <button type="button" class="cj-confirmar" :disabled="!puede" @click="confirmar()" wire:loading.attr="disabled" wire:target="cobrar">Confirmar cobro</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Cobro registrado --}}
    @if($cobrado)
        <div class="cj-modal" wire:key="cobrado" @keydown.escape.window="$wire.cerrarCobrado()" @click.self="$wire.cerrarCobrado()">
            <div class="cj-ok" @click.stop>
                <div class="tilde"><svg style="width:2rem;height:2rem;" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg></div>
                <div style="font-size:1.25rem;font-weight:800;">Cobro registrado</div>
                <div style="font-size:.85rem;color:#64748b;margin-top:.2rem;">Venta N.° {{ $cobrado['numero'] }} · {{ $cobrado['cliente'] }}</div>
                <div style="font-size:1.9rem;font-weight:800;letter-spacing:-.03em;margin:.7rem 0 .3rem;">Gs. {{ $gs($cobrado['total']) }}</div>
                <div style="font-size:.8rem;color:#475569;display:flex;gap:.8rem;justify-content:center;flex-wrap:wrap;">
                    @if($cobrado['efectivo'] > 0)<span>💵 Efectivo {{ $gs($cobrado['efectivo']) }}</span>@endif
                    @if($cobrado['transferencia'] > 0)<span>🏦 Transf. {{ $gs($cobrado['transferencia']) }}</span>@endif
                    @if($cobrado['tarjeta'] > 0)<span>💳 Tarjeta {{ $gs($cobrado['tarjeta']) }}</span>@endif
                </div>
                @if($cobrado['vuelto'] > 0)
                    <div class="vuelto"><small style="font-weight:800;letter-spacing:.08em;text-transform:uppercase;font-size:.65rem;color:#16a34a;">Vuelto</small><b>Gs. {{ $gs($cobrado['vuelto']) }}</b></div>
                @endif
                <div class="cj-acciones" style="margin-top:1.2rem;">
                    <button type="button" class="cj-cancelar" wire:click="cerrarCobrado">Listo</button>
                    <a href="{{ $cobrado['ticket'] }}" target="_blank" rel="noopener" class="cj-confirmar" style="text-align:center;text-decoration:none;" wire:click="cerrarCobrado">Imprimir ticket</a>
                </div>
            </div>
        </div>
    @endif
</div>
</x-filament-panels::page>
