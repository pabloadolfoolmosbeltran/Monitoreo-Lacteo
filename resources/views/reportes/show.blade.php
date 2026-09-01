@extends('layouts.app')

@section('content')
<style>
    :root {
        --app-bg: #f0fdfa;        
        --card-bg: #ffffff;
        --primary-cyan: #008080;   
        --primary-teal: #006666;   
        --sky-blue: #0ea5e9;      
        --text-dark: #1e293b;     
        --text-muted: #64748b;    
        --border-soft: #b2dfdb;    
        --shadow-sm: 0 4px 6px -1px rgba(0, 128, 128, 0.1), 0 2px 4px -1px rgba(0, 128, 128, 0.06);
        --shadow-md: 0 10px 15px -3px rgba(0, 128, 128, 0.1), 0 4px 6px -2px rgba(0, 128, 128, 0.05);
    }

    .report-wrapper {
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        color: var(--text-dark);
    }

    .pretty-card {
        background-color: var(--card-bg);
        border: none;
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .pretty-card:hover {
        box-shadow: var(--shadow-md);
    }

    .pretty-card-header {
        background: linear-gradient(135deg, var(--primary-cyan) 0%, var(--primary-teal) 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        border-bottom: none;
    }

    .pretty-card-title {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 600;
        letter-spacing: 0.3px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .pretty-card-body {
        padding: 1.75rem;
    }

    .data-group {
        margin-bottom: 1.2rem;
    }

    .data-label {
        color: var(--primary-teal);
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.3rem;
        display: block;
    }

    .data-value {
        color: var(--text-dark);
        font-size: 1.05rem;
        font-weight: 500;
    }

    .metric-box {
        background: #f8fafc;
        border: 2px solid var(--border-soft);
        border-radius: 12px;
        padding: 1.5rem;
        height: 100%;
        transition: all 0.3s ease;
    }

    .metric-box:hover {
        border-color: var(--primary-cyan);
        background: #f0fdfa;
        transform: translateY(-3px);
    }

    .metric-label {
        color: var(--text-muted);
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .metric-value {
        font-size: 1.8rem;
        font-weight: 800;
        margin-top: 0.5rem;
        display: block;
        background: linear-gradient(135deg, var(--sky-blue), var(--primary-teal));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .pretty-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .pretty-table th {
        background-color: #f0f9ff;
        color: var(--sky-blue);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        padding: 1rem;
        border-bottom: 2px solid var(--border-soft);
    }

    .pretty-table td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        color: var(--text-dark);
        font-size: 0.95rem;
    }

    .pretty-table tbody tr:hover td {
        background-color: #f8fafc;
    }

    .btn-celeste {
        background: var(--card-bg);
        color: var(--primary-cyan);
        border: 2px solid var(--primary-cyan);
        border-radius: 50px;
        padding: 0.5rem 1.25rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-celeste:hover {
        background: var(--primary-cyan);
        color: white;
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
    }

    .btn-turquesa {
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal));
        color: white;
        border: none;
        border-radius: 50px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(20, 184, 166, 0.3);
    }

    .btn-turquesa:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(20, 184, 166, 0.4);
        color: white;
    }
</style>

<div class="container pb-5 report-wrapper pt-3">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h2 class="fw-bold m-0" style="color: var(--text-dark);">
            <span style="color: var(--primary-teal);">💧</span> Reporte Detallado de Producción
        </h2>
        <div class="d-flex gap-2">
            <a href="{{ url('/reportes') }}" class="btn-celeste">
                ⬅ Volver al Listado
            </a>
            <a href="{{ url('/reportes/'.$produccion->id.'/pdf') }}" class="btn-turquesa">
                📄 Descargar PDF
            </a>
        </div>
    </div>
    
    <!-- Panel 1: Información General -->
    <div class="pretty-card">
        <div class="pretty-card-header">
            <h4 class="pretty-card-title">📋 Información General del Lote</h4>
        </div>
        <div class="pretty-card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="data-group">
                        <span class="data-label">Producción ID</span>
                        <div class="data-value"><span class="badge bg-dark">#{{ $produccion->id }}</span></div>
                    </div>
                    
                    <div class="data-group">
                        <span class="data-label">Productor</span>
                        <div class="data-value">{{ $produccion->user?->nombre_unidad_productiva ?? $produccion->user?->name ?? '-' }}</div>
                    </div>
                    
                    <div class="data-group">
                        <span class="data-label">Producto a Elaborar</span>
                        <div class="data-value fw-bold" style="color: var(--primary-cyan);">{{ $produccion->producto->nombre }}</div>
                    </div>

                    <div class="data-group">
                        <span class="data-label">Tipo de Cuajo Utilizado</span>
                        <div class="data-value fw-semibold text-dark">{{ $produccion->tipo_cuajo ?? 'No especificado' }}</div>
                    </div>
                    
                    <div class="data-group">
                        <span class="data-label">Estado del Lote</span>
                        <div class="data-value">
                            <span class="badge bg-{{ $produccion->estado == 'En proceso' ? 'warning text-dark' : 'success' }}">
                                {{ $produccion->estado }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="data-group">
                        <span class="data-label">Cantidad de Leche</span>
                        <div class="data-value">{{ number_format($produccion->cantidad_leche, 2) }} Litros</div>
                    </div>
                    
                    <div class="data-group">
                        <span class="data-label">Temperatura Objetivo</span>
                        <div class="data-value">{{ number_format($produccion->temperatura_objetivo, 2) }} °C</div>
                    </div>

                    <div class="data-group">
                        <span class="data-label">Cantidad de Cuajo</span>
                        <div class="data-value">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2 fs-6">
                                {{ isset($produccion->cantidad_cuajo) ? number_format($produccion->cantidad_cuajo, 2) . ' ml / g' : 'No especificado' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="data-group">
                        <span class="data-label">Fecha de Inicio</span>
                        <div class="data-value">{{ $produccion->fecha_inicio ? $produccion->fecha_inicio->format('d/m/Y H:i:s') : '-' }}</div>
                    </div>
                    
                    <div class="data-group">
                        <span class="data-label">Fecha de Fin</span>
                        <div class="data-value">{{ $produccion->fecha_fin ? $produccion->fecha_fin->format('d/m/Y H:i:s') : 'Producción Activa' }}</div>
                    </div>
                    
                    <div class="data-group mb-0">
                        <span class="data-label">Duración Total</span>
                        <div class="data-value">
                            <span class="badge bg-info text-dark fs-6">{{ $duracion }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel 2: Métricas -->
    <div class="pretty-card">
        <div class="pretty-card-header" style="background: linear-gradient(135deg, var(--sky-blue) 0%, var(--primary-cyan) 100%);">
            <h4 class="pretty-card-title">🌡️ Resumen Métrico de Temperaturas</h4>
        </div>
        <div class="pretty-card-body">
            <div class="row text-center g-3">
                <div class="col-md-3">
                    <div class="metric-box">
                        <span class="metric-label">MÍNIMA</span>
                        <span class="metric-value">{{ isset($temperaturaMinima) ? number_format($temperaturaMinima, 2) : '0.00' }} °C</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-box">
                        <span class="metric-label">MÁXIMA</span>
                        <span class="metric-value">{{ isset($temperaturaMaxima) ? number_format($temperaturaMaxima, 2) : '0.00' }} °C</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-box">
                        <span class="metric-label">PROMEDIO</span>
                        <span class="metric-value">{{ isset($temperaturaPromedio) ? number_format($temperaturaPromedio, 2) : '0.00' }} °C</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-box">
                        <span class="metric-label">TOTAL LECTURAS</span>
                        <span class="metric-value" style="background: var(--text-dark); -webkit-background-clip: text;">
                            {{ $totalLecturas ?? $produccion->lecturas->count() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel 3: Alertas -->
    <div class="pretty-card">
        <div class="pretty-card-header" style="background: linear-gradient(135deg, #f43f5e 0%, #fb7185 100%);">
            <h4 class="pretty-card-title">⚠️ Alertas Registradas durante el Proceso</h4>
        </div>
        <div class="pretty-card-body p-0">
            @if($produccion->alertas->count())
                <div class="table-responsive">
                    <table class="pretty-table">
                        <thead style="background-color: #fff1f2;">
                            <tr>
                                <th style="color: #e11d48;">Tipo de Alerta</th>
                                <th style="color: #e11d48;">Mensaje Detallado</th>
                                <th class="text-center" style="color: #e11d48;">Atendida</th>
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
                <div class="text-center py-5">
                    <p class="mb-0 fw-bold text-success fs-5">Excelente</p>
                    <p class="text-muted">No se registraron anomalías ni alertas en esta producción.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Panel 4: Historial de Eventos -->
    <div class="pretty-card">
        <div class="pretty-card-header" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
            <h4 class="pretty-card-title">📜 Historial Cronológico de Eventos (Bitácora del Lote)</h4>
        </div>
        <div class="pretty-card-body p-0">
            @if($produccion->eventos->count())
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="pretty-table">
                        <thead style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Elemento / Acción</th>
                                <th>Descripción del Evento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($produccion->eventos as $evento)
                            <tr>
                                <td class="text-muted small fw-medium">
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
                                <td><strong style="color: var(--text-dark);">{{ $evento->descripcion }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <p class="text-muted mb-0 fs-5">No se encontraron eventos registrados para este lote.</p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection