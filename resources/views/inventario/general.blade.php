@extends('layouts.app')

@section('commercial-panel', '1')

@section('title', 'Inventario General')

@section('subtitle', 'Stock consolidado por producto, presentación y productor.')

@section('content')
    <!-- Resumen de métricas -->
    <div class="metric-grid">
        <article class="metric sellable">
            <small>Disponible para venta</small>
            <strong>{{ number_format((int) $totales->vendible, 0, ',', '.') }}</strong>
        </article>

        <article class="metric warning">
            <small>Próximo a vencer (7 días)</small>
            <strong>{{ number_format((int) $totales->proximo, 0, ',', '.') }}</strong>
        </article>

        <article class="metric blocked">
            <small>Vencido o bloqueado</small>
            <strong>{{ number_format((int) $totales->no_vendible, 0, ',', '.') }}</strong>
        </article>

        <article class="metric">
            <small>Stock físico</small>
            <strong>{{ number_format((int) $totales->fisico, 0, ',', '.') }}</strong>
        </article>
    </div>

    <!-- Filtros de búsqueda -->
    <form method="GET"
          action="{{ route('inventario-general.index') }}"
          class="panel filter-grid">
        <label>
            Producto o presentación
            <input type="search"
                   name="producto"
                   value="{{ $filtros['producto'] ?? '' }}"
                   placeholder="Ej.: yogurt frutilla 2 litros">
        </label>

        <label>
            Productor
            <input type="search"
                   name="productor"
                   value="{{ $filtros['productor'] ?? '' }}"
                   placeholder="Nombre o unidad productiva">
        </label>

        <label>
            Vence desde
            <input type="date"
                   name="vence_desde"
                   value="{{ $filtros['vence_desde'] ?? '' }}">
        </label>

        <label>
            Vence hasta
            <input type="date"
                   name="vence_hasta"
                   value="{{ $filtros['vence_hasta'] ?? '' }}">
        </label>

        <div class="filter-actions">
            <button><i class="bi bi-search"></i> Buscar</button>
            <a class="button secondary"
               href="{{ route('inventario-general.index') }}">
                Limpiar
            </a>
        </div>
    </form>

    <!-- Tabla de resultados -->
    <div class="panel table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Productor</th>
                    <th>Vendible</th>
                    <th>No vendible</th>
                    <th>Físico</th>
                    <th>Lotes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($grupos as $grupo)
                    <tr>
                        <td>
                            <strong>{{ $grupo->producto }} · {{ $grupo->presentacion }}</strong>
                            <small>
                                {{ $grupo->sabor }} {{ $grupo->contenido }} {{ $grupo->unidad }}
                            </small>
                        </td>
                        <td>
                            {{ $grupo->nombres }} {{ $grupo->primer_apellido }}
                            <small>{{ $grupo->nombre_unidad_productiva }}</small>
                        </td>
                        <td class="stock-positive">
                            {{ number_format((int) $grupo->vendible, 0, ',', '.') }}
                        </td>
                        <td class="stock-blocked">
                            {{ number_format((int) $grupo->no_vendible, 0, ',', '.') }}
                        </td>
                        <td>
                            <strong>{{ number_format((int) $grupo->fisico, 0, ',', '.') }}</strong>
                        </td>
                        <td>
                            {{ $grupo->lotes }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No existe stock que coincida con la búsqueda.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $grupos->links() }}
        </div>
    </div>

    <!-- Detalle de lotes (cuando se solicite) -->
    @if($detalle)
        <section class="panel table-wrap">
            <div class="section-heading">
                <h2>Detalle de lotes</h2>
                <a class="button secondary"
                   href="{{ route('inventario-general.index', request()->except(['detalle_presentacion','detalle_productor'])) }}">
                    Cerrar
                </a>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Lote</th>
                        <th>Producto</th>
                        <th>Productor</th>
                        <th>Saldo</th>
                        <th>Vencimiento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detalle as $item)
                        <tr>
                            <td>#{{ $item->id }}</td>
                            <td>
                                {{ $item->presentacion?->producto?->nombre }}
                                ·
                                {{ $item->presentacion?->nombre }}
                            </td>
                            <td>{{ $item->ingresoProductor?->productor?->nombre_completo }}</td>
                            <td>{{ number_format((int) $item->cantidad_disponible, 0, ',', '.') }}</td>
                            <td>{{ $item->fecha_caducidad?->format('d/m/Y') ?? 'Sin fecha' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif
@endsection
