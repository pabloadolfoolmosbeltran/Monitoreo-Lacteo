<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Actuador;
use App\Models\Sensor;
use Illuminate\Http\Request;
use App\Models\Produccion;
use App\Models\Alerta;
use App\Models\Dispositivo;
use App\Services\EventoService;
use Illuminate\Support\Facades\Cache;

class Esp32Controller extends Controller
{
    public function estado()
    {
        $motor = Actuador::where('tipo', 'Motor')->first();
        $ventilador = Actuador::where('tipo', 'Ventilador')->first();
        $sensor = Sensor::first();
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

    public function temperatura(Request $request)
    {
        $request->validate(['temperatura' => 'required|numeric']);

        $tempActual = $request->temperatura;

        $sensor = Sensor::first();
        if ($sensor) {
            $sensor->update(['temperatura_actual' => $tempActual]);
        }

        $produccion = Produccion::with('producto')->where('estado', 'En proceso')->first();

        if (!$produccion) {
            $chartKey = "chart_global";
            $historial = Cache::get($chartKey, []);
            $historial[] = ['t' => now()->format('H:i:s'), 'v' => $tempActual];
            if (count($historial) > 20) array_shift($historial);
            Cache::put($chartKey, $historial, now()->addHours(24));

            return response()->json([
                'success' => true,
                'temperatura' => $tempActual,
                'estado_produccion' => null,
                'etapa' => null
            ]);
        }

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

        $tempPasteurizacion = $produccion->producto->temperatura_pasteurizacion ?? 70.0;
        if ($tempActual >= $tempPasteurizacion) {
            $existe = Alerta::where('produccion_id', $produccion->id)
                            ->where('tipo', 'LIKE', '%Pasteuriz%')
                            ->where('atendida', false)
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

        if ($produccion->etapa == 'Produccion' && $produccion->estado == 'En proceso') {
            if ($produccion->producto) {
                $limiteEnfriamiento = (float) $produccion->producto->temperatura_maxima;
                $sensorValido = ($tempActual > -50 && $tempActual < 150);

                if ($sensorValido && $tempActual <= $limiteEnfriamiento) {
                    $motor = Actuador::where('tipo', 'Motor')->first();
                    $ventilador = Actuador::where('tipo', 'Ventilador')->first();

                    if ($motor && $motor->modo == 'Automatico' && $motor->estado) {
                        $motor->update(['estado' => false]);
                        EventoService::registrar($produccion->id, 'Motor', 'Motor apagado automáticamente.');
                    }

                    if ($ventilador && $ventilador->modo == 'Automatico' && $ventilador->estado) {
                        $ventilador->update(['estado' => false]);
                        EventoService::registrar($produccion->id, 'Ventilador', 'Ventilador apagado automáticamente.');
                    }

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

        $chartKey = "chart_produccion_{$produccion->id}";
        $historial = Cache::get($chartKey, []);
        $historial[] = ['t' => now()->format('H:i:s'), 'v' => $tempActual];
        if (count($historial) > 20) array_shift($historial);
        Cache::put($chartKey, $historial, now()->addHours(24));

        return response()->json([
            'success' => true,
            'temperatura' => $tempActual,
            'estado_produccion' => $produccion->estado,
            'etapa' => $produccion->etapa
        ]);
    }

    public function temperaturas()
    {
        $produccion = Produccion::where('estado', 'En proceso')->first();

        if (!$produccion) {
            $produccion = Produccion::latest('updated_at')->first();
        }

        if ($produccion) {
            $chartKey = "chart_produccion_{$produccion->id}";
            $datos = Cache::get($chartKey, []);
            if (!empty($datos)) {
                return response()->json($datos);
            }
        }

        return response()->json(Cache::get('chart_global', []));
    }

    public function obtenerDatosGrafico()
    {
        return $this->temperaturas();
    }

    public function ping(Request $request)
    {
        $request->validate(['mac_address' => 'required|string']);

        $dispositivo = Dispositivo::where('mac_address', $request->mac_address)->first();

        if (!$dispositivo) {
            return response()->json(['success' => false, 'mensaje' => 'Dispositivo no registrado.'], 404);
        }

        $dispositivo->update(['ultima_conexion' => now(), 'estado' => 'Activo']);

        return response()->json(['success' => true, 'mensaje' => 'Ping recibido.']);
    }

    public function dashboard()
    {
        $motor = Actuador::where('tipo', 'Motor')->first();
        $ventilador = Actuador::where('tipo', 'Ventilador')->first();
        $sensor = Sensor::first();

        $produccion = Produccion::with('producto')->where('estado', 'En proceso')->first();

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

        $temperaturaActual = $sensor?->temperatura_actual ?? $produccion?->temperatura_final ?? 0.0;

        return response()->json([
            'esp32' => [
                'conectado' => $conectado,
                'ultima_conexion' => $dispositivo?->ultima_conexion
                    ? \Carbon\Carbon::parse($dispositivo->ultima_conexion)->format('d/m/Y H:i:s')
                    : 'Sin registros'
            ],
            'temperatura' => $temperaturaActual,
            'motor' => ['estado' => (bool) $motor?->estado, 'modo' => $motor?->modo],
            'ventilador' => ['estado' => (bool) $ventilador?->estado, 'modo' => $ventilador?->modo],
            'sensor' => ['estado' => $sensor?->estado],
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

        return response()->json(['success' => true, 'mensaje' => 'Alerta recibida correctamente.']);
    }

    public function finalizarProduccionAutomatica(Request $request)
    {
        $request->validate(['temperatura_final' => 'required|numeric']);

        $produccion = Produccion::where('estado', 'En proceso')->first();

        if (!$produccion) {
            return response()->json(['success' => false, 'mensaje' => 'No existe una producción activa.'], 404);
        }

        $produccion->temperatura_final = $request->temperatura_final;
        $produccion->fecha_fin = now();
        $produccion->estado = 'Finalizada';
        $produccion->save();

        Actuador::where('tipo', 'Motor')->update(['estado' => false]);
        Actuador::where('tipo', 'Ventilador')->update(['estado' => false]);

        EventoService::registrar(
            $produccion->id,
            'Producción',
            'Producción finalizada automáticamente al alcanzar la temperatura objetivo.'
        );

        Alerta::create([
            'produccion_id' => $produccion->id,
            'lectura_id'    => null,
            'tipo'          => 'Produccion Finalizada',
            'mensaje'       => 'El lote terminó automáticamente.',
            'atendida'      => false
        ]);

        return response()->json(['success' => true]);
    }

    public function pasteurizacion(Request $request)
    {
        $request->validate(['temperatura' => 'required|numeric']);

        return response()->json(['success' => true, 'mensaje' => 'Alerta recibida.']);
    }
}
