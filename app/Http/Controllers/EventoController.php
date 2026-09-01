<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;

class EventoController extends Controller
{
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
