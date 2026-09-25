<?php

namespace App\Services;

use App\Models\DescarteProducto;
use App\Models\IngresoProductorItem;
use App\Models\Presentacion;
use App\Models\User;
use App\Support\Decimal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DescarteProductoService
{
    public function crear(array $datos, User $usuario): DescarteProducto
    {
        $lote = IngresoProductorItem::findOrFail($datos['lote_id']);
        $costo = $lote->precio_acopio_unitario ?? '0.00';

        return DescarteProducto::create([
            ...$datos, 'creado_por' => $usuario->id, 'actualizado_por' => $usuario->id,
            'fecha_caducidad' => $lote->fecha_caducidad, 'costo_unitario' => $costo,
            'perdida_total' => Decimal::mul($datos['cantidad'], $costo), 'estado' => 'pendiente',
        ]);
    }

    public function actualizar(DescarteProducto $descarte, array $datos, User $usuario): DescarteProducto
    {
        return DB::transaction(function () use ($descarte, $datos, $usuario): DescarteProducto {
            $referencia = IngresoProductorItem::findOrFail($datos['lote_id']);
            Presentacion::whereKey($referencia->presentacion_id)->lockForUpdate()->firstOrFail();
            $lote = IngresoProductorItem::whereKey($referencia->id)->lockForUpdate()->firstOrFail();
            $actual = DescarteProducto::whereKey($descarte->id)->lockForUpdate()->firstOrFail();
            if ($actual->estado !== 'pendiente') {
                $this->error('Un descarte procesado no puede modificarse.');
            }

            $costo = $lote->precio_acopio_unitario ?? '0.00';
            $actual->update([
                ...$datos,
                'actualizado_por' => $usuario->id,
                'fecha_caducidad' => $lote->fecha_caducidad,
                'costo_unitario' => $costo,
                'perdida_total' => Decimal::mul($datos['cantidad'], $costo),
            ]);

            return $actual->fresh();
        }, 3);
    }

    public function eliminar(DescarteProducto $descarte): void
    {
        DB::transaction(function () use ($descarte): void {
            $referencia = IngresoProductorItem::findOrFail($descarte->lote_id);
            Presentacion::whereKey($referencia->presentacion_id)->lockForUpdate()->firstOrFail();
            IngresoProductorItem::whereKey($referencia->id)->lockForUpdate()->firstOrFail();
            $actual = DescarteProducto::whereKey($descarte->id)->lockForUpdate()->firstOrFail();
            if ($actual->estado !== 'pendiente') {
                $this->error('Un descarte procesado no puede eliminarse.');
            }

            $actual->delete();
        }, 3);
    }

    public function procesar(DescarteProducto $original, User $usuario): DescarteProducto
    {
        return DB::transaction(function () use ($original, $usuario): DescarteProducto {
            $ref = IngresoProductorItem::findOrFail($original->lote_id);
            $presentacion = Presentacion::whereKey($ref->presentacion_id)->lockForUpdate()->firstOrFail();
            $lote = IngresoProductorItem::whereKey($ref->id)->lockForUpdate()->firstOrFail();
            $descarte = DescarteProducto::whereKey($original->id)->lockForUpdate()->firstOrFail();
            if ($descarte->estado !== 'pendiente') {
                $this->error('El descarte ya fue procesado.');
            }
            if ($descarte->cantidad > $lote->cantidad_disponible) {
                $this->error('El lote no tiene stock suficiente para procesar el descarte.');
            }
            if ($descarte->cantidad > $presentacion->stock) {
                $this->error('El inventario general es inconsistente y no permite procesar el descarte.');
            }
            $lote->decrement('cantidad_disponible', $descarte->cantidad);
            $presentacion->decrement('stock', $descarte->cantidad);
            $descarte->update(['estado' => 'procesado', 'actualizado_por' => $usuario->id, 'procesado_por' => $usuario->id, 'procesado_en' => now()]);
            DB::table('movimientos_inventario')->insert(['lote_id' => $lote->id, 'user_id' => $usuario->id, 'tipo' => 'descarte', 'cantidad' => -$descarte->cantidad, 'motivo' => 'Descarte #'.$descarte->id.': '.$descarte->tipo_motivo, 'created_at' => now(), 'updated_at' => now()]);

            return $descarte->fresh();
        }, 3);
    }

    private function error(string $mensaje): never
    {
        throw ValidationException::withMessages(['descarte' => $mensaje]);
    }
}
