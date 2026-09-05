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
