<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ProduccionesExport;
use App\Models\Produccion;
use App\Services\ReporteProduccionService;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    public function __construct(private readonly ReporteProduccionService $reportes)
    {
    }

    public function index()
    {
        $producciones = Produccion::with(['user', 'producto'])
            ->orderByDesc('id')
            ->get();

        return view('reportes.index', compact('producciones'));
    }

    public function pdf($id)
    {
        $produccion = Produccion::with(['user', 'producto', 'dispositivo', 'alertas', 'eventos'])
            ->findOrFail($id);

        $datos = $this->reportes->calcularDatos($produccion);

        $pdf = Pdf::loadView('reportes.pdf', array_merge(compact('produccion'), $datos));

        return $pdf->download('Reporte_Produccion_'.$produccion->id.'.pdf');
    }

    public function show($id)
    {
        $produccion = Produccion::with(['user', 'producto', 'dispositivo', 'alertas', 'eventos'])
            ->findOrFail($id);

        $datos = $this->reportes->calcularDatos($produccion);

        return view('reportes.show', array_merge(compact('produccion'), $datos));
    }

    public function excel()
    {
        return Excel::download(new ProduccionesExport, 'Reporte_Producciones.xlsx');
    }
}
