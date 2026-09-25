@extends('layouts.app')


@section('content')

@if(auth()->check() && in_array(auth()->user()->rol, ['Administrador', 'Trabajador']))
<section class="commercial-dashboard mb-4" aria-labelledby="commercial-dashboard-title">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <p class="text-uppercase text-success fw-bold small mb-1">Operación rápida</p>
            <h2 id="commercial-dashboard-title" class="h4 mb-0">Acopio y ventas</h2>
        </div>
        <span class="badge rounded-pill text-bg-light border">Existencia trazable por lote · Bs.</span>
    </div>
    <div class="row g-3">
        @foreach([
            ['route' => route('pos.index'), 'icon' => 'bi-cart3', 'label' => 'Ventas', 'help' => 'Carrito y cobro rápido'],
            ['route' => route('ingresos-productores.index'), 'icon' => 'bi-box-arrow-in-down', 'label' => 'Entradas', 'help' => 'Registrar inventario recibido'],
            ['route' => route('productores.index'), 'icon' => 'bi-person-vcard', 'label' => 'Proveedores', 'help' => 'Directorio independiente'],
            ['route' => route('inventario.index'), 'icon' => 'bi-boxes', 'label' => 'Inventario por Lote', 'help' => 'Existencias, caducidad y ajustes'],
            ['route' => route('reportes-comerciales.index'), 'icon' => 'bi-file-earmark-bar-graph', 'label' => 'Reportes de Ventas', 'help' => 'Vista, PDF y Excel'],
        ] as $action)
            <div class="col-12 col-sm-6 col-xl">
                <a href="{{ $action['route'] }}" class="commercial-dashboard-action">
                    <i class="bi {{ $action['icon'] }}"></i>
                    <span><strong>{{ $action['label'] }}</strong><small>{{ $action['help'] }}</small></span>
                    <i class="bi bi-chevron-right ms-auto"></i>
                </a>
            </div>
        @endforeach
    </div>
</section>
@endif

