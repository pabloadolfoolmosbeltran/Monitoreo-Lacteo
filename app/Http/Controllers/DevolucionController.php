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

            $cantidadVendida = $item->ventas->reduce(
                fn (string $total, $venta): string => Decimal::add($total, $venta->cantidad_vendida, 3),
                '0.000'
            );
            $cantidadDevuelta = $item->devoluciones->reduce(
                fn (string $total, $devolucion): string => Decimal::add($total, $devolucion->cantidad_devuelta, 3),
                '0.000'
            );
            $cantidadRetornable = Decimal::sub($cantidadVendida, $cantidadDevuelta, 3);

            if (Decimal::compare($datos['cantidad_devuelta'], $cantidadRetornable, 3) > 0) {
                throw ValidationException::withMessages([
                    'cantidad_devuelta' => 'La devolución no puede superar la cantidad vendida pendiente de devolución.',
                ]);
            }

            Devolucion::create([
                'consignacion_item_id' => $item->id,
                'user_id' => Auth::id(),
                'cantidad_devuelta' => $datos['cantidad_devuelta'],
                'precio_unitario_venta' => $datos['precio_unitario_venta'] ?? $item->precio_venta,
                'fecha_devolucion' => $datos['fecha_devolucion'] ?? now(),
                'tipo' => $datos['tipo'],
                'observaciones' => $datos['observaciones'] ?? null,
            ]);

            $item->cantidad_disponible = Decimal::add(
                $item->cantidad_disponible,
                $datos['cantidad_devuelta'],
                3
            );
            $item->save();
        });

        return redirect()
            ->route('consignaciones.show', $item->consignacion_id)
            ->with('success', 'Devolución registrada correctamente.');
    }
}
