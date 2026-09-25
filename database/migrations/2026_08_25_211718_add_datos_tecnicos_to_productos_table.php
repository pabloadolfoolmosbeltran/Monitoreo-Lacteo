<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->string('imagen_referencial')->nullable(); // Foto del queso o yogurt a granel
            $table->decimal('cuajo_por_litro', 8, 2)->nullable(); // Ej: 0.2 gramos por litro
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['imagen_referencial', 'cuajo_por_litro']);
        });
    }
};
