@extends('layouts.app')
@section('commercial-panel', '1')
@section('title', 'Nuevo ajuste de inventario')
@section('subtitle', 'Indica el conteo físico final del lote; el sistema calculará la diferencia.')
@section('content')
<form class="panel" method="POST" action="{{ route('ajustes-inventario.store') }}">@csrf
<label>
    Lote
    <select name="lote_id" required>
            <option value="">
                Seleccione
            </option>
            @foreach($lotes as $lote)
                <option value="{{ $lote->id }}" 
                    @selected(old('lote_id')==$lote->id)>
                    {{ $lote->id }} 
                    · 
                    {{ $lote->presentacion?->producto?->nombre }} 
                    · 
                    {{ $lote->ingresoProductor?->productor?->nombre_completo }} 
                    · 
                    actual {{ $lote->cantidad_disponible }}
                </option>
            @endforeach
    </select>
</label>
<label>
    Cantidad física final
    <input type="number" name="cantidad_nueva" min="0" step="1" required value="{{ old('cantidad_nueva') }}">
</label>
<label>
    Tipo de motivo
    <select name="tipo_motivo" required>
        <option value="conteo">
            Error de conteo
        </option>
        <option value="daño">Daño</option>
        <option value="remanente">Remanente</option>
        <option value="otro">Otro</option>
    </select>
</label>
<label>
    Motivo
    <input name="motivo" required minlength="3" maxlength="1000" value="{{ old('motivo') }}">
</label>
<button>Guardar ajuste</button>
</form>
@endsection
