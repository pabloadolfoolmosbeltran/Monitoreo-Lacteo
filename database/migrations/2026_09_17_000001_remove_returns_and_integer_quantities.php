<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $quantities = [
            'presentaciones' => ['stock', 'stock_minimo_alerta'],
            'ingresos_productores_items' => ['cantidad_ingresada', 'cantidad_disponible'],
            'ventas' => ['cantidad_vendida'],
            'movimientos_inventario' => ['cantidad'],
            'conciliaciones_stock_acopio' => ['stock_anterior', 'stock_lotes', 'diferencia'],
        ];

        $fractional = [];
        foreach ($quantities as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                $integerCast = DB::getDriverName() === 'mysql'
                    ? "CAST($column AS SIGNED)"
                    : "CAST($column AS INTEGER)";
                $ids = DB::table($table)
                    ->whereRaw("$column <> $integerCast")
                    ->orderBy('id')
                    ->limit(20)
                    ->pluck('id');

                if ($ids->isNotEmpty()) {
                    $fractional[] = "$table.$column: IDs ".$ids->implode(', ');
                }
            }
        }

        if ($fractional !== []) {
            throw new RuntimeException(
                "No se pueden convertir las cantidades a enteros. Corrija primero los registros fraccionarios:\n".
                implode("\n", $fractional)
            );
        }

        Schema::dropIfExists('devoluciones');

        if (Schema::hasTable('presentaciones')) {
            Schema::table('presentaciones', function (Blueprint $table) {
                $table->unsignedInteger('stock')->default(0)->change();
                $table->unsignedInteger('stock_minimo_alerta')->default(0)->change();
            });
        }
        if (Schema::hasTable('ingresos_productores_items')) {
            Schema::table('ingresos_productores_items', function (Blueprint $table) {
                $table->unsignedInteger('cantidad_ingresada')->change();
                $table->unsignedInteger('cantidad_disponible')->change();
            });
        }
        if (Schema::hasTable('ventas')) {
            Schema::table('ventas', fn (Blueprint $table) => $table->unsignedInteger('cantidad_vendida')->change());
        }
        if (Schema::hasTable('movimientos_inventario')) {
            Schema::table('movimientos_inventario', fn (Blueprint $table) => $table->integer('cantidad')->change());
        }
        if (Schema::hasTable('conciliaciones_stock_acopio')) {
            Schema::table('conciliaciones_stock_acopio', function (Blueprint $table) {
                $table->unsignedInteger('stock_anterior')->change();
                $table->unsignedInteger('stock_lotes')->change();
                $table->integer('diferencia')->change();
            });
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Esta migración elimina datos de devoluciones y requiere restaurar un respaldo para revertirse.');
    }
};
