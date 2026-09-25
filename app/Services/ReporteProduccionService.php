<?php

namespace App\Services;

use App\Models\Produccion;

class ReporteProduccionService
{
    // APUNTE:
    // Centraliza los cálculos usados por reporte web, PDF y Excel. Si cambia la
    // forma de medir duración o desviaciones, se modifica aquí una sola vez.
    public function calcularDatos(Produccion $produccion): array
    {
        $temperaturaMinima = $produccion->temperatura_minima;
        $temperaturaMaxima = $produccion->temperatura_maxima;   
        $temperaturaPromedio = $produccion->temperatura_promedio;
        $temperaturaInicial = $produccion->temperatura_inicial;
        $temperaturaFinal = $produccion->temperatura_final;
        $totalLecturas = $produccion->total_lecturas ?? $produccion->lecturas()->count();

        $tolerancia = 2.0;
        $huboDesviaciones = false;

        if ($temperaturaMinima && $temperaturaMaxima) {
            $huboDesviaciones = ($temperaturaMaxima - $produccion->temperatura_objetivo > $tolerancia)
                || ($produccion->temperatura_objetivo - $temperaturaMinima > $tolerancia);
        }

        return compact(
            'temperaturaMinima',
            'temperaturaMaxima',
            'temperaturaPromedio',
            'temperaturaInicial',
            'temperaturaFinal',
            'totalLecturas',
            'huboDesviaciones'
        ) + [
            'duracion' => $this->formatearDuracion($produccion),
        ];
    }

    public function formatearDuracion(Produccion $produccion): string
    {
        if (! $produccion->fecha_inicio) {
            return 'N/A';
        }

        $fin = $produccion->fecha_fin ?? now();
        $diff = $produccion->fecha_inicio->diff($fin);
        $partes = [];

        if ($diff->d > 0) {
            $partes[] = $diff->d.' '.($diff->d === 1 ? 'día' : 'días');
        }

        if ($diff->h > 0) {
            $partes[] = $diff->h.' '.($diff->h === 1 ? 'hora' : 'horas');
        }

        if ($diff->i > 0) {
            $partes[] = $diff->i.' '.($diff->i === 1 ? 'minuto' : 'minutos');
        }

        $duracion = $partes !== [] ? implode(', ', $partes) : 'Menos de un minuto';

        return $produccion->fecha_fin ? $duracion : $duracion.' (En curso)';
    }
}
