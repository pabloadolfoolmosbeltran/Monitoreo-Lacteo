@extends('layouts.app')
@section('commercial-panel', '1')
@section('title', 'Ajustes de Inventario')
@section('subtitle', 'Correcciones manuales de stock con trazabilidad antes/después.')
    @section('actions')
    <a class="button" href="{{ route('ajustes-inventario.create') }}">
        + Nuevo ajuste
    </a>
    @endsection
@section('content')
<section class="panel table-wrap">
<table>
    <thead>
        <tr
        ><th>Nro</th>
        <th>Producto</th>
        <th>Proveedor</th>
        <th>Antes</th>
        <th>Después</th>
        <th>Motivo</th>
        <th>Estado</th>
        <th>Acciones</th>
    </tr>
</thead>
<tbody>
@forelse($ajustes as $ajuste)<tr>
<td>{{ $ajuste->id }}</td>
<td>Lote 
    {{ $ajuste->lote_id }}
    <small>
        {{ $ajuste->lote?->presentacion?->producto?->nombre }} 
    </small>
    <small>
        {{ $ajuste->lote?->presentacion?->nombre }}
    </small>
</td>
<td>{{ $ajuste->lote?->ingresoProductor?->productor?->nombre_completo ?? 'Histórico' }}</td>
<td>{{ $ajuste->cantidad_anterior }}</td>
<td>{{ $ajuste->cantidad_nueva }}</td>
<td>{{ ucfirst($ajuste->tipo_motivo) }}
    <small>{{ $ajuste->motivo }}</small>
</td>
<td><span class="badge">{{ ucfirst($ajuste->estado) }}</span></td>
<td>
    <a href="{{ route('ajustes-inventario.show', $ajuste) }}">
        Ver
    </a>
    @can('update', $ajuste)
        <a href="{{ route('ajustes-inventario.edit', $ajuste) }}">
            Editar
        </a>
    @endcan
    @if(auth()->user()->can('delete',$ajuste))
    <form method="POST" action="{{ route('ajustes-inventario.anular',$ajuste) }}">
        @csrf 
        @method('PATCH')
        <input name="motivo_anulacion" required minlength="3" placeholder="Motivo de anulación">
        <button class="secondary">
            Anular
        </button>
    </form>
    @endif
</td>
    </tr>
    @empty
    <tr>
        <td colspan="9">No hay ajustes registrados.</td>
    </tr>
    @endforelse
    </tbody>
</table>
<div class="pagination">{{ $ajustes->links() }}</div>
</section>
@endsection
