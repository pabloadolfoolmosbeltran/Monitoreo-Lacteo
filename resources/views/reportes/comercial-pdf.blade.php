<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte comercial</title>
    <style>
        body {
            font: 11px DejaVu Sans, sans-serif;
            color: #18372e;
        }
        h1 {
            font-size: 22px;
            border-bottom: 3px solid #087b57;
            padding-bottom: 12px;
        }
        h2 {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 20px;
        }
        th,
        td {
            text-align: left;
            padding: 6px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #edf3e7;
            font-size: 9px;
        }
        .lot {
            page-break-inside: avoid;
        }
        .muted {
            color: #65756a;
        }
    </style>
</head>
<body>

    <h1>ACOPIO · Reporte comercial</h1>
    <p class="muted">
        Emitido: {{ now()->format('d/m/Y H:i') }} · Moneda: Bs.
    </p>

    @forelse($ingresos as $ingreso)
        <div class="lot">
            <h2>
                Entrada #{{ $ingreso->id }}
                · {{ $ingreso->productor->nombre_completo ?? 'Histórico' }}
            </h2>
            <p>
                {{ optional($ingreso->fecha_ingreso)->format('d/m/Y') }}
                · {{ $ingreso->estado }}
            </p>

            <table>
                <thead>
                    <tr>
                        <th>Lote / presentación</th>
                        <th>Entrada</th>
                        <th>Disponible</th>
                        <th>Acopio Bs.</th>
                        <th>Venta Bs.</th>
                        <th>Caducidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ingreso->items as $item)
                        <tr>
                            <td>#{{ $item->id }} {{ $item->presentacion->nombre ?? '' }}</td>
                            <td>{{ number_format($item->cantidad_ingresada, 0, ',', '.') }}</td>
                            <td>{{ number_format($item->cantidad_disponible, 0, ',', '.') }}</td>
                            <td>
                                {{ $item->precio_acopio_unitario === null
                                    ? 'Pendiente'
                                    : number_format($item->precio_acopio_unitario, 2, ',', '.') }}
                            </td>
                            <td>{{ number_format($item->precio_venta_unitario, 2, ',', '.') }}</td>
                            <td>
                                {{ optional($item->fecha_caducidad)->format('d/m/Y')
                                    ?? 'Sin fecha' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <p>Sin entradas en el período.</p>
    @endforelse

    <h2>Ventas del período</h2>
    <table>
        <thead>
            <tr>
                <th>ID / lote</th>
                <th>Fecha</th>
                <th>Cantidad</th>
                <th>Precio Bs.</th>
                <th>Total Bs.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventas as $venta)
                <tr>
                    <td>#{{ $venta->id }} / #{{ $venta->ingreso_productor_item_id }}</td>
                    <td>{{ $venta->fecha_venta }}</td>
                    <td>{{ number_format($venta->cantidad_vendida, 0, ',', '.') }}</td>
                    <td>{{ number_format($venta->precio_unitario_venta, 2, ',', '.') }}</td>
                    <td>{{ number_format($venta->total_venta, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Sin ventas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Ajustes por lote auditados</h2>
    <table>
        <thead>
            <tr>
                <th>ID / lote</th>
                <th>Fecha</th>
                <th>Operación</th>
                <th>Cantidad</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $m)
                <tr>
                    <td>#{{ $m->id }} / #{{ $m->lote_id }}</td>
                    <td>{{ $m->created_at }}</td>
                    <td>{{ $m->tipo }}</td>
                    <td>{{ number_format($m->cantidad, 0, ',', '.') }}</td>
                    <td>{{ $m->motivo }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Sin movimientos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
