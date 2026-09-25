<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    protected $fillable = ['lote_id', 'user_id', 'tipo', 'cantidad', 'motivo'];

    protected $casts = ['cantidad' => 'integer'];

    public function lote(): BelongsTo
    {
        return $this->belongsTo(IngresoProductorItem::class, 'lote_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
