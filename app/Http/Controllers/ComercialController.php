<?php

namespace App\Http\Controllers;

use App\Http\Requests\FiltroInventarioRequest;
use App\Models\IngresoProductor;
use App\Models\IngresoProductorItem;
use App\Models\MovimientoInventario;
use App\Models\Presentacion;
use App\Models\Productor;
use App\Services\ComercialService;
use App\Services\InventarioConsulta;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ComercialController extends Controller
{
    public function __construct(private ComercialService $comercial) {}

    public function pos()
    {
        return view('pos.index');
    }

    public function catalogo(Request $request)
    {
        $datos = $request->validate(['q' => 'nullable|string|max:100', 'categoria' => 'nullable|integer']);
        $query = Presentacion::with(['producto', 'lotes' => fn ($q) => $q->where('cantidad_disponible', '>', 0)
            ->whereDate('fecha_caducidad', '>=', today())->whereHas('ingresoProductor', fn ($h) => $h->where('estado', 'abierta')
            ->whereHas('productor', fn ($p) => $p->where('activo', true)))
            ->with('ingresoProductor.productor')->orderBy('fecha_caducidad')->orderBy('id')])
            ->where('activo', true)->whereHas('producto', fn ($q) => $q->where('activo', true));
        if (! empty($datos['q'])) {
            $query->where(fn ($q) => $q->where('nombre', 'like', '%'.$datos['q'].'%')
                ->orWhereHas('producto', fn ($p) => $p->where('nombre', 'like', '%'.$datos['q'].'%')));
        }
        if (! empty($datos['categoria'])) {
            $query->where('producto_id', $datos['categoria']);
        }

        return $query->orderBy('nombre')->limit(200)->get()->map(function ($p) {
            $proveedores = $p->lotes->groupBy(fn ($l) => $l->ingresoProductor->productor_id)->map(function ($lotes) {
                $primero = $lotes->first();
                $productor = $primero->ingresoProductor->productor;

                return [
                    'productor_id' => $productor->id,
                    'nombre' => $productor->nombre_completo,
                    'precio' => $primero->precio_venta_unitario,
                    'stock' => $lotes->sum('cantidad_disponible'),
                    'lotes' => $lotes->map(fn ($l) => ['id' => $l->id, 'disponible' => $l->cantidad_disponible,
                        'precio' => $l->precio_venta_unitario, 'caducidad' => $l->fecha_caducidad->format('Y-m-d')])->values(),
                ];
            })->values();

            return [
                'id' => $p->id, 'nombre' => $p->nombre, 'producto' => $p->producto->nombre, 'producto_id' => $p->producto_id,
                'unidad' => $p->unidad, 'imagen_comercial' => $p->imagen_comercial ? asset('storage/'.$p->imagen_comercial) : null,
                'stock' => $p->lotes->sum('cantidad_disponible'), 'precio' => $proveedores->first()['precio'] ?? $p->precio,
                'proveedores' => $proveedores,
            ];
        });
    }

    public function vender(Request $request)
    {
        $datos = $request->validate(['clave' => 'required|uuid', 'metodo_pago' => 'required|in:efectivo,qr,credito',
            'cliente' => 'nullable|required_if:metodo_pago,credito|string|max:255',
            'items' => 'required|array|min:1|max:100', 'items.*.presentacion_id' => 'required|integer|distinct|exists:presentaciones,id',
            'items.*.productor_id' => 'nullable|integer|exists:productores,id',
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:999999']]);

        return response()->json($this->comercial->vender($datos, $request->user()->id), 201);
    }

    public function ingresos()
    {
        return view('ingresos.index', ['ingresos' => IngresoProductor::with(['productor', 'items'])->latest('id')->paginate(20)]);
    }

    public function crearIngreso()
    {
        return view('ingresos.create', [
            'presentaciones' => Presentacion::with('producto')->where('activo', true)
                ->whereHas('producto', fn ($q) => $q->where('activo', true))->orderBy('nombre')->get(),
            'productoresActivos' => Productor::activos()->orderBy('nombres')->orderBy('primer_apellido')->get(),
        ]);
    }

    public function guardarIngreso(Request $request)
    {
        $datos = $request->validate(['productor_id' => ['required', 'integer', Rule::exists('productores', 'id')->where(fn ($q) => $q->where('activo', true)->whereNull('deleted_at'))], 'fecha_ingreso' => 'required|date|before_or_equal:now',
            'observaciones' => 'nullable|string|max:2000', 'items' => 'required|array|min:1|max:100',
            'items.*.presentacion_id' => 'required|integer|exists:presentaciones,id',
            'items.*.cantidad_ingresada' => ['required', 'integer', 'min:1', 'max:999999'],
            'items.*.precio_acopio_unitario' => 'nullable|numeric|min:0|max:999999.99|decimal:0,2',
            'items.*.precio_venta_unitario' => 'required|numeric|min:0|max:999999.99|decimal:0,2',
            'items.*.fecha_caducidad' => 'required|date|after:today']);
        $ingreso = $this->comercial->ingreso($datos, $request->user()->id);

        return $request->expectsJson() ? response()->json(['id' => $ingreso->id, 'url' => url('/ingresos-productores/'.$ingreso->id)], 201) : redirect('/ingresos-productores/'.$ingreso->id)->with('success', 'Entrada de inventario registrada.');
    }

    public function detalle(IngresoProductor $ingreso)
    {
        return view('ingresos.show', ['ingreso' => $ingreso->load(['productor', 'operador', 'items.presentacion', 'items.ventas'])]);
    }

    public function detalleJson(IngresoProductor $ingreso)
    {
        return $ingreso->load(['productor', 'operador', 'items.presentacion', 'items.ventas']);
    }

    public function inventario(FiltroInventarioRequest $request, InventarioConsulta $consulta)
    {
        $filtros = $request->validated();

        return view('inventario.index', ['lotes' => $consulta->lotes($filtros), 'filtros' => $filtros,
            'movimientos' => MovimientoInventario::with(['lote.presentacion', 'responsable'])->latest('id')->limit(50)->get()]);
    }

    public function inventarioGeneral(FiltroInventarioRequest $request, InventarioConsulta $consulta)
    {
        $filtros = $request->validated();

        return view('inventario.general', ['grupos' => $consulta->grupos($filtros), 'totales' => $consulta->totales($filtros),
            'detalle' => $consulta->detalleGrupo($filtros), 'filtros' => $filtros]);
    }

    public function movimiento(Request $request, IngresoProductorItem $item)
    {
        $datos = $request->validate(['tipo' => 'required|in:ajuste,entrada,salida',
            'cantidad' => ['required', 'integer', 'not_in:0', 'between:-999999,999999'],
            'motivo' => 'required|string|min:3|max:1000']);
        abort_if($datos['tipo'] === 'ajuste' && $request->user()->rol !== 'Administrador', 403);
        if ($datos['tipo'] !== 'ajuste' && (int) $datos['cantidad'] <= 0) {
            throw ValidationException::withMessages(['cantidad' => 'Indique una cantidad positiva.']);
        }
        $this->comercial->movimiento($item, $datos, $request->user()->id);

        return $this->ok($request, 'Ajuste por lote registrado con auditoría.');
    }

    public function regularizar(Request $request, IngresoProductorItem $item)
    {
        abort_unless($request->user()->rol === 'Administrador', 403);
        $datos = $request->validate(['fecha_caducidad' => 'sometimes|nullable|date|after:today',
            'precio_acopio_unitario' => 'sometimes|nullable|numeric|min:0|max:999999.99|decimal:0,2', 'motivo' => 'required|string|min:3|max:500']);
        $this->comercial->regularizar($item, $datos, $request->user()->id);

        return $this->ok($request, 'Datos del lote completados con auditoría.');
    }

    private function ok(Request $request, string $mensaje)
    {
        return $request->expectsJson() ? response()->json(['message' => $mensaje]) : back()->with('success', $mensaje);
    }
}
