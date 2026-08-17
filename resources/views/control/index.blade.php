@extends('layouts.app')

@section('content')

<h2 class="mb-4">
    <i class="bi bi-toggles"></i>
    Control del Sistema
</h2>

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

<div class="row">

    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-gear-fill"></i>
                Motor Batidor
            </div>
            <div class="card-body text-center">
                <h4 class="mb-3">
                    @if($motor && $motor->estado)
                        <span class="text-success">🟢 Encendido</span>
                    @else
                        <span class="text-danger">🔴 Apagado</span>
                    @endif
                </h4>

                <form action="{{ url('/control/Motor/modo') }}" method="POST" class="mb-3">
                    @csrf
                    <div class="form-check form-switch d-flex justify-content-center align-items-center gap-2">
                        <input class="form-check-input"
                               type="checkbox"
                               name="modo"
                               role="switch"
                               id="switchMotor"
                               onchange="this.form.submit()"
                               {{ ($motor && $motor->modo == 'Automatico') ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-muted" for="switchMotor">
                            Modo: {{ ($motor && $motor->modo == 'Automatico') ? 'Automático 🤖' : 'Manual ⚙️' }}
                        </label>
                    </div>
                </form>

                <form action="{{ url('/control/motor/on') }}" method="POST">
                    @csrf
                    <button class="btn btn-success w-100 mb-2" {{ ($motor && $motor->modo == 'Automatico') ? 'disabled' : '' }}>
                        Encender
                    </button>
                </form>

                <form action="{{ url('/control/motor/off') }}" method="POST">
                    @csrf
                    <button class="btn btn-danger w-100" {{ ($motor && $motor->modo == 'Automatico') ? 'disabled' : '' }}>
                        Apagar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header bg-info text-white">
                <i class="bi bi-fan"></i>
                Ventilador
            </div>
            <div class="card-body text-center">
                <h4 class="mb-3">
                    @if($ventilador && $ventilador->estado)
                        <span class="text-success">🟢 Encendido</span>
                    @else
                        <span class="text-danger">🔴 Apagado</span>
                    @endif
                </h4>

                <form action="{{ url('/control/Ventilador/modo') }}" method="POST" class="mb-3">
                    @csrf
                    <div class="form-check form-switch d-flex justify-content-center align-items-center gap-2">
                        <input class="form-check-input"
                               type="checkbox"
                               name="modo"
                               role="switch"
                               id="switchVentilador"
                               onchange="this.form.submit()"
                               {{ ($ventilador && $ventilador->modo == 'Automatico') ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-muted" for="switchVentilador">
                            Modo: {{ ($ventilador && $ventilador->modo == 'Automatico') ? 'Automático 🤖' : 'Manual ⚙️' }}
                        </label>
                    </div>
                </form>

                <form action="{{ url('/control/ventilador/on') }}" method="POST">
                    @csrf
                    <button class="btn btn-success w-100 mb-2" {{ ($ventilador && $ventilador->modo == 'Automatico') ? 'disabled' : '' }}>
                        Encender
                    </button>
                </form>

                <form action="{{ url('/control/ventilador/off') }}" method="POST">
                    @csrf
                    <button class="btn btn-danger w-100" {{ ($ventilador && $ventilador->modo == 'Automatico') ? 'disabled' : '' }}>
                        Apagar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header bg-warning">
                <i class="bi bi-thermometer-half"></i>
                Sensor de Temperatura
            </div>
            <div class="card-body text-center">
                <h4>
                    @if($sensor && $sensor->estado == 'Activo')
                        <span class="text-success">🟢 Activo</span>
                    @else
                        <span class="text-danger">🔴 Inactivo</span>
                    @endif
                </h4>

                <p>
                    <strong>Tipo:</strong>
                    {{ $sensor->tipo ?? '-' }}
                </p>

                <form action="{{ url('/control/sensor/on') }}" method="POST">
                    @csrf
                    <button class="btn btn-success w-100 mb-2">
                        Activar
                    </button>
                </form>

                <form action="{{ url('/control/sensor/off') }}" method="POST">
                    @csrf
                    <button class="btn btn-danger w-100">
                        Desactivar
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

@endsection
