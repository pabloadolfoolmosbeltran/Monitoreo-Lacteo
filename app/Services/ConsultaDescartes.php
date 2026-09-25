<?php

namespace App\Services;

use App\Models\DescarteProducto;
use Illuminate\Database\Eloquent\Builder;

class ConsultaDescartes
{
    public function query(array $filtros): Builder
    {
        return DescarteProducto::with(['lote.presentacion.producto', 'lote.ingresoProductor.productor', 'creador', 'procesador'])
            ->when($filtros['productor_id'] ?? null, fn ($q, $id) => $q->whereHas('lote.ingresoProductor', fn ($i) => $i->where('productor_id', $id)))
            ->when($filtros['desde'] ?? null, fn ($q, $fecha) => $q->whereDate('created_at', '>=', $fecha))
            ->when($filtros['hasta'] ?? null, fn ($q, $fecha) => $q->whereDate('created_at', '<=', $fecha))
            ->when($filtros['estado'] ?? null, fn ($q, $estado) => $q->where('estado', $estado))
            ->when($filtros['tipo_motivo'] ?? null, fn ($q, $tipo) => $q->where('tipo_motivo', $tipo))
            ->latest('id');
    }
}
