<?php

namespace App\Models;

use App\Support\Decimal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Consignacion extends Model
{
    use HasFactory;

    protected $table = 'consignaciones';

    protected $fillable = [
        'user_id',
        'fecha_entrada',
