<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Produccion;
use App\Models\Producto;
use App\Models\User;
use App\Models\Dispositivo;
use App\Models\Actuador;
use App\Services\EventoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProduccionController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();

        if ($usuario && $usuario->rol === 'Administrador') {
            $users = User::where('activo', true)->orderBy('nombre_unidad_productiva')->get();
        } else {
            $users = $usuario ? collect([$usuario]) : collect();
        }

        return view('produccion.index', [
            'productos' => Producto::where('activo', true)->get(),
            'users' => $users,
            'produccionActiva' => Produccion::where('estado', 'En proceso')->first(),
            'usuario' => $usuario
        ]);
    }

    public function iniciar(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'producto_id' => 'required|exists:productos,id',
            'cantidad_leche' => 'required|numeric|min:1',
            'temperatura_objetivo' => 'required|numeric|min:1',
            'observaciones' => 'nullable|string'
        ]);

        $usuario = Auth::user();
        if ($usuario && $usuario->rol === 'Trabajador') {
            $request->merge(['user_id' => $usuario->id]);
        }

        if (Produccion::where('estado', 'En proceso')->exists()) {
            return redirect()->back()->with('error', 'Ya existe una producción en proceso.');
        }

        $dispositivo = Dispositivo::first();
        if (!$dispositivo) {
            return redirect()->back()->with('error', 'No existe un dispositivo ESP32 registrado.');
        }

        $producto = Producto::find($request->producto_id);
        $insumoTotalRequerido = 0;

        if ($producto) {
            $cuajoPorLitro = (float) ($producto->cuajo_por_litro ?? 0);
            $insumoTotalRequerido = $request->cantidad_leche * $cuajoPorLitro;

            if ($cuajoPorLitro > 0) {
                if ($producto->stock_cuajo < $insumoTotalRequerido) {
                    $unidad = $producto->unidad_cuajo ?? 'ml';
                    return redirect()->back()->with('error',
                        "Stock insuficiente del insumo ({$producto->tipo_cuajo}). " .
                        "Se requieren {$insumoTotalRequerido} {$unidad} " .
                        "y solo hay {$producto->stock_cuajo} {$unidad} disponibles."
                    );
                }
                $producto->decrement('stock_cuajo', $insumoTotalRequerido);
            }
        }

        $produccion = Produccion::create([
            'user_id' => $request->user_id,
            'producto_id' => $request->producto_id,
            'dispositivo_id' => $dispositivo->id,
            'cantidad_leche' => $request->cantidad_leche,
            'tipo_cuajo' => $producto->tipo_cuajo ?? 'N/A',
            'cantidad_cuajo' => $insumoTotalRequerido,
            'temperatura_objetivo' => $request->temperatura_objetivo,
            'fecha_inicio' => now(),
            'estado' => 'En proceso',
            'etapa' => 'Produccion',
            'observaciones' => $request->observaciones,
        ]);

        $unidad = $producto->unidad_cuajo ?? 'ml';
        $detalleInsumo = $insumoTotalRequerido > 0
            ? " Se descontaron {$insumoTotalRequerido} {$unidad} de insumo."
            : "";

        EventoService::registrar(
            $produccion->id,
            'Producción',
            'Producción iniciada. Etapa actual: Producción.' . $detalleInsumo
        );

        $motor = Actuador::where('tipo', 'Motor')->first();
        if ($motor && $motor->modo == 'Automatico') {
            $motor->estado = true;
            $motor->save();
            EventoService::registrar($produccion->id, 'Motor', 'Motor encendido automáticamente al iniciar producción.');
        }

        $ventilador = Actuador::where('tipo', 'Ventilador')->first();
        if ($ventilador && $ventilador->modo == 'Automatico') {
            $ventilador->estado = true;
            $ventilador->save();
            EventoService::registrar($produccion->id, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.');
        }

        return redirect('/produccion')
            ->with('success', 'Producción iniciada correctamente y registrada en bitácora.');
    }

    public function finalizar()
    {
        $produccion = Produccion::where('estado', 'En proceso')->first();

        if (!$produccion) {
            return redirect()->back()->with('error', 'No existe una producción activa.');
        }

        $produccion->update([
            'estado' => 'Finalizada',
            'fecha_fin' => now()
        ]);

        Cache::forget("prod_count_{$produccion->id}");
        Cache::forget("prod_sum_{$produccion->id}");
        Cache::forget("chart_produccion_{$produccion->id}");

        EventoService::registrar($produccion->id, 'Producción', 'La producción fue finalizada correctamente.');

        $motor = Actuador::where('tipo', 'Motor')->first();
        if ($motor) {
            $motor->estado = false;
            $motor->save();
            EventoService::registrar($produccion->id, 'Motor', 'Motor apagado automáticamente al finalizar producción.');
        }

        $ventilador = Actuador::where('tipo', 'Ventilador')->first();
        if ($ventilador) {
            $ventilador->estado = false;
            $ventilador->save();
            EventoService::registrar($produccion->id, 'Ventilador', 'Ventilador apagado automáticamente al finalizar producción.');
        }

        return redirect('/produccion')
            ->with('success', 'Producción finalizada correctamente y registrada en bitácora.');
    }
}
