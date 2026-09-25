<?php

namespace App\Models;

use App\Support\Decimal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IngresoProductor extends Model
{
    use HasFactory;

    protected $table = 'ingresos_productores';

    // APUNTE:
    // Un ingreso agrupa productos entregados por un productor. Sus lotes
    // se venden o ajustan desde las relaciones de este modelo.

    protected $fillable = [
        'user_id',
        'productor_id',
        'fecha_ingreso',
        'observaciones',
        'estado',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
    ];

    public function productor(): BelongsTo
    {
        return $this->belongsTo(Productor::class, 'productor_id')->withTrashed();
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(IngresoProductorItem::class, 'ingreso_productor_id');
    }

    public function getMontoProductorAttribute(): string
    {
        return $this->sumarItems('monto_productor');
    }

    public function getMargenEncargadoAttribute(): string
    {
        return $this->sumarItems('margen_encargado');
    }

    private function sumarItems(string $attribute): string
    {
        $items = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->with('ventas')->get();

        return $items->reduce(
            fn (string $total, IngresoProductorItem $item): string => Decimal::add($total, $item->{$attribute}, 2),
            '0.00'
        );
    }
}
