@extends('layouts.app')

@push('styles')
@vite(['resources/css/produccion.css'])
@endpush

@section('content')

<div class="container dairy-production-container pt-3">
    <h2 class="mb-4 fw-bold" style="color: var(--turquoise-primary);">
        🥛 Producción
    </h2>

    @if(session('success'))
    <div class="alert border-0 shadow-sm mb-4 rounded-4" style="background-color: var(--turquoise-light); color: #00695c;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm mb-4 rounded-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm mb-4 rounded-4">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($produccionActiva)
    @php
        $factorCuajo = (float)($produccionActiva->producto->cuajo_por_litro ?? 0);
        $totalCuajoCalculado = $produccionActiva->cantidad_leche * $factorCuajo;
        $unidadCuajo = $produccionActiva->producto->unidad_cuajo ?? 'ml';
        $tipoCuajo = $produccionActiva->producto->tipo_cuajo ?? 'Insumo/Cuajo';
    @endphp

    <div class="dairy-card">
        <div class="dairy-card-header-active d-flex align-items-center">
            <i class="bi bi-activity me-2 fs-5"></i>
            <h5 class="mb-0 fw-bold">Producción Activa</h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <p class="mb-2">
                        <strong style="color: var(--text-muted);">Productor responsable:</strong><br>
                        <span class="fw-semibold text-dark">{{ $produccionActiva->user?->nombre_unidad_productiva ?? $produccionActiva->user?->name }}</span>
                    </p>
                    <p class="mb-2">
                        <strong style="color: var(--text-muted);">Producto:</strong><br>
                        <span class="fw-semibold text-dark">{{ $produccionActiva->producto->nombre }}</span>
                    </p>
                    <p class="mb-2">
                        <strong style="color: var(--text-muted);">Estado:</strong><br>
                        <span class="status-badge mt-1">
                            <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem; color: var(--turquoise-primary);"></i>
                            {{ $produccionActiva->estado }}
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2">
                        <strong style="color: var(--text-muted);">Fecha de inicio:</strong><br>
                        <span class="fw-semibold text-dark">{{ $produccionActiva->fecha_inicio->format('d/m/Y H:i') }}</span>
                    </p>
                    <p class="mb-2">
                        <strong style="color: var(--text-muted);">Cantidad de leche:</strong><br>
                        <span class="fw-semibold text-dark">{{ $produccionActiva->cantidad_leche }} Litros</span>
                    </p>
                    <p class="mb-2">
                        <strong style="color: var(--text-muted);">Temperatura objetivo:</strong><br>
                        <span class="fw-semibold text-dark">{{ $produccionActiva->temperatura_objetivo }} °C</span>
                    </p>
                </div>
            </div>

            <!-- DETALLE DE INSUMO/CUAJO UTILIZADO -->
            <div class="mt-3 p-3 rounded-3 border shadow-sm" style="background-color: var(--milk-bg); border-color: var(--border-soft) !important;">
                <div class="d-flex align-items-center">
                    <div class="me-3 text-center">
                        <i class="bi bi-droplet-half fs-2" style="color: var(--turquoise-primary);"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="d-block text-uppercase fw-semibold" style="color: var(--text-muted); font-size: 0.75rem;">
                            Insumo / Cuajo Aplicado y Descontado:
                        </span>
                        <div class="d-flex align-items-baseline gap-2 flex-wrap">
                            <span class="fw-bold fs-4 text-dark">
                                {{ number_format($totalCuajoCalculado, 2) }} {{ $unidadCuajo }}
                            </span>
                            <span class="badge rounded-pill" style="background-color: var(--turquoise-light); color: var(--turquoise-primary); border: 1px solid var(--border-soft); font-size: 0.8rem;">
                                {{ $tipoCuajo }}
                            </span>
                        </div>
                        <small class="text-muted d-block mt-1">
                            Dosis configurada: <strong>{{ $factorCuajo }} {{ $unidadCuajo }}/L</strong> para <strong>{{ $produccionActiva->cantidad_leche }} L</strong> en tina.
                        </small>
                    </div>
                </div>
            </div>

            @if($produccionActiva->observaciones)
            <hr style="border-color: var(--border-soft);">
            <p class="mb-0">
                <strong style="color: var(--text-muted);">Observaciones:</strong><br>
                <span class="text-dark">{{ $produccionActiva->observaciones }}</span>
            </p>
            @endif

            <hr style="border-color: var(--border-soft);">

            <form action="{{ url('/produccion/finalizar') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-industrial btn-industrial-danger shadow-sm">
                    Finalizar Producción
                </button>
            </form>
        </div>
    </div>
    @else
    <div class="dairy-card">
        <div class="dairy-card-header d-flex align-items-center">
            <i class="bi bi-plus-circle me-2 fs-5"></i>
            <h5 class="mb-0 fw-bold" style="color: var(--turquoise-primary);">Nueva Producción</h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ url('/produccion/iniciar') }}" method="POST">
                @csrf

                <!-- Campos ocultos para enviar tipo y cantidad de cuajo calculados -->
                <input type="hidden" name="tipo_cuajo" id="input-hidden-tipo-cuajo" value="">
                <input type="hidden" name="cantidad_cuajo" id="input-hidden-cantidad-cuajo" value="">
                
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="color: var(--text-muted);">
                        Productor responsable
                    </label>
                    @if(isset($usuario) && $usuario->rol == 'Administrador')
                        <select name="user_id" class="form-select shadow-sm" required>
                            <option value="">Seleccione un usuario/productor</option>
                            @foreach($users as $user)
                            <option
                                value="{{ $user->id }}"
                                {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->nombre_unidad_productiva ?? $user->name }} ({{ $user->rol }})
                            </option>
                            @endforeach
                        </select>
                    @else
                        <input
                            type="text"
                            class="form-control bg-light shadow-sm"
                            value="{{ ($usuario->nombre_unidad_productiva ?? $usuario->name) ?? '' }}"
                            readonly>
                        <input
                            type="hidden"
                            name="user_id"
                            value="{{ $usuario->id ?? '' }}">
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="color: var(--text-muted);">
                        Producto
                    </label>
                    <select name="producto_id" id="select-producto" class="form-select shadow-sm" required>
                        <option value="">Seleccione un producto</option>
                        @foreach($productos as $producto)
                        <option
                            value="{{ $producto->id }}"
                            {{ old('producto_id') == $producto->id ? 'selected' : '' }}>
                            {{ $producto->nombre }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="color: var(--text-muted);">
                        Cantidad de leche (Litros)
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="1"
                        name="cantidad_leche"
                        id="input-cantidad-leche"
                        class="form-control shadow-sm"
                        placeholder="Ej: 100"
                        value="{{ old('cantidad_leche') }}"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="color: var(--text-muted);">
                        Temperatura objetivo (°C)
                    </label>
                    <input
                        type="number"
                        step="0.1"
                        min="1"
                        name="temperatura_objetivo"
                        id="input-temperatura-objetivo"
                        class="form-control shadow-sm"
                        placeholder="Ej: 38.0"
                        value="{{ old('temperatura_objetivo') }}"
                        required>
                    <small id="temp-sugerida-help" class="form-text text-muted mt-1" style="display: none;"></small>
                </div>

                <!-- CÁLCULO DINÁMICO E INFORMACIÓN DEL PRODUCTO -->
                <div id="info-lote-dinamico" style="display: none;" class="mb-4 p-3 border rounded-3 shadow-sm" style="background-color: var(--milk-bg); border-color: var(--border-soft);">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center mb-3 mb-md-0">
                            <img id="img-producto-referencial" src="" alt="Referencia" class="img-fluid rounded shadow-sm" style="max-height: 130px; object-fit: cover;">
                        </div>
                        <div class="col-md-9">
                            <h5 id="nombre-lote" class="fw-bold mb-2" style="color: var(--turquoise-primary);"></h5>
                            
                            <!-- Alerta dinámica de instrucciones -->
                            <div id="alerta-producto" class="alert alert-info py-2 mb-2 shadow-sm" style="display: none; font-size: 0.85rem; border: 1px solid #90caf9;">
                                <i class="bi bi-info-circle-fill me-1"></i> <strong>Aviso Importante:</strong> <span id="texto-alerta"></span>
                            </div>

                            <!-- Alerta de Stock Insuficiente -->
                            <div id="alerta-stock-insuficiente" class="alert alert-danger py-2 mb-2 shadow-sm" style="display: none; font-size: 0.85rem;">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>¡Atención!</strong> El stock actual de cuajo es insuficiente para procesar esta cantidad de leche.
                            </div>

                            <div class="p-3 bg-white border rounded shadow-sm">
                                <!-- Información Técnica y Térmica -->
                                <div class="row text-muted small mb-2 border-bottom pb-2">
                                    <div class="col-6 col-md-3">
                                        <span class="d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Tipo Cuajo:</span>
                                        <span id="tipo-cuajo-val" class="fw-bold text-dark fs-6">-</span>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <span class="d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Dosis Requerida:</span>
                                        <span id="dosis-cuajo-val" class="fw-bold text-dark fs-6">-</span>
                                    </div>
                                    <div class="col-6 col-md-3 mt-2 mt-md-0">
                                        <span class="d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Rango Temp:</span>
                                        <span id="rango-temp-val" class="fw-bold text-dark fs-6">-</span>
                                    </div>
                                    <div class="col-6 col-md-3 mt-2 mt-md-0">
                                        <span class="d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Stock Actual:</span>
                                        <span id="stock-actual-val" class="fw-bold text-primary fs-6">0</span> <span class="unidad-cuajo-val fw-bold text-primary">ml</span>
                                    </div>
                                </div>

                                <!-- Proyección de Consumo -->
                                <div class="d-flex justify-content-between align-items-center flex-wrap pt-1">
                                    <div>
                                        <span class="text-muted d-block mb-1" style="font-size: 0.85rem;">Cuajo Total a Gastar:</span>
                                        <h4 class="mb-0 text-dark">
                                            <span id="resultado-cuajo" class="text-danger fw-bold">0</span> <span class="unidad-cuajo-val text-danger fw-bold">ml</span>
                                        </h4>
                                        <small class="text-muted">Para <span id="litros-ingresados" class="fw-semibold">0</span> litros en tina</small>
                                    </div>
                                    <div class="text-md-end mt-2 mt-md-0 bg-light p-2 rounded border">
                                        <span class="text-muted d-block small fw-semibold">Stock Proyectado Tras Proceso:</span>
                                        <span id="stock-restante-val" class="fw-bold fs-5 text-success">0</span> <span class="unidad-cuajo-val fw-bold text-success">ml</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold" style="color: var(--text-muted);">
                        Observaciones
                    </label>
                    <textarea
                        name="observaciones"
                        class="form-control shadow-sm"
                        rows="3">{{ old('observaciones') }}</textarea>
                </div>

                <button type="submit" id="btn-iniciar-produccion" class="btn btn-industrial btn-industrial-success shadow-sm">
                    Iniciar Producción
                </button>
            </form>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
    window.produccionData = @json($productos);
</script>
@vite(['resources/js/produccion.js'])
@endpush
@endsection