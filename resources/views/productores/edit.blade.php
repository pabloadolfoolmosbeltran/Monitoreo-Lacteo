@extends('layouts.app')
@section('commercial-panel', '1')
@section('title','Editar Proveedor')
@section('subtitle',$productor->nombre_completo)
@section('actions')
    <a class="button secondary" href="{{ route('productores.show', $productor) }}">← Regresar</a>
@endsection
@section('content')
<form action="{{ route('productores.update', $productor) }}" method="POST" class="panel">
    @csrf
    @method('PUT')
    <h2>Información del proveedor</h2>
    @include('productores._form')
    <div class="form-actions">
        <button type="submit">Actualizar proveedor</button>
        <a class="button secondary" href="{{ route('productores.index') }}">Cancelar</a>
    </div>
</form>
@endsection
