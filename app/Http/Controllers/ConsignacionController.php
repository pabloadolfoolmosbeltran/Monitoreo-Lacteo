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
