<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {

            $table->boolean('motor_encendido')
                  ->default(false)
                  ->after('motor_automatico');

            $table->boolean('ventilador_encendido')
                  ->default(false)
                  ->after('ventilador_automatico');

            $table->boolean('sensor_activo')
                  ->default(true)
                  ->after('ventilador_encendido');

        });
    }

    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {

            $table->dropColumn([
                'motor_encendido',
                'ventilador_encendido',
                'sensor_activo'
            ]);

        });
    }
};
