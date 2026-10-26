<x-filament-panels::page>
    <style>
        .wa-wrap { --ia1: #6366f1; --ia2: #9333ea; display: grid; grid-template-columns: 22.5rem 1fr; height: calc(100dvh - 16.5rem); min-height: 28rem; border: 1px solid #e5e9f2; border-radius: 1.25rem; overflow: hidden; background: #fff; box-shadow: 0 18px 40px -28px rgba(30, 41, 99, .35); }
        .dark .wa-wrap { background: #111827; border-color: #374151; }

        /* ───────── Lista ───────── */
        .wa-list { border-right: 1px solid #e5e9f2; display: flex; flex-direction: column; min-width: 0; min-height: 0; background: #fbfcfe; }
        .dark .wa-list { border-color: #374151; background: #0f172a; }
        .wa-list-top { padding: .9rem .85rem .75rem; border-bottom: 1px solid #e5e9f2; display: flex; flex-direction: column; gap: .6rem; }
        .dark .wa-list-top { border-color: #374151; }
        .wa-marca { display: flex; align-items: center; gap: .6rem; }
        .wa-marca img { width: 2.1rem; height: 2.1rem; border-radius: .7rem; object-fit: cover; box-shadow: 0 6px 14px -8px var(--ia2); }
        .wa-marca b { display: block; font-size: .92rem; line-height: 1.1; }
        .wa-marca span { font-size: .7rem; color: #16a34a; font-weight: 700; display: flex; align-items: center; gap: .3rem; }
        .wa-search { width: 100%; padding: .55rem .8rem; border: 1px solid #dbe1ea; border-radius: .8rem; font-size: .85rem; background: #fff; }
        .dark .wa-search { background: #1f2937; border-color: #374151; color: #e5e7eb; }
        .wa-filtros { display: flex; gap: .35rem; flex-wrap: wrap; }
        .wa-filtro { padding: .28rem .7rem; border-radius: 999px; font-size: .72rem; font-weight: 700; border: 1px solid #dbe1ea; background: #fff; cursor: pointer; color: #64748b; transition: all .15s; }
        .dark .wa-filtro { background: #1f2937; border-color: #374151; color: #cbd5e1; }
        .wa-filtro.on { background: linear-gradient(135deg, var(--ia1), var(--ia2)); border-color: transparent; color: #fff; }
        .wa-items { overflow-y: auto; flex: 1; min-height: 0; }
        .wa-item { display: flex; gap: .7rem; padding: .75rem .85rem; border: 0; border-bottom: 1px solid #eef1f6; cursor: pointer; width: 100%; text-align: left; background: transparent; transition: background .15s; }
        .dark .wa-item { border-color: #1f2937; }
        .wa-item:hover { background: #f1f5fb; }
        .dark .wa-item:hover { background: #1f2937; }
        .wa-item.sel { background: linear-gradient(90deg, #eef2ff, #f5f3ff); box-shadow: inset 3px 0 0 var(--ia1); }
        .dark .wa-item.sel { background: #312e81; }
        .wa-av { position: relative; flex: 0 0 2.8rem; height: 2.8rem; }
        .wa-av > .wa-inicial { width: 100%; height: 100%; border-radius: 999px; background: linear-gradient(135deg, #64748b, #475569); color: #fff; display: grid; place-items: center; font-weight: 800; font-size: 1rem; }
        .wa-av .wa-mini { position: absolute; right: -.25rem; bottom: -.2rem; width: 1.2rem; height: 1.2rem; border-radius: .4rem; border: 2px solid #fff; object-fit: cover; background: #fff; }
        .dark .wa-av .wa-mini { border-color: #0f172a; }
        .wa-av .wa-mini.persona { display: grid; place-items: center; background: #f59e0b; color: #fff; font-size: .62rem; }
        .wa-item-body { min-width: 0; flex: 1; }
        .wa-item-top { display: flex; justify-content: space-between; gap: .5rem; align-items: baseline; }
        .wa-item-name { font-weight: 800; font-size: .9rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .wa-item-time { font-size: .68rem; color: #94a3b8; white-space: nowrap; }
        .wa-item-preview { font-size: .78rem; color: #64748b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: .1rem; }
        .wa-badges { display: flex; gap: .3rem; margin-top: .3rem; }
        .wa-tag { font-size: .6rem; font-weight: 800; padding: .1rem .5rem; border-radius: 999px; text-transform: uppercase; letter-spacing: .04em; }
        .wa-tag.agente { background: linear-gradient(135deg, #e0e7ff, #ede9fe); color: #5b21b6; }
        .wa-tag.humano { background: #fef3c7; color: #92400e; }
        .wa-tag.pausado { background: #e2e8f0; color: #475569; }
        .wa-tag.espera { background: #fee2e2; color: #b91c1c; animation: wa-latido 1.6s ease-in-out infinite; }
        @keyframes wa-latido { 50% { opacity: .55; } }

        /* ───────── Chat ───────── */
        .wa-chat { display: flex; flex-direction: column; min-width: 0; min-height: 0; background: radial-gradient(circle at 15% 0%, #eef2ff 0, transparent 38%), #f6f8fc; }
        .dark .wa-chat { background: #0f172a; }
        .wa-chat-head { padding: .75rem 1.1rem; background: #fff; border-bottom: 1px solid #e5e9f2; display: flex; align-items: center; gap: .8rem; flex-wrap: wrap; }
        .dark .wa-chat-head { background: #111827; border-color: #374151; }
        .wa-chat-title { min-width: 0; flex: 1; line-height: 1.25; }
        .wa-chat-title b { display: block; font-size: 1rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .wa-estado { display: inline-flex; align-items: center; gap: .4rem; font-size: .74rem; font-weight: 700; margin-top: .15rem; padding: .12rem .6rem .12rem .25rem; border-radius: 999px; }
        .wa-estado img { width: 1.2rem; height: 1.2rem; border-radius: .4rem; object-fit: cover; }
        .wa-estado.agente { background: #ecfdf5; color: #047857; }
        .wa-estado.humano { background: #fffbeb; color: #b45309; padding-left: .6rem; }
        .wa-estado.pausado { background: #f1f5f9; color: #475569; padding-left: .6rem; }
        .wa-pulso { width: .5rem; height: .5rem; border-radius: 999px; background: #10b981; box-shadow: 0 0 0 0 rgba(16, 185, 129, .6); animation: wa-pulso 1.8s infinite; }
        @keyframes wa-pulso { 70% { box-shadow: 0 0 0 .5rem rgba(16, 185, 129, 0); } 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }
        .wa-acciones { display: flex; gap: .4rem; flex-wrap: wrap; }
        .wa-btn { padding: .45rem .9rem; border-radius: .75rem; font-size: .78rem; font-weight: 800; border: none; cursor: pointer; transition: transform .12s, filter .12s; }
        .wa-btn:hover { filter: brightness(1.06); transform: translateY(-1px); }
        .wa-btn.verde { background: linear-gradient(135deg, #10b981, #059669); color: #fff; }
        .wa-btn.azul { background: linear-gradient(135deg, var(--ia1), var(--ia2)); color: #fff; }
        .wa-btn.gris { background: #e8ecf3; color: #334155; }
        .dark .wa-btn.gris { background: #374151; color: #e5e7eb; }
        .wa-volver { display: none; border: none; background: #eef2ff; color: #4338ca; border-radius: .6rem; width: 2.2rem; height: 2.2rem; font-weight: 900; font-size: 1.2rem; cursor: pointer; }
        .wa-banner { display: flex; align-items: center; gap: .6rem; padding: .45rem 1.1rem; font-size: .76rem; color: #4338ca; background: linear-gradient(90deg, #eef2ff, #f5f3ff); border-bottom: 1px solid #e0e7ff; }
        .wa-banner img { width: 1.5rem; height: 1.5rem; border-radius: .5rem; }

        .wa-msgs { flex: 1; min-height: 0; overflow-y: auto; padding: 1.1rem 1.2rem; display: flex; flex-direction: column; gap: .2rem; }
        .wa-dia { align-self: center; font-size: .68rem; font-weight: 800; color: #64748b; background: #e8ecf3; border-radius: 999px; padding: .2rem .8rem; margin: .7rem 0 .5rem; letter-spacing: .03em; text-transform: uppercase; }
        .dark .wa-dia { background: #1f2937; color: #cbd5e1; }
        .wa-corte { align-self: center; font-size: .7rem; font-weight: 700; color: #6366f1; background: #eef2ff; border-radius: 999px; padding: .2rem .8rem; margin: .5rem 0; }
        .wa-sistema { align-self: center; font-size: .72rem; color: #92400e; background: #fffbeb; border: 1px solid #fde68a; border-radius: .8rem; padding: .35rem .8rem; margin: .3rem 0; max-width: 80%; text-align: center; }
        .wa-row { display: flex; gap: .55rem; align-items: flex-end; max-width: 100%; }
        .wa-row.out { flex-direction: row-reverse; align-self: flex-end; }
        .wa-row.in { align-self: flex-start; }
        .wa-row.apilada { margin-top: -.05rem; }
        .wa-ava { flex: 0 0 2.1rem; width: 2.1rem; height: 2.1rem; }
        .wa-ava img, .wa-ava .wa-ava-i { width: 100%; height: 100%; border-radius: .75rem; object-fit: cover; }
        .wa-ava img.ia { box-shadow: 0 6px 14px -8px var(--ia2); }
        .wa-ava .wa-ava-i { display: grid; place-items: center; color: #fff; font-weight: 800; font-size: .85rem; background: linear-gradient(135deg, #64748b, #475569); border-radius: 999px; }
        .wa-ava .wa-ava-i.humano { background: linear-gradient(135deg, #10b981, #059669); }
        .wa-ava .wa-ava-i svg { width: 1.1rem; height: 1.1rem; }
        .wa-ava.vacio { visibility: hidden; }
        .wa-col { display: flex; flex-direction: column; min-width: 0; max-width: min(34rem, 78%); }
        .wa-row.out .wa-col { align-items: flex-end; }
        .wa-quien { display: flex; align-items: center; gap: .4rem; font-size: .68rem; font-weight: 800; color: #64748b; margin: 0 .35rem .2rem; }
        .wa-chip-ia { display: inline-flex; align-items: center; gap: .15rem; font-size: .58rem; font-weight: 900; letter-spacing: .06em; color: #fff; background: linear-gradient(135deg, var(--ia1), var(--ia2)); border-radius: 999px; padding: .08rem .45rem; }
        .wa-chip-ia svg { width: .6rem; height: .6rem; }
        .wa-msg { padding: .5rem .8rem .35rem; border-radius: 1.1rem; font-size: .88rem; line-height: 1.4; overflow-wrap: anywhere; box-shadow: 0 1px 2px rgba(15, 23, 42, .07); }
        .wa-texto { white-space: pre-wrap; }
        .wa-meta { font-size: .62rem; opacity: .6; margin-top: .15rem; text-align: right; }
        .wa-msg.in { background: #fff; color: #0f172a; border-bottom-left-radius: .35rem; }
        .dark .wa-msg.in { background: #1f2937; color: #f1f5f9; }
        .wa-msg.agente { background: linear-gradient(135deg, #eef2ff, #f3e8ff); color: #1e1b4b; border: 1px solid #e0e7ff; border-bottom-right-radius: .35rem; }
        .dark .wa-msg.agente { background: #312e81; color: #eef2ff; border-color: #4338ca; }
        .wa-msg.humano { background: linear-gradient(135deg, #d1fae5, #bbf7d0); color: #064e3b; border-bottom-right-radius: .35rem; }
        .wa-msg.error { outline: 2px solid #ef4444; }
        .wa-msg img.foto { width: min(15rem, 100%); border-radius: .8rem; display: block; margin-bottom: .35rem; }
        .wa-doc { display: flex; align-items: center; gap: .6rem; background: rgba(255, 255, 255, .7); border: 1px solid rgba(99, 102, 241, .25); border-radius: .85rem; padding: .5rem .7rem; text-decoration: none; color: inherit; margin-bottom: .25rem; }
        .wa-doc-i { flex: 0 0 2.2rem; height: 2.2rem; border-radius: .6rem; background: #ef4444; color: #fff; display: grid; place-items: center; font-size: .6rem; font-weight: 900; }
        .wa-doc b { display: block; font-size: .82rem; }
        .wa-doc span { font-size: .68rem; opacity: .65; }
        .wa-aviso { padding: .5rem 1.1rem; background: #fffbeb; color: #92400e; font-size: .78rem; border-top: 1px solid #fde68a; }
        .wa-form { display: flex; gap: .6rem; padding: .75rem .9rem; background: #fff; border-top: 1px solid #e5e9f2; align-items: flex-end; }
        .dark .wa-form { background: #111827; border-color: #374151; }
        .wa-input { flex: 1; padding: .65rem .95rem; border: 1px solid #dbe1ea; border-radius: 1rem; font-size: 16px; background: #f8fafc; resize: none; max-height: 8rem; }
        .dark .wa-input { background: #1f2937; border-color: #374151; color: #f1f5f9; }
        .wa-enviar { flex: 0 0 2.7rem; height: 2.7rem; border-radius: 999px; border: none; cursor: pointer; display: grid; place-items: center; color: #fff; background: linear-gradient(135deg, var(--ia1), var(--ia2)); box-shadow: 0 10px 18px -10px var(--ia2); }
        .wa-enviar svg { width: 1.2rem; height: 1.2rem; }
        .wa-vacio { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .6rem; flex: 1; color: #94a3b8; padding: 2rem; text-align: center; }
        .wa-vacio img { width: 5.5rem; height: 5.5rem; border-radius: 1.6rem; box-shadow: 0 18px 30px -16px var(--ia2); }
        .wa-vacio b { color: #334155; font-size: 1.05rem; }
        .dark .wa-vacio b { color: #e2e8f0; }

        /* ───────── Resumen superior ───────── */
        .wa-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .8rem; margin-bottom: .9rem; }
        .wa-stat { --c1: #6366f1; --c2: #9333ea; position: relative; overflow: hidden; display: flex; align-items: center; gap: .8rem; text-align: left; width: 100%; cursor: pointer;
            padding: .8rem 1rem; border-radius: 1.1rem; background: #fff; border: 1px solid #e8ecf3;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 10px 22px -18px rgba(15, 23, 42, .3); transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease; }
        .dark .wa-stat { background: #1f2937; border-color: #374151; }
        .wa-stat.verde { --c1: #10b981; --c2: #059669; }
        .wa-stat.ambar { --c1: #f59e0b; --c2: #f97316; }
        .wa-stat.rojo  { --c1: #f43f5e; --c2: #e11d48; }
        .wa-stat.gris  { --c1: #64748b; --c2: #475569; }
        .wa-stat::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: linear-gradient(180deg, var(--c1), var(--c2)); }
        .wa-stat:hover { transform: translateY(-2px); box-shadow: 0 2px 4px rgba(15, 23, 42, .05), 0 16px 26px -16px color-mix(in srgb, var(--c2) 55%, transparent); }
        .wa-stat.on { border-color: var(--c1); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c1) 18%, transparent); }
        .wa-stat-ico { flex: 0 0 2.5rem; height: 2.5rem; border-radius: .85rem; display: grid; place-items: center; color: #fff; background: linear-gradient(135deg, var(--c1), var(--c2)); box-shadow: 0 8px 14px -8px var(--c2); overflow: hidden; }
        .wa-stat-ico svg { width: 1.3rem; height: 1.3rem; }
        .wa-stat-ico img { width: 100%; height: 100%; object-fit: cover; }
        .wa-stat-n { font-size: 1.7rem; font-weight: 900; line-height: 1; letter-spacing: -.02em; color: #0f172a; }
        .dark .wa-stat-n { color: #f8fafc; }
        .wa-stat-l { font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--c2); margin-top: .2rem; line-height: 1.15; }
        .wa-stat.rojo.hay .wa-stat-ico { animation: wa-latido 1.6s ease-in-out infinite; }

        @media (max-width: 767px) {
            .wa-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .55rem; margin-bottom: .7rem; }
            .wa-stat { padding: .6rem .75rem; gap: .6rem; }
            .wa-stat-ico { flex-basis: 2.1rem; height: 2.1rem; }
            .wa-stat-n { font-size: 1.35rem; }
            .wa-wrap { grid-template-columns: 1fr; height: calc(100dvh - 17rem - var(--mnav-h, 0px)); min-height: 24rem; }
            .wa-wrap.hay-chat .wa-list { display: none; }
            .wa-wrap:not(.hay-chat) .wa-chat { display: none; }
            .wa-volver { display: block; }
            .wa-col { max-width: 84%; }
            .wa-msgs { padding: .8rem; }
        }
    </style>

    @php
        $conversaciones = $this->conversaciones;
        $actual   = $this->actual;
        $mensajes = $this->mensajes;
        $agente   = $this->agente;
        $inicial  = fn ($c) => mb_strtoupper(mb_substr(trim($c->contacto->nombre ?: '#'), 0, 1));
        $nombreEstado = ['agente' => $agente['nombre'] . ' atiende', 'humano' => 'Atiende un vendedor', 'pausado' => 'Agente pausado'];
        $icoPersona = '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.1a7.5 7.5 0 0 1 15 0"/></svg>';
        $icoChispa = '<svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l2.1 6.4L20.5 10.5l-6.4 2.1L12 19l-2.1-6.4L3.5 10.5l6.4-2.1z"/></svg>';
        // Negrita estilo WhatsApp (*texto*), con el resto del contenido escapado
        $formato = fn (string $t) => preg_replace('/\*([^*\n]+)\*/u', '<strong>$1</strong>', e($t));
        $etiquetaDia = function ($f) {
            if ($f->isToday()) return 'Hoy';
            if ($f->isYesterday()) return 'Ayer';
            return $f->format('d/m/Y');
        };
    @endphp

    @php $rs = $this->resumenEstados; @endphp
    <div class="wa-stats" wire:poll.5s>
        <button type="button" class="wa-stat verde {{ $filtro === 'agente' ? 'on' : '' }}" wire:click="$set('filtro', 'agente')" title="Conversaciones que atiende {{ $agente['nombre'] }}">
            <span class="wa-stat-ico"><img src="{{ $agente['avatar'] }}" alt=""></span>
            <div><div class="wa-stat-n">{{ $rs['agente'] }}</div><div class="wa-stat-l">Con el agente</div></div>
        </button>
        <button type="button" class="wa-stat ambar {{ $filtro === 'humano' ? 'on' : '' }}" wire:click="$set('filtro', 'humano')" title="Conversaciones con un vendedor o con el agente pausado">
            <span class="wa-stat-ico">{!! $icoPersona !!}</span>
            <div><div class="wa-stat-n">{{ $rs['humano'] }}</div><div class="wa-stat-l">Con vendedor</div></div>
        </button>
        <button type="button" class="wa-stat rojo {{ $rs['atencion'] > 0 ? 'hay' : '' }} {{ $filtro === 'atencion' ? 'on' : '' }}" wire:click="$set('filtro', 'atencion')" title="El cliente escribió y todavía nadie respondió">
            <span class="wa-stat-ico"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3.75 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></span>
            <div><div class="wa-stat-n">{{ $rs['atencion'] }}</div><div class="wa-stat-l">Esperan respuesta</div></div>
        </button>
        <button type="button" class="wa-stat gris {{ $filtro === 'todas' ? 'on' : '' }}" wire:click="$set('filtro', 'todas')" title="Conversaciones con actividad hoy">
            <span class="wa-stat-ico"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.12 2.98 2.7 3.22 1.1.17 2.2.29 3.3.37v3.4l3.6-3.4c1.4-.05 2.8-.17 4.2-.37 1.58-.24 2.7-1.62 2.7-3.22V6.74c0-1.6-1.12-2.98-2.7-3.22A48.4 48.4 0 0 0 12 3c-2.4 0-4.8.17-7.05.52-1.58.24-2.7 1.62-2.7 3.22v6.02Z"/></svg></span>
            <div><div class="wa-stat-n">{{ $rs['hoy'] }}</div><div class="wa-stat-l">Chats de hoy</div></div>
        </button>
    </div>

    <div class="wa-wrap {{ $actual ? 'hay-chat' : '' }}" wire:poll.5s>
        {{-- ───────── Lista ───────── --}}
        <div class="wa-list">
            <div class="wa-list-top">
                <div class="wa-marca">
                    <img src="{{ $agente['avatar'] }}" alt="{{ $agente['nombre'] }}">
                    <div><b>{{ $agente['nombre'] }}</b><span><i class="wa-pulso"></i> En línea</span></div>
                </div>
                <input type="search" class="wa-search" placeholder="Buscar por nombre o teléfono..." wire:model.live.debounce.400ms="busqueda">
                <div class="wa-filtros">
                    @foreach(['todas' => 'Todas', 'atencion' => 'Esperan respuesta', 'humano' => 'Con vendedor', 'agente' => 'Con el agente'] as $k => $label)
                        <button type="button" class="wa-filtro {{ $filtro === $k ? 'on' : '' }}" wire:click="$set('filtro', '{{ $k }}')">{{ $label }}</button>
                    @endforeach
                </div>
            </div>
            <div class="wa-items">
                @forelse($conversaciones as $c)
                    @php $espera = $c->estado !== 'agente' && $c->ultima_direccion === 'in'; @endphp
                    <button type="button" class="wa-item {{ $seleccionada === $c->id ? 'sel' : '' }}" wire:click="seleccionar({{ $c->id }})" wire:key="c-{{ $c->id }}">
                        <div class="wa-av">
                            <div class="wa-inicial">{{ $inicial($c) }}</div>
                            @if($c->estado === 'agente')
                                <img class="wa-mini" src="{{ $agente['avatar'] }}" alt="" title="{{ $agente['nombre'] }} atiende">
                            @else
                                <span class="wa-mini persona">👤</span>
                            @endif
                        </div>
                        <div class="wa-item-body">
                            <div class="wa-item-top">
                                <span class="wa-item-name">{{ $c->contacto->nombre ?: '+' . $c->contacto->telefono }}</span>
                                <span class="wa-item-time">{{ $c->ultimo_mensaje_at?->diffForHumans(short: true) }}</span>
                            </div>
                            <div class="wa-item-preview">{{ $c->ultima_direccion === 'out' ? '↩ ' : '' }}{{ \Illuminate\Support\Str::limit(trim($c->ultimo_texto ?? ''), 60) }}</div>
                            <div class="wa-badges">
                                <span class="wa-tag {{ $c->estado }}">{{ ['agente' => 'IA', 'humano' => 'Vendedor', 'pausado' => 'Pausado'][$c->estado] ?? $c->estado }}</span>
                                @if($espera)<span class="wa-tag espera">Esperando</span>@endif
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="wa-vacio" style="padding:2rem 1rem;">No hay conversaciones.</div>
                @endforelse
            </div>
        </div>

        {{-- ───────── Chat ───────── --}}
        <div class="wa-chat">
            @if($actual)
                <div class="wa-chat-head">
                    <button type="button" class="wa-volver" wire:click="volver" aria-label="Volver">‹</button>
                    <div class="wa-av" style="flex:0 0 2.6rem;height:2.6rem;"><div class="wa-inicial" style="width:100%;height:100%;border-radius:999px;background:linear-gradient(135deg,#64748b,#475569);color:#fff;display:grid;place-items:center;font-weight:800;">{{ $inicial($actual) }}</div></div>
                    <div class="wa-chat-title">
                        <b>{{ $actual->contacto->nombre ?: 'Cliente' }}</b>
                        <span style="font-size:.75rem;color:#64748b;">+{{ $actual->contacto->telefono }}</span>
                        <div>
                            <span class="wa-estado {{ $actual->estado }}">
                                @if($actual->estado === 'agente')<img src="{{ $agente['avatar'] }}" alt=""><i class="wa-pulso"></i>@endif
                                {{ $nombreEstado[$actual->estado] ?? $actual->estado }}
                            </span>
                        </div>
                    </div>
                    <div class="wa-acciones">
                        <button type="button" class="wa-btn gris" wire:click="reiniciarContexto"
                            wire:confirm="¿Reiniciar el contexto? El agente dejará de considerar los mensajes anteriores y empezará de cero (la conversación vuelve al agente)."
                            title="El agente olvida lo anterior; el historial sigue visible acá">Reiniciar contexto</button>
                        @if($actual->estado === 'agente')
                            <button type="button" class="wa-btn azul" wire:click="tomarControl">Tomar control</button>
                            <button type="button" class="wa-btn gris" wire:click="pausarAgente">Pausar agente</button>
                        @else
                            <button type="button" class="wa-btn verde" wire:click="devolverAlAgente">Devolver al agente</button>
                        @endif
                    </div>
                </div>

                @if($actual->estado === 'agente')
                    <div class="wa-banner"><img src="{{ $agente['avatar'] }}" alt=""><span><b>{{ $agente['nombre'] }}</b> responde automáticamente. Si escribís vos o tomás el control, se pausa.</span></div>
                @endif

                @php $corteId = $actual->contexto_desde_id ? $mensajes->firstWhere('id', '>', $actual->contexto_desde_id)?->id : null; @endphp
                <div class="wa-msgs">
                    @foreach($mensajes as $i => $m)
                        @php
                            $prev = $i > 0 ? $mensajes[$i - 1] : null;
                            $nuevoDia = !$prev || !$prev->created_at->isSameDay($m->created_at);
                            $apilado = $prev && !$nuevoDia && $prev->direccion === $m->direccion && $prev->autor === $m->autor
                                && abs($m->created_at->diffInMinutes($prev->created_at, true)) < 3 && $m->id !== $corteId;
                            $esIA = $m->direccion === 'out' && $m->autor === 'agente';
                            $esHumano = $m->direccion === 'out' && $m->autor === 'humano';
                            $texto = trim((string) $m->texto);
                        @endphp

                        @if($nuevoDia)<div class="wa-dia" wire:key="d-{{ $m->id }}">{{ $etiquetaDia($m->created_at) }}</div>@endif
                        @if($m->id === $corteId)<div class="wa-corte" wire:key="corte-{{ $m->id }}">El agente empieza de cero desde acá</div>@endif

                        @if($m->direccion === 'out' && $m->autor === 'sistema')
                            <div class="wa-sistema" wire:key="m-{{ $m->id }}">{{ $texto }}</div>
                        @else
                            <div class="wa-row {{ $m->direccion }} {{ $apilado ? 'apilada' : '' }}" wire:key="m-{{ $m->id }}">
                                <div class="wa-ava {{ $apilado ? 'vacio' : '' }}">
                                    @if($esIA)<img class="ia" src="{{ $agente['avatar'] }}" alt="{{ $agente['nombre'] }}">
                                    @elseif($esHumano)<div class="wa-ava-i humano">{!! $icoPersona !!}</div>
                                    @else<div class="wa-ava-i">{{ $inicial($actual) }}</div>@endif
                                </div>
                                <div class="wa-col">
                                    @if(!$apilado && $m->direccion === 'out')
                                        <div class="wa-quien">
                                            {{ $esIA ? $agente['nombre'] : 'Vendedor' }}
                                            @if($esIA)<span class="wa-chip-ia">{!! $icoChispa !!} IA</span>@endif
                                        </div>
                                    @endif
                                    <div class="wa-msg {{ $m->direccion === 'in' ? 'in' : ($esIA ? 'agente' : 'humano') }} {{ $m->estado === 'error' ? 'error' : '' }}">
                                        @if($m->tipo === 'image' && $m->media_url)
                                            <img class="foto" src="{{ $m->media_url }}" alt="Foto" loading="lazy">
                                        @elseif($m->tipo === 'document' && $m->media_url)
                                            <a class="wa-doc" href="{{ $m->media_url }}" target="_blank" rel="noopener"><span class="wa-doc-i">PDF</span><div><b>{{ $texto ?: 'Documento' }}</b><span>Abrir documento</span></div></a>
                                            @php $texto = ''; @endphp
                                        @elseif($m->tipo !== 'text' && $texto === '')
                                            <em>[{{ $m->tipo }}]</em>
                                        @endif
                                        @if($texto !== '')<div class="wa-texto">{!! $formato($texto) !!}</div>@endif
                                        <div class="wa-meta">{{ $m->created_at->format('H:i') }}@if($m->estado === 'error') · no se pudo enviar @endif</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    <div wire:key="fin-{{ $mensajes->last()?->id }}" x-init="$el.parentElement.scrollTop = $el.parentElement.scrollHeight"></div>
                </div>

                @if(!$this->ventanaAbierta)
                    <div class="wa-aviso">Pasaron más de 24 horas desde el último mensaje del cliente: WhatsApp no permite escribirle texto libre hasta que él escriba de nuevo.</div>
                @endif

                <form class="wa-form" wire:submit="enviar">
                    <textarea class="wa-input" rows="1" placeholder="{{ $actual->estado === 'agente' ? 'Al escribir, el agente se pausa y atendés vos...' : 'Escribí tu respuesta...' }}"
                        wire:model="mensaje" @keydown.enter.prevent="if (!$event.shiftKey) $wire.enviar()"></textarea>
                    <button type="submit" class="wa-enviar" wire:loading.attr="disabled" wire:target="enviar" aria-label="Enviar">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.27 3.13a.75.75 0 0 1 1.06-.89l16.5 8.25a.75.75 0 0 1 0 1.34l-16.5 8.25a.75.75 0 0 1-1.06-.89L6 12Zm0 0h7.5"/></svg>
                    </button>
                </form>
            @else
                <div class="wa-vacio">
                    <img src="{{ $agente['avatar'] }}" alt="{{ $agente['nombre'] }}">
                    <b>{{ $agente['nombre'] }} está atendiendo tus chats</b>
                    <span>Elegí una conversación para ver los mensajes, tomar el control o responder vos.</span>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
