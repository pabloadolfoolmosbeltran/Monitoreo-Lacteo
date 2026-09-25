<?php

namespace App\Services;

use App\Models\Evento;
use App\Models\Produccion;
use Illuminate\Support\Facades\Auth;

class EventoService
{
    // APUNTE:
    // La bitácora del sistema se alimenta desde aquí. Los controladores no crean
    // eventos directamente: llaman a este servicio para dejar registrado qué
    // ocurrió, cuándo ocurrió y con qué producción estuvo relacionado.
    public static function registrar(
        ?int $produccionId,
        string $tipo,
        string $descripcion
    ): void
    {
        $userId = Auth::id();

        if (!$userId && $produccionId) {
            $produccion = Produccion::find($produccionId);
            if ($produccion) {
                $userId = $produccion->user_id;
            }
        }

        Evento::create([
            'produccion_id' => $produccionId,
            'user_id' => $userId,
            'tipo' => $tipo,
            'descripcion' => $descripcion,
            'fecha_hora' => now()
        ]);
    }
}
