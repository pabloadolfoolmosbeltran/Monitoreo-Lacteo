@extends('layouts.app')
@section('commercial-panel', '1')
@section('title','Registrar entrada de inventario')
@section('subtitle','Identifica al proveedor y registra cantidades, precio estándar y caducidad por lote.')
@section('content')
<form id="ingreso-form" method="POST" action="/ingresos-productores">
    @csrf
    <div class="panel">
        <div class="section-heading">
            <div><h2>Proveedor de la entrada</h2><p class="muted">Selecciona un proveedor activo del directorio.</p></div>
            <a href="/productores">+ Registrar proveedor</a>
        </div>
        <label>Proveedor activo
            <select id="producer-id" name="productor_id" required>
                <option value="">Seleccionar proveedor</option>
                @foreach($productoresActivos as $productor)
                    <option value="{{ $productor->id }}" @selected((string) old('productor_id') === (string) $productor->id)>
                        {{ $productor->nombre_completo }}
                        {{ $productor->nombre_unidad_productiva ? ' — '.$productor->nombre_unidad_productiva : '' }}
                    </option>
                @endforeach
            </select>
        </label>
    </div>
    <div class="panel">
        <h2>Datos de la entrada</h2>
        <div class="form-grid">
            <label>
                Fecha de entrada
                <input type="date" name="fecha_ingreso" value="{{ old('fecha_ingreso',now()->toDateString()) }}" required>
            </label>
            <label>
                Observaciones
                <textarea name="observaciones">
                    {{ old('observaciones') }}
                </textarea>
            </label>
        </div>
    </div>
    <div class="section-heading">
        <h2>Presentaciones recibidas</h2>
        <button id="add-ingreso-item" type="button" class="secondary">
            + Agregar lote
        </button>
    </div>
    <div id="ingreso-items">
        @include('ingresos._item',['index'=>0])
    </div>
    <template id="ingreso-item-template">
        @include('ingresos._item',['index'=>'__INDEX__'])
    </template>
    <p class="muted">
        Las cantidades se registran por lote. La caducidad es obligatoria para habilitar la venta.
    </p>
    <div class="form-actions">
        <button>Guardar entrada</button>
        <a class="button secondary" href="/ingresos-productores">
            ← Regresar
        </a>
    </div>
</form>
@endsection
