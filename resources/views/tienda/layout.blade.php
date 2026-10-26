@php
    $nombre = $t->nombreTienda();
    $base   = '/tienda/' . $t->slug;
    $titulo = trim(($tituloPagina ?? '') !== '' ? $tituloPagina . ' · ' . $nombre : ($t->seo_titulo ?: $nombre . ($t->eslogan ? ' · ' . $t->eslogan : '')));
    $descripcionSeo = $descripcionPagina ?? ($t->seo_descripcion ?: ($t->eslogan ?: 'Catálogo online de ' . $nombre));
    $wa = $t->enlaceWhatsapp('Hola, quiero consultar por sus productos.');
    $ig = $t->instagram ? 'https://instagram.com/' . ltrim(preg_replace('#^https?://(www\.)?instagram\.com/#i', '', $t->instagram), '@/') : null;
    $fb = $t->facebook ? (str_starts_with($t->facebook, 'http') ? $t->facebook : 'https://facebook.com/' . ltrim($t->facebook, '/')) : null;
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }}</title>
    <meta name="description" content="{{ $descripcionSeo }}">
    <meta name="theme-color" content="{{ $t->color }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $titulo }}">
    <meta property="og:description" content="{{ $descripcionSeo }}">
    @isset($imagenSocial)<meta property="og:image" content="{{ url($imagenSocial) }}">@endisset
    @if($t->logo)<link rel="icon" href="{{ $t->urlArchivo($t->logo) }}">@endif
    <style>
        :root { --c: {{ $t->color }}; --ct: {{ $t->colorTexto() }}; --tx: #111827; --mu: #6b7280; --bd: #e5e7eb; --bg: #fafafa; }
        * { box-sizing: border-box; margin: 0; }
        html { scroll-behavior: smooth; }
        body { font-family: Inter, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color: var(--tx); background: #fff; line-height: 1.5; -webkit-font-smoothing: antialiased; }
        a { color: inherit; text-decoration: none; }
        img { max-width: 100%; display: block; }
        .wrap { width: min(1240px, 100% - 2rem); margin-inline: auto; }

        /* Encabezado */
        .hd { position: sticky; top: 0; z-index: 30; background: rgba(255, 255, 255, .92); backdrop-filter: blur(12px); border-bottom: 1px solid var(--bd); }
        .hd-in { display: flex; align-items: center; gap: 1rem; height: 4.2rem; }
        .marca { display: flex; align-items: center; gap: .6rem; font-weight: 800; font-size: 1.15rem; letter-spacing: -.02em; flex-shrink: 0; }
        .marca img { height: 2.4rem; width: auto; max-width: 9rem; object-fit: contain; }
        .busca { flex: 1; max-width: 32rem; margin-inline: auto; position: relative; }
        .busca input { width: 100%; height: 2.7rem; border: 1px solid var(--bd); border-radius: 999px; padding: 0 1rem 0 2.6rem; font-size: .95rem; background: #f3f4f6; outline: none; transition: all .15s; }
        .busca input:focus { background: #fff; border-color: var(--c); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c) 18%, transparent); }
        .busca svg { position: absolute; left: .9rem; top: 50%; translate: 0 -50%; width: 1.15rem; height: 1.15rem; color: var(--mu); }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; height: 2.7rem; padding: 0 1.2rem; border-radius: 999px; font-weight: 700; font-size: .92rem; border: 0; cursor: pointer; background: var(--c); color: var(--ct); transition: transform .15s, filter .15s, box-shadow .15s; white-space: nowrap; }
        .btn:hover { transform: translateY(-1px); filter: brightness(1.06); box-shadow: 0 10px 20px -12px var(--c); }
        .btn.wa { background: #16a34a; color: #fff; }
        .btn.sec { background: #fff; color: var(--tx); border: 1px solid var(--bd); }
        .btn svg { width: 1.15rem; height: 1.15rem; }

        /* Portada */
        .hero { position: relative; margin-top: 1.2rem; border-radius: 1.6rem; overflow: hidden; min-height: 17rem; display: flex; align-items: flex-end; color: #fff;
            background: linear-gradient(135deg, var(--c), color-mix(in srgb, var(--c) 55%, #000)); }
        .hero img.fondo { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .hero::after { content: ""; position: absolute; inset: 0; background: linear-gradient(180deg, transparent 15%, rgba(0, 0, 0, .62)); }
        .hero-in { position: relative; z-index: 1; padding: 2.2rem; max-width: 42rem; }
        .hero h1 { font-size: clamp(1.8rem, 4.5vw, 3rem); line-height: 1.08; letter-spacing: -.03em; font-weight: 800; }
        .hero p { margin-top: .6rem; opacity: .92; font-size: 1.05rem; }
        .hero .btn { margin-top: 1.1rem; background: #fff; color: #111827; }

        /* Categorías */
        .cats { display: flex; gap: .5rem; overflow-x: auto; padding: 1.4rem 0 .4rem; scrollbar-width: none; }
        .cats::-webkit-scrollbar { display: none; }
        .chip { flex-shrink: 0; padding: .5rem 1.05rem; border-radius: 999px; border: 1px solid var(--bd); font-size: .88rem; font-weight: 600; background: #fff; color: #374151; transition: all .15s; }
        .chip:hover { border-color: var(--c); color: var(--c); }
        .chip.on { background: var(--c); border-color: var(--c); color: var(--ct); }
        .chip small { opacity: .65; margin-left: .25rem; font-weight: 500; }

        .barra { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 0 1.1rem; }
        .barra h2 { font-size: 1.35rem; letter-spacing: -.02em; }
        .barra span { color: var(--mu); font-size: .9rem; }
        .barra select { height: 2.5rem; border: 1px solid var(--bd); border-radius: .75rem; padding: 0 .8rem; background: #fff; font-size: .9rem; color: #374151; }

        /* Productos */
        .grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.6rem 1.2rem; padding-bottom: 2rem; }
        .card { display: block; }
        .card .foto { position: relative; aspect-ratio: 1; border-radius: 1.1rem; background: #f6f6f7; overflow: hidden; border: 1px solid #f0f0f1; }
        .card .foto img { width: 100%; height: 100%; object-fit: contain; padding: .6rem; transition: transform .35s ease; mix-blend-mode: multiply; }
        .card:hover .foto img { transform: scale(1.05); }
        .sin-foto { width: 100%; height: 100%; display: grid; place-items: center; color: #c4c7cd; }
        .sin-foto svg { width: 3rem; height: 3rem; }
        .etq { position: absolute; left: .7rem; top: .7rem; background: #111827; color: #fff; font-size: .7rem; font-weight: 700; padding: .22rem .65rem; border-radius: 999px; letter-spacing: .02em; }
        .etq.agot { background: #fff; color: #6b7280; border: 1px solid var(--bd); }
        .etq.fotos { left: auto; right: .7rem; background: rgba(17, 24, 39, .75); }
        .card .info { padding: .8rem .15rem 0; }
        .card .cat { font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; color: var(--mu); font-weight: 600; }
        .card h3 { font-size: .98rem; font-weight: 600; line-height: 1.3; margin-top: .15rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.55em; }
        .precio { margin-top: .4rem; font-weight: 800; font-size: 1.08rem; }
        .precio.consulta { font-weight: 600; color: var(--mu); font-size: .92rem; }
        .mayor { font-size: .76rem; color: #047857; font-weight: 600; margin-top: .1rem; }
        .vacio { grid-column: 1 / -1; text-align: center; padding: 4rem 1rem; color: var(--mu); }
        .vacio b { display: block; color: var(--tx); font-size: 1.2rem; margin-bottom: .3rem; }

        .pag { display: flex; justify-content: center; align-items: center; gap: .4rem; padding: 0 0 3rem; flex-wrap: wrap; }
        .pag a, .pag span { min-width: 2.5rem; height: 2.5rem; padding: 0 .85rem; display: inline-flex; align-items: center; justify-content: center; border-radius: .75rem; border: 1px solid var(--bd); font-weight: 600; font-size: .9rem; }
        .pag .on { background: var(--c); border-color: var(--c); color: var(--ct); }
        .pag .off { opacity: .4; }

        /* Pie */
        .ft { background: #111827; color: #d1d5db; margin-top: 2rem; padding: 3rem 0 2rem; }
        .ft-g { display: grid; grid-template-columns: 1.4fr 1fr 1fr; gap: 2rem; }
        .ft h4 { color: #fff; font-size: .95rem; margin-bottom: .7rem; }
        .ft p, .ft li { font-size: .9rem; color: #9ca3af; }
        .ft ul { list-style: none; padding: 0; display: grid; gap: .4rem; }
        .ft a:hover { color: #fff; }
        .ft-c { margin-top: 2rem; padding-top: 1.2rem; border-top: 1px solid #1f2937; font-size: .8rem; color: #6b7280; }
        .flota { position: fixed; right: 1.1rem; bottom: 1.1rem; z-index: 40; width: 3.5rem; height: 3.5rem; border-radius: 999px; background: #16a34a; color: #fff; display: grid; place-items: center; box-shadow: 0 12px 24px -8px rgba(22, 163, 74, .7); transition: transform .15s; }
        .flota:hover { transform: scale(1.08); }
        .flota svg { width: 1.8rem; height: 1.8rem; }

        @media (max-width: 1024px) { .grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 720px) {
            .hd-in { height: auto; padding: .7rem 0; flex-wrap: wrap; gap: .6rem; }
            .busca { order: 3; flex-basis: 100%; max-width: none; }
            .hd .btn.wa span { display: none; }
            .hd .btn.wa { padding: 0; width: 2.7rem; }
            .hero { min-height: 13rem; border-radius: 1.2rem; }
            .hero-in { padding: 1.3rem; }
            .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.1rem .75rem; }
            .card h3 { font-size: .9rem; }
            .ft-g { grid-template-columns: 1fr; gap: 1.4rem; }
            .barra { flex-wrap: wrap; }
        }
        @yield('estilos')
    </style>
</head>
<body>
    <header class="hd">
        <div class="wrap hd-in">
            <a class="marca" href="{{ $base }}">
                @if($t->logo)<img src="{{ $t->urlArchivo($t->logo) }}" alt="{{ $nombre }}">@else<span>{{ $nombre }}</span>@endif
            </a>
            <form class="busca" method="get" action="{{ $base }}" role="search">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                <input type="search" name="q" value="{{ $q ?? '' }}" placeholder="Buscar productos..." aria-label="Buscar productos">
            </form>
            @if($wa)
                <a class="btn wa" href="{{ $wa }}" target="_blank" rel="noopener">
                    <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2a9.9 9.9 0 0 0-8.5 14.9L2 22l5.25-1.38A9.9 9.9 0 1 0 12.04 2Zm5.8 14.1c-.25.7-1.45 1.34-2 1.4-.5.07-1.13.1-1.82-.12a16.5 16.5 0 0 1-1.65-.6c-2.9-1.25-4.78-4.15-4.92-4.35-.14-.2-1.17-1.56-1.17-2.97s.74-2.1 1-2.4c.27-.28.58-.35.78-.35h.56c.18 0 .42-.07.65.5.25.6.84 2.05.9 2.2.08.14.12.32.02.5-.1.2-.15.32-.3.5l-.45.5c-.15.15-.3.3-.13.6.18.3.77 1.27 1.65 2.05 1.13 1 2.08 1.3 2.38 1.45.3.14.47.12.65-.07.18-.2.75-.87.95-1.17.2-.3.4-.25.67-.15.28.1 1.75.83 2.05.97.3.15.5.22.57.35.07.12.07.7-.18 1.4Z"/></svg>
                    <span>WhatsApp</span>
                </a>
            @endif
        </div>
    </header>

    @yield('contenido')

    <footer class="ft">
        <div class="wrap">
            <div class="ft-g">
                <div>
                    <h4>{{ $nombre }}</h4>
                    @if($t->eslogan)<p>{{ $t->eslogan }}</p>@endif
                    @if($t->texto_inicio)<p style="margin-top:.5rem;">{{ \Illuminate\Support\Str::limit($t->texto_inicio, 220) }}</p>@endif
                </div>
                <div>
                    <h4>Contacto</h4>
                    <ul>
                        @if($t->direccion)<li>📍 {{ $t->direccion }}</li>@endif
                        @if($t->whatsapp)<li>💬 <a href="{{ $t->enlaceWhatsapp() }}" target="_blank" rel="noopener">{{ $t->whatsapp }}</a></li>@endif
                        @if($ig)<li>📷 <a href="{{ $ig }}" target="_blank" rel="noopener">Instagram</a></li>@endif
                        @if($fb)<li>👍 <a href="{{ $fb }}" target="_blank" rel="noopener">Facebook</a></li>@endif
                    </ul>
                </div>
                <div>
                    @if($t->horarios)<h4>Horarios</h4><p>{!! nl2br(e($t->horarios)) !!}</p>@endif
                </div>
            </div>
            <div class="ft-c">© {{ date('Y') }} {{ $nombre }}. Los precios y la disponibilidad pueden cambiar sin previo aviso.</div>
        </div>
    </footer>

    @if($wa)
        <a class="flota" href="{{ $wa }}" target="_blank" rel="noopener" aria-label="Escribinos por WhatsApp">
            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2a9.9 9.9 0 0 0-8.5 14.9L2 22l5.25-1.38A9.9 9.9 0 1 0 12.04 2Zm5.8 14.1c-.25.7-1.45 1.34-2 1.4-.5.07-1.13.1-1.82-.12a16.5 16.5 0 0 1-1.65-.6c-2.9-1.25-4.78-4.15-4.92-4.35-.14-.2-1.17-1.56-1.17-2.97s.74-2.1 1-2.4c.27-.28.58-.35.78-.35h.56c.18 0 .42-.07.65.5.25.6.84 2.05.9 2.2.08.14.12.32.02.5-.1.2-.15.32-.3.5l-.45.5c-.15.15-.3.3-.13.6.18.3.77 1.27 1.65 2.05 1.13 1 2.08 1.3 2.38 1.45.3.14.47.12.65-.07.18-.2.75-.87.95-1.17.2-.3.4-.25.67-.15.28.1 1.75.83 2.05.97.3.15.5.22.57.35.07.12.07.7-.18 1.4Z"/></svg>
        </a>
    @endif
    @yield('scripts')
</body>
</html>
