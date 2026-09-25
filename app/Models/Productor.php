<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Productor extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'productores';
    protected $fillable = ['nombres','primer_apellido','segundo_apellido','telefono','direccion','nombre_unidad_productiva','activo'];
    protected $casts = ['activo'=>'boolean'];
    protected $appends = ['nombre_completo'];
    public function scopeActivos(Builder $query): Builder { return $query->where('activo', true); }
    public function ingresos(): HasMany { return $this->hasMany(IngresoProductor::class, 'productor_id'); }
    public function getNombreCompletoAttribute(): string { return trim(implode(' ', array_filter([$this->nombres,$this->primer_apellido,$this->segundo_apellido], fn($v)=>$v!==null && $v!==''))); }
}
