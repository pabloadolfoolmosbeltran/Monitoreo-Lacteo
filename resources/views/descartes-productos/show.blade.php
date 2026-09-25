@extends('layouts.app')

@section('commercial-panel', '1')
@section('subtitle', 'Detalle y autorización de la baja de inventario.')

@section('content')
    <section class="panel">
        <h2>
            {{ $descarte->lote?->presentacion?->producto?->nombre }}
            ·
            {{ $descarte->lote?->presentacion?->nombre }}
        </h2>

        <p>Proveedor: {{ $descarte->lote?->ingresoProductor?->productor?->nombre_completo }}</p>

        <p>
            Lote {{ $descarte->lote_id }}
            · Cantidad: {{ $descarte->cantidad }}
            · Caducidad: {{ $descarte->fecha_caducidad?->format('d/m/Y') ?? 'sin fecha' }}
        </p>

        <p>
            Costo: Bs. {{ number_format((float) $descarte->costo_unitario, 2, ',', '.') }}
            · <strong>Pérdida: Bs. {{ number_format((float) $descarte->perdida_total, 2, ',', '.') }}</strong>
        </p>

        <p>
            {{ ucfirst($descarte->tipo_motivo) }} · {{ $descarte->notas }}
        </p>

        <p>Estado: {{ ucfirst($descarte->estado) }}</p>

        <a class="button secondary"
           data-full-navigation
           href="{{ route('descartes-productos.pdf', $descarte) }}">
            Descargar PDF
        </a>

        @can('process', $descarte)
            <form method="POST"
                  action="{{ route('descartes-productos.procesar', $descarte) }}">
                @csrf
                @method('PATCH')
                <button data-confirm-message="¿Procesar y descontar el stock?">
                    Procesar descarte
                </button>
            </form>

            <form method="POST"
                  action="{{ route('descartes-productos.destroy', $descarte) }}">
                @csrf
                @method('DELETE')
                <button class="secondary"
                        data-confirm-message="¿Eliminar este registro pendiente?">
                    Eliminar pendiente
                </button>
            </form>
        @endcan
    </section>
@endsection