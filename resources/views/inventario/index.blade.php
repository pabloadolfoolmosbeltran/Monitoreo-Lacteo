@extends('layouts.app')
@section('commercial-panel', '1')
@section('title','Inventario por lote')
@section('subtitle','Existencias físicas, vencimientos y movimientos con trazabilidad.')
@section('actions')
<a class="button" href="{{ route('ingresos-productores.create') }}">+ Entrada de nuevo lote</a>
@endsection
@section('content')
<form method="GET" action="{{ route('inventario.index') }}" class="panel filter-grid">
    <label>Producto o presentación<input type="search" name="producto" maxlength="100" value="{{ $filtros['producto'] ?? '' }}" placeholder="Ej.: yogurt frutilla 2 litros"></label>
    <label>Productor<input type="search" name="productor" maxlength="100" value="{{ $filtros['productor'] ?? '' }}" placeholder="Nombre o unidad productiva"></label>
    <label>Ingreso desde<input type="date" name="ingreso_desde" value="{{ $filtros['ingreso_desde'] ?? '' }}"></label>
    <label>Ingreso hasta<input type="date" name="ingreso_hasta" value="{{ $filtros['ingreso_hasta'] ?? '' }}"></label>
    <label>Vence desde<input type="date" name="vence_desde" value="{{ $filtros['vence_desde'] ?? '' }}"></label>
    <label>Vence hasta<input type="date" name="vence_hasta" value="{{ $filtros['vence_hasta'] ?? '' }}"></label>
    <div class="filter-actions">
        <button type="submit">
            <i class="bi bi-search">
            </i> Buscar</button>
            <a class="button secondary" href="{{ route('inventario.index') }}">
                Limpiar
            </a>
        </div>
</form>
<div class="panel table-wrap">
<table>
    <thead>
        <tr>
            <th>Lote / producto</th>
            <th>Productor</th>
            <th>Existencia</th>
            <th>Recepción</th>
            <th>Vencimiento</th>
            <th>Entrada</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
@forelse($lotes as $item)
@php $estado=$item->ingresoProductor?->estado==='abierta'?$item->estado_caducidad:'bloqueado'; 
@endphp
<tr>
<td><strong>Lote #{{ $item->id }}</strong><small>{{ $item->presentacion?->producto?->nombre ?? 'Producto retirado' }} · {{ $item->presentacion?->nombre ?? 'Presentación retirada' }}</small></td>
<td>{{ $item->ingresoProductor?->productor?->nombre_completo ?? 'Histórico' }}<small>{{ $item->ingresoProductor?->productor?->nombre_unidad_productiva }}</small></td>
<td><strong>{{ number_format((int)$item->cantidad_disponible,0,',','.') }}</strong><small>Bs {{ number_format((float)$item->precio_venta_unitario,2,',','.') }}</small></td>
<td>{{ $item->fecha_recepcion?->format('d/m/Y') ?? 'Sin fecha' }}</td>
<td>{{ $item->fecha_caducidad?->format('d/m/Y') ?? 'Sin fecha' }}<small><span class="stock-state {{ $estado }}">{{ ucfirst(str_replace('_',' ',$estado)) }}</span></small></td>
<td><span class="badge">{{ ucfirst($item->ingresoProductor?->estado ?? 'histórica') }}</span><small><a href="{{ route('ingresos-productores.show',$item->ingreso_productor_id) }}">Entrada #{{ $item->ingreso_productor_id }}</a></small></td>
<td><details><summary>Movimiento</summary><form data-async-action action="{{ route('lotes.movimientos.store',$item) }}" class="lot-form" method="POST">@csrf<label>Operación<select name="tipo"><option value="entrada">Entrada adicional</option><option value="salida">Salida</option>@if(auth()->user()->rol==='Administrador')<option value="ajuste">Corrección (+ / −)</option>@endif</select></label><label>Cantidad<input name="cantidad" type="number" step="1" required></label><label>Motivo<input name="motivo" required minlength="3" maxlength="500"></label><button>Registrar</button></form></details></td>
</tr>
@empty<tr><td colspan="7">No hay lotes que coincidan con los filtros.</td></tr>@endforelse
</tbody></table><div class="pagination">{{ $lotes->links() }}</div></div>
<section class="panel table-wrap"><h2>Últimos movimientos auditados</h2><table><thead><tr><th>Fecha / ID</th><th>Lote</th><th>Tipo</th><th>Cantidad</th><th>Responsable</th><th>Motivo</th></tr></thead><tbody>
@forelse($movimientos as $movimiento)<tr><td>{{ $movimiento->created_at }}<small>#{{ $movimiento->id }}</small></td><td>#{{ $movimiento->lote_id }}</td><td>{{ $movimiento->tipo }}</td><td>{{ number_format((int)$movimiento->cantidad,0,',','.') }}</td><td>{{ $movimiento->responsable?->name ?? 'Histórico' }}</td><td>{{ $movimiento->motivo }}</td></tr>@empty<tr><td colspan="6">Sin movimientos auditados.</td></tr>@endforelse
</tbody></table></section>
@endsection
