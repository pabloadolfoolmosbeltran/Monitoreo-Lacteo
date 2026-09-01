<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'activo')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('activo')->default(true)->index()->after('rol');
            });
        }

        if (! Schema::hasColumn('presentaciones', 'activo')) {
            Schema::table('presentaciones', function (Blueprint $table) {
                $table->boolean('activo')->default(true)->index()->after('imagen_comercial');
            });
        }

        // productos ya posee activo; se garantiza el índice para los listados.
        if (Schema::hasColumn('productos', 'activo')) {
            try {
                Schema::table('productos', function (Blueprint $table) {
                    $table->index('activo', 'productos_activo_index');
                });
            } catch (\Throwable $e) {
                // El índice ya existe: no es necesario modificarlo.
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('presentaciones', 'activo')) {
            Schema::table('presentaciones', fn (Blueprint $table) => $table->dropColumn('activo'));
        }
        if (Schema::hasColumn('users', 'activo')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('activo'));
        }
    }
};