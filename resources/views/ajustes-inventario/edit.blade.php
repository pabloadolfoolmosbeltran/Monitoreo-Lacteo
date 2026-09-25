@extends('layouts.app')
@section('commercial-panel', '1')
@section('title', 'Editar ajuste #'.$ajuste->id)
@section('subtitle', 'La corrección modifica el saldo mediante un movimiento auditado.')
@section('content')
<form class="panel" method="POST" action="{{ route('ajustes-inventario.update', $ajuste) }}">
    @csrf
    @method('PUT')
    <p>
        <strong>
        Lote:
        </strong> 
        {{ $ajuste->lote_id }} 
        · 
        {{ $ajuste->lote?->presentacion?->producto?->nombre }} 
        · 
        {{ $ajuste->lote?->ingresoProductor?->productor?->nombre_completo }}
    </p>
    <p>
        <strong>
        Saldo actual:
        </strong> {{ $ajuste->lote?->cantidad_disponible }}
    </p>
    <label>
        Cantidad física corregida
        <input type="number" name="cantidad_nueva" min="0" step="1" required value="{{ old('cantidad_nueva', $ajuste->cantidad_nueva) }}">
    </label>
    <label>
        Tipo de motivo
        <select name="tipo_motivo" required>
            @foreach(['conteo' => 'Error de conteo', 'daño' => 'Daño', 'remanente' => 'Remanente', 'otro' => 'Otro'] as $valor => $texto)
                <option value="{{ $valor }}" @selected(old('tipo_motivo', $ajuste->tipo_motivo) === $valor)>
                    {{ $texto }}
                </option>
            @endforeach
        </select>
    </label>
    <label>
            Motivo
        <input name="motivo" required minlength="3" maxlength="1000" value="{{ old('motivo', $ajuste->motivo) }}">
    </label>
    <button>Guardar corrección</button>
</form>
@endsection
