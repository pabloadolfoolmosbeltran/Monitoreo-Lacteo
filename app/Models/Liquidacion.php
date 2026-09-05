<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Liquidacion extends Model
{
    use HasFactory;

    protected $table = 'liquidaciones';

    protected $fillable = [
        'consignacion_id',
        'user_id',
        'fecha_liquidacion',
        'monto_liquidado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_liquidacion' => 'date',
        'monto_liquidado' => 'decimal:2',
    ];

    public function consignacion(): BelongsTo
    {
        return $this->belongsTo(Consignacion::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
