<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presentaciones', function (Blueprint $table) {

            if (! Schema::hasColumn('presentaciones', 'envase')) {
                $table->string('envase', 100)
                    ->nullable()
                    ->after('nombre');
            }

            if (! Schema::hasColumn('presentaciones', 'sabor')) {
                $table->string('sabor', 100)
                    ->nullable()
                    ->after('envase');
            }

            if (! Schema::hasColumn('presentaciones', 'contenido')) {
                $table->decimal('contenido', 10, 2)
                    ->nullable()
                    ->after('sabor');
            }

            if (! Schema::hasColumn('presentaciones', 'unidad')) {
                $table->string('unidad', 10)
                    ->nullable()
                    ->after('contenido');
            }

            if (! Schema::hasColumn('presentaciones', 'con_fruta')) {
                $table->boolean('con_fruta')
                    ->default(false)
                    ->after('imagen_comercial');
            }

            if (! Schema::hasColumn('presentaciones', 'activo')) {
                $table->boolean('activo')
                    ->default(true)
                    ->after('con_fruta');
            }
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
            ]);
        });
    }
};
