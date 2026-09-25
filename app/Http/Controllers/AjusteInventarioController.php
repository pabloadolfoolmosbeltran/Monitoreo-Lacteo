<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActualizarAjusteInventarioRequest;
use App\Http\Requests\AnularAjusteInventarioRequest;
use App\Http\Requests\GuardarAjusteInventarioRequest;
use App\Models\AjusteInventario;
use App\Models\IngresoProductorItem;
use App\Services\AjusteInventarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AjusteInventarioController extends Controller
{
    public function __construct(private AjusteInventarioService $servicio) {}

    public function index(): View
    {
        $this->authorize('viewAny', AjusteInventario::class);

        return view('ajustes-inventario.index', [
            'ajustes' => AjusteInventario::with(['lote.presentacion.producto', 'lote.ingresoProductor.productor', 'creador', 'anulador'])->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', AjusteInventario::class);

        return view('ajustes-inventario.create', [
            'lotes' => IngresoProductorItem::with(['presentacion.producto', 'ingresoProductor.productor'])->orderBy('id')->get(),
        ]);
    }

    public function store(GuardarAjusteInventarioRequest $request): RedirectResponse
    {
        $this->servicio->crear($request->validated(), $request->user());

        return redirect()->route('ajustes-inventario.index')->with('success', 'Ajuste registrado con auditoría.');
    }

    public function show(AjusteInventario $ajusteInventario): View
    {
        $this->authorize('view', $ajusteInventario);

        return view('ajustes-inventario.show', ['ajuste' => $ajusteInventario->load(['lote.presentacion.producto', 'lote.ingresoProductor.productor', 'creador', 'anulador'])]);
    }

    public function edit(AjusteInventario $ajusteInventario): View
    {
        $this->authorize('update', $ajusteInventario);

        return view('ajustes-inventario.edit', [
            'ajuste' => $ajusteInventario->load(['lote.presentacion.producto', 'lote.ingresoProductor.productor']),
        ]);
    }

    public function update(ActualizarAjusteInventarioRequest $request, AjusteInventario $ajusteInventario): RedirectResponse
    {
        $this->servicio->actualizar($ajusteInventario, $request->validated(), $request->user());

        return redirect()->route('ajustes-inventario.show', $ajusteInventario)->with('success', 'Ajuste actualizado con trazabilidad.');
    }

    public function anular(AnularAjusteInventarioRequest $request, AjusteInventario $ajusteInventario): RedirectResponse
    {
        $this->servicio->anular($ajusteInventario, $request->validated('motivo_anulacion'), $request->user());

        return redirect()->route('ajustes-inventario.index')->with('success', 'Ajuste anulado mediante movimiento inverso.');
    }
}
