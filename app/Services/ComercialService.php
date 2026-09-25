<?php

namespace App\Services;

use App\Models\IngresoProductor;
use App\Models\IngresoProductorItem;
use App\Models\Presentacion;
use App\Models\Productor;
use App\Models\Venta;
use App\Support\Decimal as D;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** All stock writers lock presentations first, ordered by ID, then headers/lots. */
class ComercialService
{
    private function error(string $message): never
    {
        throw ValidationException::withMessages(['operacion' => $message]);
    }

    private function presentaciones(array $ids)
    {
        return Presentacion::whereIn('id', array_unique($ids))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
    }

    private function stock(Presentacion $p, int $delta): void
    {
        $nuevo = (int) $p->stock + $delta;
        if ($nuevo < 0) {
            $this->error('La existencia global es insuficiente; revise el inventario por lote.');
        }
        $p->update(['stock' => $nuevo]);
    }

    public function ingreso(array $datos, int $operador): IngresoProductor
    {
        return DB::transaction(function () use ($datos, $operador) {
            $ps = $this->presentaciones(array_column($datos['items'], 'presentacion_id'));
            $productor = Productor::whereKey($datos['productor_id'])->where('activo', true)->lockForUpdate()->first();
            if (! $productor) {
                $this->error('El proveedor no está activo.');
            }
            $ingreso = IngresoProductor::create(['productor_id' => $productor->id, 'user_id' => $operador,
                'fecha_ingreso' => $datos['fecha_ingreso'], 'observaciones' => $datos['observaciones'] ?? null, 'estado' => 'abierta']);
            foreach ($datos['items'] as $linea) {
                $p = $ps->get($linea['presentacion_id']);
                if (! $p || ! $p->activo || ! $p->producto->activo) {
                    $this->error('La presentación o producto está inactivo.');
                }
                $linea['precio_venta_unitario'] = $p->precio;
                if (D::compare($linea['precio_venta_unitario'], $linea['precio_acopio_unitario'] ?? 0) < 0) {
                    $this->error('El precio de venta debe cubrir el precio de acopio.');
                }
                $ingreso->items()->create([...$linea, 'cantidad_disponible' => $linea['cantidad_ingresada'],
                    'fecha_recepcion' => substr($datos['fecha_ingreso'], 0, 10)]);
                $this->stock($p, (int) $linea['cantidad_ingresada']);
            }

            return $ingreso;
        }, 3);
    }

    public function vender(array $datos, int $operador): array
    {
        usort($datos['items'], fn ($a, $b) => $a['presentacion_id'] <=> $b['presentacion_id']);
        foreach ($datos['items'] as &$linea) {
            $linea['cantidad'] = (int) $linea['cantidad'];
        }
        unset($linea);
        $hash = hash('sha256', json_encode([$datos['items'], $datos['metodo_pago'], $datos['cliente'] ?? null]));
        try {
            return DB::transaction(function () use ($datos, $operador, $hash) {
                $ps = $this->presentaciones(array_column($datos['items'], 'presentacion_id'));
                $existing = DB::table('tickets_venta')->where('clave', $datos['clave'])->first();
                if ($existing) {
                    return $this->repetir($existing, $hash, $operador);
                }
                $ticket = DB::table('tickets_venta')->insertGetId(['clave' => $datos['clave'], 'payload_hash' => $hash,
                    'user_id' => $operador, 'metodo_pago' => $datos['metodo_pago'], 'cliente' => $datos['cliente'] ?? null,
                    'total' => '0.00', 'created_at' => now(), 'updated_at' => now()]);
                $total = '0.00';
                foreach ($datos['items'] as $linea) {
                    $p = $ps->get($linea['presentacion_id']);
                    if (! $p || ! $p->activo || ! $p->producto->activo) {
                        $this->error('Producto no disponible.');
                    }
                    $restante = $linea['cantidad'];
                    $consultaLotes = IngresoProductorItem::where('presentacion_id', $p->id)
                        ->where('cantidad_disponible', '>', 0)->whereDate('fecha_caducidad', '>=', today())
                        ->whereHas('ingresoProductor', function ($q) use ($linea) {
                            $q->where('estado', 'abierta')
                                ->whereHas('productor', fn ($productor) => $productor->where('activo', true));
                            if (! empty($linea['productor_id'])) {
                                $q->where('productor_id', $linea['productor_id']);
                            }
                        });
                    $lotes = $consultaLotes->with('ingresoProductor:id,productor_id')
                        ->orderBy('fecha_caducidad')->orderBy('id')->lockForUpdate()->get();
                    foreach ($lotes as $lote) {
                        if ($restante <= 0) {
                            break;
                        }
                        $cantidad = min($restante, $lote->cantidad_disponible);
                        Venta::create(['ticket_venta_id' => $ticket, 'ingreso_productor_item_id' => $lote->id, 'user_id' => $operador,
                            'cantidad_vendida' => $cantidad, 'precio_unitario_venta' => $lote->precio_venta_unitario, 'fecha_venta' => now()]);
                        $total = D::add($total, D::mul($cantidad, $lote->precio_venta_unitario));
                        $lote->update(['cantidad_disponible' => $lote->cantidad_disponible - $cantidad]);
                        $restante -= $cantidad;
                    }
                    if ($restante > 0) {
                        $this->error('Existencia vendible insuficiente para '.$p->nombre.'. Se revirtió todo el carrito.');
                    }
                    $this->stock($p, -$linea['cantidad']);
                }
                DB::table('tickets_venta')->where('id', $ticket)->update(['total' => $total, 'updated_at' => now()]);

                return $this->ticket($ticket);
            }, 3);
        } catch (QueryException $e) {
            // A concurrent retry may lose the unique-key race; replay only an identical request.
            $existing = DB::table('tickets_venta')->where('clave', $datos['clave'])->first();
            if ($existing) {
                return $this->repetir($existing, $hash, $operador);
            }
            throw $e;
        }
    }

