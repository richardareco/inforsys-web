@php
    $record = $getRecord();
    $fotos  = $record ? app(\App\Services\Productos\FotosProducto::class)->listar($record->item) : [];
    $maximo = \App\Services\Productos\FotosProducto::MAXIMO;
@endphp

<div>
    <style>
        .fp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(8.5rem, 1fr)); gap: .75rem; }
        .fp-card { position: relative; border: 1px solid #e2e8f0; border-radius: .9rem; overflow: hidden; background: #f4f6fa; aspect-ratio: 1 / 1; }
        .fp-card img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .fp-principal { position: absolute; left: .4rem; top: .4rem; background: #0047AB; color: #fff; font-size: .66rem; font-weight: 800; padding: .15rem .55rem; border-radius: 999px; }
        .fp-del { position: absolute; right: .4rem; top: .4rem; width: 2rem; height: 2rem; border-radius: 999px; border: 0; background: rgba(255, 255, 255, .95); color: #b91c1c; box-shadow: 0 2px 8px rgba(0, 0, 0, .25); cursor: pointer; display: grid; place-items: center; }
        .fp-del:hover { background: #fee2e2; }
        .fp-del svg { width: 1rem; height: 1rem; }
        .fp-cont { font-size: .8rem; color: #64748b; margin-top: .5rem; }
        .fp-vacio { font-size: .85rem; color: #64748b; padding: .75rem 0; }
    </style>

    @if(empty($fotos))
        <p class="fp-vacio">Este producto todavía no tiene fotos. Subí la primera abajo.</p>
    @else
        <div class="fp-grid">
            @foreach($fotos as $i => $nro)
                <div class="fp-card" wire:key="foto-{{ $nro }}">
                    <img src="{{ route('item-image.show', ['item' => $record->item, 'nro' => $nro]) }}" alt="Foto {{ $i + 1 }}" loading="lazy">
                    @if($i === 0)<span class="fp-principal">Principal</span>@endif
                    <button type="button" class="fp-del" wire:click="eliminarFoto({{ $nro }})" wire:confirm="¿Eliminar esta foto? No se puede deshacer." title="Eliminar foto" aria-label="Eliminar foto {{ $i + 1 }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.35 9m-4.78 0L9.26 9m9.97-3.21c.34.05.68.11 1.02.17m-1.02-.17L18.16 19.67a2.25 2.25 0 0 1-2.24 2.08H8.08a2.25 2.25 0 0 1-2.24-2.08L4.77 5.79m14.46 0a48.1 48.1 0 0 0-3.48-.4m-12 .57c.34-.06.68-.12 1.02-.17m0 0a48.11 48.11 0 0 1 3.48-.4m7.5 0v-.92c0-1.18-.91-2.16-2.09-2.2a51.96 51.96 0 0 0-3.32 0c-1.18.04-2.09 1.02-2.09 2.2v.92m7.5 0a48.67 48.67 0 0 0-7.5 0"/></svg>
                    </button>
                </div>
            @endforeach
        </div>
        <p class="fp-cont">{{ count($fotos) }} de {{ $maximo }} fotos</p>
    @endif
</div>
