<?php

namespace App\Http\Controllers;

use App\Exports\ComercialExport;
use App\Models\IngresoProductor;
use App\Models\MovimientoInventario;
use App\Models\Productor;
use App\Models\TicketVenta;
use App\Models\Venta;
use App\Services\ReporteVentasService;
use App\Support\Decimal as D;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReporteComercialController extends Controller
{
    private function periodo(Request $r, $query, string $campo)
    {
        $r->validate(['desde' => 'nullable|date', 'hasta' => 'nullable|date|after_or_equal:desde']);
        if ($r->filled('desde')) {
            $query->whereDate($campo, '>=', $r->desde);
        }
        if ($r->filled('hasta')) {
            $query->whereDate($campo, '<=', $r->hasta);
        }

        return $query;
    }

    private function ingresos(Request $r)
    {
        $relations = ['productor'];
        if ($r->filled('producto')) {
            $term = $r->producto;
            $relations['items'] = fn ($items) => $items->whereHas('presentacion', fn ($q) => $q->where('nombre', 'like', '%'.$term.'%')->orWhereHas('producto', fn ($p) => $p->where('nombre', 'like', '%'.$term.'%')))->with('presentacion.producto');
        } else {
            $relations[] = 'items.presentacion.producto';
        }
        $query = $this->periodo($r, IngresoProductor::with($relations), 'fecha_ingreso');
        if ($r->filled('producto')) {
            $query->whereHas('items.presentacion', fn ($q) => $q->where('nombre', 'like', '%'.$r->producto.'%')->orWhereHas('producto', fn ($p) => $p->where('nombre', 'like', '%'.$r->producto.'%')));
        }
        if ($r->filled('productor')) {
            $query->whereHas('productor', fn ($q) => $q->where('nombres', 'like', '%'.$r->productor.'%')->orWhere('primer_apellido', 'like', '%'.$r->productor.'%')->orWhere('nombre_unidad_productiva', 'like', '%'.$r->productor.'%'));
        }

        return $query->latest('id');
    }

    private function ventas(Request $r)
    {
        $query = $this->periodo($r, Venta::with(['lote.presentacion.producto', 'lote.ingresoProductor.productor', 'ticket']), 'fecha_venta');
        if ($r->filled('producto')) {
            $query->whereHas('lote.presentacion', fn ($q) => $q->where('nombre', 'like', '%'.$r->producto.'%')->orWhereHas('producto', fn ($p) => $p->where('nombre', 'like', '%'.$r->producto.'%')));
        }
        if ($r->filled('productor')) {
            $query->whereHas('lote.ingresoProductor.productor', fn ($q) => $q->where('nombres', 'like', '%'.$r->productor.'%')->orWhere('primer_apellido', 'like', '%'.$r->productor.'%')->orWhere('nombre_unidad_productiva', 'like', '%'.$r->productor.'%'));
        }

        return $query->latest('id');
    }

    private function tickets(Request $r)
    {
        $query = $this->periodo($r, TicketVenta::with(['ventas.lote.presentacion.producto', 'ventas.lote.ingresoProductor.productor', 'vendedor']), 'created_at');
        if ($r->filled('producto') || $r->filled('productor')) {
            $query->whereHas('ventas', function ($venta) use ($r) {
                if ($r->filled('producto')) {
                    $venta->whereHas('lote.presentacion', fn ($q) => $q->where('nombre', 'like', '%'.$r->producto.'%')->orWhereHas('producto', fn ($p) => $p->where('nombre', 'like', '%'.$r->producto.'%')));
                }
                if ($r->filled('productor')) {
                    $venta->whereHas('lote.ingresoProductor.productor', fn ($q) => $q->where('nombres', 'like', '%'.$r->productor.'%')->orWhere('primer_apellido', 'like', '%'.$r->productor.'%')->orWhere('nombre_unidad_productiva', 'like', '%'.$r->productor.'%'));
                }
            });
        }

        return $query->latest('id');
    }

    public function index(Request $r, ReporteVentasService $reporteVentas)
    {
        $filtros = $r->validate([
            'desde' => 'nullable|date', 'hasta' => 'nullable|date|after_or_equal:desde',
            'producto' => 'nullable|string|max:100', 'productor' => 'nullable|string|max:100',
            'productor_id' => 'nullable|integer|exists:productores,id',
        ]);

        return view('reportes.ventas', [
            'tickets' => $this->tickets($r)->paginate(20)->withQueryString(),
            'productosVendidos' => $reporteVentas->productosMasVendidos($filtros),
            'productores' => Productor::activos()->orderBy('nombres')->get(),
        ]);
    }

    public function productosCsv(Request $r, ReporteVentasService $reporteVentas)
    {
        $filtros = $r->validate(['desde' => 'nullable|date', 'hasta' => 'nullable|date|after_or_equal:desde', 'productor_id' => 'nullable|integer|exists:productores,id']);
        $filas = $reporteVentas->productosMasVendidos($filtros);

        return response()->streamDownload(function () use ($filas): void {
            $salida = fopen('php://output', 'wb');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Producto', 'Unidades vendidas', 'Monto Bs.', 'Porcentaje'], ';');
            foreach ($filas as $fila) {
                fputcsv($salida, [$fila['producto'], $fila['unidades'], $fila['monto'], $fila['porcentaje'].'%'], ';');
            }
            fclose($salida);
        }, 'productos-mas-vendidos.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function venta(TicketVenta $ticket)
    {
        return $ticket->load(['ventas.lote.presentacion.producto', 'ventas.lote.ingresoProductor.productor', 'vendedor']);
    }

    public function ingresosIndex(Request $r)
    {
        return view('reportes.ingresos', ['ingresos' => $this->ingresos($r)->paginate(20)->withQueryString()]);
    }

    private function datos(Request $r, ?IngresoProductor $ingreso = null): array
    {
        $ventas = $this->ventas($r);
        $movimientos = $this->periodo($r, MovimientoInventario::with('lote.presentacion'), 'created_at');
        if ($ingreso) {
            $ids = $ingreso->items()->pluck('id');
            $ventas->whereIn('ingreso_productor_item_id', $ids);
            $movimientos->whereIn('lote_id', $ids);
        }

        return ['ingresos' => $ingreso ? collect([$ingreso->load(['productor', 'items.presentacion'])]) : $this->ingresos($r)->get(),
            'ventas' => $ventas->get(), 'movimientos' => $movimientos->get()];
    }

    public function pdf(Request $r)
    {
        return Pdf::loadView('reportes.comercial-pdf', $this->datos($r))->setPaper('a4', 'landscape')->download('reporte-comercial.pdf');
    }

    public function pdfIngreso(Request $r, IngresoProductor $ingreso)
    {
        return Pdf::loadView('reportes.comercial-pdf', $this->datos($r, $ingreso))->setPaper('a4', 'landscape')->download('entrada-'.$ingreso->id.'.pdf');
    }

    public function excel(Request $r)
    {
        return Excel::download(new ComercialExport($this->tablas($this->datos($r))), 'reporte-comercial.xlsx');
    }

    public function excelIngreso(Request $r, IngresoProductor $ingreso)
    {
        return Excel::download(new ComercialExport($this->tablas($this->datos($r, $ingreso))), 'entrada-'.$ingreso->id.'.xlsx');
    }

    private function tablas(array $d): array
    {
        $filas = [];
        foreach ($d['ingresos'] as $i) {
            foreach ($i->items as $l) {
                $filas[] = [(string) $i->id, $i->productor->nombre_completo, $i->fecha_ingreso->format('Y-m-d H:i'), $i->estado, (string) $l->id, $l->presentacion->nombre, $l->cantidad_ingresada, $l->cantidad_disponible, $l->precio_acopio_unitario ?? 'Pendiente', $l->precio_venta_unitario, $l->fecha_caducidad?->format('Y-m-d') ?? 'Sin fecha'];
            }
        }

        return [
            ['nombre' => 'Entradas', 'columnas' => ['Entrada', 'Proveedor', 'Fecha', 'Estado', 'Lote', 'Presentación', 'Cantidad recibida', 'Disponible', 'Acopio Bs.', 'Venta Bs.', 'Caducidad'], 'filas' => $filas],
            ['nombre' => 'Ventas', 'columnas' => ['Venta', 'Ticket', 'Fecha', 'Lote', 'Presentación', 'Proveedor', 'Cantidad', 'Precio Bs.', 'Importe Bs.', 'Pago', 'Cliente'], 'filas' => $d['ventas']->map(fn ($v) => [(string) $v->id, (string) $v->ticket_venta_id, (string) $v->fecha_venta, (string) $v->ingreso_productor_item_id, $v->lote->presentacion->nombre, $v->lote->ingresoProductor->productor->nombre_completo, $v->cantidad_vendida, $v->precio_unitario_venta, D::mul($v->cantidad_vendida, $v->precio_unitario_venta), $v->ticket?->metodo_pago ?? 'Histórico', $v->ticket?->cliente ?? ''])->all()],
            ['nombre' => 'Ajustes por Lote', 'columnas' => ['ID', 'Fecha', 'Lote', 'Operador', 'Operación', 'Cantidad', 'Motivo'], 'filas' => $d['movimientos']->map(fn ($m) => [(string) $m->id, (string) $m->created_at, (string) $m->lote_id, (string) $m->user_id, $m->tipo, $m->cantidad, $m->motivo])->all()],
        ];
    }
}
