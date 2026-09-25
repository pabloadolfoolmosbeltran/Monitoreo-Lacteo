@extends('layouts.app')
@section('commercial-panel', '1')
@section('title', $eliminados ? 'Proveedores Eliminados' : 'Proveedores')
@section('subtitle', $eliminados ? 'Registros archivados que conservan íntegro su historial.' : 'Directorio independiente de proveedores y unidades productivas; no crea cuentas de usuario.')
@section('actions')
    @if($eliminados)
        <a class="button secondary" data-full-navigation href="{{ route('productores.index') }}">← Volver a proveedores</a>
    @else
        <a class="button" href="{{ route('productores.create') }}">+ Nuevo proveedor</a>
        @if(auth()->user()->rol === 'Administrador')
            <a class="button secondary" data-full-navigation href="{{ route('productores.index', ['estado' => 'eliminados']) }}"><i class="bi bi-archive"></i> Mostrar eliminados</a>
        @endif
    @endif
@endsection
@section('content')
<form action="{{ route('productores.index') }}" method="GET" class="panel toolbar">
    @if($eliminados)
        <input type="hidden" name="estado" value="eliminados">
    @endif
    <label class="search">Buscar proveedor
        <input type="search" name="buscar" value="{{ request('buscar') }}" maxlength="100" placeholder="Nombre, unidad productiva o teléfono">
    </label>
    <button type="submit">Buscar</button>
    @if(request('buscar'))
        <a href="{{ route('productores.index', $eliminados ? ['estado' => 'eliminados'] : []) }}" class="button secondary">Limpiar</a>
    @endif
</form>

<div class="panel table-wrap">
    <table>
        <thead>
            <tr><th>N.º</th><th>Proveedor</th><th>Unidad productiva</th><th>Teléfono</th><th>Entradas</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        @forelse($productores as $productor)
            <tr>
                <td>{{ $productores->firstItem() + $loop->index }}</td>
                <td><strong>{{ $productor->nombre_completo }}</strong><small>{{ $productor->direccion ?: 'Sin dirección registrada' }}</small></td>
                <td>{{ $productor->nombre_unidad_productiva ?: 'Sin registrar' }}</td>
                <td>{{ $productor->telefono ?: 'Sin registrar' }}</td>
                <td>{{ $productor->ingresos_count }}</td>
                <td><span class="badge">{{ $eliminados ? 'Eliminado' : ($productor->activo ? 'Activo' : 'Inactivo') }}</span></td>
                <td>
                    <div class="row">
                        @if($eliminados)
                        <form action="{{ route('productores.restore', $productor->id) }}" method="POST" data-full-navigation>
                            @csrf
                            @method('PATCH')
                            <button type="submit">Restablecer</button>
                        </form>
                        @else
                        <a class="button secondary" href="{{ route('productores.show', $productor) }}"><i class="bi bi-eye"></i> Ver</a>
                        <a class="button secondary" href="{{ route('productores.edit', $productor) }}"><i class="bi bi-pencil"></i> Editar</a>
                        <form action="{{ route('productores.destroy', $productor) }}" method="POST" data-full-navigation>
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="danger" data-confirm-message="¿Eliminar este proveedor? Su historial se conservará.">Eliminar</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7">{{ request('buscar') ? 'No hay proveedores que coincidan con la búsqueda.' : ($eliminados ? 'No hay proveedores eliminados.' : 'No hay proveedores registrados.') }}</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="pagination">{{ $productores->links() }}</div>
</div>
@endsection
