<?php

namespace App\Http\Controllers;

use App\Models\Actuador;
use App\Models\Sensor;
use App\Models\Produccion;
use App\Services\EventoService; // Importamos únicamente el servicio de eventos
use Illuminate\Http\Request; // <--- NUEVO: Importar Request para capturar el switch

class ControlController extends Controller
{
    public function index()
    {
        $motor = Actuador::where('tipo', 'Motor')->first();
        $ventilador = Actuador::where('tipo', 'Ventilador')->first();
        $sensor = Sensor::first();

        return view('control.index', compact(
            'motor',
            'ventilador',
            'sensor'
        ));
    }

    public function motor($estado)
    {
        $motor = Actuador::where('tipo', 'Motor')->first();

        if ($motor) {
            $motor->estado = ($estado == 'on');
            $motor->modo = 'Manual';
            $motor->save();

            /*
            |--------------------------------------------------------------------------
            | Registrar evento del motor
            |--------------------------------------------------------------------------
            */
            $produccion = Produccion::where('estado', 'En proceso')->first();

            EventoService::registrar(
                $produccion?->id,
                'Motor',
                $estado == 'on'
                    ? 'Motor encendido manualmente.'
                    : 'Motor apagado manualmente.'
            );
        }

        return redirect('/control')
            ->with('success', 'Motor actualizado correctamente.');
    }

    public function ventilador($estado)
    {
        $ventilador = Actuador::where('tipo', 'Ventilador')->first();

        if ($ventilador) {
            $ventilador->estado = ($estado == 'on');
            $ventilador->modo = 'Manual';
            $ventilador->save();

            /*
            |--------------------------------------------------------------------------
            | Registrar evento del ventilador
            |--------------------------------------------------------------------------
            */
            $produccion = Produccion::where('estado', 'En proceso')->first();

            EventoService::registrar(
                $produccion?->id,
                'Ventilador',
                $estado == 'on'
                    ? 'Ventilador encendido manualmente.'
                    : 'Ventilador apagado manualmente.'
            );
        }

        return redirect('/control')
            ->with('success', 'Ventilador actualizado correctamente.');
    }

    public function sensor($estado)
    {
        $sensor = Sensor::first();

        if ($sensor) {
            $sensor->estado = ($estado == 'on') ? 'Activo' : 'Inactivo';
            $sensor->save();

            /*
            |--------------------------------------------------------------------------
            | Registrar evento del sensor
            |--------------------------------------------------------------------------
            */
            $produccion = Produccion::where('estado', 'En proceso')->first();

            EventoService::registrar(
                $produccion?->id,
                'Sensor',
                $estado == 'on'
                    ? 'Sensor activado.'
                    : 'Sensor desactivado.'
            );
        }

        return redirect('/control')
            ->with('success', 'Sensor actualizado correctamente.');
    }
    public function cambiarModo(Request $request, $tipo)
    {
        $actuador = Actuador::where('tipo', $tipo)->first();

        if ($actuador) {
            // Guardamos 'Automatico' (sin tilde) si el switch está marcado, sino 'Manual'
            $actuador->modo = $request->has('modo') ? 'Automatico' : 'Manual';
            $actuador->save();

            /*
            |--------------------------------------------------------------------------
            | Registrar el cambio en la bitácora
            |--------------------------------------------------------------------------
            */
            $produccion = Produccion::where('estado', 'En proceso')->first();

            EventoService::registrar(
                $produccion?->id,
                $tipo,
                "{$tipo} cambiado a modo {$actuador->modo}."
            );
        }

        return redirect('/control')
            ->with('success', "Modo de {$tipo} actualizado correctamente.");
    }
}
