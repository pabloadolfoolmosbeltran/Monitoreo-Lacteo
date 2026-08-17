@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Historial de Producciones</h2>
            <p class="text-muted mb-0">Consulta, exporta y administra el historial completo de producciones realizadas.</p>
        </div>
        <a href="{{ route('reportes.excel') }}" class="btn btn-success d-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-file-earmark-excel-fill"></i> Exportar Todo a Excel
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3" style="width: 7%">ID</th>
                            <th>Operador</th>
                            <th>Rol</th>
                            <th>Unidad Productiva</th>
                            <th>Producto</th>
                            <th>Estado</th>
                            <th>Inicio</th>
                            <th class="text-center" style="width: 15%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($producciones as $produccion)
                        <tr>
                            <td class="ps-3"><strong>#{{ $produccion->id }}</strong></td>

                            <td>
                                <strong>
                                    {{ $produccion->user?->name ?? 'Sistema' }}
                                </strong>
                            </td>

                            <td>
                                @if($produccion->user)
                                    @if($produccion->user->rol === 'Administrador')
                                        <span class="badge bg-primary">Administrador</span>
                                    @else
                                        <span class="badge bg-success">Trabajador</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Sistema</span>
                                @endif
                            </td>

                            <td>
                                {{ $produccion->user?->nombre_unidad_productiva ?? '-' }}
                            </td>

                            <td>
                                {{ $produccion->producto?->nombre ?? '-' }}
                            </td>

                            <td>
                                @if($produccion->estado === 'En proceso')
                                    <span class="badge bg-warning text-dark px-2 py-1">En proceso</span>
                                @else
                                    <span class="badge bg-success px-2 py-1">Finalizada</span>
                                @endif
                            </td>

                            <td>
                                {{ $produccion->fecha_inicio ? $produccion->fecha_inicio->format('d/m/Y H:i') : '-' }}
                            </td>

                            <td class="text-center">
                                <a href="{{ url('/reportes/'.$produccion->id) }}" class="btn btn-primary btn-sm me-1">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                <a href="{{ url('/reportes/'.$produccion->id.'/pdf') }}" class="btn btn-danger btn-sm">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </a>
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