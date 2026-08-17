@extends('layouts.app')

@section('content')
<div class="container pb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Reporte Detallado de Producción</h2>
        <a href="{{ url('/reportes') }}" class="btn btn-secondary">
            ⬅ Volver al Listado
        </a>
    </div>
    <a
        href="{{ url('/reportes/'.$produccion->id.'/pdf') }}"
        class="btn btn-danger">

        Descargar PDF

        </a>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">📋 Información General del Lote</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Producción ID:</strong> <span class="badge bg-dark">#{{ $produccion->id }}</span></p>
                    <!-- Código sugerido -->
                    <p>
                        <strong>Productor:</strong> 
                        {{ $produccion->user?->nombre_unidad_productiva ?? $produccion->user?->name ?? '-' }}
                    </p>
                    <p><strong>Producto a Elaborar:</strong> {{ $produccion->producto->nombre }}</p>
                    <p><strong>Estado del Lote:</strong>
                        <span class="badge bg-{{ $produccion->estado == 'En proceso' ? 'warning text-dark' : 'success' }}">
                            {{ $produccion->estado }}
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Cantidad de Leche:</strong> {{ number_format($produccion->cantidad_leche, 2) }} Litros</p>
                    <p><strong>Temperatura Objetivo:</strong> {{ number_format($produccion->temperatura_objetivo, 2) }} °C</p>
                    <p><strong>Fecha de Inicio:</strong> {{ $produccion->fecha_inicio ? $produccion->fecha_inicio->format('d/m/Y H:i:s') : '-' }}</p>
                    <p><strong>Fecha de Fin:</strong> {{ $produccion->fecha_fin ? $produccion->fecha_fin->format('d/m/Y H:i:s') : 'Producción Activa' }}</p>
                    <p><strong>Duración Total:</strong> <span class="badge bg-info text-dark fs-6">{{ $duracion }}</span></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h4 class="mb-0">🌡️ Resumen Métrico de Temperaturas</h4>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="border rounded p-3 bg-light">
                        <span class="text-muted small d-block">MÍNIMA</span>
                        <span class="fs-4 fw-bold text-success">{{ $temperaturaMinima ?? '0.00' }} °C</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3 bg-light">
                        <span class="text-muted small d-block">MÁXIMA</span>
                        <span class="fs-4 fw-bold text-danger">{{ $temperaturaMaxima ?? '0.00' }} °C</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3 bg-light">
                        <span class="text-muted small d-block">PROMEDIO</span>
                        <span class="fs-4 fw-bold text-primary">{{ $temperaturaPromedio ?? '0.00' }} °C</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3 bg-light">
                        <span class="text-muted small d-block">TOTAL LECTURAS</span>
                        <span class="fs-4 fw-bold text-dark">{{ $produccion->lecturas->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h4 class="mb-0">⚠️ Alertas Registradas durante el Proceso</h4>
        </div>
        <div class="card-body">
            @if($produccion->alertas->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-danger">
                            <tr>
                                <th>Tipo de Alerta</th>
                                <th>Mensaje Detallado</th>
                                <th class="text-center">Atendida</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($produccion->alertas as $alerta)
                            <tr>
                                <td><span class="badge bg-danger">{{ $alerta->tipo }}</span></td>
                                <td>{{ $alerta->mensaje }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $alerta->atendida ? 'success' : 'secondary' }}">
                                        {{ $alerta->atendida ? 'Sí' : 'No' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-3 text-muted">
                    <p class="mb-0">✅ Excelente: No se registraron anomalías ni alertas en esta producción.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h4 class="mb-0">📜 Historial Cronológico de Eventos (Bitácora del Lote)</h4>
        </div>
        <div class="card-body">
            @if($produccion->eventos->count())
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Elemento / Acción</th>
                                <th>Descripción del Evento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($produccion->eventos as $evento)
                            <tr>
                                <td class="text-muted small">
                                    {{ $evento->fecha_hora->format('d/m/Y H:i:s') }}
                                </td>
                                <td>
                                    @php
                                        $color = match($evento->tipo) {
                                            'Producción' => 'info text-dark',
                                            'Motor' => 'primary',
                                            'Ventilador' => 'success',
                                            'Alerta' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ $evento->tipo }}</span>
                                </td>
                                <td><strong>{{ $evento->descripcion }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">No se encontraron eventos registrados para este lote.</p>
            @endif
        </div>
    </div>

</div>
@endsection