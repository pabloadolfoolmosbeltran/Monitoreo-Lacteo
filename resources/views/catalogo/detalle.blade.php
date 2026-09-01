@extends('catalogo.layout')

@section('title', $presentacion->nombre)

@section('content')

<div class="container detalle-container py-4">

    {{-- VOLVER --}}
    <div class="mb-4">
        <a href="{{ route('catalogo.index') }}" class="volver">
            <i class="bi bi-arrow-left me-1"></i>
            Volver al catálogo
        </a>
    </div>


    {{-- PRODUCTO --}}
    <div class="card producto-detalle">

        <div class="row g-0">

            {{-- =====================================================
                 IMAGEN
            ====================================================== --}}
            <div class="col-lg-6">

                @if($presentacion->imagen_comercial)

                    <img
                        src="{{ asset('storage/' . $presentacion->imagen_comercial) }}"
                        class="imagen-producto"
                        alt="{{ $presentacion->nombre }}"
                    >

                @else

                    <div class="imagen-placeholder">
                        <i class="bi bi-image fs-1"></i>
                    </div>

                @endif

            </div>


            {{-- =====================================================
                 INFORMACIÓN PRINCIPAL
            ====================================================== --}}
            <div class="col-lg-6">

                <div class="p-4 p-lg-5">

                    {{-- PRODUCTO BASE --}}
                    <div class="tipo-producto mb-2">
                        {{ $presentacion->producto->nombre }}
                    </div>


                    {{-- NOMBRE --}}
                    <h1 class="titulo-producto mb-3">
                        {{ $presentacion->nombre }}
                    </h1>


                    {{-- INFORMACIÓN PRINCIPAL --}}
                    <div class="row g-2 mb-4">

                        {{-- CONTENIDO --}}
                        @if($presentacion->contenido)

                            <div class="col-6">

                                <div class="dato-principal">

                                    <div class="dato-titulo">
                                        <i class="bi bi-box"></i>
                                        Cantidad
                                    </div>

                                    <div class="dato-valor">
                                        {{ $presentacion->contenido }}
                                        {{ $presentacion->unidad }}
                                    </div>

                                </div>

                            </div>

                        @endif


                        {{-- SABOR --}}
                        @if($presentacion->sabor)

                            <div class="col-6">

                                <div class="dato-principal">

                                    <div class="dato-titulo">
                                        <i class="bi bi-stars"></i>
                                        Sabor
                                    </div>

                                    <div class="dato-valor">
                                        {{ $presentacion->sabor }}
                                    </div>

                                </div>

                            </div>

                        @endif


                        {{-- FRUTA --}}
                        @if(isset($presentacion->con_fruta))

                            <div class="col-6">

                                <div class="dato-principal">

                                    <div class="dato-titulo">
                                        <i class="bi bi-heart"></i>
                                        Preparación
                                    </div>

                                    <div class="dato-valor">

                                        @if($presentacion->con_fruta)
                                            Con fruta
                                        @else
                                            Sin fruta
                                        @endif

                                    </div>

                                </div>

                            </div>

                        @endif


                        {{-- ENVASE --}}
                        @if($presentacion->envase)

                            <div class="col-6">

                                <div class="dato-principal">

                                    <div class="dato-titulo">
                                        <i class="bi bi-bag"></i>
                                        Envase
                                    </div>

                                    <div class="dato-valor">
                                        {{ $presentacion->envase }}
                                    </div>

                                </div>

                            </div>

                        @endif

                    </div>


                    {{-- =================================================
                         PRECIO + STOCK
                    ================================================== --}}
                    <div class="precio-box mb-4">

                        <div class="row align-items-center">

                            <div class="col-6">

                                <small class="text-muted d-block">
                                    Precio
                                </small>

                                <div class="precio">
                                    Bs {{ number_format($presentacion->precio, 2) }}
                                </div>

                            </div>


                            <div class="col-6 text-end">

                                <small class="text-muted d-block">
                                    Disponibilidad
                                </small>

                                @if($presentacion->stock > 0)

                                    <div class="stock-disponible">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        {{ $presentacion->stock }} disponibles
                                    </div>

                                @else

                                    <div class="stock-agotado">
                                        <i class="bi bi-x-circle-fill me-1"></i>
                                        Sin stock
                                    </div>

                                @endif

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         DESCRIPCIÓN
                    ================================================== --}}
                    @if($presentacion->producto->descripcion)

                        <div class="mb-3">

                            <h5 class="fw-bold mb-2">
                                Sobre este producto
                            </h5>

                            <p class="text-muted mb-0">
                                {{ $presentacion->producto->descripcion }}
                            </p>

                        </div>

                    @endif


                    {{-- =================================================
                         DETALLES ADICIONALES
                    ================================================== --}}
                    <div class="detalle-extra">

                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-info-circle text-success me-1"></i>
                            Detalles del producto
                        </h5>

                        <div class="row g-2 small">

                            <div class="col-6">
                                <span class="text-muted d-block">Producto</span>
                                <strong>
                                    {{ $presentacion->producto->nombre }}
                                </strong>
                            </div>


                            <div class="col-6">
                                <span class="text-muted d-block">Presentación</span>
                                <strong>
                                    {{ $presentacion->nombre }}
                                </strong>
                            </div>


                            @if($presentacion->sabor)

                                <div class="col-6">
                                    <span class="text-muted d-block">Sabor</span>
                                    <strong>
                                        {{ $presentacion->sabor }}
                                    </strong>
                                </div>

                            @endif


                            @if($presentacion->envase)

                                <div class="col-6">
                                    <span class="text-muted d-block">Envase</span>
                                    <strong>
                                        {{ $presentacion->envase }}
                                    </strong>
                                </div>

                            @endif


                            @if($presentacion->contenido)

                                <div class="col-6">
                                    <span class="text-muted d-block">Contenido</span>
                                    <strong>
                                        {{ $presentacion->contenido }}
                                        {{ $presentacion->unidad }}
                                    </strong>
                                </div>

                            @endif


                            @if(isset($presentacion->con_fruta))

                                <div class="col-6">
                                    <span class="text-muted d-block">Fruta</span>
                                    <strong>
                                        {{ $presentacion->con_fruta ? 'Sí' : 'No' }}
                                    </strong>
                                </div>

                            @endif

                        </div>

                    </div>


                    {{-- =================================================
                         CONTACTO
                    ================================================== --}}
                    <div class="mt-4">

                        <p class="text-muted small mb-2">
                            ¿Quieres adquirir este producto?
                        </p>

                        <a
                            href="{{ route('catalogo.contacto') }}"
                            class="btn btn-success w-100"
                        >
                            <i class="bi bi-telephone me-1"></i>
                            Consultar disponibilidad y contacto
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
