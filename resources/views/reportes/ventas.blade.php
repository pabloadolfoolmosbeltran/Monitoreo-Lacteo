@extends('layouts.app')

@section('commercial-panel', '1')

@section('title', 'Reporte de ventas')

@section('subtitle', 'Ventas por comprobante con detalle trazable de productos, precios y proveedores.')

@section('actions')
    <a class="button secondary"
       data-full-navigation
       href="{{ route('reportes-comerciales.pdf', request()->query()) }}">
        PDF
    </a>
    <a class="button secondary"
       data-full-navigation
       href="{{ route('reportes-comerciales.excel', request()->query()) }}">
        Excel
    </a>
@endsection

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

        <label>
            Proveedor para ranking
            <select name="productor_id">
                <option value="">Todos</option>
                @foreach($productores as $productor)
                    <option value="{{ $productor->id }}"
                        @selected((string)request('productor_id') === (string)$productor->id)>
                        {{ $productor->nombre_completo }}
                    </option>
                @endforeach
            </select>
        </label>

        <button type="submit">Buscar</button>
        <a class="button secondary"
           href="{{ route('reportes-comerciales.index') }}">
            Limpiar
        </a>
    </form>

    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>Productos más vendidos</h2>
                <p class="muted">Top 15 por unidades en el período seleccionado.</p>
            </div>
            <a class="button secondary"
               data-full-navigation
               href="{{ route('reportes-comerciales.productos.csv', request()->only('desde','hasta','productor_id')) }}">
                Exportar datos CSV
            </a>
        </div>

        @if($productosVendidos->isNotEmpty())
            <div class="sales-ranking-chart"
                 style="--sales-chart-height: {{ min(360, max(220, $productosVendidos->count() * 30 + 100)) }}px">
                <canvas id="top-products-chart"
                        aria-label="Gráfico de productos más vendidos"></canvas>
            </div>

            <script type="application/json" id="top-products-data">
                @json($productosVendidos->values())
            </script>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Posición</th>
                            <th>Producto</th>
                            <th>Unidades</th>
                            <th>Monto Bs.</th>
                            <th>% del total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productosVendidos as $fila)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $fila['producto'] }}</td>
                                <td>{{ $fila['unidades'] }}</td>
                                <td>{{ number_format((float)$fila['monto'], 2, ',', '.') }}</td>
                                <td>{{ $fila['porcentaje'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="muted">No hay ventas para construir el ranking.</p>
        @endif
    </section>

    <div class="panel table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nro</th>
                    <th>Productos</th>
                    <th>Pago</th>
                    <th>Total Bs.</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->id }}</td>
                        <td>{{ $ticket->ventas->pluck('lote.presentacion.nombre')
                                            ->filter()
                                            ->unique()
                                            ->join(', ') }}</td>
                        <td>{{ ucfirst($ticket->metodo_pago) }}{{ $ticket->cliente ? ' · ' . $ticket->cliente : '' }}</td>
                        <td>{{ number_format((float)$ticket->total, 2, ',', '.') }}</td>
                        <td>
                            <button type="button"
                                    class="secondary"
                                    data-view-sale="{{ $ticket->id }}"
                                    aria-label="Ver detalle de venta {{ $ticket->id }}">
                                <i class="bi bi-eye"></i> Ver
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No hay ventas para los filtros indicados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $tickets->links() }}
        </div>
    </div>

    <dialog id="sale-preview">
        <div class="section-heading">
            <h2>Detalle de venta</h2>
            <button type="button"
                    class="secondary"
                    data-close-sale>
                Cerrar
            </button>
        </div>
        <div id="sale-preview-content"></div>
    </dialog>
@endsection