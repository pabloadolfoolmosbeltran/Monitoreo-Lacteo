<?php

namespace App\Services;

use App\Models\AjusteInventario;
use App\Models\IngresoProductorItem;
use App\Models\Presentacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AjusteInventarioService
{
    public function crear(array $datos, User $usuario): AjusteInventario
    {
        return DB::transaction(function () use ($datos, $usuario): AjusteInventario {
            [$lote, $presentacion] = $this->bloquear((int) $datos['lote_id']);
            $nueva = (int) $datos['cantidad_nueva'];
            $this->validarCantidad($nueva);
            $diferencia = $nueva - $lote->cantidad_disponible;
            if ($diferencia === 0) {
                $this->error('La cantidad nueva debe ser diferente al saldo actual.');
            }

            $ajuste = AjusteInventario::create([
                'lote_id' => $lote->id,
                'creado_por' => $usuario->id,
                'actualizado_por' => $usuario->id,
                'cantidad_anterior' => $lote->cantidad_disponible,
                'cantidad_nueva' => $nueva,
                'diferencia' => $diferencia,
                'tipo_motivo' => $datos['tipo_motivo'],
                'motivo' => $datos['motivo'],
                'estado' => 'activo',
            ]);

            $this->aplicar($lote, $presentacion, $diferencia);
            $this->movimiento($lote->id, $usuario->id, $diferencia, 'Ajuste #'.$ajuste->id.': '.$datos['motivo']);

            return $ajuste;
        }, 3);
    }

    public function anular(AjusteInventario $original, string $motivo, User $usuario): AjusteInventario
    {
        return DB::transaction(function () use ($original, $motivo, $usuario): AjusteInventario {
            [$lote, $presentacion] = $this->bloquear($original->lote_id);
            $ajuste = AjusteInventario::whereKey($original->id)->lockForUpdate()->firstOrFail();
            if ($ajuste->estado !== 'activo') {
                $this->error('El ajuste ya fue anulado.');
            }

            $inversa = -$ajuste->diferencia;
            $this->validarCantidad($lote->cantidad_disponible + $inversa);
            $this->aplicar($lote, $presentacion, $inversa);
            $ajuste->update([
                'estado' => 'anulado',
                'actualizado_por' => $usuario->id,
                'anulado_por' => $usuario->id,
                'anulado_en' => now(),
                'motivo_anulacion' => $motivo,
            ]);
            $this->movimiento($lote->id, $usuario->id, $inversa, 'Anulación ajuste #'.$ajuste->id.': '.$motivo);

            return $ajuste->fresh();
        }, 3);
    }

    public function actualizar(AjusteInventario $original, array $datos, User $usuario): AjusteInventario
    {
        return DB::transaction(function () use ($original, $datos, $usuario): AjusteInventario {
            [$lote, $presentacion] = $this->bloquear($original->lote_id);
            $ajuste = AjusteInventario::whereKey($original->id)->lockForUpdate()->firstOrFail();
            if ($ajuste->estado !== 'activo') {
                $this->error('Solo se puede modificar un ajuste activo.');
            }

            $cantidadNueva = (int) $datos['cantidad_nueva'];
            $this->validarCantidad($cantidadNueva);
            $diferenciaNueva = $cantidadNueva - (int) $ajuste->cantidad_anterior;
            $correccion = $diferenciaNueva - (int) $ajuste->diferencia;
            $this->validarCantidad((int) $lote->cantidad_disponible + $correccion);

            if ($correccion !== 0) {
                $this->aplicar($lote, $presentacion, $correccion);
                $this->movimiento(
                    $lote->id,
                    $usuario->id,
                    $correccion,
                    'Corrección ajuste #'.$ajuste->id.': '.$datos['motivo']
                );
            }

            $ajuste->update([
                'actualizado_por' => $usuario->id,
                'cantidad_nueva' => $cantidadNueva,
                'diferencia' => $diferenciaNueva,
                'tipo_motivo' => $datos['tipo_motivo'],
                'motivo' => $datos['motivo'],
            ]);

            return $ajuste->fresh();
        }, 3);
    }

    private function bloquear(int $loteId): array
    {
        $referencia = IngresoProductorItem::findOrFail($loteId);
        $presentacion = Presentacion::whereKey($referencia->presentacion_id)->lockForUpdate()->firstOrFail();
        $lote = IngresoProductorItem::whereKey($loteId)->lockForUpdate()->firstOrFail();

        return [$lote, $presentacion];
    }

    private function aplicar(IngresoProductorItem $lote, Presentacion $presentacion, int $diferencia): void
    {
        $saldoPresentacion = (int) $presentacion->stock + $diferencia;
        if ($saldoPresentacion < 0) {
            $this->error('El ajuste dejaría el inventario general con saldo negativo.');
        }
        $lote->update(['cantidad_disponible' => $lote->cantidad_disponible + $diferencia]);
        $presentacion->update(['stock' => $saldoPresentacion]);
    }

    private function movimiento(int $loteId, int $usuarioId, int $cantidad, string $motivo): void
    {
        DB::table('movimientos_inventario')->insert([
            'lote_id' => $loteId, 'user_id' => $usuarioId, 'tipo' => 'ajuste_inventario',
            'cantidad' => $cantidad, 'motivo' => $motivo, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function validarCantidad(int $cantidad): void
    {
        if ($cantidad < 0) {
            $this->error('El ajuste no puede dejar el lote con saldo negativo.');
        }
    }

    private function error(string $mensaje): never
    {
        throw ValidationException::withMessages(['cantidad_nueva' => $mensaje]);
    }
}
