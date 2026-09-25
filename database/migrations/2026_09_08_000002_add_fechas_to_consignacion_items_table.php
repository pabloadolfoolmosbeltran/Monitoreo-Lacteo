<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('consignacion_items', 'fecha_recepcion')) {
            Schema::table('consignacion_items', function (Blueprint $table) {
                $table->date('fecha_recepcion')->nullable()->after('precio_venta');
            });
        }

        if (! Schema::hasColumn('consignacion_items', 'fecha_caducidad')) {
            Schema::table('consignacion_items', function (Blueprint $table) {
                $table->date('fecha_caducidad')->nullable()->after('fecha_recepcion');
            });
        }
    }

    public function down(): void
    {
        Schema::table('consignacion_items', function (Blueprint $table) {
            if (Schema::hasColumn('consignacion_items', 'fecha_recepcion')) {
                $table->dropColumn('fecha_recepcion');
            }

            if (Schema::hasColumn('consignacion_items', 'fecha_caducidad')) {
                $table->dropColumn('fecha_caducidad');
            }
        });
    }
};
