@extends('layouts.app')

@section('content')
@php
    $items = old('items', [
        ['presentacion_id' => '', 'cantidad_recibida' => '', 'precio_productor' => '', 'precio_venta' => ''],
    ]);
@endphp

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 fw-bold">
            <i class="bi bi-plus-square me-2"></i>Nueva consignación
        </h2>
        <a href="{{ route('consignaciones.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Revise los datos marcados.
        </div>
    @endif

    <form method="POST" action="{{ route('consignaciones.store') }}">
        @csrf

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person-badge me-2"></i>Datos de entrada
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Productor</label>
                        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">Seleccione</option>
                            @foreach($productores as $productor)
                                <option value="{{ $productor->id }}" @selected((string) old('user_id') === (string) $productor->id)>
                                    {{ $productor->name }}{{ $productor->nombre_unidad_productiva ? ' - ' . $productor->nombre_unidad_productiva : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha de entrada</label>
                        <input type="date" name="fecha_entrada" value="{{ old('fecha_entrada', now()->toDateString()) }}" class="form-control @error('fecha_entrada') is-invalid @enderror" required>
                        @error('fecha_entrada')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control @error('observaciones') is-invalid @enderror" rows="2">{{ old('observaciones') }}</textarea>
                        @error('observaciones')<div class="invalid-feedback">{{ $message }}</div>@enderror
