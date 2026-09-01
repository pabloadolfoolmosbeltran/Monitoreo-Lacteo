@extends('catalogo.layout')

@section('title', 'Productos disponibles')

@section('content')

<div class="container py-5 catalogo-wrap">

    {{-- ============================================================
         ENCABEZADO
    ============================================================= --}}
    <section class="pil-hero p-4 p-md-5 mb-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <p class="text-uppercase small fw-bold eyebrow mb-2">
                    Directo de productores locales
                </p>

                <h1 class="display-6 mb-3">
                    Productos lácteos disponibles
                </h1>

                <p class="mb-0" style="color:#DDEFE3;">
                    Explora sabores, presentaciones y tamaños. Toca cada tarjeta
                    para ver el detalle completo.
                </p>
            </div>

            <div class="col-lg-4 text-lg-end text-center">
                <i class="bi bi-basket2-fill" style="font-size: 4.5rem; opacity:.9;"></i>
            </div>
        </div>
    </section>


    {{-- ============================================================
         ENCABEZADO DEL CATÁLOGO
    ============================================================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 fw-bold mb-1">
                Catálogo de productos
            </h2>
            <p class="text-muted mb-0 small">
                Encuentra rápidamente la presentación que buscas.
            </p>
        </div>

        <span class="badge text-success border px-3 py-2">
            {{ $presentaciones->count() }}
            {{ $presentaciones->count() === 1 ? 'producto' : 'productos' }}
        </span>
    </div>


    {{-- ============================================================
         BUSCADOR Y FILTROS
    ============================================================= --}}
    <div class="card filtros-card shadow-sm mb-5">
        <div class="card-body p-4">

            <div class="row g-3">

                {{-- BUSCADOR GENERAL --}}
                <div class="col-lg-6">
                    <label for="buscadorCatalogo" class="form-label fw-semibold small">
                        Buscar producto
                    </label>

                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-success"></i>
                        </span>
                        <input
                            type="search"
                            id="buscadorCatalogo"
                            class="form-control border-start-0"
                            placeholder="Ej.: yogurt, frutilla, 1 litro, botella..."
                        >
                    </div>

                    <small class="text-muted">
                        Busca por nombre, producto, sabor, envase, contenido o unidad.
                    </small>
                </div>

                {{-- PRODUCTO --}}
                <div class="col-md-4 col-lg-2">
                    <label for="filtroProducto" class="form-label fw-semibold small">Producto</label>
                    <select id="filtroProducto" class="form-select">
                        <option value="">Todos</option>
                        @foreach($presentaciones->pluck('producto.nombre')->filter()->unique()->sort() as $productoNombre)
                            <option value="{{ Str::lower($productoNombre) }}">{{ $productoNombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- SABOR --}}
                <div class="col-md-4 col-lg-2">
                    <label for="filtroSabor" class="form-label fw-semibold small">Sabor</label>
                    <select id="filtroSabor" class="form-select">
                        <option value="">Todos</option>
                        @foreach($presentaciones->pluck('sabor')->filter()->unique()->sort() as $sabor)
                            <option value="{{ Str::lower($sabor) }}">{{ $sabor }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- UNIDAD --}}
                <div class="col-md-4 col-lg-2">
                    <label for="filtroUnidad" class="form-label fw-semibold small">Presentación</label>
                    <select id="filtroUnidad" class="form-select">
                        <option value="">Todas</option>
                        @foreach($presentaciones->pluck('unidad')->filter()->unique()->sort() as $unidad)
                            <option value="{{ Str::lower($unidad) }}">{{ $unidad }}</option>
                        @endforeach
                    </select>
                </div>

            </div>

            {{-- CHIPS: DISPONIBILIDAD --}}
            <div class="d-flex flex-wrap align-items-center gap-2 mt-4">
                <span class="small text-muted fw-semibold me-1">Disponibilidad:</span>

                <button type="button" class="btn-chip filtro-stock active" data-stock="">
                    Todos
                </button>
                <button type="button" class="btn-chip filtro-stock" data-stock="disponible">
                    <i class="bi bi-check-circle me-1"></i>Disponibles
                </button>
                <button type="button" class="btn-chip filtro-stock" data-stock="agotado">
                    <i class="bi bi-x-circle me-1"></i>Agotados
                </button>
            </div>

            {{-- CHIPS: CON FRUTA --}}
            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <span class="small text-muted fw-semibold me-1">Con fruta:</span>

                <button type="button" class="btn-chip filtro-fruta active-fruta" data-fruta="">
                    Todos
                </button>
                <button type="button" class="btn-chip filtro-fruta" data-fruta="si">
                    <i class="bi bi-egg-fried me-1"></i>Con fruta
                </button>
                <button type="button" class="btn-chip filtro-fruta" data-fruta="no">
                    <i class="bi bi-dash-circle me-1"></i>Sin fruta
                </button>

                <button type="button" id="limpiarFiltros" class="btn-chip ms-auto">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Limpiar filtros
                </button>
            </div>

        </div>
    </div>


    {{-- ============================================================
         RESULTADOS
    ============================================================= --}}
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4" id="productosCatalogo">

        @forelse($presentaciones as $presentacion)

            @php
                $nombreProducto = $presentacion->producto->nombre ?? '';
                $nombrePresentacion = $presentacion->nombre ?? '';
                $sabor = $presentacion->sabor ?? '';
                $envase = $presentacion->envase ?? '';
                $contenido = $presentacion->contenido ?? '';
                $unidad = $presentacion->unidad ?? '';
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
                <article class="card product-card-slim h-100 shadow-sm">

                    {{-- IMAGEN --}}
                    <div class="position-relative">
                        @if($presentacion->imagen_comercial)
                            <img
                                src="{{ asset('storage/' . $presentacion->imagen_comercial) }}"
                                class="product-image-slim"
                                alt="{{ $presentacion->nombre }}"
                                loading="lazy"
                            >
                        @else
                            <div class="product-image-slim placeholder">
                                <i class="bi bi-image fs-2"></i>
                            </div>
                        @endif

                        <div class="position-absolute top-0 end-0 p-2">
                            @if($stockDisponible)
                                <span class="badge bg-success badge-disp shadow-sm">Disponible</span>
                            @else
                                <span class="badge bg-secondary badge-disp shadow-sm">Agotado</span>
                            @endif
                        </div>
                    </div>

                    {{-- INFO PRINCIPAL (mínima) --}}
                    <div class="card-slim-body">

                        <p class="eyebrow-producto mb-1">{{ $nombreProducto }}</p>
                        <h3>{{ $nombrePresentacion }}</h3>

                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="precio-slim">Bs {{ number_format($presentacion->precio, 2) }}</span>

                            @if($conFruta === true)
                                <span class="badge badge-fruta">Con fruta</span>
                            @endif
                        </div>

                        {{-- BOTÓN VER MÁS --}}
                        <button
                            class="toggle-detalle"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#{{ $collapseId }}"
                            aria-expanded="false"
                            aria-controls="{{ $collapseId }}"
                        >
                            Ver detalle <i class="bi bi-chevron-down"></i>
                        </button>

                        {{-- DETALLE COLAPSABLE --}}
                        <div class="collapse" id="{{ $collapseId }}">
                            <div class="detalle-extra">

                                @if($sabor)
                                    <div class="fila mb-1"><i class="bi bi-stars me-1"></i><strong>Sabor:</strong> {{ $sabor }}</div>
                                @endif

                                @if($envase)
                                    <div class="fila mb-1"><i class="bi bi-box-seam me-1"></i><strong>Envase:</strong> {{ $envase }}</div>
                                @endif

                                @if($contenido)
                                    <div class="fila mb-1"><i class="bi bi-rulers me-1"></i><strong>Contenido:</strong> {{ $contenido }} {{ $unidad }}</div>
                                @endif

                                <div class="fila mb-2">
                                    <i class="bi bi-boxes me-1"></i><strong>Stock:</strong>
                                    {{ $stockDisponible ? $presentacion->stock . ' disponibles' : 'Agotado' }}
                                </div>

                                <p class="text-muted mb-3">
                                    {{ Str::limit($presentacion->producto->descripcion ?? 'Producto lácteo', 110) }}
                                </p>

                                <a href="{{ route('catalogo.detalle', $presentacion) }}" class="btn btn-success btn-sm w-100 rounded-3">
                                    <i class="bi bi-eye me-1"></i>Ver producto
                                </a>
                            </div>
                        </div>

                    </div>

                </article>
            </div>

        @empty

            <div class="col-12">
                <div class="alert alert-light border text-center py-5 rounded-4">
                    <i class="bi bi-box-seam fs-1 text-muted d-block mb-3"></i>
                    <h4>No hay productos disponibles</h4>
                    <p class="text-muted mb-0">Actualmente no existen presentaciones registradas.</p>
                </div>
            </div>

        @endforelse

    </div>

    {{-- SIN RESULTADOS --}}
    <div id="sinResultados" class="alert alert-light border text-center py-5 mt-5 rounded-4 d-none">
        <i class="bi bi-search fs-1 text-muted d-block mb-3"></i>
        <h4>No encontramos productos</h4>
        <p class="text-muted mb-3">Prueba con otro nombre, sabor, contenido o presentación.</p>
        <button type="button" id="limpiarFiltros2" class="btn btn-outline-success">
            Limpiar búsqueda
        </button>
    </div>

</div>


@push('scripts')
@vite(['resources/js/catalogo.js'])
@endpush

@endsection