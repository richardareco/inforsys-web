@php
    $livewire ??= null;
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    <style>
        .lg-wrap {
            --lg-bg: #f1f5f9;
            --lg-card: #ffffff;
            --lg-text: #0f172a;
            --lg-muted: #64748b;
            --lg-border: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: stretch;
            justify-content: center;
            padding: 1.25rem;
            background:
                radial-gradient(1200px 500px at 85% -10%, rgba(14, 165, 233, .18), transparent 60%),
                radial-gradient(900px 500px at -10% 110%, rgba(79, 70, 229, .16), transparent 60%),
                var(--lg-bg);
        }
        .dark .lg-wrap {
            --lg-bg: #09090b;
            --lg-card: #18181b;
            --lg-text: #f8fafc;
            --lg-muted: #a1a1aa;
            --lg-border: #27272a;
        }
        .lg-shell {
            width: 100%;
            max-width: 1080px;
            display: grid;
            grid-template-columns: 1.05fr 1fr;
            border-radius: 1.5rem;
            overflow: hidden;
            background: var(--lg-card);
            box-shadow: 0 30px 80px -20px rgba(15, 23, 42, .35), 0 0 0 1px var(--lg-border);
            margin: auto;
        }

        /* ── Panel izquierdo ── */
        .lg-hero {
            position: relative;
            overflow: hidden;
            padding: 3rem 2.75rem;
            color: #fff;
            background: linear-gradient(150deg, #0a3fa8 0%, #0047ab 40%, #0284c7 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 2rem;
        }
        .lg-hero::before, .lg-hero::after {
            content: '';
            position: absolute;
            border-radius: 9999px;
            pointer-events: none;
        }
        .lg-hero::before {
            width: 360px; height: 360px;
            right: -140px; top: -120px;
            background: radial-gradient(circle, rgba(255, 255, 255, .22), transparent 70%);
        }
        .lg-hero::after {
            width: 420px; height: 420px;
            left: -170px; bottom: -190px;
            background: radial-gradient(circle, rgba(125, 211, 252, .28), transparent 70%);
        }
        .lg-dots {
            position: absolute; top: 1.5rem; left: 1.75rem;
            width: 92px; height: 72px; opacity: .45;
            background-image: radial-gradient(#fff 1.5px, transparent 1.5px);
            background-size: 14px 14px;
        }
        .lg-hero > * { position: relative; z-index: 1; }
        .lg-brand { display: flex; align-items: center; gap: .7rem; margin-top: 2rem; }
        .lg-brand-icon {
            width: 2.6rem; height: 2.6rem; border-radius: .8rem;
            display: grid; place-items: center;
            background: rgba(255, 255, 255, .16);
            border: 1px solid rgba(255, 255, 255, .28);
            backdrop-filter: blur(6px);
        }
        .lg-brand-icon svg { width: 1.4rem; height: 1.4rem; }
        .lg-brand-name { font-size: 1.35rem; font-weight: 800; letter-spacing: -.01em; }
        .lg-title { font-size: 2.1rem; line-height: 1.15; font-weight: 800; letter-spacing: -.02em; margin: 0; }
        .lg-sub { margin: .9rem 0 0; font-size: .98rem; line-height: 1.55; color: rgba(255, 255, 255, .82); max-width: 28rem; }
        .lg-features { list-style: none; margin: 1.75rem 0 0; padding: 0; display: grid; gap: .85rem; }
        .lg-features li { display: flex; align-items: center; gap: .8rem; font-size: .95rem; font-weight: 500; }
        .lg-feat-icon {
            flex-shrink: 0; width: 2rem; height: 2rem; border-radius: .6rem;
            display: grid; place-items: center;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .2);
        }
        .lg-feat-icon svg { width: 1.05rem; height: 1.05rem; }
        .lg-hero-foot { font-size: .8rem; color: rgba(255, 255, 255, .65); }

        /* ── Panel derecho ── */
        .lg-main {
            padding: 3rem 3rem;
            display: flex; align-items: center; justify-content: center;
            background: var(--lg-card);
            color: var(--lg-text);
        }
        .lg-form { width: 100%; max-width: 24rem; }
        .lg-form-brand { display: none; align-items: center; gap: .6rem; margin-bottom: 1.5rem; font-weight: 800; font-size: 1.2rem; color: #0047ab; }
        .dark .lg-form-brand { color: #7dd3fc; }
        .lg-heading { font-size: 1.75rem; font-weight: 800; letter-spacing: -.02em; margin: 0; color: var(--lg-text); }
        .lg-subheading { margin: .45rem 0 1.75rem; color: var(--lg-muted); font-size: .95rem; }
        .lg-form .fi-btn { padding-top: .7rem; padding-bottom: .7rem; font-weight: 700; border-radius: .7rem; }
        .lg-form .fi-input-wrp { border-radius: .7rem; }
        .lg-secure {
            margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--lg-border);
            display: flex; align-items: center; gap: .5rem; color: var(--lg-muted); font-size: .8rem;
        }
        .lg-secure svg { width: 1rem; height: 1rem; flex-shrink: 0; }

        @media (max-width: 900px) {
            .lg-wrap { padding: 0; }
            .lg-shell { grid-template-columns: 1fr; border-radius: 0; box-shadow: none; max-width: none; min-height: 100vh; }
            .lg-hero { display: none; }
            .lg-main { padding: 2rem 1.5rem; }
            .lg-form-brand { display: flex; }
        }
    </style>

    <div class="lg-wrap">
        <div class="lg-shell">
            <aside class="lg-hero">
                <div class="lg-dots"></div>

                <div>
                    <div class="lg-brand">
                        <div class="lg-brand-icon">
                            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                        </div>
                        <span class="lg-brand-name">{{ filament()->getBrandName() }}</span>
                    </div>

                    <h1 class="lg-title" style="margin-top:2.75rem;">Tu negocio,<br>siempre bajo control</h1>
                    <p class="lg-sub">Vendé más rápido, controlá tu stock y conocé tus números en tiempo real desde un solo lugar.</p>

                    <ul class="lg-features">
                        <li>
                            <span class="lg-feat-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75"/></svg></span>
                            Punto de venta rápido, con lector de código de barras
                        </li>
                        <li>
                            <span class="lg-feat-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg></span>
                            Control de productos y stock por depósito
                        </li>
                        <li>
                            <span class="lg-feat-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg></span>
                            Dashboard con ventas, rankings y metas
                        </li>
                        <li>
                            <span class="lg-feat-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg></span>
                            Historial de ventas, comprobantes y presupuestos
                        </li>
                        <li>
                            <span class="lg-feat-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg></span>
                            Gestión de clientes y equipo de vendedores
                        </li>
                    </ul>
                </div>

                <div class="lg-hero-foot">&copy; {{ date('Y') }} {{ filament()->getBrandName() }} · Sistema de gestión y punto de venta</div>
            </aside>

            <main class="lg-main">
                <div class="lg-form">
                    <div class="lg-form-brand">
                        <svg style="width:1.6rem;height:1.6rem" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                        {{ filament()->getBrandName() }}
                    </div>

                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $livewire?->getRenderHookScopes()) }}
</x-filament-panels::layout.base>
