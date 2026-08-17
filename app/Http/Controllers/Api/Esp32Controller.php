<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Actuador;
use App\Models\Sensor;
use Illuminate\Http\Request;
use App\Models\Lectura;
use App\Models\Produccion;
use App\Models\Alerta;
use App\Models\Dispositivo;
use App\Services\EventoService;
use Illuminate\Support\Facades\Cache;

class Esp32Controller extends Controller
{
    /**
     * Devuelve el estado actual del sistema para el ESP32.
     */
    public function estado()
    {
        $motor = Actuador::where('tipo', 'Motor')->first();
        $ventilador = Actuador::where('tipo', 'Ventilador')->first();
        $sensor = Sensor::first();

        // Buscamos la producción activa
        $produccion = Produccion::where('estado', 'En proceso')->first();

        return response()->json([
            'motor' => [
                'estado' => (bool) $motor?->estado,
                'modo'   => $motor?->modo,
            ],
            'ventilador' => [
                'estado' => (bool) $ventilador?->estado,
                'modo'   => $ventilador?->modo,
            ],
            'sensor' => [
                'estado' => $sensor?->estado,
            ],
            'produccion' => [
                'estado'               => $produccion?->estado,
                'etapa'                => $produccion?->etapa,
                'temperatura_objetivo' => $produccion ? (float) $produccion->temperatura_objetivo : null,
            ]
        ]);
    }

    /**
     * Registra las lecturas de temperatura del ESP32 de forma ultra-eficiente
     * y controla automáticamente el Motor y el Ventilador en tiempo real.
     */
    public function temperatura(Request $request)
    {
        $request->validate([
            'temperatura' => 'required|numeric'
        ]);

        $tempActual = $request->temperatura;
        $produccion = Produccion::with('producto')->where('estado', 'En proceso')->first();

        if (!$produccion) {
            return response()->json(['success' => false, 'mensaje' => 'No hay producción activa.'], 404);
        }

        // ==========================================================================
        // 1. CÁLCULO DE ESTADÍSTICAS (Sin guardar todas las lecturas)
        // ==========================================================================
        $countKey = "prod_count_{$produccion->id}";
        $sumKey = "prod_sum_{$produccion->id}";

        $count = Cache::get($countKey, 0) + 1;
        $sum = Cache::get($sumKey, 0.0) + $tempActual;

        $produccion->temperatura_final = $tempActual;

        if (is_null($produccion->temperatura_inicial)) {
            $produccion->temperatura_inicial = $tempActual;
        }

        $produccion->temperatura_minima = is_null($produccion->temperatura_minima) ? $tempActual : min($produccion->temperatura_minima, $tempActual);
        $produccion->temperatura_maxima = is_null($produccion->temperatura_maxima) ? $tempActual : max($produccion->temperatura_maxima, $tempActual);
        $produccion->temperatura_promedio = round($sum / $count, 2);
        $produccion->save();

        Cache::put($countKey, $count, now()->addDays(2));
        Cache::put($sumKey, $sum, now()->addDays(2));

        // ==========================================================================
        // 2. PASTEURIZACIÓN Y ALERTAS DE 70°C
        // ==========================================================================
        if ($tempActual >= 70) {
            $existe = Alerta::where('produccion_id', $produccion->id)
                            ->where('tipo', 'LIKE', '%Pasteuriz%')
                            ->exists();
            if (!$existe) {
                Alerta::create([
                    'produccion_id' => $produccion->id,
                    'tipo' => 'Pasteurizacion',
                    'mensaje' => '¡ALERTA CRÍTICA! La leche alcanzó los 70°C.',
                    'atendida' => false
                ]);
            }
        }

       // ==========================================================================
        // 3. LÓGICA DE PRODUCCIÓN (Enfriamiento Automático basado en Producto)
        // ==========================================================================
        if ($produccion->etapa == 'Produccion' && $produccion->estado == 'En proceso') {

            // Verificamos que la producción tenga un producto asignado
            if ($produccion->producto) {
                
                $limiteEnfriamiento = (float) $produccion->producto->temperatura_maxima;

                // VALIDACIÓN CRUCIAL:
                // 1. $tempActual > 0 evita que lecturas de error del sensor (0 o -127) apaguen el sistema.
                // 2. $tempActual <= $limiteEnfriamiento verifica si ya se enfrió lo suficiente.
                if ($tempActual > 0 && $tempActual <= $limiteEnfriamiento) {

                    $motor = Actuador::where('tipo', 'Motor')->first();
                    $ventilador = Actuador::where('tipo', 'Ventilador')->first();

                    // Apagar Motor
                    if ($motor && $motor->modo == 'Automatico' && $motor->estado) {
                        $motor->update(['estado' => false]);
                        EventoService::registrar($produccion->id, 'Motor', 'Motor apagado automáticamente.');
                    }

                    // Apagar Ventilador
                    if ($ventilador && $ventilador->modo == 'Automatico' && $ventilador->estado) {
                        $ventilador->update(['estado' => false]);
                        EventoService::registrar($produccion->id, 'Ventilador', 'Ventilador apagado automáticamente.');
                    }

                    // Finalizar Producción
                    $produccion->update([
                        'estado' => 'Finalizada',
                        'fecha_fin' => now()
                    ]);

                    EventoService::registrar(
                        $produccion->id,
                        'Producción',
                        "Producción finalizada: Se alcanzó la temperatura máxima del producto ({$tempActual}°C)."
                    );
                }
            }
        }

        // ==========================================================================
        // 4. HISTORIAL DE CÁLCULO PARA GRÁFICO (Caché)
        // ==========================================================================
        $chartKey = "chart_produccion_{$produccion->id}";
        $historial = Cache::get($chartKey, []);

        // Agregar nuevo punto
        $historial[] = ['t' => now()->format('H:i:s'), 'v' => $tempActual];

        // Mantener solo los últimos 20 puntos
        if (count($historial) > 20) {
            array_shift($historial);
        }

        // Guardar en caché por 24 horas para mantener la gráfica visualizable tras finalizar
        Cache::put($chartKey, $historial, now()->addHours(24));

        return response()->json([
            'success' => true,
            'temperatura' => $tempActual,
            'estado_produccion' => $produccion->estado,
            'etapa' => $produccion->etapa
        ]);
    }

