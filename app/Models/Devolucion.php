<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Devolucion extends Model
{
    use HasFactory;

    protected $table = 'devoluciones';

    protected $fillable = [
        'consignacion_item_id',
        'user_id',
        'cantidad_devuelta',
        'precio_unitario_venta',
        'fecha_devolucion',
        'tipo',
        'observaciones',
    ];

    protected $casts = [
        'cantidad_devuelta' => 'decimal:3',
        'precio_unitario_venta' => 'decimal:2',
        'fecha_devolucion' => 'datetime',
    ];

    public function consignacionItem(): BelongsTo
    {
        return $this->belongsTo(ConsignacionItem::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
