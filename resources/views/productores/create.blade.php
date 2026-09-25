@extends('layouts.app')
@section('commercial-panel', '1')
@section('title','Nuevo Proveedor')
@section('subtitle','Registra los datos de contacto y la unidad productiva del proveedor.')
@section('actions')
    <a class="button secondary" href="{{ route('productores.index') }}">← Regresar</a>
@endsection
@section('content')
<form action="{{ route('productores.store') }}" method="POST" class="panel">
    @csrf
    <h2>Información del proveedor</h2>
    <p class="muted">Esta ficha no crea credenciales ni permite iniciar sesión.</p>
    @include('productores._form')
    <div class="form-actions">
        <button type="submit">Guardar proveedor</button>
        <a class="button secondary" href="{{ route('productores.index') }}">Cancelar</a>
    </div>
</form>
@endsection
