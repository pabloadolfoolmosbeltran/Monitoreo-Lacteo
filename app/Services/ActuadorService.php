<?php

namespace App\Services;

use App\Models\Actuador;

class ActuadorService
{
    public const MOTOR = 'Motor';
    public const VENTILADOR = 'Ventilador';

    private const TIPOS_VALIDOS = [
        self::MOTOR,
        self::VENTILADOR,
    ];

    // APUNTE:
    // Este servicio concentra la lógica de los actuadores físicos. Así, el panel
    // web, el proceso de producción y la API del ESP32 actualizan motor y
    // ventilador con las mismas reglas y registran eventos de forma consistente.
    public function actualizarEstadoManual(string $tipo, string $estado, ?int $produccionId = null): bool
    {
        $estadoNormalizado = self::normalizarEstado($estado);

        if ($estadoNormalizado === null || ! self::esTipoValido($tipo)) {
            return false;
        }

        $actuador = $this->buscar($tipo);

        if (! $actuador) {
            return false;
        }

        $actuador->update([
            'estado' => $estadoNormalizado,
            'modo' => 'Manual',
        ]);

        EventoService::registrar(
            $produccionId,
            $tipo,
            $estadoNormalizado
                ? "{$tipo} encendido manualmente."
                : "{$tipo} apagado manualmente."
        );

        return true;
    }

    public function cambiarModo(string $tipo, bool $automatico, ?int $produccionId = null): bool
    {
        if (! self::esTipoValido($tipo)) {
            return false;
        }

        $actuador = $this->buscar($tipo);

        if (! $actuador) {
            return false;
        }

        $modo = $automatico ? 'Automatico' : 'Manual';
        $actuador->update(['modo' => $modo]);

        EventoService::registrar(
            $produccionId,
            $tipo,
            "{$tipo} cambiado a modo {$modo}."
        );

        return true;
    }

    public function encenderSiEstaEnAutomatico(string $tipo, int $produccionId): void
    {
        $actuador = $this->buscar($tipo);

        if (! $actuador || $actuador->modo !== 'Automatico') {
            return;
        }

        $actuador->update(['estado' => true]);

        EventoService::registrar(
            $produccionId,
            $tipo,
            "{$tipo} encendido automáticamente al iniciar producción."
        );
    }

    public function apagarAlFinalizar(string $tipo, int $produccionId): void
    {
        $actuador = $this->buscar($tipo);

        if (! $actuador) {
            return;
        }

        $actuador->update(['estado' => false]);

        EventoService::registrar(
            $produccionId,
            $tipo,
            "{$tipo} apagado automáticamente al finalizar producción."
        );
    }

    public function apagarAutomaticoSiEstaEncendido(string $tipo, int $produccionId, string $descripcion): void
    {
        $actuador = $this->buscar($tipo);

        if (! $actuador || $actuador->modo !== 'Automatico' || ! $actuador->estado) {
            return;
        }

        $actuador->update(['estado' => false]);

        EventoService::registrar($produccionId, $tipo, $descripcion);
    }

    public static function esTipoValido(string $tipo): bool
    {
        return in_array($tipo, self::TIPOS_VALIDOS, true);
    }

    public static function normalizarEstado(string $estado): ?bool
    {
        return match (strtolower($estado)) {
            'on' => true,
            'off' => false,
            default => null,
        };
    }

    private function buscar(string $tipo): ?Actuador
    {
        return Actuador::where('tipo', $tipo)->first();
    }
}
