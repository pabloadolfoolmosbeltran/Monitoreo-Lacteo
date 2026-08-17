<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispositivo extends Model
{
    use HasFactory;

    protected $table = 'dispositivos';

    protected $fillable = [
        'nombre',
        'mac_address',
        'ubicacion',
        'estado',
        'ultima_conexion'
    ];

    protected $casts = [
        'ultima_conexion' => 'datetime',
    ];

    public function sensores(): HasMany
    {
        return $this->hasMany(Sensor::class);
    }

    public function actuadores(): HasMany
    {
        return $this->hasMany(Actuador::class);
    }

    public function producciones(): HasMany
    {
        return $this->hasMany(Produccion::class);
    }
}
