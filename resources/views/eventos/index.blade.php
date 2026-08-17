@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Bitácora del Sistema</h2>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">

                <div class="col-md-4">
                    <label class="form-label">Tipo</label>
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
                    <label class="form-label">Fecha</label>
                    <input type="date" name="fecha" value="{{ request('fecha') }}" class="form-control">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Buscar</button>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ url('/eventos') }}" class="btn btn-secondary w-100">Limpiar</a>
                </div>

            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th> <th>Tipo</th>
                        <th>Descripción</th>
                        <th class="text-center">Producción</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($eventos as $evento)
                    <tr>
                        <td>
                            {{ $evento->fecha_hora->format('d/m/Y H:i:s') }}
                        </td>
                        <td>
                            <strong>{{ $evento->user?->name ?? 'Sistema / Automático' }}</strong>
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
                        <td class="text-center">
                            {{ $evento->produccion?->id ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            No existen eventos registrados con esos criterios.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($eventos->hasPages())
            <div class="card-footer d-flex justify-content-center bg-white py-3">
                {{ $eventos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
