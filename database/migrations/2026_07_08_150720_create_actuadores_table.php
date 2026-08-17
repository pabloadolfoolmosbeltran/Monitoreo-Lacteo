<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar migración.
     */
    public function up(): void
    {
        Schema::create('actuadores', function (Blueprint $table) {

            $table->id();

            // ESP32 al que pertenece
            $table->foreignId('dispositivo_id')
                  ->constrained('dispositivos')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            // Nombre del actuador
            $table->string('nombre');

            // Tipo
            $table->enum('tipo',[
                'Motor',
                'Ventilador'
            ]);

            // Estado actual
            $table->boolean('estado')->default(false);

            // Modo de funcionamiento
            $table->enum('modo',[
                'Manual',
                'Automatico'
            ])->default('Automatico');

            $table->timestamps();

        });
    }

    /**
     * Revertir migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('actuadores');
    }
};
