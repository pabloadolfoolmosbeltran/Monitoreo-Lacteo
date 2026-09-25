@extends('layouts.app')
@section('commercial-panel', '1')
@section('title',$productor->nombre_completo)
@section('subtitle',$productor->nombre_unidad_productiva ?: 'Proveedor sin unidad productiva registrada')
@section('actions')
    <a class="button secondary" href="{{ route('productores.index') }}">← Regresar</a>
    <a class="button" href="{{ route('productores.edit', $productor) }}"><i class="bi bi-pencil"></i> Editar</a>
@endsection
@section('content')
<div class="stats">
    <div class="panel"><span class="muted">Estado</span><strong>{{ $productor->activo ? 'Activo' : 'Inactivo' }}</strong></div>
    <div class="panel"><span class="muted">Entradas registradas</span><strong>{{ $productor->ingresos_count }}</strong></div>
    <div class="panel"><span class="muted">Registrado</span><strong>{{ $productor->created_at?->format('d/m/Y') }}</strong></div>
</div>

<section class="panel">
    <div class="section-heading"><h2>Datos del proveedor</h2></div>
    <div class="form-grid">
        <div><span class="muted">Nombre completo</span><strong class="d-block">{{ $productor->nombre_completo }}</strong></div>
        <div><span class="muted">Teléfono</span><strong class="d-block">{{ $productor->telefono ?: 'Sin registrar' }}</strong></div>
        <div><span class="muted">Unidad productiva</span><strong class="d-block">{{ $productor->nombre_unidad_productiva ?: 'Sin registrar' }}</strong></div>
    </div>
    <div class="mt-3"><span class="muted">Dirección</span><p>{{ $productor->direccion ?: 'Sin dirección registrada.' }}</p></div>
</section>

<section class="panel table-wrap">
    <div class="section-heading"><h2>Últimas entradas de inventario</h2><a href="{{ route('ingresos-productores.create') }}">+ Nueva entrada</a></div>
    <table>
        <thead><tr><th>Entrada</th><th>Fecha</th><th>Lotes</th><th>Estado</th><th>Detalle</th></tr></thead>
        <tbody>
        @forelse($productor->ingresos as $ingreso)
            <tr>
                <td>#{{ $ingreso->id }}</td>
                <td>{{ $ingreso->fecha_ingreso?->format('d/m/Y H:i') }}</td>
                <td>{{ $ingreso->items_count }}</td>
                <td><span class="badge">{{ ucfirst($ingreso->estado) }}</span></td>
                <td><a href="{{ route('ingresos-productores.show', $ingreso) }}">Ver entrada →</a></td>
            </tr>
        @empty
            <tr><td colspan="5">Este proveedor todavía no tiene entradas registradas.</td></tr>
        @endforelse
        </tbody>
    </table>
</section>
@endsection
