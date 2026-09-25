<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarDescarteProductoRequest;
use App\Models\DescarteProducto;
use App\Models\IngresoProductorItem;
use App\Models\Productor;
use App\Services\ConsultaDescartes;
use App\Services\DescarteProductoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DescarteProductoController extends Controller
{
    public function __construct(private DescarteProductoService $servicio, private ConsultaDescartes $consulta) {}

    private function filtros(Request $r): array
    {
        return $r->validate([
            'productor_id' => 'nullable|integer|exists:productores,id',
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
            'estado' => 'nullable|in:pendiente,procesado',
            'tipo_motivo' => 'nullable|in:caducado,dañado,deterioro,otro',
        ]);
    }

    public function index(Request $r)
    {
        $this->authorize('viewAny', DescarteProducto::class);
        $filtros = $this->filtros($r);
        $base = $this->consulta->query($filtros);
        $total = (clone $base)->sum('perdida_total');

        return view('descartes-productos.index', [
            'descartes' => $base->paginate(20)->withQueryString(),
            'total' => $total,
            'filtros' => $filtros,
            'productores' => Productor::activos()->orderBy('nombres')->get(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', DescarteProducto::class);

        return view('descartes-productos.create', ['lotes' => $this->lotes()]);
    }

    public function store(GuardarDescarteProductoRequest $r)
    {
        $this->servicio->crear($r->validated(), $r->user());

        return redirect()->route('descartes-productos.index')->with('success', 'Descarte pendiente registrado.');
    }

    public function show(DescarteProducto $descarteProducto)
    {
        $this->authorize('view', $descarteProducto);

        return view('descartes-productos.show', ['descarte' => $descarteProducto->load(['lote.presentacion.producto', 'lote.ingresoProductor.productor', 'creador', 'procesador'])]);
    }

    public function edit(DescarteProducto $descarteProducto)
    {
        $this->authorize('update', $descarteProducto);

        return view('descartes-productos.edit', [
            'descarte' => $descarteProducto,
            'lotes' => $this->lotes($descarteProducto->lote_id),
        ]);
    }

    public function update(GuardarDescarteProductoRequest $r, DescarteProducto $descarteProducto)
    {
        $this->servicio->actualizar($descarteProducto, $r->validated(), $r->user());

        return redirect()->route('descartes-productos.show', $descarteProducto)->with('success', 'Descarte actualizado.');
    }

    public function destroy(DescarteProducto $descarteProducto)
    {
        $this->authorize('delete', $descarteProducto);
        $this->servicio->eliminar($descarteProducto);

        return redirect()->route('descartes-productos.index')->with('success', 'Descarte pendiente eliminado.');
    }

    public function procesar(Request $r, DescarteProducto $descarteProducto)
    {
        $this->authorize('process', $descarteProducto);
        $this->servicio->procesar($descarteProducto, $r->user());

        return redirect()->route('descartes-productos.show', $descarteProducto)->with('success', 'Descarte procesado y stock actualizado.');
    }

    public function pdf(DescarteProducto $descarteProducto)
    {
        $this->authorize('view', $descarteProducto);

        return Pdf::loadView('descartes-productos.pdf', ['descarte' => $descarteProducto->load(['lote.presentacion.producto', 'lote.ingresoProductor.productor', 'creador', 'procesador'])])->download('descarte-'.$descarteProducto->id.'.pdf');
    }

    public function consolidadoPdf(Request $r)
    {
        $this->authorize('viewAny', DescarteProducto::class);
        $f = $this->filtros($r);
        $items = $this->consulta->query($f)->get();

        return Pdf::loadView('descartes-productos.consolidado-pdf', ['descartes' => $items, 'total' => $items->sum('perdida_total')])->download('descartes-consolidado.pdf');
    }

    public function csv(Request $r)
    {
        $this->authorize('viewAny', DescarteProducto::class);
        $items = $this->consulta->query($this->filtros($r))->get();

        return response()->streamDownload(function () use ($items): void {
            $salida = fopen('php://output', 'wb');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Referencia', 'Producto', 'Proveedor', 'Cantidad', 'Costo', 'Pérdida', 'Motivo', 'Estado'], ';');
            foreach ($items as $d) {
                fputcsv($salida, [
                    $d->id,
                    $d->lote?->presentacion?->producto?->nombre,
                    $d->lote?->ingresoProductor?->productor?->nombre_completo,
                    $d->cantidad,
                    $d->costo_unitario,
                    $d->perdida_total,
                    $d->tipo_motivo,
                    $d->estado,
                ], ';');
            }
            fclose($salida);
        }, 'descartes-productos.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function lotes(?int $incluirLoteId = null)
    {
        return IngresoProductorItem::with(['presentacion.producto', 'ingresoProductor.productor'])
            ->where(function ($query) use ($incluirLoteId): void {
                $query->where('cantidad_disponible', '>', 0);
                if ($incluirLoteId !== null) {
                    $query->orWhere('id', $incluirLoteId);
                }
            })
            ->orderBy('id')
            ->get();
    }
}
