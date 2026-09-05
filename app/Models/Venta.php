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
