@extends('layouts.app')

@section('content')

<style>
    /* Paleta Temática: Turquesa Lechero / Industrial Fresco */
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

    .dairy-production-container {
        font-family: system-ui, -apple-system, sans-serif;
        color: var(--text-main);
        padding-bottom: 3rem;
    }

    .dairy-card {
        border: 1px solid var(--border-soft);
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 128, 128, 0.05);
        background: var(--card-bg);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .dairy-card-header {
        background-color: var(--turquoise-light);
        color: var(--turquoise-primary);
        border-bottom: 1px solid var(--border-soft);
        padding: 1rem 1.25rem;
        font-weight: 700;
    }

    .dairy-card-header-active {
        background-color: var(--turquoise-primary);
        color: #ffffff;
        border-bottom: 1px solid var(--turquoise-hover);
        padding: 1.05rem 1.25rem;
        font-weight: 700;
    }

    .form-control, .form-select {
        border: 1px solid var(--border-soft);
        border-radius: 8px;
        padding: 0.6rem 0.9rem;
        font-size: 0.95rem;
        color: var(--text-main);
        background-color: #fff;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--turquoise-primary);
        box-shadow: 0 0 0 3px rgba(0, 128, 128, 0.15);
    }

    .btn-industrial {
        border-radius: 10px;
        font-weight: 600;
        letter-spacing: 0.5px;
        padding: 0.6rem 1.2rem;
        text-transform: uppercase;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }

    .btn-industrial-success {
        background-color: var(--turquoise-primary);
        color: #fff;
        border: none;
    }
    .btn-industrial-success:hover {
        background-color: var(--turquoise-hover);
        color: #fff;
    }

    .btn-industrial-danger {
        background-color: #ffffff;
        color: #e11d48;
        border: 1px solid #fecaca;
    }
    .btn-industrial-danger:hover {
        background-color: #fff1f2;
        color: #be123c;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 1rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.85rem;
        background-color: var(--turquoise-light);
        color: var(--turquoise-hover);
        border: 1px solid var(--border-soft);
    }
</style>

<div class="container dairy-production-container pt-3">
    <h2 class="mb-4 fw-bold" style="color: var(--turquoise-primary);">
        🥛 Producción
    </h2>

    @if(session('success'))
    <div class="alert border-0 shadow-sm mb-4 rounded-4" style="background-color: var(--turquoise-light); color: #00695c;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm mb-4 rounded-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm mb-4 rounded-4">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($produccionActiva)
    <div class="dairy-card">
        <div class="dairy-card-header-active d-flex align-items-center">
            <i class="bi bi-activity me-2 fs-5"></i>
            <h5 class="mb-0 fw-bold">Producción Activa</h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <p class="mb-2">
                        <strong style="color: var(--text-muted);">Productor responsable:</strong><br>
                        <span class="fw-semibold text-dark">{{ $produccionActiva->user?->nombre_unidad_productiva ?? $produccionActiva->user?->name }}</span>
                    </p>
                    <p class="mb-2">
                        <strong style="color: var(--text-muted);">Producto:</strong><br>
                        <span class="fw-semibold text-dark">{{ $produccionActiva->producto->nombre }}</span>
                    </p>
                    <p class="mb-2">
                        <strong style="color: var(--text-muted);">Estado:</strong><br>
                        <span class="status-badge mt-1">
                            <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem; color: var(--turquoise-primary);"></i>
                            {{ $produccionActiva->estado }}
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2">
                        <strong style="color: var(--text-muted);">Fecha de inicio:</strong><br>
                        <span class="fw-semibold text-dark">{{ $produccionActiva->fecha_inicio->format('d/m/Y H:i') }}</span>
                    </p>
                    <p class="mb-2">
                        <strong style="color: var(--text-muted);">Cantidad de leche:</strong><br>
                        <span class="fw-semibold text-dark">{{ $produccionActiva->cantidad_leche }} Litros</span>
                    </p>
                    <p class="mb-2">
                        <strong style="color: var(--text-muted);">Temperatura objetivo:</strong><br>
                        <span class="fw-semibold text-dark">{{ $produccionActiva->temperatura_objetivo }} °C</span>
                    </p>
                </div>
            </div>

            @if($produccionActiva->observaciones)
            <hr style="border-color: var(--border-soft);">
            <p class="mb-0">
                <strong style="color: var(--text-muted);">Observaciones:</strong><br>
                <span class="text-dark">{{ $produccionActiva->observaciones }}</span>
            </p>
            @endif

            <hr style="border-color: var(--border-soft);">

            <form action="{{ url('/produccion/finalizar') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-industrial btn-industrial-danger shadow-sm">
                    Finalizar Producción
                </button>
            </form>
        </div>
    </div>
    @else
    <div class="dairy-card">
        <div class="dairy-card-header d-flex align-items-center">
            <i class="bi bi-plus-circle me-2 fs-5"></i>
            <h5 class="mb-0 fw-bold" style="color: var(--turquoise-primary);">Nueva Producción</h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ url('/produccion/iniciar') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="color: var(--text-muted);">
                        Productor responsable
                    </label>
                    @if(isset($usuario) && $usuario->rol == 'Administrador')
                        <select name="user_id" class="form-select shadow-sm" required>
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
                            class="form-control bg-light shadow-sm"
                            value="{{ ($usuario->nombre_unidad_productiva ?? $usuario->name) ?? '' }}"
                            readonly>
                        <input
                            type="hidden"
                            name="user_id"
                            value="{{ $usuario->id ?? '' }}">
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="color: var(--text-muted);">
                        Producto
                    </label>
                    <select name="producto_id" class="form-select shadow-sm" required>
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
                    <label class="form-label fw-semibold" style="color: var(--text-muted);">
                        Cantidad de leche (Litros)
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="1"
                        name="cantidad_leche"
                        class="form-control shadow-sm"
                        value="{{ old('cantidad_leche') }}"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="color: var(--text-muted);">
                        Temperatura objetivo (°C)
                    </label>
                    <input
                        type="number"
                        step="0.1"
                        min="1"
                        name="temperatura_objetivo"
                        class="form-control shadow-sm"
                        value="{{ old('temperatura_objetivo') }}"
                        required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold" style="color: var(--text-muted);">
                        Observaciones
                    </label>
                    <textarea
                        name="observaciones"
                        class="form-control shadow-sm"
                        rows="4">{{ old('observaciones') }}</textarea>
                </div>

                <button type="submit" class="btn btn-industrial btn-industrial-success shadow-sm">
                    Iniciar Producción
                </button>
            </form>
        </div>
    </div>
    @endif

</div>

@endsection