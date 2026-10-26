@php
    $nombre = $t->nombreTienda();
    $base   = '/tienda/' . $t->slug;
    $titulo = trim(($tituloPagina ?? '') !== '' ? $tituloPagina . ' · ' . $nombre : ($t->seo_titulo ?: $nombre . ($t->eslogan ? ' · ' . $t->eslogan : '')));
    $descripcionSeo = $descripcionPagina ?? ($t->seo_descripcion ?: ($t->eslogan ?: 'Catálogo online de ' . $nombre));
    $wa = $t->enlaceWhatsapp('Hola, quiero consultar por sus productos.');
    $ig = $t->instagram ? 'https://instagram.com/' . ltrim(preg_replace('#^https?://(www\.)?instagram\.com/#i', '', $t->instagram), '@/') : null;
    $carritoOn = $t->carritoDisponible();
    $cfgCarrito = [
        'slug' => $t->slug, 'base' => $base, 'api' => $base . '/carrito', 'nombre' => $nombre, 'whatsapp' => $t->whatsappDigitos(),
        'precios' => (bool) $t->mostrar_precios, 'mayoristaMin' => ($t->mostrar_mayorista && (int) $t->mayorista_min_unidades >= 2) ? (int) $t->mayorista_min_unidades : 0,
        'maxProductos' => 30,
    ];
    $anuncios = $t->lineasAnuncio();
    if ($anuncios) {
        $tramo = collect($anuncios)->map(fn ($a) => e($a))->implode('<i class="sep">✦</i>');
        $ancho = max(300, mb_strlen(implode(' ✦ ', $anuncios)) * 8.5 + 80);   // ancho aproximado de un tramo en px
        $repite = (int) max(1, ceil(2400 / $ancho));
        $mitad = str_repeat('<span class="tramo">' . $tramo . '<i class="sep">✦</i></span>', $repite);
        $segundos = (int) max(22, round($ancho * $repite / 55));                  // ~55 px por segundo
    }
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
        :root { --top-fijo: {{ $anuncios ? '8.6rem' : '5.5rem' }}; --c: {{ $t->color }}; --ct: {{ $t->colorTexto() }}; --tx: #111827; --mu: #6b7280; --bd: #e5e7eb; --bg: #fafafa; }
        * { box-sizing: border-box; margin: 0; }
        html { scroll-behavior: smooth; }
        body { font-family: Inter, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color: var(--tx); background: #fff; line-height: 1.5; -webkit-font-smoothing: antialiased; }
        a { color: inherit; text-decoration: none; }
        img { max-width: 100%; display: block; }
        .wrap { width: min(1240px, 100% - 2rem); margin-inline: auto; }

        /* Barra de anuncios (marquee) */
        .anuncio { background: {{ $t->anuncioFondo() }}; color: {{ $t->anuncioTexto() }}; overflow: hidden; font-size: .85rem; font-weight: 600; letter-spacing: .01em; }
        .anuncio-pista { display: flex; width: max-content; padding: .55rem 0; animation: anuncio-mover {{ $segundos ?? 30 }}s linear infinite; }
        .anuncio:hover .anuncio-pista, .anuncio:focus-within .anuncio-pista { animation-play-state: paused; }
        .anuncio .mitad { display: flex; flex-shrink: 0; white-space: nowrap; }
        .anuncio .tramo { display: inline-flex; align-items: center; }
        .anuncio .sep { font-style: normal; opacity: .55; margin: 0 1.6rem; font-size: .7em; }
        @keyframes anuncio-mover { from { transform: translateX(0); } to { transform: translateX(-50%); } }
        @media (max-width: 720px) { .anuncio { font-size: .78rem; } .anuncio-pista { animation-duration: {{ (int) (($segundos ?? 30) * 1.25) }}s; } }
        @media (prefers-reduced-motion: reduce) { .anuncio-pista { animation: none; width: auto; overflow-x: auto; } .anuncio .mitad + .mitad { display: none; } }

        /* Encabezado */
        .fijo { position: sticky; top: 0; z-index: 30; }   /* barra de anuncios + encabezado: quedan fijos al hacer scroll */
        .hd { position: relative; background: rgba(255, 255, 255, .92); backdrop-filter: blur(12px); border-bottom: 1px solid var(--bd); }
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
            .fijo.compacto .busca { display: none; }   /* al bajar, el buscador se oculta para dejar más lugar al contenido */
            .fijo.compacto .hd-in { padding: .5rem 0; }
            .hd .btn.wa span { display: none; }
            .hd .btn.wa { padding: 0; width: 2.7rem; }
            .hero { min-height: 13rem; border-radius: 1.2rem; }
            .hero-in { padding: 1.3rem; }
            .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.1rem .75rem; }
            .card h3 { font-size: .9rem; }
            .ft-g { grid-template-columns: 1fr; gap: 1.4rem; }
            .barra { flex-wrap: wrap; }
        }

        /* Carrito de compras */
        .cart-btn { position: relative; width: 2.7rem; height: 2.7rem; border-radius: 999px; border: 1px solid var(--bd); background: #fff; display: grid; place-items: center; cursor: pointer; flex-shrink: 0; transition: transform .15s; }
        .cart-btn:hover { transform: translateY(-1px); border-color: var(--c); }
        .cart-btn svg { width: 1.35rem; height: 1.35rem; }
        .cart-btn .num { position: absolute; top: -.35rem; right: -.35rem; min-width: 1.3rem; height: 1.3rem; padding: 0 .3rem; border-radius: 999px; background: var(--c); color: var(--ct); font-size: .72rem; font-weight: 800; display: grid; place-items: center; box-shadow: 0 0 0 2px #fff; }
        .cart-btn .num[hidden] { display: none; }
        .cart-btn.bump { animation: cart-bump .35s ease; }
        @keyframes cart-bump { 40% { transform: scale(1.18); } }
        .fotowrap { position: relative; }
        a.foto, a.info { display: block; }
        .add { position: absolute; right: .65rem; bottom: .65rem; width: 2.6rem; height: 2.6rem; border-radius: 999px; border: 0; background: var(--c); color: var(--ct); font-size: 1.6rem; line-height: 1; cursor: pointer; box-shadow: 0 10px 18px -8px rgba(0, 0, 0, .55); transition: transform .15s; display: grid; place-items: center; padding: 0 0 .15rem; }
        .add:hover { transform: scale(1.1); } .add:active { transform: scale(.92); }
        .overlay { position: fixed; inset: 0; background: rgba(17, 24, 39, .5); opacity: 0; pointer-events: none; transition: opacity .2s; z-index: 60; }
        .overlay.on { opacity: 1; pointer-events: auto; }
        .drawer { position: fixed; top: 0; right: 0; bottom: 0; width: min(28rem, 100%); background: #fff; z-index: 61; transform: translateX(100%); transition: transform .25s ease; display: flex; flex-direction: column; box-shadow: -24px 0 40px -24px rgba(0, 0, 0, .35); }
        .drawer.on { transform: none; }
        .drawer-h { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.2rem; border-bottom: 1px solid var(--bd); }
        .drawer-h h2 { font-size: 1.2rem; letter-spacing: -.02em; }
        .cerrar { border: 0; background: #f3f4f6; width: 2.3rem; height: 2.3rem; border-radius: 999px; cursor: pointer; font-size: 1rem; }
        .drawer-b { flex: 1; overflow-y: auto; padding: .2rem 1.2rem; }
        .lin { display: grid; grid-template-columns: 4rem 1fr; gap: .85rem; padding: 1rem 0; border-bottom: 1px solid #f1f1f2; }
        .lin img, .lin .ph { width: 4rem; height: 4rem; object-fit: contain; background: #f6f6f7; border-radius: .8rem; mix-blend-mode: multiply; display: block; }
        .lin .ph { display: grid; place-items: center; color: #c4c7cd; font-size: 1.4rem; mix-blend-mode: normal; }
        .lin .cod { font-size: .72rem; color: var(--mu); font-weight: 700; letter-spacing: .02em; }
        .lin .nom { font-size: .92rem; font-weight: 600; line-height: 1.25; margin-top: .1rem; overflow-wrap: anywhere; }
        .lin .det { display: flex; align-items: center; justify-content: space-between; gap: .6rem; margin-top: .55rem; }
        .lin .precios { text-align: right; line-height: 1.25; }
        .lin .unit { font-size: .76rem; color: var(--mu); }
        .lin .sub { font-weight: 800; font-size: .98rem; }
        .lin .quitar { border: 0; background: none; color: #ef4444; font-size: .76rem; cursor: pointer; padding: 0; margin-top: .35rem; }
        .lin .aviso { font-size: .74rem; color: #b45309; margin-top: .3rem; }
        .lin.nodisp .nom, .lin.nodisp img { opacity: .5; }
        .paso { display: inline-flex; align-items: center; border: 1px solid var(--bd); border-radius: 999px; background: #fff; }
        .paso button { width: 2rem; height: 2rem; border: 0; background: transparent; font-size: 1.15rem; cursor: pointer; border-radius: 999px; }
        .paso button:hover { background: #f3f4f6; }
        .paso span { min-width: 1.8rem; text-align: center; font-weight: 800; font-size: .92rem; }
        .drawer-f { padding: 1rem 1.2rem 1.2rem; border-top: 1px solid var(--bd); background: #fafafa; }
        .cart-res { font-size: .82rem; color: var(--mu); display: flex; justify-content: space-between; margin-bottom: .2rem; }
        .cart-tot { display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: 800; margin-bottom: .4rem; }
        .cart-nota { font-size: .75rem; color: var(--mu); margin-bottom: .85rem; line-height: 1.35; }
        .drawer-f .btn { width: 100%; height: 3.2rem; font-size: 1rem; }
        .cart-sec { display: flex; gap: .6rem; margin-top: .7rem; }
        .cart-sec button { flex: 1; background: none; border: 1px solid var(--bd); border-radius: 999px; padding: .55rem; font-size: .82rem; color: var(--mu); cursor: pointer; font-weight: 600; }
        .cart-ok { margin-top: .7rem; background: #ecfdf5; color: #047857; border-radius: .8rem; padding: .6rem .8rem; font-size: .8rem; line-height: 1.35; }
        .cart-vacio { text-align: center; color: var(--mu); padding: 3.5rem 1rem; }
        .cart-vacio b { display: block; color: var(--tx); font-size: 1.05rem; margin-bottom: .3rem; }
        .toast { position: fixed; left: 50%; bottom: 1.4rem; transform: translate(-50%, 2rem); background: #111827; color: #fff; padding: .7rem 1.1rem; border-radius: 999px; font-size: .9rem; font-weight: 600; opacity: 0; pointer-events: none; transition: all .25s; z-index: 70; display: flex; gap: .9rem; align-items: center; max-width: calc(100% - 2rem); }
        .toast.on { opacity: 1; transform: translate(-50%, 0); pointer-events: auto; }
        .toast span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .toast button { background: none; border: 0; color: #86efac; font-weight: 800; cursor: pointer; white-space: nowrap; }
        .cart-bar { display: none; }
        @media (max-width: 720px) {
            .cart-bar.on { display: flex; position: fixed; left: .75rem; right: .75rem; bottom: calc(.75rem + env(safe-area-inset-bottom)); z-index: 60; align-items: center; justify-content: space-between; gap: .75rem; padding: .85rem 1.1rem; border: 0; border-radius: 999px; background: var(--c); color: var(--ct); font: inherit; font-weight: 800; font-size: 1rem; box-shadow: 0 6px 20px rgba(0,0,0,.28); cursor: pointer; }
            .cart-bar .cb-n { display: inline-flex; align-items: center; gap: .5rem; }
            .cart-bar .cb-n i { font-style: normal; min-width: 1.5rem; height: 1.5rem; padding: 0 .35rem; border-radius: 999px; background: var(--ct); color: var(--c); font-size: .8rem; display: grid; place-items: center; }
            body.con-barra { padding-bottom: 5rem; } body.con-barra .flota { bottom: calc(5rem + env(safe-area-inset-bottom)); }
            body.con-barra .toast { bottom: 5.2rem; }
        }
        @media (max-width: 720px) { .add { width: 2.4rem; height: 2.4rem; font-size: 1.45rem; right: .5rem; bottom: .5rem; } }
        @yield('estilos')
    </style>
</head>
<body>
    <div class="fijo">
    @if($anuncios)
        <div class="anuncio" role="region" aria-label="Anuncios">
            <div class="anuncio-pista">
                <div class="mitad">{!! $mitad !!}</div>
                <div class="mitad" aria-hidden="true">{!! $mitad !!}</div>
            </div>
        </div>
    @endif
    <header class="hd">
        <div class="wrap hd-in">
            <a class="marca" href="{{ $base }}">
                @if($t->logo)<img src="{{ $t->urlArchivo($t->logo) }}" alt="{{ $nombre }}">@else<span>{{ $nombre }}</span>@endif
            </a>
            <form class="busca" method="get" action="{{ $base }}" role="search">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                <input type="search" name="q" value="{{ $q ?? '' }}" placeholder="Buscar productos..." aria-label="Buscar productos">
            </form>
            @if($carritoOn)
                <button type="button" class="cart-btn" data-cart-open aria-label="Abrir el carrito">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.36-1.99 1.26 12c.07.66-.45 1.24-1.12 1.24H4.25c-.67 0-1.19-.58-1.12-1.24l1.26-12a1.13 1.13 0 0 1 1.12-1.01h13.1c.58 0 1.07.44 1.12 1.01Z"/></svg>
                    <span class="num" id="cart-num" hidden>0</span>
                </button>
            @endif
            @if($wa)
                <a class="btn wa" href="{{ $wa }}" target="_blank" rel="noopener">
                    <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2a9.9 9.9 0 0 0-8.5 14.9L2 22l5.25-1.38A9.9 9.9 0 1 0 12.04 2Zm5.8 14.1c-.25.7-1.45 1.34-2 1.4-.5.07-1.13.1-1.82-.12a16.5 16.5 0 0 1-1.65-.6c-2.9-1.25-4.78-4.15-4.92-4.35-.14-.2-1.17-1.56-1.17-2.97s.74-2.1 1-2.4c.27-.28.58-.35.78-.35h.56c.18 0 .42-.07.65.5.25.6.84 2.05.9 2.2.08.14.12.32.02.5-.1.2-.15.32-.3.5l-.45.5c-.15.15-.3.3-.13.6.18.3.77 1.27 1.65 2.05 1.13 1 2.08 1.3 2.38 1.45.3.14.47.12.65-.07.18-.2.75-.87.95-1.17.2-.3.4-.25.67-.15.28.1 1.75.83 2.05.97.3.15.5.22.57.35.07.12.07.7-.18 1.4Z"/></svg>
                    <span>WhatsApp</span>
                </a>
            @endif
        </div>
    </header>
    </div>

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
    <script>
    (function () {
        // En el celular, el buscador se esconde al bajar y vuelve al subir (con margen para que no parpadee)
        var f = document.querySelector('.fijo'); if (!f) return;
        var compacto = false;
        window.addEventListener('scroll', function () {
            var y = window.scrollY;
            if (!compacto && y > 160) { compacto = true; f.classList.add('compacto'); }
            else if (compacto && y < 60) { compacto = false; f.classList.remove('compacto'); }
        }, { passive: true });
    })();
    </script>
    @if($carritoOn)
        <div class="overlay" id="cart-overlay"></div>
        <aside class="drawer" id="cart-drawer" role="dialog" aria-label="Carrito de compras" aria-hidden="true">
            <div class="drawer-h"><h2>Tu carrito</h2><button type="button" class="cerrar" id="cart-cerrar" aria-label="Cerrar">✕</button></div>
            <div class="drawer-b" id="cart-lista"></div>
            <div class="drawer-f" id="cart-pie"></div>
        </aside>
        <button type="button" class="cart-bar" id="cart-bar" data-cart-open aria-label="Ver carrito"><span class="cb-n"><i id="cb-n">0</i> Ver carrito</span><span id="cb-t"></span></button>
        <div class="toast" id="toast" role="status"><span id="toast-t"></span><button type="button" id="toast-ver">Ver carrito</button></div>
    <script>
    (function () {
        var CFG = @json($cfgCarrito);
        var KEY = 'tienda:' + CFG.slug + ':carrito';
        var $ = function (s, r) { return (r || document).querySelector(s); };
        var cart = {};
        try { cart = JSON.parse(localStorage.getItem(KEY) || '{}') || {}; } catch (e) { cart = {}; }
        var enviado = false;

        var gs = function (n) { return 'Gs. ' + String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); };
        var limpio = function (t) { return String(t || '').replace(/[\r\n\t]+/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 80); };
        var lista = function () { return Object.keys(cart).map(function (k) { return cart[k]; }); };
        var unidad = function (it) { return (CFG.mayoristaMin > 0 && it.m > 0 && it.m < it.p && it.q >= CFG.mayoristaMin) ? it.m : it.p; };
        var guardar = function () { try { localStorage.setItem(KEY, JSON.stringify(cart)); } catch (e) {} dibujar(); };
        var activos = function () { return lista().filter(function (i) { return i.d !== 0; }); };
        var totalUnidades = function () { return lista().reduce(function (s, i) { return s + i.q; }, 0); };
        var total = function () { return activos().reduce(function (s, i) { return s + unidad(i) * i.q; }, 0); };

        function agregar(d, qty) {
            qty = Math.max(1, Math.min(99, parseInt(qty, 10) || 1));
            var it = cart[d.c];
            if (!it && Object.keys(cart).length >= CFG.maxProductos) { aviso('El carrito admite hasta ' + CFG.maxProductos + ' productos distintos'); return; }
            if (it) { it.q = Math.min(999, it.q + qty); it.d = 1; it.n = d.n; it.p = d.p; it.m = d.m; }
            else { cart[d.c] = { c: d.c, n: d.n, p: d.p, m: d.m, f: d.f, q: qty, d: 1 }; }
            enviado = false;
            guardar();
            var b = $('.cart-btn'); if (b) { b.classList.remove('bump'); void b.offsetWidth; b.classList.add('bump'); }
            aviso('Agregado: ' + limpio(d.n), true);
        }

        var tt;
        function aviso(texto, conBoton) {
            var t = $('#toast'); if (!t) return;
            $('#toast-t').textContent = texto; $('#toast-ver').style.display = conBoton ? '' : 'none';
            t.classList.add('on'); clearTimeout(tt); tt = setTimeout(function () { t.classList.remove('on'); }, 2800);
        }

        function abrir() { var d = $('#cart-drawer'); d.classList.add('on'); $('#cart-overlay').classList.add('on'); d.setAttribute('aria-hidden', 'false'); document.body.style.overflow = 'hidden'; refrescar(); }
        function cerrar() { var d = $('#cart-drawer'); d.classList.remove('on'); $('#cart-overlay').classList.remove('on'); d.setAttribute('aria-hidden', 'true'); document.body.style.overflow = ''; }

        // Precios y disponibilidad al día (el carrito guardado puede tener días)
        function refrescar() {
            var cod = Object.keys(cart); if (!cod.length || !window.fetch) return;
            fetch(CFG.api + '?c=' + encodeURIComponent(cod.join(',')), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (j) {
                    if (!j || !j.items) return;
                    cod.forEach(function (c) {
                        var s = j.items[c], it = cart[c]; if (!it) return;
                        if (s) { it.n = s.nombre; it.p = s.precio; it.m = s.mayor; it.d = s.disponible ? 1 : 0; } else { it.d = 0; }
                    });
                    guardar();
                }).catch(function () {});
        }

        function el(tag, cls, txt) { var e = document.createElement(tag); if (cls) e.className = cls; if (txt !== undefined) e.textContent = txt; return e; }

        function dibujar() {
            var n = totalUnidades(), badge = $('#cart-num');
            if (badge) { badge.textContent = n > 99 ? '99+' : n; badge.hidden = n === 0; }
            var barra = $('#cart-bar');
            if (barra) {
                $('#cb-n').textContent = n > 99 ? '99+' : n;
                $('#cb-t').textContent = (CFG.precios && total() > 0) ? gs(total()) : '';
                barra.classList.toggle('on', n > 0); document.body.classList.toggle('con-barra', n > 0);
            }
            var cuerpo = $('#cart-lista'), pie = $('#cart-pie'); if (!cuerpo) return;
            cuerpo.textContent = ''; pie.textContent = '';
            var items = lista();
            if (!items.length) {
                var v = el('div', 'cart-vacio'); v.appendChild(el('b', '', 'Tu carrito está vacío')); v.appendChild(document.createTextNode('Agregá productos con el botón +'));
                cuerpo.appendChild(v); return;
            }
            items.forEach(function (it) {
                var u = unidad(it), fila = el('div', 'lin' + (it.d === 0 ? ' nodisp' : ''));
                if (it.f) { var im = el('img'); im.alt = ''; im.loading = 'lazy'; im.src = CFG.base + '/foto/' + encodeURIComponent(it.c); fila.appendChild(im); } else { fila.appendChild(el('div', 'ph', '🛍')); }
                var col = el('div');
                col.appendChild(el('div', 'cod', 'Cód. ' + it.c));
                col.appendChild(el('div', 'nom', it.n));
                if (it.d === 0) col.appendChild(el('div', 'aviso', 'Ya no está disponible: no se incluirá en el pedido.'));
                var det = el('div', 'det'), paso = el('div', 'paso');
                [['−', -1], [null, 0], ['+', 1]].forEach(function (b) {
                    if (b[0] === null) { paso.appendChild(el('span', '', it.q)); return; }
                    var bt = el('button', '', b[0]); bt.type = 'button'; bt.setAttribute('aria-label', b[1] < 0 ? 'Menos' : 'Más');
                    bt.addEventListener('click', function () { it.q = Math.max(1, Math.min(999, it.q + b[1])); enviado = false; guardar(); });
                    paso.appendChild(bt);
                });
                det.appendChild(paso);
                if (CFG.precios && u > 0) {
                    var pr = el('div', 'precios');
                    pr.appendChild(el('div', 'unit', it.q + ' x ' + gs(u) + (u < it.p ? ' (mayorista)' : '')));
                    pr.appendChild(el('div', 'sub', gs(u * it.q)));
                    det.appendChild(pr);
                }
                col.appendChild(det);
                var q = el('button', 'quitar', 'Quitar'); q.type = 'button';
                q.addEventListener('click', function () { delete cart[it.c]; enviado = false; guardar(); });
                col.appendChild(q);
                fila.appendChild(col); cuerpo.appendChild(fila);
            });

            var hay = activos().length > 0;
            var r = el('div', 'cart-res'); r.appendChild(el('span', '', activos().length + (activos().length === 1 ? ' producto' : ' productos') + ' · ' + activos().reduce(function (s, i) { return s + i.q; }, 0) + ' unidades')); pie.appendChild(r);
            if (CFG.precios && total() > 0) { var t = el('div', 'cart-tot'); t.appendChild(el('span', '', 'Total estimado')); t.appendChild(el('span', '', gs(total()))); pie.appendChild(t); }
            pie.appendChild(el('div', 'cart-nota', 'El total y la disponibilidad finales se confirman al registrar tu pedido por WhatsApp.'));
            var env = el('button', 'btn wa', 'Enviar pedido por WhatsApp'); env.type = 'button'; env.disabled = !hay; env.style.opacity = hay ? '' : '.5';
            env.addEventListener('click', enviar); pie.appendChild(env);
            if (enviado) pie.appendChild(el('div', 'cart-ok', 'Se abrió WhatsApp con tu pedido. Tocá "Enviar" allá para completarlo; después podés vaciar el carrito.'));
            var sec = el('div', 'cart-sec'), seg = el('button', '', 'Seguir comprando'), vac = el('button', '', 'Vaciar carrito');
            seg.type = vac.type = 'button'; seg.addEventListener('click', cerrar);
            vac.addEventListener('click', function () { if (confirm('¿Vaciar el carrito?')) { cart = {}; enviado = false; guardar(); } });
            sec.appendChild(seg); sec.appendChild(vac); pie.appendChild(sec);
        }

        // Resumen que recibe el agente: código, descripción, cantidad, precio y subtotal de cada producto
        function mensaje(compacto) {
            var L = ['🛒 *Pedido desde la tienda web*', CFG.nombre, ''], tot = 0;
            activos().forEach(function (it, i) {
                var u = unidad(it), s = u * it.q; tot += s;
                var l = (i + 1) + ') ' + it.q + ' x ' + limpio(it.n) + ' (Cód. ' + it.c + ')';
                if (CFG.precios && u > 0 && !compacto) l += ' · ' + gs(u) + ' c/u · Subtotal ' + gs(s);
                L.push(l);
            });
            L.push('');
            if (CFG.precios && tot > 0) L.push('Total estimado: ' + gs(tot));
            L.push('El total final lo confirma el vendedor.');
            return L.join('\n');
        }

        function enviar() {
            if (!activos().length) return;
            var m = mensaje(false); if (m.length > 3500) m = mensaje(true);
            var url = 'https://wa.me/' + CFG.whatsapp + '?text=' + encodeURIComponent(m);
            window.__ultimoMensaje = m;
            var w = window.open(url, '_blank', 'noopener'); if (!w) { window.location.href = url; }
            enviado = true; dibujar();
        }

        document.addEventListener('click', function (e) {
            var a = e.target.closest('[data-add]');
            if (a) {
                e.preventDefault();
                var qi = a.getAttribute('data-qty'), q = qi ? parseInt(($(qi) || {}).textContent, 10) : 1;
                agregar({ c: a.dataset.codigo, n: a.dataset.nombre, p: parseFloat(a.dataset.precio) || 0, m: parseFloat(a.dataset.mayor) || 0, f: a.dataset.foto === '1' ? 1 : 0 }, q);
                return;
            }
            var p = e.target.closest('[data-paso]');
            if (p) { var s = $('#cant'); s.textContent = Math.max(1, Math.min(99, (parseInt(s.textContent, 10) || 1) + parseInt(p.dataset.paso, 10))); return; }
            if (e.target.closest('[data-cart-open]') || e.target.id === 'toast-ver') { abrir(); return; }
            if (e.target.id === 'cart-overlay' || e.target.id === 'cart-cerrar') cerrar();
        });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') cerrar(); });
        window.addEventListener('storage', function (e) { if (e.key === KEY) { try { cart = JSON.parse(e.newValue || '{}') || {}; } catch (x) { cart = {}; } dibujar(); } });
        window.__carrito = { mensaje: mensaje, items: lista };
        dibujar();
    })();
    </script>

    @endif
    @yield('scripts')
</body>
</html>
