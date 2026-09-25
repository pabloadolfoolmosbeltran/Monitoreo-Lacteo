@extends('layouts.app')
@section('commercial-panel', '1')
@section('title', 'Ajuste '.$ajuste->id)
@section('subtitle', 'Registro de auditoría del inventario.')
@section('content')
<section class="panel">
    <p>
        <strong>
            Lote:
        </strong> 
        {{ $ajuste->lote_id }}
    </p>
    <p>
        <strong>
            Diferencia:
        </strong> 
        ({{ $ajuste->diferencia }})
    </p>
    <p>
        <strong>
            Cantidad:
        </strong> 
        {{ $ajuste->cantidad_anterior }} → {{ $ajuste->cantidad_nueva }} ({{ $ajuste->diferencia }})
    </p>
    <p>
        <strong>
            Motivo:
        </strong> 
        {{ $ajuste->motivo }}
    </p>
    <p>
        <strong>
            Creado por:
        </strong> 
        {{ $ajuste->creador?->name }}
    </p>
    <p>
        <strong>
            Estado:
        </strong> 
        {{ ucfirst($ajuste->estado) }}
    </p>
    @can('update', $ajuste)
        <p>
            <a class="button" href="{{ route('ajustes-inventario.edit', $ajuste) }}">
                Editar ajuste
            </a>
        </p>
    @endcan
    @if($ajuste->estado==='anulado')
        <p>
            <strong>
                Anulación:
            </strong> 
            {{ $ajuste->motivo_anulacion }} 
            · {{ $ajuste->anulador?->name }} 
            · {{ $ajuste->anulado_en }}
        </p>
    @endif
</section>
@endsection
