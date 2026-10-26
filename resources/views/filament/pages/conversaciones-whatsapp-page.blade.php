<x-filament-panels::page>
    <style>
        .wa-wrap { display: grid; grid-template-columns: 22rem 1fr; height: calc(100dvh - 11rem); min-height: 28rem; border: 1px solid #e2e8f0; border-radius: 1rem; overflow: hidden; background: #fff; }
        .dark .wa-wrap { background: #111827; border-color: #374151; }
        .wa-list { border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; min-width: 0; min-height: 0; }
        .dark .wa-list { border-color: #374151; }
        .wa-list-top { padding: .75rem; border-bottom: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: .5rem; }
        .dark .wa-list-top { border-color: #374151; }
        .wa-search { width: 100%; padding: .5rem .75rem; border: 1px solid #cbd5e1; border-radius: .7rem; font-size: .85rem; background: transparent; }
        .wa-filtros { display: flex; gap: .35rem; flex-wrap: wrap; }
        .wa-filtro { padding: .25rem .65rem; border-radius: 999px; font-size: .72rem; font-weight: 700; border: 1px solid #cbd5e1; background: transparent; cursor: pointer; color: #64748b; }
        .wa-filtro.on { background: #4f46e5; border-color: #4f46e5; color: #fff; }
        .wa-items { overflow-y: auto; flex: 1; min-height: 0; }
        .wa-item { display: flex; gap: .65rem; padding: .7rem .8rem; border-bottom: 1px solid #f1f5f9; cursor: pointer; width: 100%; text-align: left; background: transparent; border-left: 0; border-right: 0; border-top: 0; }
        .dark .wa-item { border-color: #1f2937; }
        .wa-item:hover { background: #f8fafc; }
        .dark .wa-item:hover { background: #1f2937; }
        .wa-item.sel { background: #eef2ff; }
        .dark .wa-item.sel { background: #312e81; }
        .wa-avatar { flex: 0 0 2.4rem; height: 2.4rem; border-radius: 999px; background: linear-gradient(135deg, #6366f1, #7c3aed); color: #fff; display: grid; place-items: center; font-weight: 800; font-size: .9rem; }
        .wa-item-body { min-width: 0; flex: 1; }
        .wa-item-top { display: flex; justify-content: space-between; gap: .5rem; align-items: baseline; }
        .wa-item-name { font-weight: 800; font-size: .88rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .wa-item-time { font-size: .68rem; color: #94a3b8; white-space: nowrap; }
        .wa-item-preview { font-size: .78rem; color: #64748b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .wa-badges { display: flex; gap: .3rem; margin-top: .2rem; }
        .wa-tag { font-size: .62rem; font-weight: 800; padding: .08rem .45rem; border-radius: 999px; text-transform: uppercase; letter-spacing: .03em; }
        .wa-tag.agente { background: #dcfce7; color: #166534; }
        .wa-tag.humano { background: #fef3c7; color: #92400e; }
        .wa-tag.pausado { background: #e2e8f0; color: #475569; }
        .wa-tag.espera { background: #fee2e2; color: #b91c1c; }
        .wa-chat { display: flex; flex-direction: column; min-width: 0; min-height: 0; background: #f8fafc; }
        .dark .wa-chat { background: #0f172a; }
        .wa-chat-head { padding: .7rem 1rem; background: #fff; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; }
        .dark .wa-chat-head { background: #111827; border-color: #374151; }
        .wa-chat-title { min-width: 0; flex: 1; line-height: 1.2; }
        .wa-chat-title b { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .wa-acciones { display: flex; gap: .4rem; flex-wrap: wrap; }
        .wa-btn { padding: .4rem .8rem; border-radius: .7rem; font-size: .78rem; font-weight: 800; border: none; cursor: pointer; }
        .wa-btn.verde { background: linear-gradient(135deg, #10b981, #059669); color: #fff; }
        .wa-btn.azul { background: #4f46e5; color: #fff; }
        .wa-btn.gris { background: #e2e8f0; color: #334155; }
        .wa-volver { display: none; border: none; background: #eef2ff; color: #4338ca; border-radius: .6rem; width: 2.2rem; height: 2.2rem; font-weight: 900; cursor: pointer; }
        .wa-msgs { flex: 1; min-height: 0; overflow-y: auto; padding: 1rem; display: flex; flex-direction: column; gap: .45rem; }
        .wa-msg { max-width: 78%; padding: .5rem .75rem; border-radius: 1rem; font-size: .86rem; line-height: 1.35; white-space: pre-wrap; overflow-wrap: anywhere; box-shadow: 0 1px 2px rgba(15,23,42,.08); }
        .wa-msg small { display: block; font-size: .64rem; opacity: .65; margin-top: .2rem; }
        .wa-msg.in { align-self: flex-start; background: #fff; color: #111827; border-bottom-left-radius: .3rem; }
        .dark .wa-msg.in { background: #1f2937; color: #f1f5f9; }
        .wa-msg.out { align-self: flex-end; background: #e0e7ff; color: #1e1b4b; border-bottom-right-radius: .3rem; }
        .wa-msg.out.humano { background: #d1fae5; color: #064e3b; }
        .wa-msg.out.sistema { background: #fef3c7; color: #78350f; }
        .wa-msg.error { outline: 2px solid #ef4444; }
        .wa-msg img { max-width: 14rem; border-radius: .6rem; display: block; margin-bottom: .3rem; }
        .wa-corte { align-self: center; font-size: .7rem; font-weight: 700; color: #6366f1; background: #eef2ff; border-radius: 999px; padding: .2rem .8rem; margin: .4rem 0; }
        .wa-quien { font-size: .62rem; font-weight: 800; text-transform: uppercase; opacity: .6; margin-bottom: .1rem; }
        .wa-aviso { padding: .5rem 1rem; background: #fffbeb; color: #92400e; font-size: .78rem; border-top: 1px solid #fde68a; }
        .wa-form { display: flex; gap: .5rem; padding: .7rem; background: #fff; border-top: 1px solid #e2e8f0; }
        .dark .wa-form { background: #111827; border-color: #374151; }
        .wa-input { flex: 1; padding: .6rem .8rem; border: 1px solid #cbd5e1; border-radius: .8rem; font-size: 16px; background: transparent; resize: none; }
        .wa-vacio { display: grid; place-items: center; flex: 1; color: #94a3b8; font-size: .95rem; padding: 2rem; text-align: center; }

        @media (max-width: 767px) {
            .wa-wrap { grid-template-columns: 1fr; height: calc(100dvh - 12rem - var(--mnav-h, 0px)); }
            .wa-wrap.hay-chat .wa-list { display: none; }
            .wa-wrap:not(.hay-chat) .wa-chat { display: none; }
            .wa-volver { display: block; }
            .wa-msg { max-width: 88%; }
        }
    </style>

    @php
        $conversaciones = $this->conversaciones;
        $actual   = $this->actual;
        $mensajes = $this->mensajes;
        $inicial  = fn ($c) => mb_strtoupper(mb_substr(trim($c->contacto->nombre ?: '#'), 0, 1));
    @endphp

    <div class="wa-wrap {{ $actual ? 'hay-chat' : '' }}" wire:poll.5s>
        {{-- Lista --}}
        <div class="wa-list">
            <div class="wa-list-top">
                <input type="search" class="wa-search" placeholder="Buscar por nombre o teléfono..." wire:model.live.debounce.400ms="busqueda">
                <div class="wa-filtros">
                    @foreach(['todas' => 'Todas', 'atencion' => 'Esperan respuesta', 'humano' => 'Con vendedor', 'agente' => 'Con agente'] as $k => $label)
                        <button type="button" class="wa-filtro {{ $filtro === $k ? 'on' : '' }}" wire:click="$set('filtro', '{{ $k }}')">{{ $label }}</button>
                    @endforeach
                </div>
            </div>
            <div class="wa-items">
                @forelse($conversaciones as $c)
                    @php $espera = $c->estado !== 'agente' && $c->ultima_direccion === 'in'; @endphp
                    <button type="button" class="wa-item {{ $seleccionada === $c->id ? 'sel' : '' }}" wire:click="seleccionar({{ $c->id }})" wire:key="c-{{ $c->id }}">
                        <div class="wa-avatar">{{ $inicial($c) }}</div>
                        <div class="wa-item-body">
                            <div class="wa-item-top">
                                <span class="wa-item-name">{{ $c->contacto->nombre ?: '+' . $c->contacto->telefono }}</span>
                                <span class="wa-item-time">{{ $c->ultimo_mensaje_at?->diffForHumans(short: true) }}</span>
                            </div>
                            <div class="wa-item-preview">{{ $c->ultima_direccion === 'out' ? 'Vos: ' : '' }}{{ \Illuminate\Support\Str::limit($c->ultimo_texto ?? '', 60) }}</div>
                            <div class="wa-badges">
                                <span class="wa-tag {{ $c->estado }}">{{ ['agente' => 'Agente', 'humano' => 'Vendedor', 'pausado' => 'Pausado'][$c->estado] ?? $c->estado }}</span>
                                @if($espera)<span class="wa-tag espera">Esperando</span>@endif
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="wa-vacio">No hay conversaciones.</div>
                @endforelse
            </div>
        </div>

        {{-- Chat --}}
        <div class="wa-chat">
            @if($actual)
                <div class="wa-chat-head">
                    <button type="button" class="wa-volver" wire:click="volver" aria-label="Volver">‹</button>
                    <div class="wa-chat-title">
                        <b>{{ $actual->contacto->nombre ?: 'Cliente' }}</b>
                        <span style="font-size:.75rem;color:#64748b;">+{{ $actual->contacto->telefono }} ·
                            {{ ['agente' => 'Atiende el agente', 'humano' => 'Atiende un vendedor', 'pausado' => 'Agente pausado'][$actual->estado] ?? $actual->estado }}</span>
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

                @php $corteId = $actual->contexto_desde_id ? $mensajes->firstWhere('id', '>', $actual->contexto_desde_id)?->id : null; @endphp
                <div class="wa-msgs">
                    @foreach($mensajes as $m)
                        @if($m->id === $corteId)
                            <div class="wa-corte" wire:key="corte-{{ $m->id }}">El agente empieza de cero desde acá</div>
                        @endif
                        <div class="wa-msg {{ $m->direccion }} {{ $m->autor === 'humano' ? 'humano' : '' }} {{ $m->autor === 'sistema' ? 'sistema' : '' }} {{ $m->estado === 'error' ? 'error' : '' }}" wire:key="m-{{ $m->id }}">
                            @if($m->direccion === 'out')
                                <div class="wa-quien">{{ ['agente' => 'Agente IA', 'humano' => 'Vendedor', 'sistema' => 'Sistema'][$m->autor] ?? $m->autor }}</div>
                            @endif
                            @if($m->tipo === 'image' && $m->media_url)
                                <img src="{{ $m->media_url }}" alt="Foto" loading="lazy">
                            @elseif($m->tipo !== 'text' && !$m->texto)
                                <em>[{{ $m->tipo }}]</em>
                            @endif
                            {{ $m->texto }}
                            <small>{{ $m->created_at->format('d/m H:i') }}@if($m->estado === 'error') · no se pudo enviar @endif</small>
                        </div>
                    @endforeach
                    <div wire:key="fin-{{ $mensajes->last()?->id }}" x-init="$el.parentElement.scrollTop = $el.parentElement.scrollHeight"></div>
                </div>

                @if(!$this->ventanaAbierta)
                    <div class="wa-aviso">Pasaron más de 24 horas desde el último mensaje del cliente: WhatsApp no permite escribirle texto libre hasta que él escriba de nuevo.</div>
                @endif

                <form class="wa-form" wire:submit="enviar">
                    <textarea class="wa-input" rows="1" placeholder="{{ $actual->estado === 'agente' ? 'Al escribir, el agente se pausa y atendés vos...' : 'Escribí tu respuesta...' }}"
                        wire:model="mensaje" @keydown.enter.prevent="if (!$event.shiftKey) $wire.enviar()"></textarea>
                    <button type="submit" class="wa-btn azul" wire:loading.attr="disabled" wire:target="enviar">Enviar</button>
                </form>
            @else
                <div class="wa-vacio">Elegí una conversación de la lista para ver los mensajes y responder.</div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
