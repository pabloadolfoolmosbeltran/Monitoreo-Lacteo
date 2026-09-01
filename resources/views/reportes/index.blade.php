@extends('layouts.app')

@section('content')
<div class="container py-3">
    <!-- Encabezado de la vista -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1">Historial de Producciones</h2>
            <p class="text-secondary mb-0">Consulta, exporta y administra el historial completo de producciones realizadas.</p>
        </div>
        <a href="{{ route('reportes.excel') }}" class="btn btn-success rounded-pill d-flex align-items-center gap-2 shadow-sm px-4 py-2 fw-medium">
            <i class="bi bi-file-earmark-excel-fill"></i> Exportar Todo a Excel
        </a>
    </div>

    <!-- Contenedor de la tabla -->
    <div class="card shadow border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle border-top-0">
                    <thead class="table-light text-secondary text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-4 py-3" style="width: 8%">ID</th>
                            <th class="py-3">Operador</th>
                            <th class="py-3">Rol</th>
                            <th class="py-3">Unidad Productiva</th>
                            <th class="py-3">Producto</th>
                            <th class="py-3">Estado</th>
                            <th class="py-3">Inicio</th>
                            <th class="text-center py-3" style="width: 15%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                    @foreach($producciones as $produccion)
                        <tr>
                            <td class="ps-4 py-3">
                                <span class="text-primary fw-bold">{{ $produccion->id }}</span>
                            </td>

                            <td class="py-3">
                                <span class="fw-semibold text-dark">
                                    {{ $produccion->user?->name ?? 'Sistema' }}
                                </span>
                            </td>

                            <td class="py-3">
                                @if($produccion->user)
                                    @if($produccion->user->rol === 'Administrador')
                                        <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2">Administrador</span>
                                    @else
                                        <span class="badge rounded-pill bg-info bg-opacity-10 text-info-emphasis border border-info-subtle px-3 py-2">Trabajador</span>
                                    @endif
                                @else
                                    <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-3 py-2">Sistema</span>
                                @endif
                            </td>

                            <td class="py-3 text-muted fw-medium">
                                {{ $produccion->user?->nombre_unidad_productiva ?? '-' }}
                            </td>

                            <td class="py-3 text-dark fw-medium">
                                {{ $produccion->producto?->nombre ?? '-' }}
                            </td>

                            <td class="py-3">
                                @if($produccion->estado === 'En proceso')
                                    <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning-emphasis border border-warning-subtle px-3 py-2">
                                        <i class="bi bi-arrow-repeat me-1"></i> En proceso
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success-emphasis border border-success-subtle px-3 py-2">
                                        <i class="bi bi-check-circle me-1"></i> Finalizada
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 text-muted">
                                <i class="bi bi-calendar-event me-1"></i> 
                                {{ $produccion->fecha_inicio ? $produccion->fecha_inicio->format('d/m/Y H:i') : '-' }}
                            </td>

                            <td class="text-center py-3">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ url('/reportes/'.$produccion->id) }}" class="btn btn-outline-primary btn-sm rounded-3 px-3">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                    <a href="{{ url('/reportes/'.$produccion->id.'/pdf') }}" class="btn btn-outline-danger btn-sm rounded-3 px-3">
                                        <i class="bi bi-file-earmark-pdf"></i> PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection