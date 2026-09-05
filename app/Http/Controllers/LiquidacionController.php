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
