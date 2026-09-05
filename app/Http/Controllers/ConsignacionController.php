<?php

namespace App\Http\Controllers;

use App\Models\Consignacion;
use App\Models\Presentacion;
use App\Models\User;
use App\Support\Decimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConsignacionController extends Controller
{
    public function index(Request $request)
    {
        $consulta = Consignacion::with('productor')
            ->withCount('items')
            ->orderByDesc('fecha_entrada')
            ->orderByDesc('id');

        if ($request->filled('estado')) {
            $consulta->where('estado', $request->estado);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;

            $consulta->whereHas('productor', function ($query) use ($buscar) {
                $query->where('name', 'like', '%'.$buscar.'%')
                    ->orWhere('nombre_unidad_productiva', 'like', '%'.$buscar.'%');
            });
        }

        return view('consignaciones.index', [
            'consignaciones' => $consulta->paginate(10)->withQueryString(),
            'estados' => ['abierta', 'liquidada', 'devuelta'],
        ]);
    }

    public function create()
    {
        return view('consignaciones.create', [
            'productores' => User::where('activo', true)->orderBy('name')->get(),
            'presentaciones' => Presentacion::with('producto')
                ->where('activo', true)
                ->whereHas('producto', fn ($query) => $query->where('activo', true))
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $this->validarMargenes($datos['items']);

        $consignacion = DB::transaction(function () use ($datos) {
            $consignacion = Consignacion::create([
                'user_id' => $datos['user_id'],
                'fecha_entrada' => $datos['fecha_entrada'],
                'observaciones' => $datos['observaciones'] ?? null,
                'estado' => 'abierta',
            ]);

            foreach ($datos['items'] as $item) {
                $consignacion->items()->create([
                    'presentacion_id' => $item['presentacion_id'],
                    'cantidad_recibida' => $item['cantidad_recibida'],
                    'cantidad_disponible' => $item['cantidad_recibida'],
                    'precio_productor' => $item['precio_productor'],
                    'precio_venta' => $item['precio_venta'],
                ]);
            }

            return $consignacion;
        });

        return redirect()
            ->route('consignaciones.show', $consignacion)
            ->with('success', 'Consignación registrada correctamente.');
    }

    public function show(Consignacion $consignacion)
    {
        $consignacion->load([
            'productor',
            'liquidacion.responsable',
            'items.presentacion.producto',
            'items.ventas.vendedor',
            'items.devoluciones.responsable',
        ]);

        return view('consignaciones.show', compact('consignacion'));
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'user_id' => 'required|exists:users,id,activo,1',
            'fecha_entrada' => 'required|date',
            'observaciones' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.presentacion_id' => 'required|distinct|exists:presentaciones,id,activo,1',
            'items.*.cantidad_recibida' => 'required|numeric|gt:0',
