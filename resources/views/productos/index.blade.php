@extends('layouts.app')

@push('styles')
@vite(['resources/css/productos.css'])
@endpush

@section('content')

<!-- Encabezado -->
<div class="dash-header">
    <h2 class="dash-title"><i class="bi bi-box-seam me-2"></i>Gestión de Productos</h2>
    <a href="{{ url('/productos/create') }}" class="btn btn-primary shadow-sm rounded-pill px-4 fw-semibold">
        <i class="bi bi-plus-lg me-1"></i> Nuevo Producto
    </a>
</div>

<!-- Alertas -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Buscador -->
<div class="search-card p-3 mb-4 shadow-sm">
    <form method="GET" class="row g-3 align-items-center">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input 
                    type="text" 
                    name="buscar" 
                    class="form-control border-start-0 ps-0" 
                    placeholder="Buscar producto por nombre..." 
                    value="{{ request('buscar') }}">
            </div>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-dark w-100 fw-semibold">
                Buscar
            </button>
        </div>
        <div class="col-md-2">
            <a href="{{ url('/productos') }}" class="btn btn-outline-secondary w-100 fw-semibold bg-white">
                <i class="bi bi-eraser me-1"></i> Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Tabla de Productos -->
<div class="card panel-card mb-4">
    <div class="card-header text-white">
        <i class="bi bi-list-ul me-2"></i>Lista de Productos Registrados
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0">
                <table class="table align-middle mb-0">
    <thead>
        <tr>
            <th class="ps-4">Nro</th>
            <th>Nombre</th>
            <th>Imagen</th>
            <th>Temp. Mínima</th>
            <th>Temp. Máxima</th>
            <th>Temp. Pasteurización</th>
            <th>Descripcion del producto</th>
            <th>Stock del cuajo</th>
            <th>Tipo de Cuajo</th>
            <th>Cuajo por Litro</th>
            <th>Estado</th>
            <th width="180" class="text-center">Acciones</th>
        </tr>
    </thead>
        <tbody>
            @php
                $i = 1; // 1. Inicializamos el contador antes del bucle
            @endphp

            @forelse($productos as $producto)
            <tr>
                <!-- Columna del número secuencial falso (Nro) -->
                <td class="ps-4 fw-semibold text-muted">
                    {{ $i }}
                </td>
                
                <td class="fw-bold" style="color: #1e293b;">
                    {{ $producto->nombre }}
                </td>
                <td>
                    @if($producto->imagen_referencial)
                        <img src="{{ asset('storage/' . $producto->imagen_referencial) }}" alt="{{ $producto->nombre }}" width="45" height="45" class="rounded object-fit-cover shadow-sm">
                    @else
                        <span class="text-muted small">Sin imagen</span>
                    @endif
                </td>
                <td>
                    <span class="badge bg-info text-dark bg-opacity-10 border border-info rounded-pill px-3 py-2">
                        <i class="bi bi-thermometer-snow text-info me-1"></i> {{ $producto->temperatura_minima }} °C
                    </span>
                </td>
                <td>
                    <span class="badge bg-danger text-danger bg-opacity-10 border border-danger rounded-pill px-3 py-2">
                        <i class="bi bi-thermometer-sun text-danger me-1"></i> {{ $producto->temperatura_maxima }} °C
                    </span>
                </td>
                <td>
                    <span class="badge bg-warning text-dark bg-opacity-10 border border-warning rounded-pill px-3 py-2">
                        <i class="bi bi-thermometer-half text-warning me-1"></i> {{ $producto->temperatura_pasteurizacion }} °C
                    </span>
                </td>

                <td>
                    <span class="badge text-dark bg-opacity-10">
                        <i class="bi me-1"></i> {{ $producto->descripcion }}
                    </span>
                </td>
                <td>
                    <span class="badge text-dark bg-opacity-10">
                        <i class="bi me-1"></i> {{ $producto->stock_cuajo }}
                    </span>
                </td>

                <td>
                    @if($producto->tipo_cuajo)
                        <span class="text-dark fw-semibold small">{{ $producto->tipo_cuajo }}</span>
                    @else
                        <span class="text-muted small">No especificado</span>
                    @endif
                </td>
                <td>    
                    @if($producto->cuajo_por_litro)
                        <span class="badge bg-secondary text-dark bg-opacity-10 border border-secondary rounded-pill px-3 py-2">
                            <i class="bi bi-droplet text-secondary me-1"></i> {{ $producto->cuajo_por_litro }} {{ $producto->unidad_cuajo ?? 'ml' }}/L
                        </span>
                    @else
                        <span class="text-muted small">N/A</span>
                    @endif
                </td>
                <td>
                    @if($producto->activo)
                        <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3">
                            <i class="bi bi-check-circle me-1"></i> Activo
                        </span>
                    @else
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary rounded-pill px-3">
                            <i class="bi bi-x-circle me-1"></i> Inactivo
                        </span>
                    @endif
                </td>
                <td class="text-center">
                    <a 
                        href="{{ url('/productos/'.$producto->id.'/edit') }}" 
                        class="btn btn-warning action-btn text-dark shadow-sm me-1"
                        title="Editar">
                        <i class="bi bi-pencil-square"></i>
                    </a>

                    @if(auth()->user()->rol == 'Administrador')
                    <form 
                        action="{{ url('/productos/'.$producto->id) }}" 
                        method="POST" 
                        class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button 
                            class="btn btn-danger action-btn shadow-sm" 
                            onclick="return confirm('¿Está seguro de eliminar este producto? Esta acción no se puede deshacer.')"
                            title="Eliminar">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                    @endif
                </td>
            </tr>

            @php
                $i++; // 2. Incrementamos el contador al finalizar cada vuelta
            @endphp

            @empty
            <tr>
                <td colspan="12" class="text-center py-5">
                    <div class="text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No existen productos registrados en este momento.
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
        </div>
    </div>
    
    <!-- Paginación -->
    @if($productos->hasPages())
    <div class="card-footer bg-white border-top py-3 d-flex justify-content-center">
        {{ $productos->links() }}
    </div>
    @endif
</div>

@push('scripts')
@vite(['resources/js/productos.js'])
@endpush
@endsection