@extends('layouts.app')

@section('content')

<h2 class="mb-4">
    Historial de Alertas
</h2>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($alertas->isEmpty())

<div class="alert alert-success">
    No existen alertas registradas.
</div>

@else

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Mensaje</th>
                        <th>Producción</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alertas as $alerta)
                    <tr>
                        <td>{{ $alerta->id }}</td>
                        <td>{{ $alerta->tipo }}</td>
                        <td>{{ $alerta->mensaje }}</td>
                        <td>{{ $alerta->produccion ? $alerta->produccion->id : 'N/A' }}</td>
                        <td>{{ $alerta->created_at }}</td>
                        <td>
                            @if($alerta->atendida)
                                <span class="badge bg-success">
                                    Atendida
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    Pendiente
                                </span>
                            @endif
                        </td>
                        <td>
                            @if(!$alerta->atendida)
                                <form action="{{ route('alertas.atender', $alerta->id) }}" method="POST" class="d-inline" data-async-action>
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success">
                                        ✓ Atender
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small">Completado</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endif

@endsection
