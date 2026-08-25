@extends('layouts.app')

@section('content')
<style>
    /* Paleta Temática: Turquesa Lechero / Fresco & Limpio */
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

    .dairy-log-container {
        font-family: system-ui, -apple-system, sans-serif;
        color: var(--text-main);
        padding-bottom: 3rem;
    }

    .dairy-card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-soft);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 128, 128, 0.05);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .dairy-label {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--turquoise-primary);
        letter-spacing: 0.5px;
        margin-bottom: 0.4rem;
        display: block;
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

    .btn-dairy-primary {
        background-color: var(--turquoise-primary);
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        padding: 0.6rem 1.2rem;
        font-size: 0.9rem;
        transition: background-color 0.2s ease;
    }

    .btn-dairy-primary:hover {
        background-color: var(--turquoise-hover);
        color: #ffffff;
    }

    .btn-dairy-secondary {
        background-color: var(--turquoise-light);
        color: var(--turquoise-primary);
        border: 1px solid var(--border-soft);
        border-radius: 8px;
        font-weight: 600;
        padding: 0.6rem 1.2rem;
        font-size: 0.9rem;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }

    .btn-dairy-secondary:hover {
        background-color: #b2dfdb;
        color: var(--turquoise-hover);
    }

    .dairy-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }

    .dairy-table th {
        background-color: var(--turquoise-light);
        color: var(--turquoise-primary);
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 1rem 1.25rem;
        border-bottom: 2px solid var(--border-soft);
        border-top: none;
    }

    .dairy-table td {
        padding: 1rem 1.25rem;
        color: var(--text-main);
        font-size: 0.95rem;
        border-bottom: 1px solid #f0f4f4;
        vertical-align: middle;
    }

    .dairy-table tbody tr:last-child td {
        border-bottom: none;
    }

    .dairy-table tbody tr:hover {
        background-color: var(--milk-bg);
    }
</style>

<div class="container dairy-log-container pt-3">
    <h2 class="mb-4 fw-bold" style="color: var(--turquoise-primary);">
        🥛 Bitácora del Sistema
    </h2>

    <div class="dairy-card">
        <div class="card-body p-4">
            <form method="GET" class="row g-3">

                <div class="col-md-4">
                    <label class="dairy-label">Tipo</label>
                    <select name="tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="Producción" {{ request('tipo') == 'Producción' ? 'selected' : '' }}>Producción</option>
                        <option value="Motor" {{ request('tipo') == 'Motor' ? 'selected' : '' }}>Motor</option>
                        <option value="Ventilador" {{ request('tipo') == 'Ventilador' ? 'selected' : '' }}>Ventilador</option>
                        <option value="Sensor" {{ request('tipo') == 'Sensor' ? 'selected' : '' }}>Sensor</option>
                        <option value="Temperatura" {{ request('tipo') == 'Temperatura' ? 'selected' : '' }}>Temperatura</option>
                        <option value="Alerta" {{ request('tipo') == 'Alerta' ? 'selected' : '' }}>Alerta</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="dairy-label">Fecha</label>
                    <input type="date" name="fecha" value="{{ request('fecha') }}" class="form-control">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-dairy-primary w-100">Buscar</button>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ url('/eventos') }}" class="btn btn-dairy-secondary w-100">Limpiar</a>
                </div>

            </form>
        </div>
    </div>

    <div class="dairy-card">
        <div class="card-body p-0 overflow-hidden">
            <table class="dairy-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th> 
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th class="text-center">Producción</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($eventos as $evento)
                    <tr>
                        <td class="text-secondary small">
                            {{ $evento->fecha_hora->format('d/m/Y H:i:s') }}
                        </td>
                        <td>
                            <strong style="color: var(--text-main);">{{ $evento->user?->name ?? 'Sistema / Automático' }}</strong>
                        </td>
                        <td>
                            @php
                                $badgeClass = match($evento->tipo) {
                                    'Alerta' => 'bg-danger text-white',
                                    'Producción' => 'bg-success text-white',
                                    'Temperatura' => 'bg-warning text-dark',
                                    'Motor' => 'bg-info text-dark',
                                    'Ventilador' => 'bg-primary text-white',
                                    default => 'bg-secondary text-white'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $evento->tipo }}</span>
                        </td>
                        <td>
                            {{ $evento->descripcion }}
                        </td>
                        <td class="text-center fw-semibold">
                            {{ $evento->produccion?->id ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            No existen eventos registrados con esos criterios.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($eventos->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-center">
                {{ $eventos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection