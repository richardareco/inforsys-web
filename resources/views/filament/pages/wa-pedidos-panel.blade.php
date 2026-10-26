@php
    $gs = fn ($v) => 'Gs. ' . number_format((float) $v, 0, ',', '.');
    $totalPedidos = max(1, $resumen['pendiente']['n'] + $resumen['pagado']['n'] + $resumen['anulado']['n']);
    $tarjetas = [
        'pendiente' => ['Pendientes', 'Esperan el pago', 'amber',   'M12 6v6l3.75 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        'pagado'    => ['Pagados',    'Venta generada',  'emerald', 'M2.25 18.75a60.07 60.07 0 0 1 15.8 2.1c.73.2 1.45-.34 1.45-1.1V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.38c0-.62.5-1.12 1.13-1.12H20.25M2.25 6v9m18-10.5v.75c0 .41.34.75.75.75h.75m-1.5-1.5h.38c.62 0 1.12.5 1.12 1.13v9.75c0 .62-.5 1.12-1.12 1.12h-.38m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.38a1.13 1.13 0 0 1-1.12-1.13V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.01v.01H18V10.5Zm-12 0h.01v.01H6V10.5Z'],
        'anulado'   => ['Anulados',   'No se concretaron', 'rose',  'm9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
    ];
    $tabDe = ['pendiente' => 'pendiente', 'pagado' => 'pagado', 'anulado' => 'anulado'];
@endphp

<style>
    .wp-top { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: 1.1rem; }
    .wp-periodos { display: flex; flex-wrap: wrap; gap: .4rem; align-items: center; }
    .wp-chip { padding: .42rem .95rem; border-radius: 999px; font-size: .8rem; font-weight: 700; border: 1px solid #dbe1ea; background: #fff; color: #475569; cursor: pointer; transition: all .15s; }
    .dark .wp-chip { background: #1f2937; border-color: #374151; color: #cbd5e1; }
    .wp-chip:hover { border-color: #6366f1; color: #4f46e5; transform: translateY(-1px); }
    .wp-chip.on { background: linear-gradient(135deg, #4f46e5, #7c3aed); border-color: transparent; color: #fff; box-shadow: 0 8px 16px -8px rgba(79, 70, 229, .85); }
    .wp-fecha { padding: .38rem .65rem; border: 1px solid #dbe1ea; border-radius: .7rem; font-size: .8rem; background: #fff; color: #334155; }
    .dark .wp-fecha { background: #1f2937; border-color: #374151; color: #e2e8f0; }
    .wp-etiqueta { font-size: .82rem; font-weight: 700; color: #64748b; background: #f1f5f9; padding: .3rem .8rem; border-radius: 999px; }
    .dark .wp-etiqueta { background: #1f2937; color: #cbd5e1; }

    .wp-cards { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.2rem; }
    .wp-card {
        --c1: #f59e0b; --c2: #f97316; --tinte: rgba(245, 158, 11, .12);
        position: relative; overflow: hidden; text-align: left; width: 100%; cursor: pointer;
        padding: 1.15rem 1.25rem 1rem; border-radius: 1.25rem; background: #fff; border: 1px solid #e8ecf3;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 12px 28px -18px rgba(15, 23, 42, .28);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .dark .wp-card { background: #1f2937; border-color: #374151; }
    .wp-card.emerald { --c1: #10b981; --c2: #059669; --tinte: rgba(16, 185, 129, .12); }
    .wp-card.rose    { --c1: #f43f5e; --c2: #e11d48; --tinte: rgba(244, 63, 94, .11); }
    .wp-card::before { content: ""; position: absolute; inset: 0 0 auto 0; height: 4px; background: linear-gradient(90deg, var(--c1), var(--c2)); }
    .wp-card::after { content: ""; position: absolute; right: -2.2rem; top: -2.2rem; width: 8rem; height: 8rem; border-radius: 50%; background: radial-gradient(circle, var(--tinte), transparent 68%); pointer-events: none; }
    .wp-card:hover { transform: translateY(-3px); box-shadow: 0 2px 4px rgba(15, 23, 42, .05), 0 20px 36px -18px color-mix(in srgb, var(--c2) 55%, transparent); }
    .wp-card.activa { border-color: var(--c1); box-shadow: 0 0 0 3px var(--tinte), 0 20px 36px -20px color-mix(in srgb, var(--c2) 60%, transparent); }
    .wp-card-head { display: flex; align-items: center; gap: .75rem; position: relative; z-index: 1; }
    .wp-ico { flex: 0 0 2.6rem; height: 2.6rem; border-radius: .9rem; display: grid; place-items: center; color: #fff;
        background: linear-gradient(135deg, var(--c1), var(--c2)); box-shadow: 0 10px 18px -10px var(--c2); }
    .wp-ico svg { width: 1.4rem; height: 1.4rem; }
    .wp-card-lbl { font-size: .74rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: var(--c2); line-height: 1.1; }
    .wp-card-sub { font-size: .72rem; color: #94a3b8; margin-top: .15rem; }
    .wp-card-body { display: flex; align-items: flex-end; justify-content: space-between; gap: .75rem; margin-top: .9rem; position: relative; z-index: 1; }
    .wp-card-n { font-size: 2.6rem; font-weight: 900; line-height: .95; letter-spacing: -.03em; color: #0f172a; }
    .dark .wp-card-n { color: #f8fafc; }
    .wp-card-n small { font-size: .78rem; font-weight: 700; letter-spacing: 0; color: #94a3b8; margin-left: .25rem; }
    .wp-card-monto { font-size: 1.15rem; font-weight: 800; color: var(--c2); white-space: nowrap; }
    .wp-barra { height: .42rem; border-radius: 999px; background: #eef1f6; margin-top: .95rem; overflow: hidden; position: relative; z-index: 1; }
    .dark .wp-barra { background: #374151; }
    .wp-barra i { display: block; height: 100%; border-radius: 999px; background: linear-gradient(90deg, var(--c1), var(--c2)); transition: width .5s ease; }
    .wp-pie { display: flex; justify-content: space-between; margin-top: .4rem; font-size: .7rem; font-weight: 600; color: #94a3b8; position: relative; z-index: 1; }
    .wp-aviso { font-size: .78rem; color: #64748b; margin: -.5rem 0 1rem; display: flex; align-items: center; gap: .4rem; }
    .wp-aviso b { background: #e2e8f0; color: #475569; border-radius: 999px; padding: .05rem .5rem; font-size: .72rem; }
    @media (max-width: 767px) {
        .wp-cards { grid-template-columns: 1fr; gap: .7rem; }
        .wp-card { padding: .9rem 1rem .8rem; }
        .wp-card-n { font-size: 2rem; }
        .wp-etiqueta { order: -1; }
    }
</style>

<div>
    <div class="wp-top">
        <div class="wp-periodos">
            @foreach(['hoy' => 'Hoy', 'ayer' => 'Ayer', 'semana' => 'Esta semana', 'mes' => 'Este mes', 'rango' => 'Rango de fechas'] as $k => $label)
                <button type="button" class="wp-chip {{ $pagina->periodo === $k ? 'on' : '' }}" wire:click="setPeriodo('{{ $k }}')">{{ $label }}</button>
            @endforeach
            @if($pagina->periodo === 'rango')
                <input type="date" class="wp-fecha" wire:model.live="desde" aria-label="Desde">
                <span style="color:#94a3b8;">a</span>
                <input type="date" class="wp-fecha" wire:model.live="hasta" aria-label="Hasta">
            @endif
        </div>
        <div class="wp-etiqueta">{{ $pagina->etiquetaPeriodo() }}</div>
    </div>

    <div class="wp-cards">
        @foreach($tarjetas as $clave => [$titulo, $sub, $color, $icono])
            @php $pct = round($resumen[$clave]['n'] / $totalPedidos * 100); @endphp
            <button type="button" class="wp-card {{ $color }} {{ ($pagina->activeTab ?? '') === $tabDe[$clave] ? 'activa' : '' }}" wire:click="$set('activeTab', '{{ $tabDe[$clave] }}')" title="Ver {{ strtolower($titulo) }}">
                <div class="wp-card-head">
                    <span class="wp-ico"><svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icono }}"/></svg></span>
                    <div>
                        <div class="wp-card-lbl">{{ $titulo }}</div>
                        <div class="wp-card-sub">{{ $sub }}</div>
                    </div>
                </div>
                <div class="wp-card-body">
                    <div class="wp-card-n">{{ $resumen[$clave]['n'] }}<small>{{ $resumen[$clave]['n'] === 1 ? 'pedido' : 'pedidos' }}</small></div>
                    <div class="wp-card-monto">{{ $gs($resumen[$clave]['monto']) }}</div>
                </div>
                <div class="wp-barra"><i style="width: {{ $pct }}%"></i></div>
                <div class="wp-pie"><span>{{ $pct }}% de los pedidos del período</span></div>
            </button>
        @endforeach
    </div>

    @if($resumen['sin']['n'] > 0)
        <p class="wp-aviso"><b>{{ $resumen['sin']['n'] }}</b> pedido(s) que el cliente vio pero no confirmó (pestaña "Sin confirmar"); no cuentan en los totales de arriba.</p>
    @endif
</div>
