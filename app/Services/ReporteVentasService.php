<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReporteVentasService
{
    public function productosMasVendidos(array $filtros): Collection
    {
        $query = DB::table('ventas')
            ->join('ingresos_productores_items as lotes', 'lotes.id', '=', 'ventas.ingreso_productor_item_id')
            ->join('ingresos_productores as ingresos', 'ingresos.id', '=', 'lotes.ingreso_productor_id')
            ->join('presentaciones', 'presentaciones.id', '=', 'lotes.presentacion_id')
            ->join('productos', 'productos.id', '=', 'presentaciones.producto_id');

        if (! empty($filtros['desde'])) {
            $query->whereDate('ventas.fecha_venta', '>=', $filtros['desde']);
        }
        if (! empty($filtros['hasta'])) {
            $query->whereDate('ventas.fecha_venta', '<=', $filtros['hasta']);
        }
        if (! empty($filtros['productor_id'])) {
            $query->where('ingresos.productor_id', $filtros['productor_id']);
        }

        $filas = $query->groupBy('productos.id', 'productos.nombre')
            ->selectRaw('productos.id as producto_id, productos.nombre as producto')
            ->selectRaw('SUM(ventas.cantidad_vendida) as unidades')
            ->selectRaw('SUM(ventas.cantidad_vendida * ventas.precio_unitario_venta) as monto')
            ->orderByDesc('unidades')->orderBy('productos.nombre')->limit(15)->get();
        $total = $filas->reduce(fn (string $suma, object $fila): string => bcadd($suma, (string) $fila->monto, 2), '0.00');

        return $filas->map(fn (object $fila): array => [
            'producto_id' => (int) $fila->producto_id,
            'producto' => $fila->producto,
            'unidades' => (int) $fila->unidades,
            'monto' => number_format((float) $fila->monto, 2, '.', ''),
            'porcentaje' => bccomp($total, '0', 2) === 0 ? '0.00' : bcdiv(bcmul((string) $fila->monto, '100', 4), $total, 2),
        ]);
    }
}
