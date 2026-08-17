@extends('layouts.app')

@section('content')

<h2 class="mb-4">Dashboard</h2>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Total Usuarios</h5>
                <h2>{{ $totalUsuarios }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Usuarios Registrados</h5>
                <h2>{{ $totalUsuarios }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Dispositivos</h5>
                <h2>{{ $totalDispositivos }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Producciones Activas</h5>
                <h2 id="produccionesActivas">
                    {{ $produccionesActivas }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Alertas Pendientes</h5>
                <h2 id="alertasPendientes">
                    {{ $alertasPendientes }}
                </h2>
            </div>
        </div>
    </div>
</div>

@if($ultimaLectura)
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h4>Última Temperatura Registrada</h4>
        <h2 id="temperaturaActual">
            {{ $ultimaLectura ? $ultimaLectura->temperatura : '--' }} °C
        </h2>
    </div>
</div>
@endif

@if($produccion)
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                Estado actual del sistema
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <h6>Producción</h6>
                        <span class="badge bg-success">
                            {{ $produccion->estado }}
                        </span>
                    </div>

                    <div class="col-md-3">
                        <h6>Temperatura objetivo</h6>
                        <strong>
                            {{ $produccion->temperatura_objetivo }} °C
                        </strong>
                    </div>

                    <div class="col-md-3">
                        <h6>Motor</h6>
                        <span id="estadoMotorTexto">
                            @if($motor && $motor->estado)
                                🟢 Encendido
                            @else
                                🔴 Apagado
                            @endif
                        </span>
                    </div>

                    <div class="col-md-3">
                        <h6>Ventilador</h6>
                        <span id="estadoVentiladorTexto">
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
                        <h6>Sensor</h6>
                        <span id="estadoSensorTexto">
                            @if($sensor && $sensor->estado == 'Activo')
                                🟢 Activo
                            @else
                                🔴 Inactivo
                            @endif
                        </span>
                    </div>

                    <div class="col-md-4">
                        <h6>Última lectura</h6>
                        <strong id="ultimaLecturaTexto">
                            @if($ultimaLectura)
                                {{ $ultimaLectura->temperatura }} °C
                            @endif
                        </strong>
                    </div>

                    <div class="col-md-4">
                        <h6>Alertas pendientes</h6>
                        <span id="alertasPendientesBadge" class="badge bg-danger">
                            {{ $alertasPendientes }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<hr>

<div class="row mt-3">
    <div class="col-md-6">
        <h6>ESP32</h6>
        <span id="esp32Badge" class="badge {{ $esp32Conectado ? 'bg-success' : 'bg-danger' }}">
            {{ $esp32Conectado ? '🟢 Conectado' : '🔴 Desconectado' }}
        </span>
    </div>

    <div class="col-md-6">
        <h6>Última conexión</h6>
        <span id="esp32UltimaConexion">
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
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h5>Motor</h5>
                <h2 id="estadoMotor">--</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h5>Ventilador</h5>
                <h2 id="estadoVentilador">--</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h5>Sensor</h5>
                <h2 id="estadoSensor">--</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h5>ESP32</h5>
                <h2 id="estadoESP32">Desconocido</h2>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                📈 Historial de Temperatura en Tiempo Real
            </div>
            <div class="card-body">
                <canvas id="graficoTemperatura" style="max-height: 250px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

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
                <button type="button" class="btn btn-danger btn-lg px-4" data-bs-dismiss="modal" onclick="silenciarAlarma()">Entendido / Silenciar</button>
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
                <button type="button" class="btn btn-success btn-lg px-4" data-bs-dismiss="modal" onclick="silenciarAlarma()">Entendido / Silenciar</button>
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
                <button type="button" class="btn btn-danger btn-lg px-5" onclick="aceptarAlertaPanel()">
                    ✅ Entendido / Aceptar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// --- VARIABLES GLOBALES ---
let grafico = null;
let alarmaSonando = false;
let alarmaSilenciada = false; 
let audioCtx = null;
let beepInterval = null;

let alertaTemperaturaDisparada = false;          
let alertaFinalizadaDisparada = false;
let idProduccionNotificada = null;

// Guarda el estado de la producción visto en el polling anterior,
// para solo disparar alertas cuando hay una TRANSICIÓN real y no al cargar la página.
let estadoProduccionAnterior = null;

// --- FUNCIONES DE AUDIO Y ALERTAS ---
function reproducirAlarmaSonora() {
    if (alarmaSilenciada || alarmaSonando) return;
    
    alarmaSonando = true;

    if (beepInterval) {
        clearInterval(beepInterval);
        beepInterval = null;
    }

    try {
        if (!audioCtx || audioCtx.state === 'closed') {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        
        beepInterval = setInterval(() => {
            if (!alarmaSonando || alarmaSilenciada) return;

            try {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                
                osc.type = 'square';
                osc.frequency.setValueAtTime(900, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
                
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                
                osc.start();
                osc.stop(audioCtx.currentTime + 0.2);
            } catch(err) {
                console.log("Error en beep:", err);
            }
        }, 400);
    } catch (e) {
        console.log("Audio bloqueado por navegador:", e);
    }
}

function silenciarAlarma() {
    alarmaSonando = false;
    alarmaSilenciada = true; 
    
    if (idProduccionNotificada) {
        sessionStorage.setItem(`prod_silenciada_${idProduccionNotificada}`, 'true');
    }

    if (beepInterval) {
        clearInterval(beepInterval);
        beepInterval = null;
    }
    
    if (audioCtx) {
        try {
            if (audioCtx.state !== 'closed') {
                audioCtx.close();
            }
        } catch (e) {}
        audioCtx = null;
    }
}

function aceptarAlertaPanel() {
    silenciarAlarma();
    const panelContenedor = document.getElementById('panelAlertaContenedor');
    if (panelContenedor) {
        panelContenedor.classList.add('d-none');
    }
}

// --- GRAFICO CHART.JS ---
function inicializarGrafico() {
    const canvas = document.getElementById('graficoTemperatura');
    if (!canvas) return;

    grafico = new Chart(canvas, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Temperatura (°C)',
                data: [],
                borderColor: 'rgb(220, 53, 69)',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });
}

// --- ACTUALIZACIÓN EN TIEMPO REAL (LECTURA ESP32 Y BASE DE DATOS) ---
async function actualizarDashboard() {
    try {
        const respuesta = await fetch('/api/dashboard');
        const datos = await respuesta.json();

        // Actualizar temperatura
        const tempActual = document.getElementById("temperaturaActual");
        if (tempActual) tempActual.innerHTML = (datos.temperatura ?? "--") + " °C";

        const ultLectura = document.getElementById("ultimaLecturaTexto");
        if (ultLectura) ultLectura.innerHTML = (datos.temperatura ?? "--") + " °C";

        // Actualizar alertas
        const alPendientes = document.getElementById("alertasPendientes");
        if (alPendientes) alPendientes.innerHTML = datos.alertas ?? 0;
        
        const alBadge = document.getElementById("alertasPendientesBadge");
        if (alBadge) alBadge.innerHTML = datos.alertas ?? 0;

        const tempVal = datos.temperatura ? parseFloat(datos.temperatura) : 0;
        const panelContenedor = document.getElementById('panelAlertaContenedor');
        const panelCard = document.getElementById('panelAlertaCard');
        const panelHeader = document.getElementById('panelAlertaHeader');
        const panelTitulo = document.getElementById('panelAlertaTitulo');
        const panelMensaje = document.getElementById('panelAlertaMensaje');
        const panelDetalle = document.getElementById('panelAlertaDetalle');

        if (datos.produccion) {
            const prodId = datos.produccion.id;
            idProduccionNotificada = prodId;

            const estadoProd = datos.produccion.estado?.toLowerCase() ?? '';
            const produccionFinalizada = (estadoProd === "finalizada" || estadoProd === "completada");

            const yaNotificadaEnSesion = sessionStorage.getItem(`prod_silenciada_${prodId}`) === 'true';

            // Si la producción vuelve a estar "En proceso", se rearma la alerta
            // de finalización para la próxima transición a "Finalizada".
            if (estadoProd === "en proceso") {
                alertaFinalizadaDisparada = false;
            }

            const tempPasteurizacion = datos.produccion.producto?.temperatura_pasteurizacion 
                ? parseFloat(datos.produccion.producto.temperatura_pasteurizacion) 
                : (datos.produccion.temperatura_objetivo ? parseFloat(datos.produccion.temperatura_objetivo) : 0);

            const temperaturaAlertaModal = document.getElementById('temperaturaAlertaModal');
            if (temperaturaAlertaModal) {
                temperaturaAlertaModal.innerHTML = tempPasteurizacion + "°C";
            }

            // 1. Modal / Panel Temperatura Alcanzada
            if (estadoProd === "en proceso" && tempPasteurizacion > 0 && tempVal >= tempPasteurizacion && !alertaTemperaturaDisparada) {
                alertaTemperaturaDisparada = true;
                alarmaSilenciada = false;
                reproducirAlarmaSonora();
                
                if (panelContenedor) {
                    panelContenedor.classList.remove('d-none');
                    panelCard.className = "card shadow-lg border-danger";
                    panelHeader.className = "card-header bg-danger text-white";
                    panelTitulo.innerHTML = `🚨 ¡TEMPERATURA ${tempPasteurizacion} ALCANZADA!`;
                    panelMensaje.innerHTML = tempVal + " °C";
                    panelDetalle.innerHTML = `Temperatura ${tempPasteurizacion} alcanzada, listo para la producción.`;
                }

                const modalEmergenciaEl = document.getElementById('modalAlertaEmergencia');
                if (modalEmergenciaEl && typeof bootstrap !== 'undefined') {
                    const modalEmergencia = bootstrap.Modal.getOrCreateInstance(modalEmergenciaEl);
                    modalEmergencia.show();
                }
            } 
            // 2. Modal / Panel Producción Finalizada
            // Solo se dispara si la página observó el cambio: En proceso -> Finalizada
            else if (produccionFinalizada && estadoProduccionAnterior === "en proceso" && !alertaFinalizadaDisparada && !yaNotificadaEnSesion) {
                alertaFinalizadaDisparada = true;
                sessionStorage.setItem(`prod_silenciada_${prodId}`, 'true');
                reproducirAlarmaSonora(); 
                
                if (panelContenedor) {
                    panelContenedor.classList.remove('d-none');
                    panelCard.className = "card shadow-lg border-success";
                    panelHeader.className = "card-header bg-success text-white";
                    panelTitulo.innerHTML = "🎉 ¡PRODUCCIÓN FINALIZADA CON ÉXITO!";
                    panelMensaje.innerHTML = "FINALIZADA";
                    panelDetalle.innerHTML = "El sistema ha completado el proceso de manera exitosa y los actuadores se han apagado.";
                }

                const modalFinalizadaEl = document.getElementById('modalProduccionFinalizada');
                if (modalFinalizadaEl && typeof bootstrap !== 'undefined') {
                    const modalFinalizada = bootstrap.Modal.getOrCreateInstance(modalFinalizadaEl);
                    modalFinalizada.show();
                }
            }

            // Guardamos el estado visto en este polling para detectar transiciones reales
            estadoProduccionAnterior = estadoProd;
        } else {
            idProduccionNotificada = null;
            estadoProduccionAnterior = null;
        }

        const estadoProd = datos.produccion?.estado?.toLowerCase() ?? '';
        
        const prodActivas = document.getElementById("produccionesActivas");
        if (prodActivas) prodActivas.innerHTML = (estadoProd === "en proceso") ? "1" : "0";

        // Estado del Motor
        const estMotor = document.getElementById("estadoMotor");
        if (estMotor) estMotor.innerHTML = datos.motor?.estado ? "Encendido" : "Apagado";
        
        const estMotorTxt = document.getElementById("estadoMotorTexto");
        if (estMotorTxt) estMotorTxt.innerHTML = datos.motor?.estado ? "🟢 Encendido" : "🔴 Apagado";

        // Estado del Ventilador
        const estVentilador = document.getElementById("estadoVentilador");
        if (estVentilador) estVentilador.innerHTML = datos.ventilador?.estado ? "Encendido" : "Apagado";
        
        const estVentiladorTxt = document.getElementById("estadoVentiladorTexto");
        if (estVentiladorTxt) estVentiladorTxt.innerHTML = datos.ventilador?.estado ? "🟢 Encendido" : "🔴 Apagado";

        // Estado del Sensor
        const estSensor = document.getElementById("estadoSensor");
        if (estSensor) estSensor.innerHTML = datos.sensor?.estado ?? "--";
        
        const estSensorTxt = document.getElementById("estadoSensorTexto");
        if (estSensorTxt) estSensorTxt.innerHTML = datos.sensor?.estado === "Activo" ? "🟢 Activo" : "🔴 Inactivo";

        // Estado del ESP32
        const estESP32 = document.getElementById("estadoESP32");
        if (estESP32) estESP32.innerHTML = datos.esp32?.conectado ? "🟢 Conectado" : "🔴 Desconectado";

        const espBadge = document.getElementById("esp32Badge");
        if (espBadge) {
            if (datos.esp32?.conectado) {
                espBadge.innerHTML = "🟢 Conectado";
                espBadge.className = "badge bg-success";
            } else {
                espBadge.innerHTML = "🔴 Desconectado";
                espBadge.className = "badge bg-danger";
            }
        }

        const espUltima = document.getElementById("esp32UltimaConexion");
        if (espUltima) espUltima.innerHTML = datos.esp32?.ultima_conexion ?? "Sin registros";

    } catch (error) {
        console.error("Error consultando el dashboard:", error);
    }
}

async function actualizarGrafico() {
    if (!grafico) return;

    try {
        const respuesta = await fetch('/api/temperaturas');
        const lecturas = await respuesta.json();

        const etiquetas = lecturas.map(l => l.t);
        const valores = lecturas.map(l => parseFloat(l.v));

        grafico.data.labels = etiquetas;
        grafico.data.datasets[0].data = valores;
        grafico.update();
    } catch (error) {
        console.error("Error actualizando la gráfica:", error);
    }
}

// --- CICLO DE MONITOREO (Cada 3 segundos) ---
document.addEventListener("DOMContentLoaded", () => {
    inicializarGrafico();
    actualizarDashboard();
    actualizarGrafico();

    setInterval(() => {
        actualizarDashboard();
        actualizarGrafico();
    }, 3000);
});
</script>
@endsection