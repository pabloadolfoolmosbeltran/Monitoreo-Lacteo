@extends('layouts.app')

@section('content')

<h2 class="mb-4">Gestión de Usuarios</h2>

<div class="d-flex justify-content-between align-items-center mb-4">
    <form action="{{ route('usuarios.index') }}" method="GET" class="d-flex w-50">
        <input
            type="text"
            name="buscar"
            value="{{ request('buscar') }}"
            class="form-control me-2"
            placeholder="Buscar por nombre o email...">

        <button type="submit" class="btn btn-primary me-2">
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
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Teléfono</th>
                    <th>Unidad Productiva</th>
                    <th width="180">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody>
            @forelse($usuarios as $usuario)
                <tr>
                    <td>
                        {{ $usuario->id }}
                    </td>
                    <td>
                        {{ $usuario->name }}
                    </td>
                    <td>
                        {{ $usuario->email }}
                    </td>
                    <td>
                        @if($usuario->rol == 'Administrador')
                            <span class="badge bg-primary">
                                Administrador
                            </span>
                        @else
                            <span class="badge bg-success">
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
                    <td>
                        <a
                            href="{{ route('usuarios.edit', $usuario) }}"
                            class="btn btn-warning btn-sm">
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
                                class="btn btn-danger btn-sm">
                                Eliminar
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">
                        No existen usuarios registrados.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        {{ $usuarios->links() }}
    </div>
</div>

@endsection