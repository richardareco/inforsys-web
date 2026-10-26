@extends('tienda.layout', ['tituloPagina' => $categoriaNombre ?: ($q ? 'Búsqueda: ' . $q : '')])

@php
    $base = '/tienda/' . $t->slug;
    $gs = fn ($v) => 'Gs. ' . number_format($v, 0, ',', '.');
    $href = fn (array $extra = []) => $base . '?' . http_build_query(array_filter(array_merge(['categoria' => $categoria, 'q' => $q, 'orden' => $orden === 'nombre' ? null : $orden], $extra), fn ($v) => $v !== null && $v !== ''));
@endphp

@section('contenido')
<main class="wrap">
    @if($esInicio)
        <section class="hero">
            @if($t->portada)<img class="fondo" src="{{ $t->urlArchivo($t->portada) }}" alt="">@endif
            <div class="hero-in">
                <h1>{{ $t->eslogan ?: $t->nombreTienda() }}</h1>
                @if($t->texto_inicio)<p>{{ \Illuminate\Support\Str::limit($t->texto_inicio, 200) }}</p>@endif
                <a class="btn" href="#catalogo">Ver productos</a>
            </div>
        </section>
    @endif

    <nav class="cats" aria-label="Categorías">
        <a class="chip {{ $categoria === '' ? 'on' : '' }}" href="{{ $base }}{{ $q ? '?q=' . urlencode($q) : '' }}">Todos</a>
        @foreach($categorias as $c)
            <a class="chip {{ $categoria === $c['codigo'] ? 'on' : '' }}" href="{{ $base }}?{{ http_build_query(array_filter(['categoria' => $c['codigo'], 'q' => $q])) }}">{{ $c['nombre'] }}<small>{{ $c['total'] }}</small></a>
        @endforeach
    </nav>

    <section id="catalogo">
        <div class="barra">
            <div>
                <h2>{{ $categoriaNombre ?: ($q ? 'Resultados para "' . $q . '"' : 'Todos los productos') }}</h2>
                <span>{{ $productos->total() }} {{ $productos->total() === 1 ? 'producto' : 'productos' }}</span>
            </div>
            <form method="get" action="{{ $base }}">
                <input type="hidden" name="categoria" value="{{ $categoria }}"><input type="hidden" name="q" value="{{ $q }}">
                <select name="orden" onchange="this.form.submit()" aria-label="Ordenar">
                    @foreach(['nombre' => 'Nombre (A-Z)', 'precio_asc' => 'Precio: menor a mayor', 'precio_desc' => 'Precio: mayor a menor', 'recientes' => 'Más recientes'] as $k => $l)
                        @continue(!$t->mostrar_precios && str_starts_with($k, 'precio'))
                        <option value="{{ $k }}" @selected($orden === $k)>{{ $l }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="grid">
            @forelse($productos as $p)
                @php $may = $catalogo->mayorista($p); $agotado = $p['stock'] <= 0; @endphp
                <a class="card" href="{{ $base }}/producto/{{ rawurlencode($p['codigo']) }}">
                    <div class="foto">
                        @if($p['tiene_foto'])
                            <img src="{{ $base }}/foto/{{ rawurlencode($p['codigo']) }}" alt="{{ $p['nombre'] }}" loading="lazy">
                        @else
                            <div class="sin-foto"><svg fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.16-5.16a2.25 2.25 0 0 1 3.18 0l5.16 5.16m-1.5-1.5 1.41-1.41a2.25 2.25 0 0 1 3.18 0l2.41 2.41M3.75 21h16.5A1.5 1.5 0 0 0 21.75 19.5V4.5A1.5 1.5 0 0 0 20.25 3H3.75A1.5 1.5 0 0 0 2.25 4.5v15A1.5 1.5 0 0 0 3.75 21Zm10.5-11.25h.01v.01h-.01V9.75Z"/></svg></div>
                        @endif
                        @if($t->mostrar_stock && $agotado)<span class="etq agot">Agotado</span>@endif
                        @if($p['fotos'] > 1)<span class="etq fotos">{{ $p['fotos'] }} fotos</span>@endif
                    </div>
                    <div class="info">
                        <div class="cat">{{ $p['categoria'] }}</div>
                        <h3>{{ $p['nombre'] }}</h3>
                        @if($t->mostrar_precios && $p['precio'] > 0)
                            <div class="precio">{{ $gs($p['precio']) }}</div>
                            @if($may)<div class="mayor">Mayorista {{ $gs($may['precio']) }} desde {{ $may['desde'] }} u.</div>@endif
                        @else
                            <div class="precio consulta">Consultar precio</div>
                        @endif
                    </div>
                </a>
            @empty
                <div class="vacio"><b>No encontramos productos</b>{{ $q ? 'Probá con otra búsqueda o ' : '' }}<a href="{{ $base }}" style="color:var(--c);font-weight:600;">ver todo el catálogo</a></div>
            @endforelse
        </div>

        @if($productos->lastPage() > 1)
            @php $act = $productos->currentPage(); $ult = $productos->lastPage(); @endphp
            <nav class="pag" aria-label="Páginas">
                @if($act > 1)<a href="{{ $href(['page' => $act - 1]) }}#catalogo">‹ Anterior</a>@else<span class="off">‹ Anterior</span>@endif
                @foreach(range(max(1, $act - 2), min($ult, $act + 2)) as $n)
                    @if($n === $act)<span class="on">{{ $n }}</span>@else<a href="{{ $href(['page' => $n]) }}#catalogo">{{ $n }}</a>@endif
                @endforeach
                @if($act < $ult)<a href="{{ $href(['page' => $act + 1]) }}#catalogo">Siguiente ›</a>@else<span class="off">Siguiente ›</span>@endif
            </nav>
        @endif
    </section>
</main>
@endsection