    /**
     * Devuelve las últimas temperaturas para el gráfico (Desde la Caché).
     * Soporta producciones activas o la última finalizada.
     */
    public function temperaturas()
    {
        $produccion = Produccion::where('estado', 'En proceso')->first();

        if (!$produccion) {
            $produccion = Produccion::latest('updated_at')->first();
        }

        if ($produccion) {
            $chartKey = "chart_produccion_{$produccion->id}";
            return response()->json(Cache::get($chartKey, []));
        }

        return response()->json([]);
    }

    /**
     * Alias para compatibilidad de rutas de gráfico.
     */
    public function obtenerDatosGrafico()
    {
        return $this->temperaturas();
    }

    /**
     * Actualiza la última conexión del ESP32.
     */
    public function ping(Request $request)
    {
        $request->validate([
            'mac_address' => 'required|string'
        ]);

        $dispositivo = Dispositivo::where('mac_address', $request->mac_address)->first();

        if (!$dispositivo) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Dispositivo no registrado.'
            ], 404);
        }

        $dispositivo->update([
            'ultima_conexion' => now(),
            'estado' => 'Activo'
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Ping recibido.'
        ]);
    }

    /**
     * Información consolidada para el Dashboard en Tiempo Real.
     */
    public function dashboard()
    {
        $motor = Actuador::where('tipo', 'Motor')->first();
        $ventilador = Actuador::where('tipo', 'Ventilador')->first();
        $sensor = Sensor::first();

        // Si no hay producción en proceso, traemos la última registrada
        $produccion = Produccion::with('producto')->where('estado', 'En proceso')->first();

        // Si no hay producción activa, solo se devuelve una producción finalizada
        // si terminó hace poco (para no tratar datos viejos como el estado actual).
        if (!$produccion) {
            $produccion = Produccion::with('producto')
                ->where('estado', 'Finalizada')
                ->where('updated_at', '>=', now()->subMinutes(30))
                ->latest('updated_at')
                ->first();
        }

        $alertas = Alerta::where('atendida', false)->count();

        $dispositivo = Dispositivo::first();
        $conectado = false;

        if ($dispositivo && $dispositivo->ultima_conexion) {
            $conectado = now()->diffInSeconds($dispositivo->ultima_conexion) <= 25;
        }

        $temperaturaActual = $produccion?->temperatura_final ?? 0.0;

        return response()->json([
            'esp32' => [
                'conectado' => $conectado,
                'ultima_conexion' => $dispositivo?->ultima_conexion ? \Carbon\Carbon::parse($dispositivo->ultima_conexion)->format('d/m/Y H:i:s') : 'Sin registros'
            ],
            'temperatura' => $temperaturaActual,
            'motor' => [
                'estado' => (bool) $motor?->estado,
                'modo' => $motor?->modo
            ],
            'ventilador' => [
                'estado' => (bool) $ventilador?->estado,
                'modo' => $ventilador?->modo
            ],
            'sensor' => [
                'estado' => $sensor?->estado
            ],
            'produccion' => $produccion,
            'alertas' => $alertas
        ]);
    }

    public function alertaPasteurizacion(Request $request)
    {
        $request->validate([
            'mensaje' => 'required|string',
            'temperatura_final' => 'required|numeric'
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Alerta recibida correctamente.'
        ]);
    }

    public function finalizarProduccionAutomatica(Request $request)
    {
        $request->validate([
            'temperatura_final' => 'required|numeric'
        ]);

        $produccion = Produccion::where('estado', 'En proceso')->first();

        if (!$produccion) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No existe una producción activa.'
            ], 404);
        }

        $produccion->temperatura_final = $request->temperatura_final;
        $produccion->fecha_fin = now();
        $produccion->estado = 'Finalizada';
        $produccion->save();

        // Apagar automáticamente los actuadores
        Actuador::where('tipo', 'Motor')->update(['estado' => false]);
        Actuador::where('tipo', 'Ventilador')->update(['estado' => false]);

        // Registrar el evento
        EventoService::registrar(
            $produccion->id,
            'Producción',
            'Producción finalizada automáticamente al alcanzar la temperatura objetivo.'
        );

        // Registrar una alerta informativa
        Alerta::create([
            'produccion_id' => $produccion->id,
            'lectura_id'    => null,
            'tipo'          => 'Produccion Finalizada',
            'mensaje'       => 'El lote terminó automáticamente.',
            'atendida'      => false
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    public function pasteurizacion(Request $request)
    {
        $request->validate([
            'temperatura' => 'required|numeric'
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Alerta recibida.'
        ]);
    }
}