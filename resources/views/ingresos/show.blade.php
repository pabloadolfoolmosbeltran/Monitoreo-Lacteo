@extends('layouts.app')
@section('commercial-panel', '1')
@section('title','Entrada de Inventario #'.$ingreso->id)
@section('subtitle',$ingreso->productor->nombre_completo ?? 'Proveedor histórico')
@section('actions')
<div class="row">
    <a class="button secondary" href="/ingresos-productores">
        ← Regresar</a>
        <a class="button secondary" href="/ingresos-productores/{{ $ingreso->id }}/pdf">
            PDF
        </a>
        <a class="button secondary" href="/ingresos-productores/{{ $ingreso->id }}/excel">
            Excel
        </a>
    </div>
@endsection
@section('content')
<div class="stats">
    <div class="panel">
        <span class="muted">
            Estado
        </span>
        <strong>
            {{ ucfirst($ingreso->estado) }}
        </strong>
    </div>
    <div class="panel">
        <span class="muted">
            Fecha
        </span>
        <strong>
            {{ optional($ingreso->fecha_ingreso)->format('d/m/Y') }}
        </strong>
    </div>
    <div class="panel">
        <span class="muted">
            Lotes
        </span>
        <strong>
            {{ $ingreso->items->count() }}
        </strong>
    </div>
</div>
@if($ingreso->observaciones)
    <div class="panel">
        {{ $ingreso->observaciones }}
    </div>
@endif
@foreach($ingreso->items as $item)
<section class="panel">
  <div class="section-heading">
    <h2>
        {{ $item->presentacion->nombre ?? 'Presentación retirada' }}
         <span class="badge">
            Lote #{{ $item->id }}
        </span>
    </h2>
    <strong>
        {{ number_format($item->cantidad_disponible,0,',','.') }} disponibles
    </strong>
</div>
  <p class="muted">
    Entrada: {{ number_format($item->cantidad_ingresada,0,',','.') }}
     · Acopio:
    {{ $item->precio_acopio_unitario === null ? 'Pendiente' : 'Bs. '.number_format($item->precio_acopio_unitario,2,',','.') }}
     · Venta: Bs.
     {{ number_format($item->precio_venta_unitario,2,',','.') }}
      · Caducidad:
      {{ optional($item->fecha_caducidad)->format('d/m/Y') ?? 'Sin fecha · venta bloqueada' }}
    </p>
  <details>
    <summary>
        Ventas({{ $item->ventas->count() }})
    </summary>
    <div class="table-wrap">
        <table>
                <thead>
                    <tr>
                        <th>
                            Venta
                        </th>
                        <th>
                            Cantidad
                        </th>
                        <th>
                            Fecha
                        </th>
                    </tr>
                </thead>
            <tbody>
                @forelse($item->ventas as $venta)
                <tr>
                    <td>
                        {{ $venta->id }}
                    </td>
                    <td>
                        {{ number_format($venta->cantidad_vendida,0,',','.') }}
                    </td>
                    <td>
                        {{ $venta->fecha_venta }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3">
                        Sin ventas.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</details>
  @if(auth()->user()->rol === 'Administrador' && $ingreso->estado === 'abierta' && (!$item->fecha_caducidad || $item->precio_acopio_unitario === null))
  <details>
        <summary>
            Regularizar datos pendientes del lote
        </summary>
        <form data-async-action action="/lotes/{{ $item->id }}/regularizar" method="POST" class="form-grid">
            @csrf @method('PATCH')
            @if(!$item->fecha_caducidad)
            <label>
                Caducidad
                <input type="date" name="fecha_caducidad" required min="{{ now()->addDay()->toDateString() }}">
            </label>
            @endif
            @if($item->precio_acopio_unitario === null)
            <label>
                Precio de acopio (Bs.)
                <input type="number" name="precio_acopio_unitario" step="0.01" min="0">
            </label>
            @endif
            <label>
                Motivo
                <input name="motivo" required minlength="3" maxlength="500">
            </label>
            <button>
                Guardar regularización
            </button>
        </form>
    </details>
  @endif
</section>
@endforeach
@endsection
