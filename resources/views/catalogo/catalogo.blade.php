@extends('catalogo.layout')

@section('title', 'Productos disponibles')

@section('content')

@php
    $totalPresentaciones = $presentaciones->count();
    $productosUnicos = $presentaciones->pluck('producto.nombre')->filter()->unique()->count();
    $presentacionesDisponibles = $presentaciones->filter(function ($presentacion) {
        return (float) $presentacion->stock > 0;
    })->count();
@endphp

<div class="catalogo-page">

    <section class="catalog-hero">
        <div class="container">
            <div class="catalog-hero__grid">
                <div class="catalog-hero__copy">
                    <span class="catalog-hero__kicker">
                        <i class="bi bi-droplet-half"></i>
                        Directo de productores locales
                    </span>

                    <h1>Productos lácteos disponibles</h1>

                    <p>
                        Catálogo público con presentaciones, precios y disponibilidad
                        actualizada para elegir productos frescos con confianza.
                    </p>

                    <div class="catalog-hero__actions">
                        <a href="#catalogoResultados" class="btn btn-light catalog-hero__button">
                            <i class="bi bi-grid-3x3-gap"></i>
                            Ver catálogo
                        </a>
                        <a href="{{ route('catalogo.contacto') }}" class="btn btn-outline-light catalog-hero__button">
                            <i class="bi bi-telephone"></i>
                            Contacto
                        </a>
                    </div>
                </div>

                <aside class="catalog-hero__panel" aria-label="Resumen del catálogo">
                    <div class="catalog-hero__panel-head">
                        <span>Resumen disponible</span>
                        <i class="bi bi-basket2-fill"></i>
                    </div>

                    <div class="catalog-hero__stats">
                        <div>
                            <strong>{{ $totalPresentaciones }}</strong>
                            <span>{{ $totalPresentaciones === 1 ? 'presentación' : 'presentaciones' }}</span>
                        </div>
                        <div>
                            <strong>{{ $productosUnicos }}</strong>
                            <span>{{ $productosUnicos === 1 ? 'producto base' : 'productos base' }}</span>
                        </div>
                        <div>
                            <strong>{{ $presentacionesDisponibles }}</strong>
                            <span>con stock</span>
                        </div>
                    </div>

                    <div class="catalog-hero__note">
                        <i class="bi bi-check2-circle"></i>
                        Información comercial clara para visitantes externos.
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <div class="container catalogo-wrap py-5">

        <section class="catalog-section-head" id="catalogoResultados">
            <div>
                <span class="catalog-section-head__label">Catálogo público</span>
                <h2>Encuentra la presentación adecuada</h2>
                <p>
                    Filtra por producto, sabor, unidad o disponibilidad y revisa
                    cada presentación sin perder el contexto del catálogo.
                </p>
            </div>

            <div class="catalog-count-pill" aria-live="polite">
                <i class="bi bi-box-seam"></i>
                <span>
                    <strong id="resultadoConteo">{{ $totalPresentaciones }}</strong>
                    de {{ $totalPresentaciones }}
                </span>
            </div>
        </section>

        <section class="catalog-filter-panel mb-4" aria-label="Filtros del catálogo">
            <div class="row g-3 align-items-end">
                <div class="col-lg-5">
                    <label for="buscadorCatalogo" class="form-label">
                        Buscar producto
                    </label>

                    <div class="catalog-search">
                        <i class="bi bi-search"></i>
                        <input
                            type="search"
                            id="buscadorCatalogo"
                            class="form-control"
                            placeholder="Yogurt, frutilla, 1 litro, botella..."
                        >
                    </div>
                </div>

                <div class="col-md-4 col-lg-3">
                    <label for="filtroProducto" class="form-label">Producto</label>
                    <select id="filtroProducto" class="form-select">
                        <option value="">Todos</option>
                        @foreach($presentaciones->pluck('producto.nombre')->filter()->unique()->sort() as $productoNombre)
                            <option value="{{ Str::lower($productoNombre) }}">{{ $productoNombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label for="filtroSabor" class="form-label">Sabor</label>
                    <select id="filtroSabor" class="form-select">
                        <option value="">Todos</option>
                        @foreach($presentaciones->pluck('sabor')->filter()->unique()->sort() as $sabor)
                            <option value="{{ Str::lower($sabor) }}">{{ $sabor }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label for="filtroUnidad" class="form-label">Presentación</label>
                    <select id="filtroUnidad" class="form-select">
                        <option value="">Todas</option>
                        @foreach($presentaciones->pluck('unidad')->filter()->unique()->sort() as $unidad)
                            <option value="{{ Str::lower($unidad) }}">{{ $unidad }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="catalog-filter-strip">
                <div class="catalog-chip-group" role="group" aria-label="Filtrar por disponibilidad">
                    <span>Disponibilidad</span>

                    <button type="button" class="btn-chip filtro-stock active" data-stock="">
                        Todos
                    </button>
                    <button type="button" class="btn-chip filtro-stock" data-stock="disponible">
                        <i class="bi bi-check-circle"></i>
                        Disponibles
                    </button>
                    <button type="button" class="btn-chip filtro-stock" data-stock="agotado">
                        <i class="bi bi-x-circle"></i>
                        Agotados
                    </button>
                </div>

                <div class="catalog-chip-group" role="group" aria-label="Filtrar por fruta">
                    <span>Con fruta</span>

                    <button type="button" class="btn-chip filtro-fruta active-fruta" data-fruta="">
                        Todos
                    </button>
                    <button type="button" class="btn-chip filtro-fruta" data-fruta="si">
                        <i class="bi bi-stars"></i>
                        Con fruta
                    </button>
                    <button type="button" class="btn-chip filtro-fruta" data-fruta="no">
                        <i class="bi bi-dash-circle"></i>
                        Sin fruta
                    </button>
                </div>

                <button type="button" id="limpiarFiltros" class="catalog-reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Limpiar filtros
                </button>
            </div>
        </section>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4" id="productosCatalogo">

            @forelse($presentaciones as $presentacion)

                @php
                    $nombreProducto = $presentacion->producto->nombre ?? '';
                    $nombrePresentacion = $presentacion->nombre ?? '';
                    $sabor = $presentacion->sabor ?? '';
                    $envase = $presentacion->envase ?? '';
                    $contenido = $presentacion->contenido ?? '';
                    $unidad = $presentacion->unidad ?? '';
                    $descripcion = $presentacion->producto->descripcion ?? 'Producto lácteo disponible para consulta comercial.';
                    $imagenCatalogo = $presentacion->imagen_comercial ?: ($presentacion->producto->imagen_referencial ?? null);
                    $conFruta = isset($presentacion->con_fruta) ? (bool) $presentacion->con_fruta : null;

                    $textoBusqueda = Str::lower(
                        $nombreProducto . ' ' . $nombrePresentacion . ' ' . $sabor . ' ' .
                        $envase . ' ' . $contenido . ' ' . $unidad
                    );

                    $stockDisponible = (float) $presentacion->stock > 0;
                    $collapseId = 'detalle-' . $presentacion->id;
                @endphp

                <div
                    class="col tarjeta-producto"
                    data-busqueda="{{ $textoBusqueda }}"
                    data-producto="{{ Str::lower($nombreProducto) }}"
                    data-sabor="{{ Str::lower($sabor) }}"
                    data-unidad="{{ Str::lower($unidad) }}"
                    data-stock="{{ $stockDisponible ? 'disponible' : 'agotado' }}"
                    data-fruta="{{ $conFruta === null ? '' : ($conFruta ? 'si' : 'no') }}"
                >
                    <article class="catalog-product h-100 {{ $stockDisponible ? '' : 'is-out' }}">
                        <div class="catalog-product__media">
                            @if($imagenCatalogo)
                                <img
                                    src="{{ asset('storage/' . $imagenCatalogo) }}"
                                    class="catalog-product__image"
                                    alt="{{ $presentacion->nombre }}"
                                    loading="lazy"
                                >
                            @else
                                <div class="catalog-product__placeholder">
                                    <i class="bi bi-image"></i>
                                </div>
                            @endif

                            <div class="catalog-product__badges">
                                @if($stockDisponible)
                                    <span class="stock-badge stock-badge--available">
                                        <i class="bi bi-check2-circle"></i>
                                        Disponible
                                    </span>
                                @else
                                    <span class="stock-badge stock-badge--out">
                                        <i class="bi bi-x-circle"></i>
                                        Agotado
                                    </span>
                                @endif

                                @if($conFruta === true)
                                    <span class="fruit-badge">Con fruta</span>
                                @endif
                            </div>
                        </div>

                        <div class="catalog-product__body">
                            <p class="catalog-product__family">
                                {{ $nombreProducto ?: 'Producto lácteo' }}
                            </p>

                            <h3>{{ $nombrePresentacion ?: 'Presentación comercial' }}</h3>

                            <p class="catalog-product__description">
                                {{ Str::limit($descripcion, 96) }}
                            </p>

                            <div class="catalog-product__facts">
                                @if($contenido)
                                    <span>
                                        <i class="bi bi-cup-straw"></i>
                                        {{ $contenido }} {{ $unidad }}
                                    </span>
                                @endif

                                @if($sabor)
                                    <span>
                                        <i class="bi bi-stars"></i>
                                        {{ $sabor }}
                                    </span>
                                @endif

                                @if($envase)
                                    <span>
                                        <i class="bi bi-bag"></i>
                                        {{ $envase }}
                                    </span>
                                @endif

                                @if($conFruta !== null)
                                    <span>
                                        <i class="bi bi-flower1"></i>
                                        {{ $conFruta ? 'Con fruta' : 'Sin fruta' }}
                                    </span>
                                @endif
                            </div>

                            <div class="catalog-product__commercial">
                                <div>
                                    <span>Precio</span>
                                    <strong>Bs {{ number_format($presentacion->precio, 2) }}</strong>
                                </div>

                                <div class="{{ $stockDisponible ? 'text-success' : 'text-muted' }}">
                                    <span>Stock</span>
                                    <strong>{{ $stockDisponible ? $presentacion->stock . ' disp.' : 'Sin stock' }}</strong>
                                </div>
                            </div>

                            <div class="catalog-product__actions">
                                <a href="{{ route('catalogo.detalle', $presentacion) }}" class="btn catalog-product__primary">
                                    <i class="bi bi-eye"></i>
                                    Ver producto
                                </a>

                                <button
                                    class="catalog-product__toggle"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#{{ $collapseId }}"
                                    aria-expanded="false"
                                    aria-controls="{{ $collapseId }}"
                                    aria-label="Ver detalle de {{ $nombrePresentacion ?: 'producto' }}"
                                >
                                    <span>Detalle</span>
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                            </div>

                            <div class="collapse" id="{{ $collapseId }}">
                                <div class="catalog-product__detail">
                                    <dl>
                                        @if($sabor)
                                            <div>
                                                <dt>Sabor</dt>
                                                <dd>{{ $sabor }}</dd>
                                            </div>
                                        @endif

                                        @if($envase)
                                            <div>
                                                <dt>Envase</dt>
                                                <dd>{{ $envase }}</dd>
                                            </div>
                                        @endif

                                        @if($contenido)
                                            <div>
                                                <dt>Contenido</dt>
                                                <dd>{{ $contenido }} {{ $unidad }}</dd>
                                            </div>
                                        @endif

                                        <div>
                                            <dt>Disponibilidad</dt>
                                            <dd>{{ $stockDisponible ? $presentacion->stock . ' disponibles' : 'Agotado' }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>

            @empty

                <div class="col-12">
                    <div class="catalog-empty-state">
                        <i class="bi bi-box-seam"></i>
                        <h3>No hay productos disponibles</h3>
                        <p>Actualmente no existen presentaciones registradas.</p>
                    </div>
                </div>

            @endforelse

        </div>

        <div id="sinResultados" class="catalog-empty-state mt-5 d-none">
            <i class="bi bi-search"></i>
            <h3>No encontramos productos</h3>
            <p>Prueba con otro nombre, sabor, contenido o presentación.</p>
            <button type="button" id="limpiarFiltros2" class="btn btn-outline-success">
                Limpiar búsqueda
            </button>
        </div>

    </div>

</div>

@push('scripts')
@vite(['resources/js/catalogo.js'])
@endpush

@endsection
