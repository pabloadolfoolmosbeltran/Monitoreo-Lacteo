@extends('layouts.app')

@section('commercial-panel', '1')

@section('title', 'Descartes de Productos')

@section('subtitle', 'Bajas por caducidad, daño o deterioro con pérdida económica trazable.')

@section('actions')
    <a class="button" href="{{ route('descartes-productos.create') }}">+ Registrar descarte</a>
@endsection

@section('content')
    <form class="panel filter-grid" method="GET">
        <label>
            Proveedor
            <select name="productor_id">
                <option value="">Todos</option>
                @foreach($productores as $p)
                    <option value="{{ $p->id }}" @selected((string)($filtros['productor_id'] ?? '') === (string) $p->id)>
                        {{ $p->nombre_completo }}
                    </option>
                @endforeach
            </select>
        </label>

        <label>
            Desde
            <input type="date" name="desde" value="{{ $filtros['desde'] ?? '' }}">
        </label>

        <label>
            Hasta
            <input type="date" name="hasta" value="{{ $filtros['hasta'] ?? '' }}">
        </label>

        <label>
            Estado
            <select name="estado">
                <option value="">Todos</option>
                <option value="pendiente" @selected(($filtros['estado'] ?? '') === 'pendiente')>Pendiente</option>
                <option value="procesado" @selected(($filtros['estado'] ?? '') === 'procesado')>Procesado</option>
            </select>
        </label>

        <label>
            Motivo
            <select name="tipo_motivo">
                <option value="">Todos</option>
                @foreach(['caducado','dañado','deterioro','otro'] as $tipo)
                    <option @selected(($filtros['tipo_motivo'] ?? '') === $tipo)>{{ $tipo }}</option>
                @endforeach
            </select>
        </label>

        <button>Buscar</button>
    </form>

    <section class="metric-grid">
        <article class="metric blocked">
            <small>Pérdida del filtro</small>
            <strong>Bs. {{ number_format((float)$total,2,',','.') }}</strong>
        </article>
    </section>

    <p>
        <a class="button secondary" data-full-navigation href="{{ route('descartes-productos.reporte.pdf', request()->query()) }}">
            PDF consolidado
        </a>
        <a class="button secondary" data-full-navigation href="{{ route('descartes-productos.csv', request()->query()) }}">
            CSV
        </a>
    </p>

    <div class="panel table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nro</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Pérdida</th>
                    <th>Notas</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($descartes as $d)
                    <tr>
                        <td>
                            {{ $d->id }}
                        </td>
                        <td>
                            {{ $d->lote?->presentacion?->producto?->nombre }} 
                                <small>
                                {{ $d->lote?->presentacion?->nombre }}
                                </small>
                            <small>
                                Lote {{ $d->lote_id }}
                            </small>
                        </td>
                        
                        <td>{{ $d->cantidad }}</td>
                        <td>{{ number_format((float)$d->perdida_total,2,',','.') }}</td>
                        {{-- ¿Qué hace? Muestra la nota guardada o actualizada para identificar el motivo operativo del descarte. --}}
                        <td>{{ $d->notas ?: 'Sin notas' }}</td>
                        <td><span class="badge">{{ ucfirst($d->estado) }}</span></td>
                        <td>
                            <a href="{{ route('descartes-productos.show', $d) }}">Ver</a> ·
                            <a href="{{ route('descartes-productos.pdf', $d) }}">PDF</a>
                            @can('update', $d)
                                · <a href="{{ route('descartes-productos.edit', $d) }}">Editar</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No hay descartes para los filtros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $descartes->links() }}
    </div>
@endsection
