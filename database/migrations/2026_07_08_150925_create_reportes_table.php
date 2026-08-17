<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes', function (Blueprint $table) {

            $table->id();

            $table->foreignId('produccion_id')
                  ->constrained('producciones')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->string('nombre_archivo');

            $table->string('ruta_archivo');

            $table->timestamp('fecha_generacion')->useCurrent();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
