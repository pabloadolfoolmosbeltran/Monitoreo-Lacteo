<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lectura extends Model
{
    use HasFactory;

    protected $table = 'lecturas';

    protected $fillable = [
        'produccion_id',
        'sensor_id',
        'temperatura',
        'fecha_hora'
    ];

    protected $casts = [
        'temperatura' => 'decimal:2',
        'fecha_hora' => 'datetime'
    ];

    public function produccion(): BelongsTo
    {
        return $this->belongsTo(Produccion::class);
    }

    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class);
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class);
    }
}
