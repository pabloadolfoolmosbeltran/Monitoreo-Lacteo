<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DescarteProducto extends Model
{
    protected $table = 'descartes_productos';

    protected $fillable = ['lote_id', 'creado_por', 'actualizado_por', 'cantidad', 'fecha_caducidad', 'costo_unitario', 'perdida_total', 'tipo_motivo', 'notas', 'estado', 'procesado_por', 'procesado_en'];

    protected $casts = ['cantidad' => 'integer', 'fecha_caducidad' => 'date', 'costo_unitario' => 'decimal:2', 'perdida_total' => 'decimal:2', 'procesado_en' => 'datetime'];

    public function lote(): BelongsTo
    {
        return $this->belongsTo(IngresoProductorItem::class, 'lote_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }

    public function procesador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'procesado_por');
    }
}
