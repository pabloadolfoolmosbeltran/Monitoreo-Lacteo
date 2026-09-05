<?php

namespace App\Models;

use App\Support\Decimal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
        'consignacion_item_id',
        'user_id',
        'cantidad_vendida',
        'precio_unitario_venta',
        'fecha_venta',
        'observaciones',
    ];

    protected $casts = [
        'cantidad_vendida' => 'decimal:3',
        'precio_unitario_venta' => 'decimal:2',
        'fecha_venta' => 'datetime',
    ];

    protected $appends = [
        'total_venta',
        'margen_obtenido',
    ];

    public function consignacionItem(): BelongsTo
    {
        return $this->belongsTo(ConsignacionItem::class);
    }

    public function vendedor(): BelongsTo
    {
