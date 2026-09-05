<?php

namespace App\Http\Controllers;

use App\Models\ConsignacionItem;
use App\Models\Devolucion;
use App\Support\Decimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DevolucionController extends Controller
{
    public function store(Request $request, ConsignacionItem $item)
    {
        $datos = $request->validate([
            'cantidad_devuelta' => 'required|numeric|gt:0',
            'precio_unitario_venta' => 'nullable|numeric|min:0',
            'fecha_devolucion' => 'nullable|date',
            'tipo' => ['required', Rule::in(['parcial', 'total'])],
            'observaciones' => 'nullable|string',
        ]);

        DB::transaction(function () use ($datos, $item) {
            $item = ConsignacionItem::with(['consignacion', 'ventas', 'devoluciones'])
                ->lockForUpdate()
                ->findOrFail($item->id);

            if ($item->consignacion->estado !== 'abierta') {
                throw ValidationException::withMessages([
                    'consignacion' => 'Una consignación liquidada o devuelta no puede recibir devoluciones.',
                ]);
            }
