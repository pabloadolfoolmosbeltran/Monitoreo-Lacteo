<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Presentacion extends Model
{
    use HasFactory;

    protected $table = 'presentaciones';

    protected $fillable = [
        'producto_id',
        'nombre',
        'envase',
        'sabor',
        'contenido',
        'unidad',
        'precio',
        'stock',
        'stock_minimo_alerta',
        'imagen_comercial',
        'con_fruta',
        'activo',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'contenido' => 'decimal:2',
        'stock' => 'integer',
        'stock_minimo_alerta' => 'integer',
        'con_fruta' => 'boolean',
        'activo' => 'boolean',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(IngresoProductorItem::class, 'presentacion_id');
    }
}
