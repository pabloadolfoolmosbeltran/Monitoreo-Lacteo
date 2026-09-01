<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presentaciones', function (Blueprint $table) {

            $table->string('envase', 100)
                ->nullable()
                ->after('nombre');

            $table->string('sabor', 100)
                ->nullable()
                ->after('envase');

            $table->decimal('contenido', 10, 2)
                ->nullable()
                ->after('sabor');

            $table->string('unidad', 10)
                ->nullable()
                ->after('contenido');

            $table->boolean('con_fruta')
                ->default(false)
                ->after('imagen_comercial');

            $table->boolean('activo')
                ->default(true)
                ->after('con_fruta');
        });
    }

    public function down(): void
    {
        Schema::table('presentaciones', function (Blueprint $table) {
            $table->dropColumn([
                'envase',
                'sabor',
                'contenido',
                'unidad',
                'con_fruta',
                'activo'
            ]);
        });
    }
};