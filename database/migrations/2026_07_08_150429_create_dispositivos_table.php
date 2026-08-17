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
        Schema::create('dispositivos', function (Blueprint $table) {

            $table->id();

            // Nombre del dispositivo
            $table->string('nombre');

            // Dirección MAC del ESP32
            $table->string('mac_address')->unique();

            // Ubicación física
            $table->string('ubicacion')->nullable();

            // Estado
            $table->enum('estado',[
                'Activo',
                'Inactivo',
                'Mantenimiento'
            ])->default('Activo');

            // Última conexión del ESP32
            $table->timestamp('ultima_conexion')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Revertir migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispositivos');
    }
};
