<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Produccion;
use App\Models\Producto;
use App\Models\User;
use App\Models\Dispositivo;
use App\Models\Lectura;
use App\Models\Actuador;
use App\Services\EventoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProduccionController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $usuario */
        $usuario = Auth::user();

        // 🛡️ Si es Administrador, obtiene todos los usuarios.
        // 🛡️ Si es Trabajador, solo se le permite ver y usar su propio usuario.
        if ($usuario && $usuario->rol === 'Administrador') {
            $users = User::orderBy('nombre_unidad_productiva')->get();
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

        // 🛡️ BLINDAJE DE SEGURIDAD: Sobreescribir el user_id si el rol es Trabajador
        $usuario = Auth::user();
        if ($usuario && $usuario->rol === 'Trabajador') {
            $request->merge([
                'user_id' => $usuario->id
            ]);
        }

        // Evitar más de una producción activa
        if (Produccion::where('estado', 'En proceso')->exists()) {
            return redirect()
                ->back()
                ->with('error', 'Ya existe una producción en proceso.');
        }

        $dispositivo = Dispositivo::first();

        if (!$dispositivo) {
            return redirect()
                ->back()
                ->with('error', 'No existe un dispositivo ESP32 registrado.');
        }

        // 1. Guardamos la producción vinculada directamente al user_id
        $produccion = Produccion::create([
            'user_id' => $request->user_id,
            'producto_id' => $request->producto_id,
            'dispositivo_id' => $dispositivo->id,
            'cantidad_leche' => $request->cantidad_leche,
            'temperatura_objetivo' => $request->temperatura_objetivo,
            'fecha_inicio' => now(),
            'estado' => 'En proceso',
            'etapa' => 'Produccion',
            'observaciones' => $request->observaciones,
        ]);

        // 2. REGISTRO CON SERVICIO: Inicio de producción
        EventoService::registrar(
            $produccion->id,
            'Producción',
            'Producción iniciada. Etapa actual: Producción.'
        );

        // Encender automáticamente el motor si está en modo Automático
        $motor = Actuador::where('tipo', 'Motor')->first();

        if ($motor && $motor->modo == 'Automatico') {
            $motor->estado = true;
            $motor->save();

            EventoService::registrar(
                $produccion->id,
                'Motor',
                'Motor encendido automáticamente al iniciar producción.'
            );
        }

        // Encender automáticamente el ventilador si está en modo Automático
        $ventilador = Actuador::where('tipo', 'Ventilador')->first();

        if ($ventilador && $ventilador->modo == 'Automatico') {
            $ventilador->estado = true;
            $ventilador->save();

            EventoService::registrar(
                $produccion->id,
                'Ventilador',
                'Ventilador encendido automáticamente al iniciar producción.'
            );
        }

        return redirect('/produccion')
            ->with('success', 'Producción iniciada correctamente y registrada en bitácora.');
    }

    public function finalizar()
    {
        $produccion = Produccion::where('estado', 'En proceso')->first();

        if (!$produccion) {
            return redirect()
                ->back()
                ->with('error', 'No existe una producción activa.');
        }

        // 1. Finalizar la producción en la base de datos
        $produccion->update([
            'estado' => 'Finalizada',
            'fecha_fin' => now()
        ]);

        // Limpieza de caché
        Cache::forget("prod_count_{$produccion->id}");
        Cache::forget("prod_sum_{$produccion->id}");
        Cache::forget("chart_produccion_{$produccion->id}");

        // 2. REGISTRO CON SERVICIO: Finalización de producción
        EventoService::registrar(
            $produccion->id,
            'Producción',
            'La producción fue finalizada correctamente.'
        );

        // Apagar automáticamente el motor
        $motor = Actuador::where('tipo', 'Motor')->first();

        if ($motor) {
            $motor->estado = false;
            $motor->save();

            EventoService::registrar(
                $produccion->id,
                'Motor',
                'Motor apagado automáticamente al finalizar producción.'
            );
        }

        // Apagar automáticamente el ventilador
        $ventilador = Actuador::where('tipo', 'Ventilador')->first();

        if ($ventilador) {
            $ventilador->estado = false;
            $ventilador->save();

            EventoService::registrar(
                $produccion->id,
                'Ventilador',
                'Ventilador apagado automáticamente al finalizar producción.'
            );
        }

        return redirect('/produccion')
            ->with('success', 'Producción finalizada correctamente y registrada en bitácora.');
    }
}