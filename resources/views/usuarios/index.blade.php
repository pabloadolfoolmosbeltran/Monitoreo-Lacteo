@extends('layouts.app')


@section('content')

<div class="container dairy-container pt-3">
    <h2 class="mb-4 fw-bold" style="color: var(--turquoise-primary);">
        👥 {{ $eliminados ? 'Usuarios Eliminados' : 'Gestión de Usuarios' }}
    </h2>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <form action="{{ route('usuarios.index') }}" method="GET" class="d-flex w-100 w-md-50 gap-2">
            @if($eliminados)
                <input type="hidden" name="estado" value="eliminados">
            @endif
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
                <a href="{{ route('usuarios.index', $eliminados ? ['estado' => 'eliminados'] : []) }}" class="btn btn-secondary">
                    Limpiar
                </a>
            @endif
        </form>

        <div class="d-flex gap-2">
            @if($eliminados)
                <a href="{{ route('usuarios.index') }}" data-full-navigation class="btn btn-secondary">← Volver a usuarios</a>
            @else
                <a href="{{ route('usuarios.create') }}" class="btn btn-success">➕ Nuevo Usuario</a>
                <a href="{{ route('usuarios.index', ['estado' => 'eliminados']) }}" data-full-navigation class="btn btn-outline-secondary">
                    <i class="bi bi-archive me-1"></i> Mostrar eliminados
                </a>
            @endif
        </div>
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
            <th width="180">Acciones</th>
        </tr>
    </thead>
    <tbody>
    @forelse($usuarios as $usuario)
        <tr>
            <td>
                {{ $usuarios->firstItem() + $loop->index }}
            </td>
            <td>
                <strong style="color: var(--text-main);">{{ $usuario->name }}</strong>
            </td>
            <td>
                {{ $usuario->email }}
            </td>
            <td>
                @switch($usuario->rol)
                    @case('Administrador')
                        <span class="badge bg-danger px-2 py-1">
                            Administrador
                        </span>
                        @break
                    @case('Trabajador')
                        <span class="badge bg-primary px-2 py-1">
                            Trabajador
                        </span>
                        @break
                @endswitch
            </td>
            <td>
                {{ $usuario->telefono ?? '-' }}
            </td>
            <td class="action-buttons">
                @if($eliminados)
                <form action="{{ route('usuarios.restore', $usuario) }}" method="POST" class="d-inline" data-full-navigation>
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success btn-sm px-2 py-1">Restablecer</button>
                </form>
                @else
                <a
                    href="{{ route('usuarios.edit', $usuario) }}"
                    class="btn btn-warning btn-sm text-dark fw-semibold px-2 py-1">
                    Editar
                </a>

                <form
                    action="{{ route('usuarios.destroy', $usuario) }}"
                    method="POST"
                    class="d-inline"
                    data-full-navigation>
                    @csrf
                    @method('DELETE')
                    <button
                        data-confirm-message="¿Eliminar usuario?"
                        class="btn btn-danger btn-sm px-2 py-1">
                        Eliminar
                    </button>
                </form>
                @endif
            </td>
        </tr>
        
    @empty
        <tr>
            <td colspan="6" class="text-center py-5 text-muted">
                {{ $eliminados ? 'No hay usuarios eliminados.' : 'No existen usuarios registrados.' }}
            </td>
        </tr>
    @endforelse
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

@endsection
