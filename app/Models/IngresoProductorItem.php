<?php

namespace App\Models;

use App\Support\Decimal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IngresoProductorItem extends Model
{
    use HasFactory;

    protected $table = 'ingresos_productores_items';

    // APUNTE:
    // El ítem guarda cantidad, precios y fechas de una presentación dentro de
    // un ingreso de productor. Sus atributos calculados resumen ventas y margen.

    protected $fillable = [
        'ingreso_productor_id',
        'presentacion_id',
        'cantidad_ingresada',
        'cantidad_disponible',
        'precio_acopio_unitario',
        'precio_venta_unitario',
        'fecha_recepcion',
        'fecha_caducidad',
    ];

    protected $casts = [
        'cantidad_ingresada' => 'integer',
        'cantidad_disponible' => 'integer',
        'precio_acopio_unitario' => 'decimal:2',
        'precio_venta_unitario' => 'decimal:2',
        'fecha_recepcion' => 'date',
        'fecha_caducidad' => 'date',
    ];

    protected $appends = [
        'margen_unitario',
        'dias_para_caducar',
        'estado_caducidad',
    ];

    public function ingresoProductor(): BelongsTo
    {
        return $this->belongsTo(IngresoProductor::class, 'ingreso_productor_id');
    }

    public function presentacion(): BelongsTo
    {
        return $this->belongsTo(Presentacion::class, 'presentacion_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'ingreso_productor_item_id');
    }

    public function ajustesInventario(): HasMany
    {
        return $this->hasMany(AjusteInventario::class, 'lote_id');
    }

    public function descartesProductos(): HasMany
    {
        return $this->hasMany(DescarteProducto::class, 'lote_id');
    }

    public function getMargenUnitarioAttribute(): string
    {
        return Decimal::sub($this->precio_venta_unitario, $this->precio_acopio_unitario, 2);
    }

    public function getCantidadVendidaAttribute(): int
    {
        return $this->ventasParaCalculo()->sum('cantidad_vendida');
    }

    public function getCantidadVendidaNetaAttribute(): int
    {
        return $this->cantidad_vendida;
    }

    public function getMontoProductorAttribute(): string
    {
        return $this->monto_productor_neta;
    }

    public function getMontoProductorNetaAttribute(): string
    {
        return Decimal::mul($this->cantidad_vendida_neta, $this->precio_acopio_unitario, 2);
    }

    public function getMargenEncargadoAttribute(): string
    {
        return $this->margen_encargado_neto;
    }

    public function getMargenEncargadoNetoAttribute(): string
    {
        return Decimal::mul(
            $this->cantidad_vendida_neta,
            Decimal::sub($this->precio_venta_unitario, $this->precio_acopio_unitario, 2),
            2
        );
    }

    public function getDiasParaCaducarAttribute(): ?int
    {
        if (! $this->fecha_caducidad) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays(
            $this->fecha_caducidad->copy()->startOfDay(),
            false
        );
    }

    public function getEstadoCaducidadAttribute(): string
    {
        $dias = $this->dias_para_caducar;

        if ($dias === null) {
            return 'sin_fecha';
        }

        if ($dias < 0) {
            return 'caducado';
        }

        if ($dias <= 3) {
            return 'urgente';
        }

        if ($dias <= 7) {
            return 'proximo';
        }

        return 'vigente';
    }

    private function ventasParaCalculo()
    {
        return $this->relationLoaded('ventas') ? $this->ventas : $this->ventas()->get();
    }
}
