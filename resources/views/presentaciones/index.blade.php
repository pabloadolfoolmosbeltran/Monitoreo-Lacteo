@extends('layouts.app')

@push('styles')
@vite(['resources/css/presentaciones.css'])
@endpush

@section('content')
<div class="container custom-container pt-3">
    
    {{-- ENCABEZADO PRINCIPAL --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="mb-1 fw-bold" style="color: var(--primary-color);">
                <i class="bi bi-box-seam me-2"></i>Presentaciones Comerciales
            </h2>
            <p class="mb-0" style="color: var(--text-muted);">
                Administre las presentaciones disponibles en el catálogo público.
            </p>
        </div>

        <a href="{{ route('presentaciones.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nueva Presentación
        </a>
    </div>

    {{-- TABLA DE PRESENTACIONES --}}
    <div class="custom-card">
        
        <div class="d-flex justify-content-between align-items-center p-3" style="background-color: var(--secondary-beige); border-bottom: 1px solid var(--border-soft);">
            <div class="fw-bold" style="color: var(--primary-color);">
                <i class="bi bi-list-ul me-2"></i>Presentaciones Registradas
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
                            <th class="text-center">Stock</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end" width="160">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($presentaciones as $presentacion)
                            <tr>
                                {{-- NUMERACIÓN --}}
                                <td class="text-center fw-semibold" style="color: var(--text-muted);">
                                    {{ $loop->iteration + ($presentaciones->currentPage() - 1) * $presentaciones->perPage() }}
                                </td>

                                {{-- IMAGEN --}}
                                <td>
                                    @if($presentacion->imagen_comercial)
                                        <img src="{{ asset('storage/' . $presentacion->imagen_comercial) }}"
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
                                    <strong>{{ $presentacion->producto->nombre }}</strong>
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
                                            0
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
                                    <a href="{{ route('presentaciones.edit', $presentacion) }}"
                                       class="btn btn-secondary btn-sm px-2 py-1 me-1"
                                       title="Editar presentación">
                                        Editar
                                    </a>

                                    @if(auth()->user()->rol == 'Administrador')
                                    <form method="POST"
                                          action="{{ route('presentaciones.destroy', $presentacion) }}"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-outline-danger btn-sm px-2 py-1"
                                                title="Eliminar presentación"
                                                onclick="return confirm('¿Está seguro de eliminar esta presentación?')">
                                            Eliminar
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5" style="color: var(--text-muted);">
                                    <i class="bi bi-box-seam fs-1 d-block mb-3"></i>
                                    <h5 class="fw-normal mb-2">No hay presentaciones registradas</h5>
                                    <p class="mb-3">Registre una nueva presentación para mostrarla en el catálogo.</p>
                                    <a href="{{ route('presentaciones.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i> Crear presentación
                                    </a>
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

@push('scripts')
@vite(['resources/js/presentaciones.js'])
@endpush
@endsection