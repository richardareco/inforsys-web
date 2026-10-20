@php
    $acento = $this->getAcento();
@endphp

<x-filament-panels::page>
<style>
    .inf-hero {
        border-radius: 1.3rem; padding: 1.6rem 1.7rem; color: #fff; position: relative; overflow: hidden;
        background: linear-gradient(135deg, {{ $acento }}, color-mix(in srgb, {{ $acento }} 55%, #0f172a));
        box-shadow: 0 20px 38px -22px {{ $acento }};
    }
    .inf-hero::before, .inf-hero::after { content: ''; position: absolute; border-radius: 9999px; pointer-events: none; background: rgba(255,255,255,.1); }
    .inf-hero::before { width: 220px; height: 220px; right: -70px; top: -90px; }
    .inf-hero::after { width: 140px; height: 140px; right: 90px; bottom: -80px; background: rgba(255,255,255,.07); }
    .inf-hero-row { display: flex; align-items: center; gap: 1.1rem; position: relative; z-index: 1; }
    .inf-ico { width: 3.6rem; height: 3.6rem; border-radius: 1.1rem; background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.3); display: grid; place-items: center; flex-shrink: 0; }
    .inf-ico svg { width: 1.9rem; height: 1.9rem; }
    .inf-badge { display: inline-block; padding: .2rem .7rem; border-radius: 9999px; background: rgba(255,255,255,.2); font-size: .68rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; margin-bottom: .4rem; }
    .inf-title { font-size: 1.5rem; font-weight: 900; letter-spacing: -.02em; line-height: 1.15; }
    .inf-desc { margin-top: .35rem; font-size: .92rem; color: rgba(255,255,255,.88); max-width: 40rem; }

    .inf-card { margin-top: 1.25rem; border-radius: 1.2rem; padding: 1.3rem 1.5rem; background: #fff; border: 1px solid rgba(148,163,184,.25); box-shadow: 0 12px 28px -22px rgba(15,23,42,.4); }
    .dark .inf-card { background: #18181b; border-color: rgba(255,255,255,.08); }
    .inf-card h3 { font-size: .95rem; font-weight: 800; color: #0f172a; margin-bottom: .2rem; }
    .dark .inf-card h3 { color: #f1f5f9; }
    .inf-card p.sub { font-size: .8rem; color: #64748b; margin-bottom: 1rem; }
    .inf-list { display: grid; gap: .6rem; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
    .inf-item { display: flex; align-items: center; gap: .7rem; padding: .7rem .85rem; border-radius: .85rem; background: rgba(148,163,184,.1); font-size: .88rem; font-weight: 600; color: #1e293b; }
    .dark .inf-item { color: #e2e8f0; }
    .inf-dot { width: 1.5rem; height: 1.5rem; border-radius: .5rem; display: grid; place-items: center; background: {{ $acento }}; color: #fff; flex-shrink: 0; }
    .inf-dot svg { width: .85rem; height: .85rem; }
    .inf-soon { margin-top: 1rem; font-size: .78rem; color: #94a3b8; }
</style>

<div>
    <div class="inf-hero">
        <div class="inf-hero-row">
            <div class="inf-ico"><x-dynamic-component :component="$this->getIcono()" /></div>
            <div>
                <span class="inf-badge">Módulo en desarrollo</span>
                <div class="inf-title">{{ $this->getTitle() }}</div>
                <div class="inf-desc">{{ $this->getDescripcion() }}</div>
            </div>
        </div>
    </div>

    <div class="inf-card">
        <h3>Qué va a incluir este informe</h3>
        <p class="sub">Estos son los contenidos previstos. Los iremos habilitando de a uno.</p>
        <div class="inf-list">
            @foreach($this->getContenidos() as $c)
                <div class="inf-item">
                    <span class="inf-dot"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg></span>
                    {{ $c }}
                </div>
            @endforeach
        </div>
        <p class="inf-soon">Próximamente disponible.</p>
    </div>
</div>
</x-filament-panels::page>
