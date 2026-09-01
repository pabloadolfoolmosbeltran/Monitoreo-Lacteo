@extends('layouts.app')

@push('styles')
@vite(['resources/css/usuarios.css'])
@endpush

@section('content')

<div class="container dairy-container pt-3">
    <h2 class="mb-4 fw-bold" style="color: var(--turquoise-primary);">
        👥 Gestión de Usuarios
    </h2>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <form action="{{ route('usuarios.index') }}" method="GET" class="d-flex w-100 w-md-50 gap-2">
            <input
                type="text"
                name="buscar"
                value="{{ request('buscar') }}"
                class="form-control"
                placeholder="Buscar por nombre o email...">

            <button type="submit" class="btn btn-primary">
                Buscar
            </button>

            @if(request('buscar'))
                <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                    Limpiar
                </a>
            @endif
        </form>

        <a href="{{ route('usuarios.create') }}" class="btn btn-success">
            ➕ Nuevo Usuario
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm mb-4" style="background-color: #e0f2f1; color: #00695c;">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm mb-4">
        {{ session('error') }}
    </div>
    @endif

    <div class="dairy-card">
        
        <div class="card-body p-0 overflow-hidden">
            <div class="table-responsive-container">
            <table class="dairy-table">
    <thead>
        <tr>
            <th>N°</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Teléfono</th>
            <th>Unidad Productiva</th>
            <th width="180">Acciones</th>
        </tr>
    </thead>
    <tbody>
    @php
        $i = 1; // 1. Inicializamos nuestro contador en 1 antes del ciclo
    @endphp

    @forelse($usuarios as $usuario)
        <tr>
            <td>
                {{ $i }} <!-- Mostramos el número actual -->
            </td>
            <td>
                <strong style="color: var(--text-main);">{{ $usuario->name }}</strong>
            </td>
            <td>
                {{ $usuario->email }}
            </td>
            <td>
                @if($usuario->rol == 'Administrador')
                    <span class="badge bg-primary px-2 py-1">
                        Administrador
                    </span>
                @else
                    <span class="badge bg-success px-2 py-1">
                        Trabajador
                    </span>
                @endif
            </td>
            <td>
                {{ $usuario->telefono ?? '-' }}
            </td>
            <td>
                {{ $usuario->nombre_unidad_productiva ?? '-' }}
            </td>
            <td class="action-buttons">
                <a
                    href="{{ route('usuarios.edit', $usuario) }}"
                    class="btn btn-warning btn-sm text-dark fw-semibold px-2 py-1">
                    Editar
                </a>

                <form
                    action="{{ route('usuarios.destroy', $usuario) }}"
                    method="POST"
                    class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button
                        onclick="return confirm('¿Eliminar usuario?')"
                        class="btn btn-danger btn-sm px-2 py-1">
                        Eliminar
                    </button>
                </form>
            </td>
        </tr>
        
        @php
            $i++; // 2. Incrementamos el contador en 1 al terminar cada vuelta del recorrido
        @endphp

    @empty
        <tr>
            <td colspan="7" class="text-center py-5 text-muted">
                No existen usuarios registrados.
            </td>
        </tr>
    @endforelse
    </tbody>
</table>
                </tbody>
            </table>
        </div>

        @if($usuarios->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-center">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>
    </div>
</div>

@push('scripts')
@vite(['resources/js/usuarios.js'])
@endpush
@endsection