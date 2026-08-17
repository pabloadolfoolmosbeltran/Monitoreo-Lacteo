<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar la migración.
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {

            $table->id();

            // Nombre del producto
            $table->string('nombre');

            // Descripción
            $table->text('descripcion')->nullable();

            // Temperatura mínima
            $table->decimal('temperatura_minima', 5, 2);

            // Temperatura máxima
            $table->decimal('temperatura_maxima', 5, 2);

            // ¿Está activo?
            $table->boolean('activo')->default(true);

            $table->timestamps();

        });
    }

    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
