@extends('layouts.app')
@section('commercial-panel', '1')
@section('title','Entradas de Inventario')
@section('subtitle','Recepción por proveedor, seguimiento de lotes y liquidación de acopio.')
@section('actions')
    <a class="button" href="/ingresos-productores/create">+ Registrar entrada</a>@endsection
@section('content')
    <div class="panel table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nro</th>
                    <th>Proveedor</th>
                    <th>Fecha</th>
                    <th>Lotes</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>@forelse($ingresos as $ingreso)<tr>
                <td>{{ $ingreso->id }}</td>
                <td>
                    {{ $ingreso->productor->nombres ?? 'Histórico' }}
                    {{ $ingreso->productor->primer_apellido ?? '' }}
                    <small>
                        {{ $ingreso->productor->nombre_unidad_productiva ?? '' }}
                    </small>
                </td>
                <td>
                    {{ optional($ingreso->fecha_ingreso)->format('d/m/Y') }}
                </td>
                <td>
                    {{ $ingreso->items->count() }}
                </td>
                <td>
                    <span class="badge">
                        {{ $ingreso->estado }}
                    </span>
                </td>
                <td>
                    <a href="/ingresos-productores/{{ $ingreso->id }}">Ver detalle →</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">Aún no hay entradas registradas.</td>
            </tr>@endforelse</tbody></table><div class="pagination">
                {{ $ingresos->links() }}
            </div>
        </div>
        @endsection
