@extends('layouts.app')

@section('commercial-panel', '1')

@section('title', 'Reporte de entradas de inventario')

@section('subtitle', 'Historial de lo recibido por proveedor, producto, fecha y hora.')

@section('content')
    <form class="panel toolbar" method="GET">
        <label>
            Desde
            <input type="date" name="desde" value="{{ request('desde') }}">
        </label>

        <label>
            Hasta
            <input type="date" name="hasta" value="{{ request('hasta') }}">
        </label>

        <label>
            Producto
            <input type="search"
                   name="producto"
                   value="{{ request('producto') }}"
                   placeholder="Producto o presentación">
        </label>

        <label>
            Proveedor
            <input type="search"
                   name="productor"
                   value="{{ request('productor') }}"
                   placeholder="Nombre, apellido o finca">
        </label>

        <button type="submit">Buscar</button>
        <a class="button secondary" href="{{ route('reportes-ingresos.index') }}">Limpiar</a>
    </form>

    <div class="panel table-wrap">
        <table>
            <thead>
                <tr>
                    <th>N</th>
                    <th>Fecha de ingreso</th>
                    <th>Proveedor</th>
                    <th>Producto / presentación</th>
                    <th>Cantidad</th>
                    <th>Precio venta Bs.</th>
                    <th>Caducidad</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ingresos as $ingreso)
                    @foreach($ingreso->items as $item)
                        <tr>
                            <td>
                                <a href="{{ route('ingresos-productores.show', $ingreso) }}">
                                    {{ $ingreso->id }}
                                </a>
                            </td>
                            {{-- ¿Qué hace? Muestra cuándo se recibió el lote para conservar la trazabilidad del reporte. --}}
                            <td>{{ $ingreso->fecha_ingreso?->format('d/m/Y H:i') }}</td>
                            <td>
                                {{ $ingreso->productor?->nombre_completo ?? 'Histórico' }}
                            </td>
                            <td>
                                {{ $item->presentacion?->producto?->nombre }}
                                <small>{{ $item->presentacion?->nombre }}</small>
                                
                            </td>
                            <td>{{ number_format((int) $item->cantidad_ingresada, 0, ',', '.') }}</td>
                            <td>{{ number_format((float) $item->precio_venta_unitario, 2, ',', '.') }}</td>
                            <td>{{ $item->fecha_caducidad?->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="7">No hay entradas para los filtros indicados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $ingresos->links() }}
        </div>
    </div>
@endsection
