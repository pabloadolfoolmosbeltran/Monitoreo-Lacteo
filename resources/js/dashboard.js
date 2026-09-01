// ============================================================
// DASHBOARD.JS - Monitoreo en tiempo real, gráfica y alertas
// ============================================================

// --- VARIABLES GLOBALES ---
let grafico = null;
let alarmaSonando = false;
let alarmaSilenciada = false;
let audioCtx = null;
let beepInterval = null;

let alertaTemperaturaDisparada = false;
let alertaFinalizadaDisparada = false;
let idProduccionNotificada = null;
let notificacionFinalizadaActiva = false;

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
            } catch (err) {
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

    // Solo confirmar la notificación de finalización. Silenciar la alerta de
    // temperatura no debe impedir que se muestre el aviso al cerrar el lote.
    if (notificacionFinalizadaActiva && idProduccionNotificada) {
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

        // FIX: antes "estadoProd" se calculaba aquí dentro del if(datos.produccion)
        // y se volvía a calcular más abajo, fuera del if, con la misma expresión.
        // Se calcula una sola vez y se reutiliza en todo el bloque.
        const estadoProd = datos.produccion?.estado?.toLowerCase() ?? '';

        // --- ACTUALIZAR TIPO Y CANTIDAD DE CUAJO EN TIEMPO REAL ---
        const elTipoCuajo = document.getElementById("valTipoCuajo");
        const elCantidadCuajo = document.getElementById("valCantidadCuajo");

        if (datos.produccion) {
            const prodId = datos.produccion.id;
            idProduccionNotificada = prodId;

            const produccionFinalizada = (estadoProd === "finalizada" || estadoProd === "completada");

            const yaNotificadaEnSesion = sessionStorage.getItem(`prod_silenciada_${prodId}`) === 'true';

            // Una nueva producción activa rearma el aviso para su futura finalización.
            if (estadoProd === "en proceso") {
                alertaFinalizadaDisparada = false;
                notificacionFinalizadaActiva = false;
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
            // Solo se muestra al detectar el cambio real de "En proceso" a "Finalizada".
            else if (produccionFinalizada && estadoProduccionAnterior === "en proceso" && !alertaFinalizadaDisparada && !yaNotificadaEnSesion) {
                alertaFinalizadaDisparada = true;
                notificacionFinalizadaActiva = true;
                alarmaSilenciada = false;
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
