@extends('layouts.app')


@section('content')
<div class="container custom-container pt-3">
    @if(session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif
    
    {{-- ENCABEZADO PRINCIPAL --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="mb-1 fw-bold" style="color: var(--primary-color);">
                <i class="bi bi-box-seam me-2"></i>{{ $eliminados ? 'Presentaciones Eliminadas' : 'Presentaciones Comerciales' }}
            </h2>
            <p class="mb-0" style="color: var(--text-muted);">
                {{ $eliminados ? 'Revise y restablezca presentaciones retiradas del catálogo.' : 'Administre las presentaciones disponibles en el catálogo público.' }}
            </p>
        </div>

        <div class="d-flex gap-2">
            @if($eliminados)
                <a href="{{ route('presentaciones.index') }}" data-full-navigation class="btn btn-secondary">← Volver a presentaciones</a>
            @else
                <a href="{{ route('presentaciones.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Nueva Presentación
                </a>
                @if(auth()->user()->rol === 'Administrador')
                    <a href="{{ route('presentaciones.index', ['estado' => 'eliminados']) }}" data-full-navigation class="btn btn-outline-secondary">
                        <i class="bi bi-archive me-1"></i> Mostrar eliminados
                    </a>
                @endif
            @endif
        </div>
    </div>

    <form method="GET" action="{{ route('presentaciones.index') }}" class="custom-card p-3 mb-3 presentation-search">
        @if($eliminados)
            <input type="hidden" name="estado" value="eliminados">
        @endif
        <label for="presentation-search" class="form-label fw-semibold">Buscar variedad de producto</label>
        <div class="d-flex flex-column flex-md-row gap-2">
            <input
                id="presentation-search"
                name="q"
                type="search"
                class="form-control"
                value="{{ $busqueda }}"
                maxlength="100"
                placeholder="Ej.: queso 1 kg, yogurt 500 g o leche 2 L">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i> Buscar
            </button>
            @if($busqueda !== '')
                <a href="{{ route('presentaciones.index', $eliminados ? ['estado' => 'eliminados'] : []) }}" class="btn btn-secondary">Limpiar</a>
            @endif
        </div>
        <small class="d-block mt-2" style="color: var(--text-muted);">
            Busca por producto, presentación, sabor, envase, cantidad o unidad: kg, g, L y ml.
        </small>
    </form>

    {{-- TABLA DE PRESENTACIONES --}}
    <div class="custom-card">
        
        <div class="d-flex justify-content-between align-items-center p-3" style="background-color: var(--secondary-beige); border-bottom: 1px solid var(--border-soft);">
            <div class="fw-bold" style="color: var(--primary-color);">
                <i class="bi bi-list-ul me-2"></i>{{ $eliminados ? 'Presentaciones Eliminadas' : 'Presentaciones Registradas' }}
            </div>
            <span class="badge badge-neutral px-3 py-2">
                {{ $presentaciones->total() }} {{ $presentaciones->total() == 1 ? 'registro' : 'registros' }}
            </span>
        </div>

        <div class="card-body p-0 overflow-hidden">
            <div class="table-responsive-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th class="text-center">N°</th>
                            <th>Imagen</th>
                            <th>Producto</th>
                            <th>Presentación</th>
                            <th>Detalles</th>
                            <th class="text-center">Precio</th>
                            <th class="text-center">Existencia disponible</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end" width="160">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($presentaciones as $presentacion)
                            @php
                                $imagenPresentacion = $presentacion->imagen_comercial ?: ($presentacion->producto?->imagen_referencial);
                            @endphp
                            <tr>
                                {{-- NUMERACIÓN --}}
                                <td class="text-center fw-semibold" style="color: var(--text-muted);">
                                    {{ $loop->iteration + ($presentaciones->currentPage() - 1) * $presentaciones->perPage() }}
                                </td>

                                {{-- IMAGEN --}}
                                <td>
                                    @if($imagenPresentacion)
                                        <img src="{{ asset('storage/' . $imagenPresentacion) }}"
                                             alt="{{ $presentacion->nombre }}"
                                             width="55"
                                             height="55"
                                             class="rounded border"
                                             style="object-fit: cover; border-color: var(--border-soft) !important;">
                                    @else
                                        <div class="rounded d-flex align-items-center justify-content-center border"
                                             style="width:55px; height:55px; background-color: var(--bg-main); border-color: var(--border-soft) !important;">
                                            <i class="bi bi-image fs-4" style="color: var(--text-muted);"></i>
                                        </div>
                                    @endif
                                </td>

                                {{-- PRODUCTO BASE --}}
                                <td>
                                    <strong>{{ $presentacion->producto?->nombre ?? 'Producto no disponible' }}</strong>
                                </td>

                                {{-- NOMBRE DE PRESENTACIÓN --}}
                                <td>
                                    <div class="fw-bold">{{ $presentacion->nombre }}</div>
                                    @if($presentacion->con_fruta)
                                        <span class="badge badge-neutral mt-1">
                                            Con fruta
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border mt-1">
                                            Sin fruta
                                        </span>
                                    @endif
                                </td>

                                {{-- DETALLES COMERCIALES --}}
                                <td>
                                    @if($presentacion->sabor)
                                        <div class="mb-1">
                                            <span style="color: var(--text-muted);">Sabor:</span> {{ $presentacion->sabor }}
                                        </div>
                                    @endif

                                    @if($presentacion->envase)
                                        <div class="mb-1">
                                            <span style="color: var(--text-muted);">Envase:</span> {{ $presentacion->envase }}
                                        </div>
                                    @endif

                                    @if($presentacion->contenido)
                                        <div>
                                            <span style="color: var(--text-muted);">Contenido:</span> {{ $presentacion->contenido }} {{ $presentacion->unidad }}
                                        </div>
                                    @endif

                                    @if(!$presentacion->sabor && !$presentacion->envase && !$presentacion->contenido)
                                        <span style="color: var(--text-muted);">Sin detalles</span>
                                    @endif
                                </td>

                                {{-- PRECIO --}}
                                <td class="text-center fw-bold">
                                    Bs {{ number_format($presentacion->precio, 2) }}
                                </td>

                                {{-- STOCK --}}
                                <td class="text-center">
                                    @if($presentacion->stock > 0)
                                        <span class="badge badge-dark-neutral px-3 py-2">
                                            {{ $presentacion->stock }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border px-3 py-2">
                                            AGOTADO
                                        </span>
                                    @endif
                                </td>

                                {{-- ESTADO --}}
                                <td class="text-center">
                                    @if($presentacion->activo)
                                        <span class="badge badge-neutral">
                                            Activo
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border">
                                            Inactivo
                                        </span>
                                    @endif
                                </td>

                                {{-- ACCIONES --}}
                                <td class="text-end action-buttons">
                                    @if($eliminados)
                                    <form method="POST" action="{{ route('presentaciones.restore', $presentacion) }}" class="d-inline" data-full-navigation>
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm px-2 py-1">Restablecer</button>
                                    </form>
                                    @else
                                    <a href="{{ route('presentaciones.edit', $presentacion) }}"
                                       class="btn btn-secondary btn-sm px-2 py-1 me-1"
                                       title="Editar presentación">
                                        Editar
                                    </a>

                                    @if(auth()->user()->rol == 'Administrador')
                                    <form method="POST"
                                          action="{{ route('presentaciones.destroy', $presentacion) }}"
                                          class="d-inline"
                                          data-full-navigation>
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-outline-danger btn-sm px-2 py-1"
                                                title="Eliminar presentación"
                                                data-confirm-message="¿Está seguro de eliminar esta presentación?">
                                            Eliminar
                                        </button>
                                    </form>
                                    @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5" style="color: var(--text-muted);">
                                    <i class="bi bi-box-seam fs-1 d-block mb-3"></i>
                                    <h5 class="fw-normal mb-2">{{ $busqueda !== '' ? 'No hay coincidencias' : ($eliminados ? 'No hay presentaciones eliminadas' : 'No hay presentaciones registradas') }}</h5>
                                    <p class="mb-3">{{ $busqueda !== '' ? 'Pruebe otro producto, cantidad o unidad.' : ($eliminados ? 'Las presentaciones eliminadas aparecerán aquí.' : 'Registre una nueva presentación para mostrarla en el catálogo.') }}</p>
                                    @unless($eliminados)
                                        <a href="{{ route('presentaciones.create') }}" class="btn btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i> Crear presentación
                                        </a>
                                    @endunless
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINACIÓN --}}
            @if($presentaciones->hasPages())
                <div class="border-top py-3 px-3 d-flex justify-content-center" style="background-color: var(--card-bg); border-color: var(--border-soft) !important;">
                    {{ $presentaciones->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
