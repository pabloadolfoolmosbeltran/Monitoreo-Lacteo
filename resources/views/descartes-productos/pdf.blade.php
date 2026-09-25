<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body {
            font: 12px DejaVu Sans, sans-serif;
        }
        h1 {
            border-bottom: 2px solid #0f766e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td,
        th {
            padding: 7px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>REPORTE DE DESCARTE / PRODUCTO CADUCADO</h1>
    <p>
        Referencia {{ $descarte->id }} · Generado {{ now()->format('d/m/Y H:i') }}
    </p>

    <table>
        <tr>
            <th>Producto</th>
            <td>
                {{ $descarte->lote?->presentacion?->producto?->nombre }}
                ·
                {{ $descarte->lote?->presentacion?->nombre }}
            </td>
        </tr>
        <tr>
            <th>Proveedor</th>
            <td>{{ $descarte->lote?->ingresoProductor?->productor?->nombre_completo }}</td>
        </tr>
        <tr>
            <th>Lote / caducidad</th>
            <td>
                #{{ $descarte->lote_id }}
                ·
                {{ $descarte->fecha_caducidad?->format('d/m/Y') ?? 'sin fecha' }}
            </td>
        </tr>
        <tr>
            <th>Cantidad</th>
            <td>{{ $descarte->cantidad }}</td>
        </tr>
        <tr>
            <th>Costo unitario</th>
            <td>Bs. {{ number_format((float)$descarte->costo_unitario, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>PÉRDIDA TOTAL</th>
            <td>Bs. {{ number_format((float)$descarte->perdida_total, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Motivo / notas</th>
            <td>
                {{ ucfirst($descarte->tipo_motivo) }} · {{ $descarte->notas }}
            </td>
        </tr>
        <tr>
            <th>Responsable</th>
            <td>{{ $descarte->creador?->name }}</td>
        </tr>
    </table>

    <p>Firma/autorización: ______________________________</p>
</body>
</html>