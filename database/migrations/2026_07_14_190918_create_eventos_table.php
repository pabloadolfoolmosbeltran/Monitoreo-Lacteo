<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {

            $table->id();

            $table->foreignId('produccion_id')
                ->nullable()
                ->constrained('producciones')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->string('tipo',100);

            $table->text('descripcion');

            $table->timestamp('fecha_hora')->useCurrent();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
