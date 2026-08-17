@extends('layouts.app')

@section('content')

<h2 class="mb-4">Producción</h2>

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if($produccionActiva)
<div class="card shadow-sm border-success">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Producción Activa</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p>
                    <strong>Productor responsable:</strong>
                    {{ $produccionActiva->user?->nombre_unidad_productiva ?? $produccionActiva->user?->name }}
                </p>
                <p>
                    <strong>Producto:</strong>
                    {{ $produccionActiva->producto->nombre }}
                </p>
                <p>
                    <strong>Estado:</strong>
                    <span class="badge bg-success">
                        {{ $produccionActiva->estado }}
                    </span>
                </p>
            </div>
            <div class="col-md-6">
                <p>
                    <strong>Fecha de inicio:</strong>
                    {{ $produccionActiva->fecha_inicio->format('d/m/Y H:i') }}
                </p>
                <p>
                    <strong>Cantidad de leche:</strong>
                    {{ $produccionActiva->cantidad_leche }} Litros
                </p>
                <p>
                    <strong>Temperatura objetivo:</strong>
                    {{ $produccionActiva->temperatura_objetivo }} °C
                </p>
            </div>
        </div>

        @if($produccionActiva->observaciones)
        <hr>
        <p>
            <strong>Observaciones:</strong>
            {{ $produccionActiva->observaciones }}
        </p>
        @endif

        <hr>

        <form action="{{ url('/produccion/finalizar') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger">
                Finalizar Producción
            </button>
        </form>
    </div>
</div>
@else
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">
            Nueva Producción
        </h5>
    </div>
    <div class="card-body">
        <form action="{{ url('/produccion/iniciar') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-bold">
                    Productor responsable
                </label>
                @if(isset($usuario) && $usuario->rol == 'Administrador')
                    <select name="user_id" class="form-select" required>
                        <option value="">Seleccione un usuario/productor</option>
                        @foreach($users as $user)
                        <option
                            value="{{ $user->id }}"
                            {{ old('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->nombre_unidad_productiva ?? $user->name }} ({{ $user->rol }})
                        </option>
                        @endforeach
                    </select>
                @else
                    <input
                        type="text"
                        class="form-control bg-light"
                        value="{{ ($usuario->nombre_unidad_productiva ?? $usuario->name) ?? '' }}"
                        readonly>
                    <input
                        type="hidden"
                        name="user_id"
                        value="{{ $usuario->id ?? '' }}">
                @endif
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Producto
                </label>
                <select name="producto_id" class="form-select" required>
                    <option value="">Seleccione un producto</option>
                    @foreach($productos as $producto)
                    <option
                        value="{{ $producto->id }}"
                        {{ old('producto_id') == $producto->id ? 'selected' : '' }}>
                        {{ $producto->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Cantidad de leche (Litros)
                </label>
                <input
                    type="number"
                    step="0.01"
                    min="1"
                    name="cantidad_leche"
                    class="form-control"
                    value="{{ old('cantidad_leche') }}"
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Temperatura objetivo (°C)
                </label>
                <input
                    type="number"
                    step="0.1"
                    min="1"
                    name="temperatura_objetivo"
                    class="form-control"
                    value="{{ old('temperatura_objetivo') }}"
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Observaciones
                </label>
                <textarea
                    name="observaciones"
                    class="form-control"
                    rows="4">{{ old('observaciones') }}</textarea>
            </div>

            <button type="submit" class="btn btn-success">
                Iniciar Producción
            </button>
        </form>
    </div>
</div>
@endif

@endsection