<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body {
            font: 10px DejaVu Sans, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td,
        th {
            padding: 5px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>REPORTE CONSOLIDADO DE DESCARTES</h1>
    <p>Generado {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Ref.</th>
                <th>Producto</th>
                <th>Proveedor</th>
                <th>Cantidad</th>
                <th>Motivo</th>
                <th>Estado</th>
                <th>Pérdida Bs.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($descartes as $d)
                <tr>
                    <td>#{{ $d->id }}</td>
                    <td>{{ $d->lote?->presentacion?->producto?->nombre }}</td>
                    <td>{{ $d->lote?->ingresoProductor?->productor?->nombre_completo }}</td>
                    <td>{{ $d->cantidad }}</td>
                    <td>{{ $d->tipo_motivo }}</td>
                    <td>{{ $d->estado }}</td>
                    <td>{{ number_format((float)$d->perdida_total, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Total de pérdidas: Bs. {{ number_format((float)$total, 2, ',', '.') }}</h2>
</body>
</html>