<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'telefono',
        'direccion',
        'rol',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function producciones(): HasMany
    {
        return $this->hasMany(Produccion::class);
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class);
    }

    public function ingresosRegistrados(): HasMany
    {
        return $this->hasMany(IngresoProductor::class, 'user_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    public function ajustesInventarioCreados(): HasMany
    {
        return $this->hasMany(AjusteInventario::class, 'creado_por');
    }

    public function descartesProductosCreados(): HasMany
    {
        return $this->hasMany(DescarteProducto::class, 'creado_por');
    }
}
