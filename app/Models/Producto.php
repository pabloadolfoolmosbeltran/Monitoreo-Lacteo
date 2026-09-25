<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'temperatura_minima',
        'temperatura_maxima',
        'temperatura_pasteurizacion',
        'tipo_cuajo',
        'cuajo_por_litro',
        'unidad_cuajo',
        'imagen_referencial',
        'activo',
    ];

    protected $casts = [
        'temperatura_minima' => 'float',
        'temperatura_maxima' => 'float',
        'temperatura_pasteurizacion' => 'float',
        'cuajo_por_litro' => 'float',
        'activo' => 'boolean',
    ];

    public function producciones(): HasMany
    {
        return $this->hasMany(Produccion::class);
    }

    public function presentaciones(): HasMany
    {
        return $this->hasMany(Presentacion::class);
    }
}
