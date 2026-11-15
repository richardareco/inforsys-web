@extends('tienda.layout', [
    'tituloPagina' => $p['nombre'],
    'descripcionPagina' => $p['descripcion'] ?: ($p['nombre'] . ' · ' . $p['categoria']),
    'imagenSocial' => $p['tiene_foto'] ? '/tienda/' . $t->slug . '/foto/' . rawurlencode($p['codigo']) : null,
])

@php
    $base = '/tienda/' . $t->slug;
    $gs = fn ($v) => 'Gs. ' . number_format($v, 0, ',', '.');
    $may = $catalogo->mayorista($p);
    $agotado = $p['stock'] <= 0;
    $plantilla = $t->mensaje_whatsapp ?: 'Hola, quiero consultar por {producto} (Cód. {codigo}).';
    $mensaje = str_replace(['{producto}', '{codigo}'], [$p['nombre'], $p['codigo']], $plantilla);
    $linkWa = $t->enlaceWhatsapp($mensaje . ' ' . url($base . '/producto/' . rawurlencode($p['codigo'])));
    $tienePrecio = $t->mostrar_precios && $p['precio'] > 0;
    $carritoOn = $t->carritoDisponible();
@endphp

@section('estilos')
    .miga { padding: 1.2rem 0 .8rem; font-size: .85rem; color: var(--mu); }
    .miga a:hover { color: var(--c); }
    .det { display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr); gap: 3rem; padding-bottom: 2rem; align-items: start; }
    .galeria { position: sticky; top: var(--top-fijo, 5.5rem); }
    .carr { position: relative; border-radius: 1.4rem; background: #f6f6f7; border: 1px solid #f0f0f1; overflow: hidden; aspect-ratio: 1; }
    .slides { display: flex; height: 100%; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; scroll-behavior: smooth; }
    .slides::-webkit-scrollbar { display: none; }
    .slide { flex: 0 0 100%; scroll-snap-align: center; display: grid; place-items: center; }
    .slide img { width: 100%; height: 100%; object-fit: contain; padding: 1.2rem; mix-blend-mode: multiply; }
    .flecha { position: absolute; top: 50%; translate: 0 -50%; width: 2.7rem; height: 2.7rem; border-radius: 999px; border: 1px solid var(--bd); background: rgba(255, 255, 255, .95); box-shadow: 0 8px 18px -10px rgba(0, 0, 0, .4); cursor: pointer; display: grid; place-items: center; font-size: 1.3rem; line-height: 1; color: #111827; transition: transform .15s; }
    .flecha:hover { transform: scale(1.08) translateY(-50%); }
    .flecha.prev { left: .8rem; } .flecha.next { right: .8rem; }
    .cuenta { position: absolute; right: .9rem; bottom: .9rem; background: rgba(17, 24, 39, .72); color: #fff; font-size: .75rem; font-weight: 700; padding: .25rem .7rem; border-radius: 999px; }
    .miniaturas { display: flex; gap: .6rem; margin-top: .8rem; overflow-x: auto; scrollbar-width: none; }
    .mini { flex: 0 0 4.4rem; height: 4.4rem; border-radius: .8rem; border: 2px solid transparent; background: #f6f6f7; padding: .2rem; cursor: pointer; overflow: hidden; }
    .mini img { width: 100%; height: 100%; object-fit: contain; mix-blend-mode: multiply; }
    .mini.on { border-color: var(--c); }
    .puntos { display: none; justify-content: center; gap: .4rem; margin-top: .7rem; }
    .punto { width: .5rem; height: .5rem; border-radius: 999px; background: #d1d5db; } .punto.on { background: var(--c); width: 1.2rem; }
    .info-p .cat { font-size: .78rem; text-transform: uppercase; letter-spacing: .08em; color: var(--c); font-weight: 700; }
    .info-p h1 { font-size: clamp(1.5rem, 3.2vw, 2.2rem); line-height: 1.15; letter-spacing: -.03em; margin-top: .3rem; }
    .info-p .pr { margin-top: 1rem; font-size: 1.9rem; font-weight: 800; letter-spacing: -.02em; }
    .info-p .pr.consulta { font-size: 1.1rem; color: var(--mu); font-weight: 600; }
    .info-p .may { margin-top: .5rem; display: inline-block; background: #ecfdf5; color: #047857; font-weight: 700; font-size: .85rem; padding: .4rem .9rem; border-radius: 999px; }
    .estado { display: inline-flex; align-items: center; gap: .4rem; margin-top: 1rem; font-size: .88rem; font-weight: 600; }
    .estado i { width: .6rem; height: .6rem; border-radius: 999px; background: #10b981; } .estado.ag i { background: #ef4444; }
    .desc { margin-top: 1.4rem; padding-top: 1.2rem; border-top: 1px solid var(--bd); color: #374151; white-space: pre-line; }
    .info-p .marca { font-size: .78rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--mu); }
    .info-p .cont { margin-top: .5rem; display: inline-block; background: #f3f4f6; color: #374151; font-weight: 600; font-size: .8rem; padding: .25rem .75rem; border-radius: 999px; }
    .ficha-sec { margin-top: 1.2rem; padding-top: 1.1rem; border-top: 1px solid var(--bd); }
    .ficha-sec h2 { font-size: .8rem; text-transform: uppercase; letter-spacing: .08em; color: var(--c); font-weight: 800; margin-bottom: .45rem; }
    .ficha-sec p { color: #374151; white-space: pre-line; overflow-wrap: anywhere; }
    .ficha-sec.adv p { background: #fffbeb; border-left: 3px solid #f59e0b; color: #92400e; padding: .6rem .8rem; border-radius: .5rem; }
    .tonos { display: flex; flex-wrap: wrap; gap: .9rem; }
    .tono { display: flex; flex-direction: column; align-items: center; gap: .3rem; width: 4rem; }
    .tono i { width: 2.4rem; height: 2.4rem; border-radius: 999px; box-shadow: inset 0 0 0 1px rgba(0, 0, 0, .15), 0 2px 6px rgba(0, 0, 0, .12); display: block; }
    .tono span { font-size: .72rem; font-weight: 600; color: #4b5563; text-align: center; line-height: 1.15; overflow-wrap: anywhere; }
    .acc { display: flex; gap: .7rem; flex-wrap: wrap; margin-top: 1.6rem; }
    .acc .btn { height: 3.1rem; font-size: 1rem; }
    .cod { margin-top: 1.2rem; font-size: .8rem; color: var(--mu); }
    .rel h2 { font-size: 1.35rem; letter-spacing: -.02em; margin: 2rem 0 1rem; }
    @media (max-width: 800px) {
        .det { grid-template-columns: 1fr; gap: 1.4rem; }
        .galeria { position: static; }
        .flecha { width: 2.3rem; height: 2.3rem; }
        .miniaturas { display: none; } .puntos { display: flex; }
        .acc .btn { flex: 1; }
    }
@endsection

@section('contenido')
<main class="wrap">
    <nav class="miga"><a href="{{ $base }}">Inicio</a> › <a href="{{ $base }}?categoria={{ urlencode($p['grupnr']) }}">{{ $p['categoria'] }}</a> › {{ $p['nombre'] }}</nav>

    <article class="det">
        <div class="galeria">
            <div class="carr">
                @if(count($fotos) > 0)
                    <div class="slides" id="slides">
                        @foreach($fotos as $i => $nro)
                            <div class="slide"><img src="{{ $base }}/foto/{{ rawurlencode($p['codigo']) }}/{{ $nro }}" alt="{{ $p['nombre'] }} ({{ $i + 1 }})" @if($i > 0) loading="lazy" @endif></div>
                        @endforeach
                    </div>
                    @if(count($fotos) > 1)
                        <button type="button" class="flecha prev" id="prev" aria-label="Foto anterior">‹</button>
                        <button type="button" class="flecha next" id="next" aria-label="Foto siguiente">›</button>
                        <span class="cuenta" id="cuenta">1 / {{ count($fotos) }}</span>
                    @endif
                @else
                    <div class="sin-foto"><svg fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.16-5.16a2.25 2.25 0 0 1 3.18 0l5.16 5.16m-1.5-1.5 1.41-1.41a2.25 2.25 0 0 1 3.18 0l2.41 2.41M3.75 21h16.5A1.5 1.5 0 0 0 21.75 19.5V4.5A1.5 1.5 0 0 0 20.25 3H3.75A1.5 1.5 0 0 0 2.25 4.5v15A1.5 1.5 0 0 0 3.75 21Z"/></svg></div>
                @endif
            </div>
            @if(count($fotos) > 1)
                <div class="miniaturas" id="minis">
                    @foreach($fotos as $i => $nro)
                        <button type="button" class="mini {{ $i === 0 ? 'on' : '' }}" data-i="{{ $i }}" aria-label="Ver foto {{ $i + 1 }}"><img src="{{ $base }}/foto/{{ rawurlencode($p['codigo']) }}/{{ $nro }}" alt="" loading="lazy"></button>
                    @endforeach
                </div>
                <div class="puntos" id="puntos">@foreach($fotos as $i => $nro)<span class="punto {{ $i === 0 ? 'on' : '' }}"></span>@endforeach</div>
            @endif
        </div>

        <div class="info-p">
            @if(!empty($ficha['marca']))<div class="marca">{{ $ficha['marca'] }}</div>@endif
            <div class="cat">{{ $p['categoria'] }}</div>
            <h1>{{ $p['nombre'] }}</h1>
            @if(!empty($ficha['contenido']))<span class="cont">{{ $ficha['contenido'] }}</span>@endif

            @if($tienePrecio)
                <div class="pr">{{ $gs($p['precio']) }}</div>
                @if($may)<div class="may">Mayorista {{ $gs($may['precio']) }} c/u desde {{ $may['desde'] }} unidades</div>@endif
            @else
                <div class="pr consulta">Consultá el precio por WhatsApp</div>
            @endif

            @if($t->mostrar_stock)
                <div class="estado {{ $agotado ? 'ag' : '' }}"><i></i> {{ $agotado ? 'Agotado por el momento' : 'Disponible' }}</div>
            @endif

            @php $descripcion = !empty($ficha['descripcion']) ? $ficha['descripcion'] : $p['descripcion']; @endphp
            @if($descripcion !== '')<p class="desc">{{ $descripcion }}</p>@endif

            @if($ficha)
                @foreach(['beneficios' => 'Beneficios', 'modo_uso' => 'Modo de uso', 'ingredientes' => 'Ingredientes', 'tipo_piel' => 'Tipo de piel'] as $clave => $titulo)
                    @if(!empty($ficha[$clave]))
                        <section class="ficha-sec"><h2>{{ $titulo }}</h2><p>{{ $ficha[$clave] }}</p></section>
                    @endif
                @endforeach
                @if(!empty($ficha['tonos']))
                    <section class="ficha-sec">
                        <h2>Tonos</h2>
                        <div class="tonos">
                            @foreach($ficha['tonos'] as $tono)
                                <div class="tono" title="{{ $tono['nombre'] }}"><i style="background: {{ $tono['color'] }}"></i><span>{{ $tono['nombre'] }}</span></div>
                            @endforeach
                        </div>
                    </section>
                @endif
                @foreach(($ficha['extras'] ?? []) as $extra)
                    <section class="ficha-sec"><h2>{{ $extra['titulo'] }}</h2><p>{{ $extra['contenido'] }}</p></section>
                @endforeach
                @if(!empty($ficha['advertencias']))
                    <section class="ficha-sec adv"><h2>Advertencias</h2><p>{{ $ficha['advertencias'] }}</p></section>
                @endif
            @endif

            <div class="acc">
                @if($carritoOn && !$agotado)
                    <div class="paso paso-g" aria-label="Cantidad">
                        <button type="button" data-paso="-1" aria-label="Menos">−</button><span id="cant">1</span><button type="button" data-paso="1" aria-label="Más">+</button>
                    </div>
                    <button type="button" class="btn" data-add data-qty="#cant" data-codigo="{{ $p['codigo'] }}" data-nombre="{{ $p['nombre'] }}" data-precio="{{ $t->mostrar_precios ? $p['precio'] : 0 }}" data-mayor="{{ ($t->mostrar_precios && $may) ? $may['precio'] : 0 }}" data-foto="{{ $p['tiene_foto'] ? 1 : 0 }}">Agregar al carrito</button>
                @endif
                @if($linkWa)
                    <a class="btn {{ $carritoOn ? 'sec' : 'wa' }}" href="{{ $linkWa }}" target="_blank" rel="noopener">
                        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2a9.9 9.9 0 0 0-8.5 14.9L2 22l5.25-1.38A9.9 9.9 0 1 0 12.04 2Zm5.8 14.1c-.25.7-1.45 1.34-2 1.4-.5.07-1.13.1-1.82-.12a16.5 16.5 0 0 1-1.65-.6c-2.9-1.25-4.78-4.15-4.92-4.35-.14-.2-1.17-1.56-1.17-2.97s.74-2.1 1-2.4c.27-.28.58-.35.78-.35h.56c.18 0 .42-.07.65.5.25.6.84 2.05.9 2.2.08.14.12.32.02.5-.1.2-.15.32-.3.5l-.45.5c-.15.15-.3.3-.13.6.18.3.77 1.27 1.65 2.05 1.13 1 2.08 1.3 2.38 1.45.3.14.47.12.65-.07.18-.2.75-.87.95-1.17.2-.3.4-.25.67-.15.28.1 1.75.83 2.05.97.3.15.5.22.57.35.07.12.07.7-.18 1.4Z"/></svg>
                        Consultar por WhatsApp
                    </a>
                @endif
                <button type="button" class="btn sec" id="compartir" data-titulo="{{ $p['nombre'] }}">Compartir</button>
            </div>
            <div class="cod">Código: {{ $p['codigo'] }}</div>
        </div>
    </article>

    @if(count($relacionados))
        <section class="rel">
            <h2>También te puede interesar</h2>
            <div class="grid">
                @foreach($relacionados as $r)
                    @php $enlaceR = $base . '/producto/' . rawurlencode($r['codigo']); $mayR = $catalogo->mayorista($r); @endphp
                    <div class="card">
                        <div class="fotowrap">
                        <a class="foto" href="{{ $enlaceR }}">
                            @if($r['tiene_foto'])<img src="{{ $base }}/foto/{{ rawurlencode($r['codigo']) }}" alt="{{ $r['nombre'] }}" loading="lazy">@else<div class="sin-foto"><svg fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.16-5.16a2.25 2.25 0 0 1 3.18 0l5.16 5.16M3.75 21h16.5A1.5 1.5 0 0 0 21.75 19.5V4.5A1.5 1.5 0 0 0 20.25 3H3.75A1.5 1.5 0 0 0 2.25 4.5v15A1.5 1.5 0 0 0 3.75 21Z"/></svg></div>@endif
                            @if($t->mostrar_stock && $r['stock'] <= 0)<span class="etq agot">Agotado</span>@endif
                        </a>
                        @if($carritoOn && $r['stock'] > 0)
                            <button type="button" class="add" data-add data-codigo="{{ $r['codigo'] }}" data-nombre="{{ $r['nombre'] }}" data-precio="{{ $t->mostrar_precios ? $r['precio'] : 0 }}" data-mayor="{{ ($t->mostrar_precios && $mayR) ? $mayR['precio'] : 0 }}" data-foto="{{ $r['tiene_foto'] ? 1 : 0 }}" aria-label="Agregar {{ $r['nombre'] }} al carrito">+</button>
                        @endif
                        </div>
                        <a class="info" href="{{ $enlaceR }}"><h3>{{ $r['nombre'] }}</h3>
                            @if($t->mostrar_precios && $r['precio'] > 0)<div class="precio">{{ $gs($r['precio']) }}</div>@else<div class="precio consulta">Consultar precio</div>@endif
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</main>
@endsection

@section('scripts')
<script>
(function () {
    // Carrusel de fotos
    var s = document.getElementById('slides');
    if (s && s.children.length > 1) {
        var n = s.children.length, cur = 0, minis = document.querySelectorAll('.mini'), puntos = document.querySelectorAll('.punto'), cuenta = document.getElementById('cuenta');
        var marcar = function (i) {
            cur = i;
            minis.forEach(function (m, k) { m.classList.toggle('on', k === i); });
            puntos.forEach(function (m, k) { m.classList.toggle('on', k === i); });
            if (cuenta) cuenta.textContent = (i + 1) + ' / ' + n;
        };
        var ir = function (i) {
            i = (i + n) % n;
            marcar(i);                                   // el estado cambia al instante, sin esperar la animación
            s.scrollTo({ left: i * s.clientWidth, behavior: 'smooth' });
        };
        // Al deslizar con el dedo o el mouse se actualiza según la foto que queda al centro
        s.addEventListener('scroll', function () {
            var i = Math.round(s.scrollLeft / s.clientWidth);
            if (i >= 0 && i < n && i !== cur) marcar(i);
        }, { passive: true });
        document.getElementById('prev').addEventListener('click', function () { ir(cur - 1); });
        document.getElementById('next').addEventListener('click', function () { ir(cur + 1); });
        minis.forEach(function (m) { m.addEventListener('click', function () { ir(parseInt(m.dataset.i, 10)); }); });
        document.addEventListener('keydown', function (e) { if (e.key === 'ArrowLeft') ir(cur - 1); if (e.key === 'ArrowRight') ir(cur + 1); });
    }
    // Compartir
    var b = document.getElementById('compartir');
    if (b) b.addEventListener('click', function () {
        var url = window.location.href;
        if (navigator.share) { navigator.share({ title: b.dataset.titulo, url: url }).catch(function () {}); return; }
        (navigator.clipboard ? navigator.clipboard.writeText(url) : Promise.reject()).then(function () { b.textContent = '¡Enlace copiado!'; setTimeout(function () { b.textContent = 'Compartir'; }, 1800); }).catch(function () { window.prompt('Copiá el enlace:', url); });
    });
})();
</script>
@endsection
