@php
    $gs = fn ($v) => 'Gs. ' . number_format((float) $v, 0, ',', '.');
    $tarjetas = [
        ['pendiente', 'Pendientes', 'Esperan el pago', '#f59e0b', '#fffbeb'],
        ['pagado', 'Pagados', 'Venta generada', '#10b981', '#ecfdf5'],
        ['anulado', 'Anulados', 'No se concretaron', '#ef4444', '#fef2f2'],
    ];
@endphp

<style>
    .wp-top { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: 1rem; }
    .wp-periodos { display: flex; flex-wrap: wrap; gap: .4rem; align-items: center; }
    .wp-chip { padding: .4rem .9rem; border-radius: 999px; font-size: .8rem; font-weight: 700; border: 1px solid #cbd5e1; background: transparent; color: #475569; cursor: pointer; transition: all .15s; }
    .wp-chip:hover { border-color: #6366f1; color: #4f46e5; }
    .wp-chip.on { background: linear-gradient(135deg, #4f46e5, #7c3aed); border-color: transparent; color: #fff; box-shadow: 0 6px 14px -8px rgba(79, 70, 229, .8); }
    .wp-fecha { padding: .35rem .6rem; border: 1px solid #cbd5e1; border-radius: .6rem; font-size: .8rem; background: transparent; }
    .wp-etiqueta { font-size: .85rem; font-weight: 700; color: #64748b; }
    .wp-cards { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .9rem; margin-bottom: 1.1rem; }
    .wp-card { border-radius: 1rem; padding: 1rem 1.15rem; border: 1px solid; position: relative; overflow: hidden; }
    .wp-card-lbl { font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; opacity: .8; }
    .wp-card-n { font-size: 2rem; font-weight: 900; line-height: 1.1; margin-top: .15rem; }
    .wp-card-monto { font-size: 1.05rem; font-weight: 800; margin-top: .1rem; }
    .wp-card-sub { font-size: .72rem; opacity: .7; margin-top: .25rem; }
    .dark .wp-card { background: #1f2937 !important; }
    .wp-aviso { font-size: .78rem; color: #64748b; margin: -.4rem 0 1rem; }
    @media (max-width: 767px) {
        .wp-cards { grid-template-columns: 1fr; }
        .wp-card { display: flex; align-items: baseline; justify-content: space-between; gap: .75rem; padding: .75rem 1rem; }
        .wp-card-n { font-size: 1.5rem; margin: 0; }
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
        @foreach($tarjetas as [$clave, $titulo, $sub, $color, $fondo])
            <div class="wp-card" style="background: {{ $fondo }}; border-color: {{ $color }}33; color: {{ $color }};">
                <div>
                    <div class="wp-card-lbl">{{ $titulo }}</div>
                    <div class="wp-card-sub">{{ $sub }}</div>
                </div>
                <div style="text-align:right;">
                    <div class="wp-card-n">{{ $resumen[$clave]['n'] }}</div>
                    <div class="wp-card-monto">{{ $gs($resumen[$clave]['monto']) }}</div>
                </div>
            </div>
        @endforeach
    </div>

    @if($resumen['sin']['n'] > 0)
        <p class="wp-aviso">Hay {{ $resumen['sin']['n'] }} pedido(s) que el cliente vio pero no confirmó (pestaña "Sin confirmar"); no cuentan en los totales de arriba.</p>
    @endif
</div>
