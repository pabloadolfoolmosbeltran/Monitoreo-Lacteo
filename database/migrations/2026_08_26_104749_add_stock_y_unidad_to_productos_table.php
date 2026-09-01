<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // 1. Añadimos las dos columnas nuevas
            $table->string('unidad_cuajo', 10)->default('ml')->after('cuajo_por_litro');
            $table->decimal('stock_cuajo', 10, 2)->default(0)->after('unidad_cuajo');

            // 2. Modificamos las existentes para que sean opcionales (nullable)
            $table->string('tipo_cuajo', 100)->nullable()->change();
            $table->decimal('cuajo_por_litro', 8, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // Si revertimos la migración, eliminamos las columnas nuevas
            $table->dropColumn(['unidad_cuajo', 'stock_cuajo']);
            
            // Nota: Revertir el nullable()->change() a NOT NULL puede causar conflictos 
            // si ya hay datos nulos, por lo que usualmente se omite en el método down, 
            // o se debe asegurar que no haya nulos antes de revertir.
        });
    }
};