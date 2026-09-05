@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="bi bi-receipt-cutoff me-2"></i>Consignaciones y Ventas
            </h2>
            <p class="text-muted mb-0">Control de entregas, stock consignado y liquidaciones.</p>
        </div>
        <a href="{{ route('consignaciones.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nueva consignación
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Productor</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="buscar" class="form-control" value="{{ request('buscar') }}" placeholder="Nombre o unidad productiva">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        @foreach($estados as $estado)
                            <option value="{{ $estado }}" @selected(request('estado') === $estado)>
                                {{ ucfirst($estado) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-dark flex-fill" type="submit">
                        <i class="bi bi-funnel me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('consignaciones.index') }}" class="btn btn-secondary" title="Limpiar filtros">
                        <i class="bi bi-eraser"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-list-ul me-2"></i>Registros</span>
            <span class="badge bg-light text-dark border">{{ $consignaciones->total() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Productor</th>
                        <th>Unidad productiva</th>
                        <th class="text-center">Ítems</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consignaciones as $consignacion)
                        @php
                            $badge = [
                                'abierta' => 'success',
                                'liquidada' => 'primary',
                                'devuelta' => 'secondary',
                            ][$consignacion->estado] ?? 'secondary';
                        @endphp
                        <tr>
                            <td>{{ $consignacion->fecha_entrada->format('d/m/Y') }}</td>
                            <td class="fw-semibold">{{ $consignacion->productor->name }}</td>
                            <td>{{ $consignacion->productor->nombre_unidad_productiva ?? 'Sin registro' }}</td>
                            <td class="text-center">{{ $consignacion->items_count }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $badge }}">{{ ucfirst($consignacion->estado) }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('consignaciones.show', $consignacion) }}" class="btn btn-info btn-sm" title="Ver detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No hay consignaciones registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($consignaciones->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center">
                {{ $consignaciones->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
