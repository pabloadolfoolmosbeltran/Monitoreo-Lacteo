<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request; // <--- Importante para capturar los filtros de la URL

class EventoController extends Controller
{
    /**
     * Mostrar la bitácora del sistema con filtros de tipo y fecha.
     */
    public function index(Request $request)
    {
        $eventos = Evento::with(['produccion', 'user'])
            ->when($request->filled('tipo'), function ($query) use ($request) {
                $query->where('tipo', $request->tipo);
            })
            ->when($request->filled('fecha'), function ($query) use ($request) {
                $query->whereDate('fecha_hora', $request->fecha);
            })
            ->orderBy('fecha_hora', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('eventos.index', compact('eventos'));
    }
}
