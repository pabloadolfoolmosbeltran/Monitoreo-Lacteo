<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Produccion;
use App\Exports\ProduccionesExport;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    /**
     * Listado de producciones.
     */
    public function index()
    {
        $producciones = Produccion::with([
            'user', // 👈 Cargamos el usuario unificado
            'producto'
        ])
        ->orderByDesc('id')
        ->get();

        return view(
            'reportes.index',
            compact('producciones')
        );
    }

    /**
     * Mostrar el reporte de una producción en PDF.
     */
    public function pdf($id)
    {
        $produccion = Produccion::with([
            'user',
            'producto',
            'dispositivo',
            'alertas',
            'eventos'
        ])->findOrFail($id);

        // 🚀 SOLUCIÓN: Leemos directamente los campos optimizados de la tabla de producción
        $temperaturaMinima   = $produccion->temperatura_minima;
        $temperaturaMaxima   = $produccion->temperatura_maxima;
        $temperaturaPromedio = $produccion->temperatura_promedio;
        $temperaturaInicial  = $produccion->temperatura_inicial;
        $temperaturaFinal    = $produccion->temperatura_final;

        // Si no tienes la columna total_lecturas creada, usamos por seguridad un fallback a la relación
        $totalLecturas       = $produccion->total_lecturas ?? $produccion->lecturas()->count();

        // Determinar desviaciones (Tolerancia de ±2 °C respecto al objetivo de la producción)
        $tolerancia = 2.0;
        $huboDesviaciones = false;
        if ($temperaturaMinima && $temperaturaMaxima) {
            $huboDesviaciones = ($temperaturaMaxima - $produccion->temperatura_objetivo > $tolerancia) ||
                                ($produccion->temperatura_objetivo - $temperaturaMinima > $tolerancia);
        }

        /*
        |--------------------------------------------------------------------------
        | Calcular Duración exacta de la producción para el PDF
        |--------------------------------------------------------------------------
        */
        $duracion = 'N/A';
        if ($produccion->fecha_inicio) {
            $fin = $produccion->fecha_fin ?? now();
            $diff = $produccion->fecha_inicio->diff($fin);

            $partes = [];
            if ($diff->d > 0) {
                $partes[] = $diff->d . ' ' . ($diff->d == 1 ? 'día' : 'días');
            }
            if ($diff->h > 0) {
                $partes[] = $diff->h . ' ' . ($diff->h == 1 ? 'hora' : 'horas');
            }
            if ($diff->i > 0) {
                $partes[] = $diff->i . ' ' . ($diff->i == 1 ? 'minuto' : 'minutos');
            }

            $duracion = count($partes) > 0 ? implode(', ', $partes) : 'Menos de un minuto';

            if (!$produccion->fecha_fin) {
                $duracion .= ' (En curso)';
            }
        }

        $pdf = Pdf::loadView(
            'reportes.pdf',
            compact(
                'produccion',
                'temperaturaMinima',
                'temperaturaMaxima',
                'temperaturaPromedio',
                'temperaturaInicial',
                'temperaturaFinal',
                'totalLecturas',
                'huboDesviaciones',
                'duracion'
            )
        );

        return $pdf->download(
            'Reporte_Produccion_'.$produccion->id.'.pdf'
        );
    }

    /**
     * Mostrar el reporte detallado en pantalla.
     */
    public function show($id)
    {
        $produccion = Produccion::with([
            'user',
            'producto',
            'dispositivo',
            'alertas',
            'eventos'
        ])->findOrFail($id);

        // 🚀 SOLUCIÓN: Leemos directamente los campos optimizados de la tabla de producción
        $temperaturaMinima   = $produccion->temperatura_minima;
        $temperaturaMaxima   = $produccion->temperatura_maxima;
        $temperaturaPromedio = $produccion->temperatura_promedio;
        $temperaturaInicial  = $produccion->temperatura_inicial;
        $temperaturaFinal    = $produccion->temperatura_final;

        $totalLecturas       = $produccion->total_lecturas ?? $produccion->lecturas()->count();

        $tolerancia = 2.0;
        $huboDesviaciones = false;
        if ($temperaturaMinima && $temperaturaMaxima) {
            $huboDesviaciones = ($temperaturaMaxima - $produccion->temperatura_objetivo > $tolerancia) ||
                                ($produccion->temperatura_objetivo - $temperaturaMinima > $tolerancia);
        }

        /*
        |--------------------------------------------------------------------------
        | Calcular Duración exacta de la producción
        |--------------------------------------------------------------------------
        */
        $duracion = 'N/A';
        if ($produccion->fecha_inicio) {
            $fin = $produccion->fecha_fin ?? now();
            $diff = $produccion->fecha_inicio->diff($fin);

            $partes = [];
            if ($diff->d > 0) {
                $partes[] = $diff->d . ' ' . ($diff->d == 1 ? 'día' : 'días');
            }
            if ($diff->h > 0) {
                $partes[] = $diff->h . ' ' . ($diff->h == 1 ? 'hora' : 'horas');
            }
            if ($diff->i > 0) {
                $partes[] = $diff->i . ' ' . ($diff->i == 1 ? 'minuto' : 'minutos');
            }

            $duracion = count($partes) > 0 ? implode(', ', $partes) : 'Menos de un minuto';

            if (!$produccion->fecha_fin) {
                $duracion .= ' (En curso)';
            }
        }

        return view('reportes.show', compact(
            'produccion',
            'temperaturaMinima',
            'temperaturaMaxima',
            'temperaturaPromedio',
            'temperaturaInicial',
            'temperaturaFinal',
            'totalLecturas',
            'huboDesviaciones',
            'duracion'
        ));
    }

    public function excel()
    {
        return Excel::download(
            new ProduccionesExport,
            'Reporte_Producciones.xlsx'
        );
    }
}