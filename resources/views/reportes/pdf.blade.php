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
            display: inline-block;
        }
        .badge-success   { background-color: #d1e7dd; color: #0f5132; }
        .badge-danger    { background-color: #f8d7da; color: #842029; }
        .badge-warning   { background-color: #fff3cd; color: #664d03; }
        .badge-info      { background-color: #cff4fc; color: #087990; }
        .badge-primary   { background-color: #cfe2ff; color: #0a58ca; }
        .badge-secondary { background-color: #e2e3e5; color: #41464b; }
        tr {
            page-break-inside: avoid;
        }
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

    <div class="section-title">Información General del Lote</div>
    <table>
        <tr>
            <th>ID de Producción</th>
            <td>#{{ $produccion->id }}</td>
            <th>Unidad Productiva</th>
            <td>{{ $produccion->user ? $produccion->user->nombre_unidad_productiva : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Operador Responsable</th>
            <td>{{ $produccion->user ? $produccion->user->name : 'No registrado' }}</td>
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
            <th>Tipo de Cuajo</th>
            <td>{{ $produccion->tipo_cuajo ?? 'No especificado' }}</td>
            <th>Cantidad de Cuajo</th>
            <td>{{ isset($produccion->cantidad_cuajo) ? number_format($produccion->cantidad_cuajo, 2) . ' ' . ($produccion->producto?->unidad_cuajo ?? 'ml') : 'No especificado' }}</td>
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
            <td>{{ $produccion->fecha_fin ? $produccion->fecha_fin->format('d/m/Y H:i:s') : 'En Proceso' }}</td>
        </tr>
        <tr>
            <th>Duración del Proceso</th>
            <td colspan="3"><strong>{{ $duracion }}</strong></td>
        </tr>
    </table>

    <div class="section-title">Resumen Métrico de Temperaturas</div>
    <table>
        <tr>
            <th>Temperatura Inicial</th>
            <td>{{ isset($temperaturaInicial) ? number_format($temperaturaInicial, 2) . ' °C' : '0.00 °C' }}</td>
            <th>Temperatura Final</th>
            <td>{{ isset($temperaturaFinal) ? number_format($temperaturaFinal, 2) . ' °C' : '0.00 °C' }}</td>
        </tr>
        <tr>
            <th>Temperatura Mínima</th>
            <td><strong style="color: #198754;">{{ isset($temperaturaMinima) ? number_format($temperaturaMinima, 2) . ' °C' : '0.00 °C' }}</strong></td>
            <th>Temperatura Máxima</th>
            <td><strong style="color: #dc3545;">{{ isset($temperaturaMaxima) ? number_format($temperaturaMaxima, 2) . ' °C' : '0.00 °C' }}</strong></td>
        </tr>
        <tr>
            <th>Temperatura Promedio</th>
            <td><strong style="color: #0d6efd;">{{ isset($temperaturaPromedio) ? number_format($temperaturaPromedio, 2) . ' °C' : '0.00 °C' }}</strong></td>
            <th>Total de Lecturas de Sensor</th>
            <td>{{ $totalLecturas ?? 0 }} registros</td>
        </tr>
    </table>

    <div style="margin-top: 10px; margin-bottom: 20px; padding: 10px; border-radius: 4px; font-size: 11px; {{ isset($huboDesviaciones) && $huboDesviaciones ? 'background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7;' : 'background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc;' }}">
        <strong>Estado de Monitoreo Térmico:</strong><br>
        {{ isset($huboDesviaciones) && $huboDesviaciones ? 'Se detectaron desviaciones de temperatura fuera del rango de tolerancia establecido durante el proceso.' : 'Temperatura mantenida exitosamente dentro del rango de tolerancia establecido.' }}
    </div>

    <div class="section-title">Alertas Registradas en el Proceso</div>
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

    <div class="section-title">Historial Cronológico de Eventos (Bitácora del Lote)</div>
    @if($produccion->eventos->count())
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Fecha y Hora</th>
                    <th style="width: 20%;">Elemento / Acción</th>
                    <th style="width: 55%;">Descripción del Evento</th>
                </tr>
            </thead>
            <tbody>
                @foreach($produccion->eventos as $evento)
                <tr>
                    <td style="color: #6c757d;">
                        {{ $evento->fecha_hora->format('d/m/Y H:i:s') }}
                    </td>
                    <td>
                        @php
                            $badgeClass = match($evento->tipo) {
                                'Producción' => 'badge-info',
                                'Motor'      => 'badge-primary',
                                'Ventilador' => 'badge-success',
                                'Alerta'     => 'badge-danger',
                                default      => 'badge-secondary'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $evento->tipo }}</span>
                    </td>
                    <td><strong>{{ $evento->descripcion }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color: #6c757d; font-style: italic; margin-bottom: 15px;">No se encontraron eventos registrados para este lote.</p>
    @endif

</body>
</html>
