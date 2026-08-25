@extends('layouts.app')

@section('content')

<style>
    /* Paleta Temática: Turquesa Lechero / HMI Industrial Fresco */
    :root {
        --milk-bg: #f4fbfb;
        --card-bg: #ffffff;
        --turquoise-primary: #008080; /* Turquesa profesional profundo */
        --turquoise-light: #e0f2f1;   /* Aqua suave / Leche fresca */
        --turquoise-hover: #006666;
        --text-main: #2b3a42;
        --text-muted: #5c768d;
        --border-soft: #b2dfdb;
    }

    .dairy-control-container {
        font-family: system-ui, -apple-system, sans-serif;
        color: var(--text-main);
        padding-bottom: 3rem;
    }

    .hmi-header {
        background: linear-gradient(135deg, #f4fbfb 0%, #e0f2f1 100%);
        border-radius: 16px;
        padding: 1.5rem;
        border-left: 5px solid var(--turquoise-primary);
        border: 1px solid var(--border-soft);
        box-shadow: 0 2px 8px rgba(0, 128, 128, 0.04);
    }
    
    .hmi-card {
        border: 1px solid var(--border-soft);
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 128, 128, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        background: var(--card-bg);
    }

    .hmi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 128, 128, 0.1);
    }

    .icon-box {
        width: 70px;
        height: 70px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin: 0 auto 1.25rem auto;
        background: var(--turquoise-light);
        color: var(--turquoise-primary);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1.2rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
        letter-spacing: 0.3px;
    }
    
    .status-on { 
        background: var(--turquoise-light); 
        color: var(--turquoise-hover); 
        border: 1px solid var(--border-soft); 
    }
    
    .status-off { 
        background: #f8fafc; 
        color: var(--text-muted); 
        border: 1px solid #e2e8f0; 
    }

    .form-switch .form-check-input {
        width: 3.5em;
        height: 1.75em;
        cursor: pointer;
        background-color: #cbd5e1;
        border-color: #cbd5e1;
    }
    
    .form-switch .form-check-input:checked {
        background-color: var(--turquoise-primary);
        border-color: var(--turquoise-primary);
    }
    
    .btn-industrial {
        border-radius: 10px;
        font-weight: 600;
        letter-spacing: 0.5px;
        padding: 0.6rem 1rem;
        text-transform: uppercase;
        font-size: 0.8rem;
        transition: all 0.2s ease;
    }

    .btn-industrial-success {
        background-color: var(--turquoise-primary);
        color: #fff;
        border: none;
    }
    .btn-industrial-success:hover:not(:disabled) {
        background-color: var(--turquoise-hover);
        color: #fff;
    }

    .btn-industrial-danger {
        background-color: #ffffff;
        color: #e11d48;
        border: 1px solid #fecaca;
    }
    .btn-industrial-danger:hover:not(:disabled) {
        background-color: #fff1f2;
        color: #be123c;
    }
</style>

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

                    <form action="{{ url('/control/Motor/modo') }}" method="POST" class="mb-4">
                        @csrf
                        <div class="form-check form-switch d-flex justify-content-center align-items-center gap-3 p-3 rounded-4" style="background-color: var(--milk-bg); border: 1px solid var(--border-soft);">
                            <input class="form-check-input shadow-sm"
                                   type="checkbox"
                                   name="modo"
                                   role="switch"
                                   id="switchMotor"
                                   onchange="this.form.submit()"
                                   {{ ($motor && $motor->modo == 'Automatico') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold mb-0" style="color: var(--text-muted);" for="switchMotor">
                                {{ ($motor && $motor->modo == 'Automatico') ? 'MODO AUTO' : 'MODO MANUAL' }}
                            </label>
                        </div>
                    </form>

                    <div class="d-flex gap-2">
                        <form action="{{ url('/control/motor/on') }}" method="POST" class="w-50">
                            @csrf
                            <button class="btn btn-industrial btn-industrial-success w-100 shadow-sm" {{ ($motor && $motor->modo == 'Automatico') ? 'disabled' : '' }}>
                                Arrancar
                            </button>
                        </form>
                        <form action="{{ url('/control/motor/off') }}" method="POST" class="w-50">
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

                    <form action="{{ url('/control/Ventilador/modo') }}" method="POST" class="mb-4">
                        @csrf
                        <div class="form-check form-switch d-flex justify-content-center align-items-center gap-3 p-3 rounded-4" style="background-color: var(--milk-bg); border: 1px solid var(--border-soft);">
                            <input class="form-check-input shadow-sm"
                                   type="checkbox"
                                   name="modo"
                                   role="switch"
                                   id="switchVentilador"
                                   onchange="this.form.submit()"
                                   {{ ($ventilador && $ventilador->modo == 'Automatico') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold mb-0" style="color: var(--text-muted);" for="switchVentilador">
                                {{ ($ventilador && $ventilador->modo == 'Automatico') ? 'MODO AUTO' : 'MODO MANUAL' }}
                            </label>
                        </div>
                    </form>

                    <div class="d-flex gap-2">
                        <form action="{{ url('/control/ventilador/on') }}" method="POST" class="w-50">
                            @csrf
                            <button class="btn btn-industrial btn-industrial-success w-100 shadow-sm" {{ ($ventilador && $ventilador->modo == 'Automatico') ? 'disabled' : '' }}>
                                Arrancar
                            </button>
                        </form>
                        <form action="{{ url('/control/ventilador/off') }}" method="POST" class="w-50">
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

                    <!-- Espaciador para igualar alturas con las tarjetas anteriores -->
                    <div class="p-3 mb-4 d-none d-md-block" style="height: 56px;"></div>

                    <div class="d-flex gap-2 mt-auto">
                        <form action="{{ url('/control/sensor/on') }}" method="POST" class="w-50">
                            @csrf
                            <button class="btn btn-industrial btn-industrial-success w-100 shadow-sm">
                                Activar
                            </button>
                        </form>
                        <form action="{{ url('/control/sensor/off') }}" method="POST" class="w-50">
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