<?php

namespace App\Http\Controllers;

use App\Models\Alerta;

class AlertaController extends Controller
{
    public function index()
    {   
        $alertas = Alerta::with([
                'produccion',
                'lectura'
            ])
            ->latest()
            ->get();

        return view('alertas.index', compact('alertas'));
    }

    public function atender($id)
    {
        $alerta = Alerta::findOrFail($id);
        $alerta->atendida = 1;
        $alerta->save();

        return redirect()->back()->with('success', 'La alerta ha sido marcada como atendida.');
    }
}