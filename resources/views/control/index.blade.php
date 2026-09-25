@extends('layouts.app')


@section('content')

<div class="container dairy-control-container pt-3">
    <!-- Encabezado del Tablero -->
    <div class="hmi-header mb-4 shadow-sm">
        <div class="d-flex align-items-center">
            <i class="bi bi-droplet-half fs-1 me-3" style="color: var(--turquoise-primary);"></i>
            <div>
                <h2 class="mb-1 fw-bold" style="color: var(--turquoise-primary);">Control de Enfriamiento</h2>
                <p class="text-muted mb-0">Gestión automatizada de agitación y enfriamiento</p>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert" style="background-color: var(--turquoise-light); color: #00695c;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row g-4">

        <!-- 1. MOTOR BATIDOR -->
        <div class="col-md-4">
            <div class="card hmi-card h-100 p-4">
                <div class="text-center">
                    <div class="icon-box">
                        <i class="bi bi-gear-wide-connected"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">Motor Batidor</h4>
                    <p class="text-muted small mb-3">Agitación de cuajada</p>
                    
                    <div class="mb-4">
                        @if($motor && $motor->estado)
                            <span class="status-badge status-on">
                                <i class="bi bi-lightning-charge-fill me-2"></i> En Operación
                            </span>
                        @else
                            <span class="status-badge status-off">
                                <i class="bi bi-power me-2"></i> Detenido
                            </span>
                        @endif
                    </div>

                    <form action="{{ url('/control/Motor/modo') }}" method="POST" class="mb-4" data-async-action>
                        @csrf
                        <div class="form-check form-switch d-flex justify-content-center align-items-center gap-3 p-3 rounded-4" style="background-color: var(--milk-bg); border: 1px solid var(--border-soft);">
                            <input class="form-check-input shadow-sm"
                                   type="checkbox"
                                   name="modo"
                                   role="switch"
                                   id="switchMotor"
                                   data-auto-submit
                                   {{ ($motor && $motor->modo == 'Automatico') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold mb-0" style="color: var(--text-muted);" for="switchMotor">
                                {{ ($motor && $motor->modo == 'Automatico') ? 'MODO AUTO' : 'MODO MANUAL' }}
                            </label>
                        </div>
                    </form>

                    <div class="d-flex gap-2">
                        <form action="{{ url('/control/motor/on') }}" method="POST" class="w-50" data-async-action>
                            @csrf
                            <button class="btn btn-industrial btn-industrial-success w-100 shadow-sm" {{ ($motor && $motor->modo == 'Automatico') ? 'disabled' : '' }}>
                                Arrancar
                            </button>
                        </form>
                        <form action="{{ url('/control/motor/off') }}" method="POST" class="w-50" data-async-action>
                            @csrf
                            <button class="btn btn-industrial btn-industrial-danger w-100 shadow-sm" {{ ($motor && $motor->modo == 'Automatico') ? 'disabled' : '' }}>
                                Parar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. VENTILADOR -->
        <div class="col-md-4">
            <div class="card hmi-card h-100 p-4">
                <div class="text-center">
                    <div class="icon-box">
                        <i class="bi bi-fan"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">Ventilador</h4>
                    <p class="text-muted small mb-3">Extracción térmica</p>
                    
                    <div class="mb-4">
                        @if($ventilador && $ventilador->estado)
                            <span class="status-badge status-on">
                                <i class="bi bi-wind me-2"></i> En Operación
                            </span>
                        @else
                            <span class="status-badge status-off">
                                <i class="bi bi-power me-2"></i> Detenido
                            </span>
                        @endif
                    </div>

                    <form action="{{ url('/control/Ventilador/modo') }}" method="POST" class="mb-4" data-async-action>
                        @csrf
                        <div class="form-check form-switch d-flex justify-content-center align-items-center gap-3 p-3 rounded-4" style="background-color: var(--milk-bg); border: 1px solid var(--border-soft);">
                            <input class="form-check-input shadow-sm"
                                   type="checkbox"
                                   name="modo"
                                   role="switch"
                                   id="switchVentilador"
                                   data-auto-submit
                                   {{ ($ventilador && $ventilador->modo == 'Automatico') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold mb-0" style="color: var(--text-muted);" for="switchVentilador">
                                {{ ($ventilador && $ventilador->modo == 'Automatico') ? 'MODO AUTO' : 'MODO MANUAL' }}
                            </label>
                        </div>
                    </form>

                    <div class="d-flex gap-2">
                        <form action="{{ url('/control/ventilador/on') }}" method="POST" class="w-50" data-async-action>
                            @csrf
                            <button class="btn btn-industrial btn-industrial-success w-100 shadow-sm" {{ ($ventilador && $ventilador->modo == 'Automatico') ? 'disabled' : '' }}>
                                Arrancar
                            </button>
                        </form>
                        <form action="{{ url('/control/ventilador/off') }}" method="POST" class="w-50" data-async-action>
                            @csrf
                            <button class="btn btn-industrial btn-industrial-danger w-100 shadow-sm" {{ ($ventilador && $ventilador->modo == 'Automatico') ? 'disabled' : '' }}>
                                Parar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. SENSOR DE TEMPERATURA -->
        <div class="col-md-4">
            <div class="card hmi-card h-100 p-4">
                <div class="text-center">
                    <div class="icon-box">
                        <i class="bi bi-thermometer-half"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">Sensor Térmico</h4>
                    <p class="text-muted small mb-3">
                        Tipo de sonda: <span class="badge px-2 py-1" style="background-color: var(--turquoise-light); color: var(--turquoise-primary);">{{ $sensor->tipo ?? 'No definido' }}</span>
                    </p>
                    
                    <div class="mb-4">
                        @if($sensor && $sensor->estado == 'Activo')
                            <span class="status-badge status-on">
                                <i class="bi bi-activity me-2"></i> Sensor Activo
                            </span>
                        @else
                            <span class="status-badge status-off">
                                <i class="bi bi-slash-circle me-2"></i> Inactivo
                            </span>
                        @endif
                    </div>

                    <div class="p-3 mb-4 d-none d-md-block" style="height: 56px;"></div>

                    <div class="d-flex gap-2 mt-auto">
                        <form action="{{ url('/control/sensor/on') }}" method="POST" class="w-50" data-async-action>
                            @csrf
                            <button class="btn btn-industrial btn-industrial-success w-100 shadow-sm">
                                Activar
                            </button>
                        </form>
                        <form action="{{ url('/control/sensor/off') }}" method="POST" class="w-50" data-async-action>
                            @csrf
                            <button class="btn btn-industrial btn-industrial-danger w-100 shadow-sm">
                                Desactivar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