    private function repetir(object $ticket, string $hash, int $operador): array
    {
        if ((int) $ticket->user_id !== $operador || $ticket->payload_hash !== $hash) {
            $this->error('La clave de venta ya fue utilizada con otros datos.');
        }

        return $this->ticket($ticket->id);
    }

    public function ticket(int $id): array
    {
        $ticket = DB::table('tickets_venta')->find($id);

        return ['ticket_id' => $id, 'total' => D::round($ticket->total, 2), 'metodo_pago' => $ticket->metodo_pago,
            'cliente' => $ticket->cliente, 'fecha' => \Illuminate\Support\Carbon::parse($ticket->created_at)->toIso8601String(),
            'ventas' => Venta::with(['lote.ingresoProductor.productor', 'lote.presentacion.producto'])
                ->where('ticket_venta_id', $id)->orderBy('id')->get()->map(fn ($v) => [
                'id' => $v->id, 'cantidad' => $v->cantidad_vendida, 'precio' => $v->precio_unitario_venta,
                'subtotal' => D::mul($v->cantidad_vendida, $v->precio_unitario_venta), 'lote_id' => $v->ingreso_productor_item_id,
                'producto' => trim(($v->lote->presentacion?->producto?->nombre ?? 'Producto').' · '.($v->lote->presentacion?->nombre ?? '')),
                'productor' => $v->lote->ingresoProductor->productor->nombre_completo, 'caducidad' => $v->lote->fecha_caducidad?->format('Y-m-d')])->all()];
    }

    public function movimiento(IngresoProductorItem $original, array $datos, int $operador): void
    {
        DB::transaction(function () use ($original, $datos, $operador) {
            $p = $this->presentaciones([$original->presentacion_id])->first();
            $ingreso = IngresoProductor::whereKey($original->ingreso_productor_id)->lockForUpdate()->firstOrFail();
            $lote = IngresoProductorItem::whereKey($original->id)->lockForUpdate()->firstOrFail();
            if ($ingreso->estado !== 'abierta') {
                $this->error('El ingreso está cerrado.');
            }
            $delta = $datos['tipo'] === 'salida' ? -(int) $datos['cantidad'] : (int) $datos['cantidad'];
            $saldo = $lote->cantidad_disponible + $delta;
            if ($saldo < 0) {
                $this->error('El movimiento dejaría el lote con saldo negativo.');
            }
            $cambios = ['cantidad_disponible' => $saldo];
            if ($delta > 0) {
                if (! $lote->fecha_caducidad || $lote->fecha_caducidad->lt(today())) {
                    $this->error('No se pueden agregar existencias a un lote vencido o sin caducidad.');
                }
                $cambios['cantidad_ingresada'] = $lote->cantidad_ingresada + $delta;
            }
            $lote->update($cambios);
            $this->stock($p, $delta);
            DB::table('movimientos_inventario')->insert(['lote_id' => $lote->id, 'user_id' => $operador, 'tipo' => $datos['tipo'],
                'cantidad' => $delta, 'motivo' => $datos['motivo'], 'created_at' => now(), 'updated_at' => now()]);
        }, 3);
    }

    public function regularizar(IngresoProductorItem $original, array $datos, int $operador): void
    {
        DB::transaction(function () use ($original, $datos, $operador) {
            $this->presentaciones([$original->presentacion_id]);
            $ingreso = IngresoProductor::whereKey($original->ingreso_productor_id)->lockForUpdate()->firstOrFail();
            $lote = IngresoProductorItem::whereKey($original->id)->lockForUpdate()->firstOrFail();
            if ($ingreso->estado !== 'abierta') {
                $this->error('El ingreso está cerrado.');
            }
            $cambios = [];
            foreach (['fecha_caducidad', 'precio_acopio_unitario'] as $campo) {
                if (isset($datos[$campo])) {
                    if ($lote->{$campo} !== null) {
                        $this->error('Solo se pueden completar datos faltantes; los datos conocidos se conservan.');
                    }
                    $cambios[$campo] = $datos[$campo];
                }
            }
            if (! $cambios) {
                $this->error('Indique al menos un dato faltante.');
            }
            if (isset($cambios['precio_acopio_unitario']) && D::compare($cambios['precio_acopio_unitario'], $lote->precio_venta_unitario) > 0) {
                $this->error('El precio de acopio no puede superar el precio de venta.');
            }
            $lote->update($cambios);
            DB::table('movimientos_inventario')->insert(['lote_id' => $lote->id, 'user_id' => $operador, 'tipo' => 'regularizacion', 'cantidad' => 0,
                'motivo' => $datos['motivo'].' | Datos antes: NULL. Datos completados: '.json_encode($cambios, JSON_UNESCAPED_UNICODE), 'created_at' => now(), 'updated_at' => now()]);
        }, 3);
    }
}