<div id="dashboard-config"
     data-dashboard-url="{{ route('dashboard.datos') }}"
     data-temperaturas-url="{{ route('dashboard.temperaturas') }}"></div>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-people"></i></div>
                <div>
                    <h5>Total Usuarios</h5>
                    <h2>{{ $totalUsuarios }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-person-check"></i></div>
                <div>
                    <h5>Usuarios Registrados</h5>
                    <h2>{{ $totalUsuarios }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-cpu"></i></div>
                <div>
                    <h5>Dispositivos</h5>
                    <h2>{{ $totalDispositivos }}</h2>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    

    <!-- TARJETA 1: Tipo de Cuajo (Se muestra solo si hay producción activa) -->
    <div class="col-md-6 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-droplet-fill"></i></div>
                <div>
                    <h5>Tipo de Cuajo</h5>
                    <h2 id="valTipoCuajo" style="font-size: 1.3rem; margin-top: 5px;">
                        @if($produccion && strtolower($produccion->estado) == 'en proceso')
                            {{ $produccion->producto->tipo_cuajo ?? 'No especificado' }}
                        @else
                            --
                        @endif
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <!-- TARJETA 2: Cuajo Necesario (Calculado en tiempo real según la leche) -->
    <div class="col-md-6 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-water"></i></div>
                <div>
                    <h5>Cuajo Necesario</h5>
                    <h2 id="valCantidadCuajo">
                        @if($produccion && strtolower($produccion->estado) == 'en proceso')
                            {{ number_format((float) ($produccion->cantidad_cuajo ?? 0), 2) }} {{ $produccion->producto?->unidad_cuajo ?? 'ml' }}
                        @else
                            --
                        @endif
                    </h2>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-gear-wide-connected"></i></div>
                <div>
                    <h5>Producciones Activas</h5>
                    <h2 id="produccionesActivas">
                        {{ $produccionesActivas }}
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div>
                    <h5>Alertas Pendientes</h5>
                    <h2 id="alertasPendientes">
                        {{ $alertasPendientes }}
                    </h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card stat-card mb-4">
    <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon"><i class="bi bi-thermometer-half"></i></div>
        <div>
            <h5>Última Temperatura Registrada</h5>
            <h2 id="temperaturaActual">
                {{ $sensor?->temperatura_actual ?? ($ultimaLectura->temperatura ?? '--') }} °C
            </h2>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card panel-card">
            <div class="card-header text-white">
                <i class="bi bi-activity me-2"></i>Estado actual del sistema
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="estado-label">Producción</div>
                        <span class="badge bg-{{ $produccion ? 'success' : 'secondary' }}">
                            {{ $produccion->estado ?? 'Sin producción' }}
                        </span>
                    </div>

                    <div class="col-md-3">
                        <div class="estado-label">Temperatura objetivo</div>
                        <span class="estado-valor">
                            {{ $produccion?->temperatura_objetivo ?? '--' }} °C
                        </span>
                    </div>

                    <div class="col-md-3">
                        <div class="estado-label">Motor</div>
                        <span id="estadoMotorTexto" class="estado-valor">
                            @if($motor && $motor->estado)
                                🟢 Encendido
                            @else
                                🔴 Apagado
                            @endif
                        </span>
                    </div>

                    <div class="col-md-3">
                        <div class="estado-label">Ventilador</div>
                        <span id="estadoVentiladorTexto" class="estado-valor">
                            @if($ventilador && $ventilador->estado)
                                🟢 Encendido
                            @else
                                🔴 Apagado
                            @endif
                        </span>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-4">
                        <div class="estado-label">Sensor</div>
                        <span id="estadoSensorTexto" class="estado-valor">
                            @if($sensor && $sensor->estado == 'Activo')
                                🟢 Activo
                            @else
                                🔴 Inactivo
                            @endif
                        </span>
                    </div>

                    <div class="col-md-4">
                        <div class="estado-label">Última lectura</div>
                        <span id="ultimaLecturaTexto" class="estado-valor">
                            {{ $sensor?->temperatura_actual ?? ($ultimaLectura->temperatura ?? '--') }} °C
                        </span>
                    </div>

                    <div class="col-md-4">
                        <div class="estado-label">Alertas pendientes</div>
                        <span id="alertasPendientesBadge" class="badge bg-danger">
                            {{ $alertasPendientes }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<hr class="my-4">

<div class="row mt-3">
    <div class="col-md-6">
        <div class="estado-label">ESP32</div>
        <span id="esp32Badge" class="badge {{ $esp32Conectado ? 'bg-success' : 'bg-danger' }}">
            {{ $esp32Conectado ? '🟢 Conectado' : '🔴 Desconectado' }}
        </span>
    </div>

    <div class="col-md-6">
        <div class="estado-label">Última conexión</div>
        <span id="esp32UltimaConexion" class="estado-valor">
            @if($dispositivo && $dispositivo->ultima_conexion)
                {{ $dispositivo->ultima_conexion->format('d/m/Y H:i:s') }}
            @else
                Sin registros
            @endif
        </span>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-3">
        <div class="card mini-stat-card">
            <div class="card-body text-center">
                <h5><i class="bi bi-gear me-1"></i>Motor</h5>
                <h2 id="estadoMotor">--</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card mini-stat-card">
            <div class="card-body text-center">
                <h5><i class="bi bi-fan me-1"></i>Ventilador</h5>
                <h2 id="estadoVentilador">--</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card mini-stat-card">
            <div class="card-body text-center">
                <h5><i class="bi bi-broadcast me-1"></i>Sensor</h5>
                <h2 id="estadoSensor">--</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card mini-stat-card">
            <div class="card-body text-center">
                <h5><i class="bi bi-router me-1"></i>ESP32</h5>
                <h2 id="estadoESP32">Desconocido</h2>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card chart-card">
            <div class="card-header text-white">
                <i class="bi bi-graph-up"></i> Historial de Temperatura en Tiempo Real
            </div>
            <div class="card-body">
                <canvas id="graficoTemperatura" style="max-height: 250px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Alerta de Temperatura Crítica -->
<div class="modal fade" id="modalAlertaEmergencia" tabindex="-1" aria-labelledby="modalAlertaLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalAlertaLabel">🚨 ¡ALERTA DE TEMPERATURA CRÍTICA!</h5>
            </div>
            <div class="modal-body text-center">
                <h1 class="display-3 text-danger fw-bold" id="temperaturaAlertaModal">--°C</h1>
                <p class="lead">La temperatura actual ha alcanzado el objetivo configurado en el sistema.</p>
                <div class="alert alert-warning">
                    ¡Temperatura objetivo alcanzada con éxito! Verifique el estado del proceso.
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-danger btn-lg px-4" data-bs-dismiss="modal" data-silenciar-alarma>Entendido / Silenciar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Producción Finalizada -->
<div class="modal fade" id="modalProduccionFinalizada" tabindex="-1" aria-labelledby="modalProdFinalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-success shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalProdFinalLabel">🎉 ¡PRODUCCIÓN FINALIZADA CON ÉXITO!</h5>
            </div>
            <div class="modal-body text-center">
                <h1 class="display-4 text-success fw-bold">FINALIZADA</h1>
                <p class="lead">El sistema ha completado el proceso de manera exitosa y los actuadores se han apagado.</p>
                <div class="alert alert-success">
                    La temperatura bajó al nivel requerido y el lote se ha cerrado correctamente.
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success btn-lg px-4" data-bs-dismiss="modal" data-silenciar-alarma>Entendido / Silenciar</button>
            </div>
        </div>
    </div>
</div>

<!-- Panel dinámico de alerta o finalización -->
<div id="panelAlertaContenedor" class="row mt-4 d-none">
    <div class="col-md-12">
        <div id="panelAlertaCard" class="card shadow-lg border-danger">
            <div id="panelAlertaHeader" class="card-header bg-danger text-white">
                <h5 id="panelAlertaTitulo" class="mb-0">🚨 ¡ALERTA DEL SISTEMA!</h5>
            </div>
            <div class="card-body text-center">
                <h2 id="panelAlertaMensaje" class="fw-bold text-danger mb-3">--</h2>
                <p id="panelAlertaDetalle" class="lead">Atención requerida en el proceso.</p>
                <button type="button" class="btn btn-danger btn-lg px-5" data-aceptar-alerta-panel>
                    ✅ Entendido / Aceptar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
