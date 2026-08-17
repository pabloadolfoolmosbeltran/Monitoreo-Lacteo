<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Producción #{{ $produccion->id }}</title>
    <style>
        @page {
            margin: 1.5cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333333;
            font-size: 11px;
            line-height: 1.4;
        }
        /* Encabezado del Reporte */
        .header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header td {
            border: none;
            padding: 0;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #0d6efd;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 10px;
            color: #6c757d;
        }
        .doc-id {
            text-align: right;
            font-size: 14px;
            font-weight: bold;
            color: #212529;
        }

        /* Títulos de sección */
        .section-title {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: bold;
            color: #212529;
            margin-top: 20px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        /* Tablas de Datos */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table, th, td {
            border: 1px solid #dee2e6;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f1f3f5;
            font-weight: bold;
            color: #495057;
            width: 30%;
        }

        /* Tablas de registros (Alertas) */
        .data-table th {
            background-color: #343a40;
            color: #ffffff;
            font-size: 10px;
            width: auto;
            text-align: left;
        }
        .data-table td {
            font-size: 10px;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-success { background-color: #d1e7dd; color: #0f5132; }
        .badge-danger { background-color: #f8d7da; color: #842029; }
        .badge-warning { background-color: #fff3cd; color: #664d03; }
        .badge-info { background-color: #cff4fc; color: #087990; }
        .badge-secondary { background-color: #e2e3e5; color: #41464b; }
    </style>
</head>
<body>

    <table class="header">
        <tr>
            <td>
                <span class="title">Sistema de Control Lácteo</span><br>
                <span class="subtitle">Reporte Técnico de Control de Procesos y Temperatura</span>
            </td>
            <td class="doc-id">
                LOTE: #{{ $produccion->id }}
            </td>
        </tr>
    </table>

    <hr style="border: 0; border-top: 1px solid #dee2e6; margin-bottom: 20px;">

    <div class="section-title">📋 Información General del Lote</div>
    <table>
        <tr>
            <th>ID de Producción</th>
            <td>#{{ $produccion->id }}</td>
            <th>Unidad Productiva</th>
            <td>{{ $produccion->user ? $produccion->user->nombre_unidad_productiva : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Operador Responsable</th>
            <td>{{ $produccion->user ? $produccion->user->name : 'No registrado (Histórico)' }}</td>
            <th>Rol del Operador</th>
            <td>{{ $produccion->user ? $produccion->user->rol : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Producto Elaborado</th>
            <td>{{ $produccion->producto->nombre }}</td>
            <th>Cantidad de Leche</th>
            <td>{{ number_format($produccion->cantidad_leche, 2) }} Litros</td>
        </tr>
        <tr>
            <th>Temperatura Objetivo</th>
            <td>{{ number_format($produccion->temperatura_objetivo, 2) }} °C</td>
            <th>Estado Actual</th>
            <td>{{ $produccion->estado }}</td>
        </tr>
        <tr>
            <th>Fecha de Inicio</th>
            <td>{{ $produccion->fecha_inicio ? $produccion->fecha_inicio->format('d/m/Y H:i:s') : '-' }}</td>
            <th>Fecha de Cierre</th>
            <td>{{ $produccion->fecha_fin ? $produccion->fecha_fin->format('d/m/Y H:i:s') : 'En Proceso / Activo' }}</td>
        </tr>
        <tr>
            <th>Duración del Proceso</th>
            <td colspan="3"><strong>{{ $duracion }}</strong></td>
        </tr>
    </table>

    <div class="section-title">🌡️ Resumen Métrico de Temperaturas</div>
    <table>
        <tr>
            <th>Temperatura Inicial</th>
            <td>{{ $temperaturaInicial ? number_format($temperaturaInicial, 2) . ' °C' : '0.00 °C' }}</td>
            <th>Temperatura Final</th>
            <td>{{ $temperaturaFinal ? number_format($temperaturaFinal, 2) . ' °C' : '0.00 °C' }}</td>
        </tr>
        <tr>
            <th>Temperatura Mínima</th>
            <td><strong style="color: #198754;">{{ $temperaturaMinima ? number_format($temperaturaMinima, 2) . ' °C' : '0.00 °C' }}</strong></td>
            <th>Temperatura Máxima</th>
            <td><strong style="color: #dc3545;">{{ $temperaturaMaxima ? number_format($temperaturaMaxima, 2) . ' °C' : '0.00 °C' }}</strong></td>
        </tr>
        <tr>
            <th>Temperatura Promedio</th>
            <td><strong style="color: #0d6efd;">{{ $temperaturaPromedio ? number_format($temperaturaPromedio, 2) . ' °C' : '0.00 °C' }}</strong></td>
            <th>Total de Lecturas de Sensor</th>
            <td>{{ $totalLecturas }} registros</td>
        </tr>
    </table>

    <div style="margin-top: 10px; margin-bottom: 20px; padding: 10px; border-radius: 4px; font-size: 11px; {{ $huboDesviaciones ? 'background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7;' : 'background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc;' }}">
        <strong>Estado de Monitoreo Térmico:</strong><br>
        {{ $huboDesviaciones ? 'Se detectaron desviaciones de temperatura fuera del rango de tolerancia establecido durante el proceso.' : 'Temperatura mantenida exitosamente dentro del rango de tolerancia establecido.' }}
    </div>

    <div class="section-title">⚠️ Alertas Registradas en el Proceso</div>
    @if($produccion->alertas->count())
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Tipo de Alerta</th>
                    <th style="width: 60%;">Mensaje del Sistema</th>
                    <th style="width: 15%; text-align: center;">Atendida</th>
                </tr>
            </thead>
            <tbody>
                @foreach($produccion->alertas as $alerta)
                <tr>
                    <td>{{ $alerta->tipo }}</td>
                    <td>{{ $alerta->mensaje }}</td>
                    <td class="text-center">
                        <span class="badge {{ $alerta->atendida ? 'badge-success' : 'badge-danger' }}">
                            {{ $alerta->atendida ? 'Sí' : 'No' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color: #198754; font-style: italic; margin-bottom: 15px;">✅ Excelente: No se registraron anomalías ni alertas de temperatura durante esta producción.</p>
    @endif

</body>
</html>