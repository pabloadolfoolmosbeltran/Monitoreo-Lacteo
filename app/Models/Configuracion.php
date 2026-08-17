<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';

    protected $fillable = [
        'intervalo_lectura',
        'temperatura_ventilador',
        'motor_automatico',
        'ventilador_automatico',
        'motor_encendido',
        'ventilador_encendido',
        'sensor_activo',
        'nombre_sistema'
    ];

    protected $casts = [
        'intervalo_lectura' => 'integer',
        'temperatura_ventilador' => 'decimal:2',
        'motor_automatico' => 'boolean',
        'ventilador_automatico' => 'boolean',
        'motor_encendido' => 'boolean',
        'ventilador_encendido' => 'boolean',
        'sensor_activo' => 'boolean',
    ];

    public static function sistema()
    {
        return self::first();
    }
}
