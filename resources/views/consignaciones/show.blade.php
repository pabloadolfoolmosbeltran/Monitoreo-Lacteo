@extends('layouts.app')

@section('content')
@php
    $estadoBadge = [
        'abierta' => 'success',
        'liquidada' => 'primary',
        'devuelta' => 'secondary',
    ][$consignacion->estado] ?? 'secondary';
@endphp

<div class="container-fluid">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h2 class="mb-1 fw-bold">
                <i class="bi bi-receipt me-2"></i>Consignación #{{ $consignacion->id }}
            </h2>
            <div class="text-muted">
                {{ $consignacion->productor->name }} · {{ $consignacion->fecha_entrada->format('d/m/Y') }}
            </div>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-{{ $estadoBadge }} align-self-center px-3 py-2">{{ ucfirst($consignacion->estado) }}</span>
            <a href="{{ route('consignaciones.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Monto productor</div>
                    <div class="fs-4 fw-bold">Bs {{ $consignacion->monto_productor }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Margen encargado</div>
                    <div class="fs-4 fw-bold">Bs {{ $consignacion->margen_encargado }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
