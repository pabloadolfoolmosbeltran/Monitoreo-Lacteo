<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ingresos_productores') && Schema::hasColumn('ingresos_productores', 'estado')) {
            Schema::table('ingresos_productores', function (Blueprint $table) {
                $table->string('estado', 20)->default('abierta')->change();
            });

            DB::table('ingresos_productores')
                ->whereIn('estado', ['liquidada', 'devuelta'])
                ->orderBy('id')
                ->each(function (object $ingreso): void {
                    $stock = DB::table('ingresos_productores_items')
                        ->where('ingreso_productor_id', $ingreso->id)
                        ->sum('cantidad_disponible');

                    DB::table('ingresos_productores')
                        ->where('id', $ingreso->id)
                        ->update(['estado' => $stock > 0 ? 'abierta' : 'cerrada']);
                });
        }

        Schema::dropIfExists('liquidaciones');
    }

    public function down(): void
    {
        throw new RuntimeException(
            'La eliminación de liquidaciones y sus datos es irreversible. Restaure un respaldo para recuperarlos.'
        );
    }
};
