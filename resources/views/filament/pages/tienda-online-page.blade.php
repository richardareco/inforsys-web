<x-filament-panels::page>
    @php
        $enlace = $this->enlace();
        $puedeConfigurar = \App\Filament\Pages\TiendaOnlinePage::puedeConfigurar();
    @endphp

    {{-- Enlace de la tienda: copiarlo para enviárselo a un cliente, o abrirla --}}
    <div
        x-data="{
            url: @js($enlace['url'] ?? ''),
            copiado: false,
            copiar() {
                const listo = () => { this.copiado = true; setTimeout(() => this.copiado = false, 2200); };
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(this.url).then(listo);
                } else {
                    const t = document.createElement('textarea');
                    t.value = this.url; t.style.position = 'fixed'; t.style.opacity = '0';
                    document.body.appendChild(t); t.select();
                    try { document.execCommand('copy'); listo(); } catch (e) {}
                    document.body.removeChild(t);
                }
            }
        }"
        style="border-radius:1.2rem;padding:1.2rem 1.3rem;background:linear-gradient(135deg,#f3f7fd,#eaf1fb);border:1px solid #dde8f7;"
    >
        @if($enlace)
            <div style="display:flex;align-items:center;justify-content:space-between;gap:.6rem;flex-wrap:wrap;margin-bottom:.8rem;">
                <div>
                    <div style="font-size:.7rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#5b6b86;">Enlace de la tienda</div>
                    <div style="font-size:1.05rem;font-weight:800;color:#0b1b3a;">{{ $enlace['nombre'] }}</div>
                </div>
                <span style="font-size:.72rem;font-weight:800;padding:.25rem .7rem;border-radius:999px;{{ $enlace['activo'] ? 'background:#dcfce7;color:#166534;' : 'background:#fef3c7;color:#92400e;' }}">
                    {{ $enlace['activo'] ? 'Activa' : 'Apagada' }}
                </span>
            </div>

            <div style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:stretch;">
                <input type="text" readonly x-model="url" @focus="$event.target.select()" aria-label="Enlace de la tienda"
                       style="flex:1 1 16rem;min-width:0;padding:.65rem .9rem;border-radius:.8rem;border:1px solid #c7d6ef;background:#fff;font-size:.9rem;color:#0b1b3a;">

                <button type="button" @click="copiar()" @if(!$enlace['activo']) disabled @endif
                        style="display:inline-flex;align-items:center;gap:.45rem;padding:.65rem 1.1rem;border-radius:.8rem;border:0;background:#0047AB;color:#fff;font-weight:700;font-size:.9rem;cursor:pointer;{{ $enlace['activo'] ? '' : 'opacity:.45;cursor:not-allowed;' }}">
                    <svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"/></svg>
                    <span x-text="copiado ? '¡Link copiado!' : 'Copiar link'">Copiar link</span>
                </button>

                <a href="{{ $enlace['url'] }}" target="_blank" rel="noopener" @if(!$enlace['activo']) aria-disabled="true" onclick="return false;" @endif
                   style="display:inline-flex;align-items:center;gap:.45rem;padding:.65rem 1.1rem;border-radius:.8rem;border:1px solid #c7d6ef;background:#fff;color:#0047AB;font-weight:700;font-size:.9rem;text-decoration:none;{{ $enlace['activo'] ? '' : 'opacity:.45;cursor:not-allowed;' }}">
                    <svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                    Abrir tienda
                </a>
            </div>

            @unless($enlace['activo'])
                <p style="margin:.8rem 0 0;font-size:.82rem;color:#92400e;">
                    La tienda está apagada y por eso el enlace todavía no funciona.
                    {{ $puedeConfigurar ? 'Activala más abajo y guardá los cambios.' : 'Pedile a tu administrador que la active.' }}
                </p>
            @endunless
        @else
            <div style="text-align:center;padding:.6rem;">
                <div style="font-weight:800;">Todavía no hay una tienda online</div>
                <div style="font-size:.85rem;opacity:.65;margin-top:.2rem;">Pedile a tu administrador que la configure para poder compartir el enlace.</div>
            </div>
        @endif
    </div>

    {{-- Configuración: solo administradores --}}
    @if($puedeConfigurar)
        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <div class="flex justify-end gap-3">
                <x-filament::button type="submit" color="primary">
                    Guardar tienda
                </x-filament::button>
            </div>
        </form>
    @endif
</x-filament-panels::page>
