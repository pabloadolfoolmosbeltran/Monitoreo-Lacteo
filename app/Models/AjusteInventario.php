<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AjusteInventario extends Model
{
    protected $table = 'ajustes_inventario';

    protected $fillable = [
        'lote_id', 'creado_por', 'actualizado_por', 'cantidad_anterior', 'cantidad_nueva',
        'diferencia', 'tipo_motivo', 'motivo', 'estado', 'anulado_por', 'anulado_en',
        'motivo_anulacion',
    ];

    protected $casts = [
        'cantidad_anterior' => 'integer',
        'cantidad_nueva' => 'integer',
        'diferencia' => 'integer',
        'anulado_en' => 'datetime',
    ];

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

    public function anulador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }
}
