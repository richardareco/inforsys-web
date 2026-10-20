<div x-data="{
        fs: !!document.fullscreenElement,
        toggle() {
            if (document.fullscreenElement) {
                document.exitFullscreen?.()?.catch(() => {});
            } else {
                document.documentElement.requestFullscreen?.()?.catch(() => {});
            }
        }
    }"
    @fullscreenchange.document="fs = !!document.fullscreenElement">
    <button type="button" @click="toggle()" class="app-fullscreen-btn"
        :title="fs ? 'Salir de pantalla completa' : 'Pantalla completa'"
        :aria-label="fs ? 'Salir de pantalla completa' : 'Pantalla completa'">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <g x-show="!fs">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/>
            </g>
            <g x-show="fs" style="display:none;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25"/>
            </g>
        </svg>
    </button>
</div>

<style>
.app-fullscreen-btn {
    flex-shrink: 0;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 0.75rem;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    border: 1px solid rgba(255, 255, 255, 0.22);
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
    box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.25);
    padding: 0;
}
.app-fullscreen-btn:hover {
    transform: translateY(-1px) scale(1.06);
    filter: brightness(1.1);
    box-shadow: 0 8px 22px rgba(124, 58, 237, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.3);
}
.app-fullscreen-btn:active {
    transform: scale(0.94);
    box-shadow: 0 2px 8px rgba(79, 70, 229, 0.4);
}
.app-fullscreen-btn:focus-visible {
    outline: 2px solid #c4b5fd;
    outline-offset: 2px;
}
.app-fullscreen-btn svg {
    width: 1.15rem;
    height: 1.15rem;
    stroke-width: 2.2;
}
</style>
