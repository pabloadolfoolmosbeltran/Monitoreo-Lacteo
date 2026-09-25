<?php

namespace App\Services;

use App\Models\IngresoProductorItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class InventarioConsulta
{
    public function __construct(private BusquedaPresentacion $busqueda) {}

    public function lotes(array $filtros): LengthAwarePaginator
    {
        $query = IngresoProductorItem::query()->with(['presentacion.producto', 'ingresoProductor.productor']);
        $this->aplicarFiltrosLotes($query, $filtros);

        return $query->orderByDesc('id')->paginate(30)->withQueryString();
    }

    public function aplicarFiltrosLotes(Builder $query, array $filtros): Builder
    {
        if ($texto = trim((string) ($filtros['producto'] ?? ''))) {
            $query->whereHas('presentacion', fn (Builder $p) => $this->busqueda->aplicar($p, $texto));
        }
        if ($texto = trim((string) ($filtros['productor'] ?? ''))) {
            $query->whereHas('ingresoProductor.productor', fn (Builder $p) => $this->filtrarProductor($p, $texto));
        }
        $this->aplicarFecha($query, 'fecha_recepcion', $filtros['ingreso_desde'] ?? null, $filtros['ingreso_hasta'] ?? null);
        $this->aplicarFecha($query, 'fecha_caducidad', $filtros['vence_desde'] ?? null, $filtros['vence_hasta'] ?? null);
        return $query;
    }

    public function filtrarProductor(Builder $query, string $texto): Builder
    {
        $like = '%'.trim($texto).'%';

        return $query->where(fn (Builder $q) => $q->where('nombres', 'like', $like)->orWhere('primer_apellido', 'like', $like)
            ->orWhere('segundo_apellido', 'like', $like)->orWhere('nombre_unidad_productiva', 'like', $like));
    }

    public function grupos(array $filtros): LengthAwarePaginator
    {
        return $this->consultaGeneralAgrupada($filtros)
            ->orderBy('productos.nombre')->orderBy('presentaciones.nombre')->orderBy('productores.nombres')
            ->paginate(20)->withQueryString();
    }

    public function totales(array $filtros): object
    {
        $grupos = $this->consultaGeneralAgrupada($filtros)->toBase();

        return DB::query()->fromSub($grupos, 'stock_agrupado')
            ->selectRaw('COALESCE(SUM(vendible),0) AS vendible, COALESCE(SUM(proximo),0) AS proximo, COALESCE(SUM(no_vendible),0) AS no_vendible, COALESCE(SUM(fisico),0) AS fisico')
            ->first();
    }

    public function detalleGrupo(array $filtros): array
    {
        if (empty($filtros['detalle_presentacion']) || empty($filtros['detalle_productor'])) {
            return [];
        }
        $query = IngresoProductorItem::query()->with(['presentacion.producto', 'ingresoProductor.productor'])
            ->where('presentacion_id', $filtros['detalle_presentacion'])
            ->whereHas('ingresoProductor', fn (Builder $q) => $q->where('productor_id', $filtros['detalle_productor']))
            ->where('cantidad_disponible', '>', 0);
        $this->aplicarFecha($query, 'fecha_caducidad', $filtros['vence_desde'] ?? null, $filtros['vence_hasta'] ?? null);

        return $query->orderBy('fecha_caducidad')->limit(100)->get()->all();
    }

    private function consultaGeneralAgrupada(array $filtros): Builder
    {
        $hoy = today()->toDateString();
        $proximo = today()->addDays(7)->toDateString();
        $query = IngresoProductorItem::query()
            ->join('ingresos_productores', 'ingresos_productores.id', '=', 'ingresos_productores_items.ingreso_productor_id')
            ->join('productores', 'productores.id', '=', 'ingresos_productores.productor_id')
            ->join('presentaciones', 'presentaciones.id', '=', 'ingresos_productores_items.presentacion_id')
            ->join('productos', 'productos.id', '=', 'presentaciones.producto_id')
            ->where('ingresos_productores_items.cantidad_disponible', '>', 0)
            ->selectRaw('presentaciones.id AS presentacion_id, productores.id AS productor_id, productos.nombre AS producto, presentaciones.nombre AS presentacion, presentaciones.sabor, presentaciones.contenido, presentaciones.unidad, productores.nombres, productores.primer_apellido, productores.nombre_unidad_productiva')
            ->selectRaw("SUM(CASE WHEN ingresos_productores.estado='abierta' AND fecha_caducidad IS NOT NULL AND fecha_caducidad >= ? THEN cantidad_disponible ELSE 0 END) AS vendible", [$hoy])
            ->selectRaw("SUM(CASE WHEN ingresos_productores.estado='abierta' AND fecha_caducidad BETWEEN ? AND ? THEN cantidad_disponible ELSE 0 END) AS proximo", [$hoy, $proximo])
            ->selectRaw("SUM(CASE WHEN NOT (ingresos_productores.estado='abierta' AND fecha_caducidad IS NOT NULL AND fecha_caducidad >= ?) THEN cantidad_disponible ELSE 0 END) AS no_vendible", [$hoy])
            ->selectRaw('SUM(cantidad_disponible) AS fisico, COUNT(*) AS lotes')
            ->groupBy('presentaciones.id', 'productores.id', 'productos.nombre', 'presentaciones.nombre', 'presentaciones.sabor', 'presentaciones.contenido', 'presentaciones.unidad', 'productores.nombres', 'productores.primer_apellido', 'productores.nombre_unidad_productiva');
        if ($texto = trim((string) ($filtros['producto'] ?? ''))) {
            $query->whereHas('presentacion', fn (Builder $p) => $this->busqueda->aplicar($p, $texto));
        }
        if ($texto = trim((string) ($filtros['productor'] ?? ''))) {
            $like = '%'.$texto.'%';
            $query->where(fn (Builder $q) => $q->where('productores.nombres', 'like', $like)->orWhere('productores.primer_apellido', 'like', $like)->orWhere('productores.nombre_unidad_productiva', 'like', $like));
        }
        if (! empty($filtros['vence_desde'])) {
            $query->whereDate('ingresos_productores_items.fecha_caducidad', '>=', $filtros['vence_desde']);
        }
        if (! empty($filtros['vence_hasta'])) {
            $query->whereDate('ingresos_productores_items.fecha_caducidad', '<=', $filtros['vence_hasta']);
        }

        return $query;
    }

    private function aplicarFecha(Builder $query, string $campo, ?string $desde, ?string $hasta): void
    {
        if ($desde) {
            $query->whereDate($campo, '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate($campo, '<=', $hasta);
        }
    }
}
