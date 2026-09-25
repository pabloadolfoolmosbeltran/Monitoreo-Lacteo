@extends('layouts.app')


@section('content')

<!-- Encabezado -->
<div class="dash-header">
    <h2 class="dash-title"><i class="bi bi-box-seam me-2"></i>{{ $eliminados ? 'Productos Eliminados' : 'Gestión de Productos' }}</h2>
    <div class="d-flex gap-2">
        @if($eliminados)
            <a href="{{ url('/productos') }}" data-full-navigation class="btn btn-outline-secondary shadow-sm rounded-pill px-4 fw-semibold">← Volver a productos</a>
        @else
            <a href="{{ url('/productos/create') }}" class="btn btn-primary shadow-sm rounded-pill px-4 fw-semibold">
                <i class="bi bi-plus-lg me-1"></i> Nuevo Producto
            </a>
            @if(auth()->user()->rol === 'Administrador')
                <a href="{{ url('/productos?estado=eliminados') }}" data-full-navigation class="btn btn-outline-secondary shadow-sm rounded-pill px-4 fw-semibold">
                    <i class="bi bi-archive me-1"></i> Mostrar eliminados
                </a>
            @endif
        @endif
    </div>
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
        @if($eliminados)
            <input type="hidden" name="estado" value="eliminados">
        @endif
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
            <a href="{{ $eliminados ? url('/productos?estado=eliminados') : url('/productos') }}" class="btn btn-outline-secondary w-100 fw-semibold bg-white">
                <i class="bi bi-eraser me-1"></i> Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Tabla de Productos -->
<div class="card panel-card mb-4">
    <div class="card-header text-white">
        <i class="bi bi-list-ul me-2"></i>{{ $eliminados ? 'Lista de Productos Eliminados' : 'Lista de Productos Registrados' }}
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-custom align-middle mb-0">
    <thead>
        <tr>
            <th class="ps-4">Nro</th>
            <th>Nombre</th>
            <th>Temp. Minima Y Maxima Para el cuajo</th>
            <th>Temp. Pasteurización</th>
            <th>Tipo de Cuajo</th>
            <th>Recomendaciones de Cuajo</th>
            <th>Estado</th>
            <th width="180" class="text-center">Acciones</th>
        </tr>
    </thead>
        <tbody>
            @forelse($productos as $producto)
            <tr>
                <!-- Columna del número secuencial falso (Nro) -->
                <td class="ps-4 fw-semibold text-muted">
                    {{ $productos->firstItem() + $loop->index }}
                </td>
                
                <td class="fw-bold" style="color: #1e293b;">
                    {{ $producto->nombre }}
                </td>
                <td>
                    <span class="badge bg-danger text-danger bg-opacity-10 border border-danger rounded-pill px-3 py-2">
                        <i class="bi bi-thermometer-sun text-danger me-1"></i> {{ $producto->temperatura_maxima }} °C
                    </span>
                    <small>
                        <span class="badge bg-info text-dark bg-opacity-10 border border-info rounded-pill px-3 py-2">
                        <i class="bi bi-thermometer-snow text-info me-1"></i> {{ $producto->temperatura_minima }} °C
                    </span>
                    </small>
                </td>
                <td>
                    <span class="badge bg-warning text-dark bg-opacity-10 border border-warning rounded-pill px-3 py-2">
                        <i class="bi bi-thermometer-half text-warning me-1"></i> {{ $producto->temperatura_pasteurizacion }} °C
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
                        <div class="d-flex flex-column gap-1 small">
                            @foreach([1, 5, 10] as $litros)
                                <span>{{ $litros }} L: {{ number_format($producto->cuajo_por_litro * $litros, 2, ',', '.') }} {{ $producto->unidad_cuajo ?? 'ml' }}</span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted small">No configurado</span>
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
                    @if($eliminados)
                    <form action="{{ route('productos.restore', $producto) }}" method="POST" class="d-inline" data-full-navigation>
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success btn-sm fw-semibold">Restablecer</button>
                    </form>
                    @else
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
                        class="d-inline"
                        data-full-navigation>
                        @csrf
                        @method('DELETE')
                        <button 
                            class="btn btn-danger action-btn shadow-sm" 
                            data-confirm-message="¿Está seguro de eliminar este producto? Esta acción no se puede deshacer."
                            title="Eliminar">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                    @endif
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center py-5">
                    <div class="text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        {{ $eliminados ? 'No hay productos eliminados.' : 'No existen productos registrados en este momento.' }}
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

@endsection
