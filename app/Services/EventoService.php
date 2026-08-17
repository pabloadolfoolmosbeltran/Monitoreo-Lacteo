<?php

namespace App\Services;

use App\Models\Evento;
use App\Models\Produccion;

use Illuminate\Support\Facades\Auth;
class EventoService
{
    /**
     * Registrar un evento del sistema.
     */
    public static function registrar(
        ?int $produccionId,
        string $tipo,
        string $descripcion
    ): void
    {
        // 1. Si hay un usuario logueado en la web, capturamos su ID
        $userId = Auth::id();

        // 2. Si no hay sesión activa (ej. peticiones automáticas del ESP32 o la API)
        // buscamos al operador que tiene asignada esta producción para heredarlo
        if (!$userId && $produccionId) {
            $produccion = Produccion::find($produccionId);
            if ($produccion) {
                $userId = $produccion->user_id;
            }
        }
        Evento::create([

            'produccion_id' => $produccionId,
            'user_id'       => $userId, 

            'tipo' => $tipo,

            'descripcion' => $descripcion,

            'fecha_hora' => now()

        ]);
    }
}
