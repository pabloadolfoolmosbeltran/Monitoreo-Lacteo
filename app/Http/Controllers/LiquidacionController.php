<?php

namespace App\Http\Controllers;

use App\Models\Consignacion;
use App\Models\Liquidacion;
use App\Support\Decimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LiquidacionController extends Controller
{
    public function store(Request $request, Consignacion $consignacion)
    {
        $datos = $request->validate([
            'fecha_liquidacion' => 'nullable|date',
            'observaciones' => 'nullable|string',
        ]);

        DB::transaction(function () use ($datos, $consignacion) {
            $consignacion = Consignacion::with('items.ventas')
                ->lockForUpdate()
                ->findOrFail($consignacion->id);

            if ($consignacion->estado !== 'abierta') {
                throw ValidationException::withMessages([
                    'consignacion' => 'Solo se pueden liquidar consignaciones abiertas.',
                ]);
            }

            if ($consignacion->liquidacion()->exists()) {
                throw ValidationException::withMessages([
                    'consignacion' => 'Esta consignación ya tiene una liquidación registrada.',
                ]);
            }

            $montoLiquidado = $this->calcularMontoLiquidado($consignacion);

            Liquidacion::create([
                'consignacion_id' => $consignacion->id,
                'user_id' => Auth::id(),
                'fecha_liquidacion' => $datos['fecha_liquidacion'] ?? now()->toDateString(),
                'monto_liquidado' => $montoLiquidado,
                'observaciones' => $datos['observaciones'] ?? null,
            ]);

            $consignacion->update(['estado' => 'liquidada']);
        });

        return redirect()
            ->route('consignaciones.show', $consignacion)
            ->with('success', 'Consignación liquidada correctamente.');
    }

    private function calcularMontoLiquidado(Consignacion $consignacion): string
    {
        return $consignacion->items->reduce(function (string $total, $item): string {
            $montoItem = $item->ventas->reduce(
                fn (string $subtotal, $venta): string => Decimal::add(
                    $subtotal,
                    Decimal::mul($venta->cantidad_vendida, $item->precio_productor, 2),
                    2
                ),
                '0.00'
            );

            return Decimal::add($total, $montoItem, 2);
        }, '0.00');
    }
}
