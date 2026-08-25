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

    .dairy-container {
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

    .form-control {
        border: 1px solid var(--border-soft);
        border-radius: 8px;
        padding: 0.6rem 0.9rem;
        font-size: 0.95rem;
        color: var(--text-main);
        background-color: #fff;
    }

    .form-control:focus {
        border-color: var(--turquoise-primary);
        box-shadow: 0 0 0 3px rgba(0, 128, 128, 0.15);
    }

    /* Sobrescribir botones de Bootstrap con la paleta turquesa */
    .btn-primary {
        background-color: var(--turquoise-primary) !important;
        border-color: var(--turquoise-primary) !important;
        color: #fff !important;
    }
    .btn-primary:hover {
        background-color: var(--turquoise-hover) !important;
        border-color: var(--turquoise-hover) !important;
    }

    .btn-success {
        background-color: #00897b !important;
        border-color: #00796b !important;
        color: #fff !important;
    }
    .btn-success:hover {
        background-color: #00695c !important;
    }

    .btn-secondary {
        background-color: var(--turquoise-light) !important;
        border-color: var(--border-soft) !important;
        color: var(--turquoise-primary) !important;
    }
    .btn-secondary:hover {
        background-color: #b2dfdb !important;
        color: var(--turquoise-hover) !important;
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
            <table class="dairy-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Teléfono</th>
                        <th>Unidad Productiva</th>
                        <th width="180">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($usuarios as $usuario)
                    <tr>
                        <td>
                            {{ $usuario->id }}
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
                        <td>
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
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            No existen usuarios registrados.
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

@endsection