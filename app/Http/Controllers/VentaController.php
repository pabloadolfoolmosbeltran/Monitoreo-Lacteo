<?php

namespace App\Http\Controllers;

use App\Models\ConsignacionItem;
use App\Models\Venta;
use App\Support\Decimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaController extends Controller
{
    public function store(Request $request, ConsignacionItem $item)
    {
        $datos = $request->validate([
            'cantidad_vendida' => 'required|numeric|gt:0',
            'precio_unitario_venta' => 'nullable|numeric|min:0',
            'fecha_venta' => 'nullable|date',
            'observaciones' => 'nullable|string',
        ]);

        DB::transaction(function () use ($datos, $item) {
            $item = ConsignacionItem::with(['consignacion', 'presentacion'])
                ->lockForUpdate()
                ->findOrFail($item->id);

            if ($item->consignacion->estado !== 'abierta') {
                throw ValidationException::withMessages([
                    'consignacion' => 'Una consignación liquidada o devuelta no puede recibir nuevas ventas.',
                ]);
            }

            if (Decimal::compare($datos['cantidad_vendida'], $item->cantidad_disponible, 3) > 0) {
                throw ValidationException::withMessages([
                    'cantidad_vendida' => 'La cantidad vendida no puede superar la cantidad disponible.',
                ]);
            }

            $precioVenta = $datos['precio_unitario_venta'] ?? $item->precio_venta;

            if (Decimal::compare($precioVenta, $item->precio_productor, 2) < 0) {
                throw ValidationException::withMessages([
                    'precio_unitario_venta' => 'El precio real de venta no puede ser menor al precio del productor.',
                ]);
            }

            Venta::create([
                'consignacion_item_id' => $item->id,
                'user_id' => Auth::id(),
                'cantidad_vendida' => $datos['cantidad_vendida'],
                'precio_unitario_venta' => $precioVenta,
                'fecha_venta' => $datos['fecha_venta'] ?? now(),
                'observaciones' => $datos['observaciones'] ?? null,
            ]);

            $item->cantidad_disponible = Decimal::sub(
                $item->cantidad_disponible,
                $datos['cantidad_vendida'],
                3
            );
            $item->save();

            if (Decimal::compare($item->cantidad_disponible, '0', 3) === 0 && $item->presentacion) {
                $item->presentacion->update(['stock' => 0]);
            }
        });

        return redirect()
            ->route('consignaciones.show', $item->consignacion_id)
            ->with('success', 'Venta registrada correctamente.');
    }
}
