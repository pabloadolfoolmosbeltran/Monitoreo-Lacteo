<?php

namespace App\Http\Controllers;

use App\Models\Actuador;
use App\Models\Produccion;
use App\Models\Sensor;
use App\Services\ActuadorService;
use App\Services\EventoService;
use Illuminate\Http\Request;

class ControlController extends Controller
{
    public function __construct(private readonly ActuadorService $actuadores)
    {
    }

    public function index()
    {
        $motor = Actuador::where('tipo', ActuadorService::MOTOR)->first();
        $ventilador = Actuador::where('tipo', ActuadorService::VENTILADOR)->first();
        $sensor = Sensor::first();

        return view('control.index', compact('motor', 'ventilador', 'sensor'));
    }

    public function motor(string $estado)
    {
        return $this->actualizarActuadorManual(
            ActuadorService::MOTOR,
            $estado,
            'Motor actualizado correctamente.'
        );
    }

    public function ventilador(string $estado)
    {
        return $this->actualizarActuadorManual(
            ActuadorService::VENTILADOR,
            $estado,
            'Ventilador actualizado correctamente.'
        );
    }

    public function sensor(string $estado)
    {
        $estadoSensor = match (strtolower($estado)) {
            'on' => 'Activo',
            'off' => 'Inactivo',
            default => null,
        };

        if ($estadoSensor === null) {
            return redirect('/control')->with('error', 'Estado de sensor no válido.');
        }

        $sensor = Sensor::first();

        if (! $sensor) {
            return redirect('/control')->with('error', 'No existe un sensor registrado.');
        }

        $sensor->update(['estado' => $estadoSensor]);
        $produccion = $this->produccionActiva();

        EventoService::registrar(
            $produccion?->id,
            'Sensor',
            $estadoSensor === 'Activo' ? 'Sensor activado.' : 'Sensor desactivado.'
        );

        return redirect('/control')
            ->with('success', 'Sensor actualizado correctamente.');
    }

    public function cambiarModo(Request $request, string $tipo)
    {
        if (! ActuadorService::esTipoValido($tipo)) {
            return redirect('/control')->with('error', 'Tipo de actuador no válido.');
        }

        $actualizado = $this->actuadores->cambiarModo(
            $tipo,
            $request->boolean('modo'),
            $this->produccionActiva()?->id
        );

        if (! $actualizado) {
            return redirect('/control')->with('error', "No existe el actuador {$tipo}.");
        }

        return redirect('/control')
            ->with('success', "Modo de {$tipo} actualizado correctamente.");
    }

    // APUNTE:
    // Este método evita repetir el mismo flujo para motor y ventilador:
    // valida el estado recibido, actualiza el actuador y deja trazabilidad
    // en la bitácora mediante EventoService.
    private function actualizarActuadorManual(string $tipo, string $estado, string $mensajeExito)
    {
        if (ActuadorService::normalizarEstado($estado) === null) {
            return redirect('/control')->with('error', "Estado no válido para {$tipo}.");
        }

        $actualizado = $this->actuadores->actualizarEstadoManual(
            $tipo,
            $estado,
            $this->produccionActiva()?->id
        );

        if (! $actualizado) {
            return redirect('/control')->with('error', "No existe el actuador {$tipo}.");
        }

        return redirect('/control')->with('success', $mensajeExito);
    }

    private function produccionActiva(): ?Produccion
    {
        return Produccion::where('estado', 'En proceso')->first();
    }
}
