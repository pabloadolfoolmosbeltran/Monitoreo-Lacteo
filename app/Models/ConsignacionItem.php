<?php

namespace App\Models;

use App\Support\Decimal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsignacionItem extends Model
{
    use HasFactory;

    protected $table = 'consignacion_items';

    protected $fillable = [
        'consignacion_id',
        'presentacion_id',
        'cantidad_recibida',
