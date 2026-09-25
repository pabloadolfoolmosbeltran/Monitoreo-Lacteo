<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Native renames preserve primary IDs and update referencing foreign keys/checks.
        Schema::create('productores', function (Blueprint $t) {
            $t->id();
            $t->foreignId('legacy_user_id')->nullable()->unique()->constrained('users')->restrictOnDelete();
            $t->string('nombres');
            $t->string('primer_apellido');
            $t->string('segundo_apellido')->nullable();
            $t->string('telefono', 20)->nullable();
            $t->text('direccion')->nullable();
            $t->string('nombre_unidad_productiva')->nullable();
            $t->boolean('activo')->default(true);
            $t->timestamps();
            $t->softDeletes();
        });
        $legacyIds = DB::table('consignaciones')->distinct()->pluck('user_id');
        foreach (DB::table('users')->whereIn('id', $legacyIds)->orWhere('rol', 'Productor')->get() as $u) {
            DB::table('productores')->insert(['legacy_user_id' => $u->id, 'nombres' => $u->name, 'primer_apellido' => '', 'telefono' => $u->telefono, 'direccion' => $u->direccion, 'nombre_unidad_productiva' => $u->nombre_unidad_productiva, 'activo' => $u->activo ?? true, 'created_at' => $u->created_at, 'updated_at' => $u->updated_at]);
        }
        Schema::rename('consignaciones', 'ingresos_productores');
        Schema::rename('consignacion_items', 'ingresos_productores_items');
        Schema::table('ingresos_productores', function (Blueprint $t) {
            $t->renameColumn('fecha_entrada', 'fecha_ingreso');
            $t->unsignedBigInteger('user_id')->nullable()->change();
            $t->foreignId('productor_id')->nullable()->constrained('productores')->restrictOnDelete();
        });
        foreach (DB::table('productores')->whereNotNull('legacy_user_id')->get() as $p) {
            DB::table('ingresos_productores')->where('user_id', $p->legacy_user_id)->update(['productor_id' => $p->id, 'user_id' => null]);
        }
        Schema::table('ingresos_productores', fn (Blueprint $t) => $t->unsignedBigInteger('productor_id')->nullable(false)->change());
        foreach (['consignacion_id' => 'ingreso_productor_id', 'cantidad_recibida' => 'cantidad_ingresada', 'precio_productor' => 'precio_acopio_unitario', 'precio_venta' => 'precio_venta_unitario'] as $old => $new) {
            Schema::table('ingresos_productores_items', fn (Blueprint $t) => $t->renameColumn($old, $new));
        }
        Schema::table('ingresos_productores_items', fn (Blueprint $t) => $t->decimal('precio_acopio_unitario', 10, 2)->nullable()->change());
        Schema::table('ventas', fn (Blueprint $t) => $t->renameColumn('consignacion_item_id', 'ingreso_productor_item_id'));
        Schema::table('liquidaciones', fn (Blueprint $t) => $t->renameColumn('consignacion_id', 'ingreso_productor_id'));
        Schema::table('presentaciones', function (Blueprint $t) {
            $t->unsignedInteger('stock')->default(0)->change();
            $t->unsignedInteger('stock_minimo_alerta')->default(0);
        });
        // Archive the previous global balance before reconciling it to physical lots.
        Schema::create('conciliaciones_stock_acopio', function (Blueprint $t) {
            $t->id();
            $t->foreignId('presentacion_id')->constrained('presentaciones')->restrictOnDelete();
            $t->unsignedInteger('stock_anterior');
            $t->unsignedInteger('stock_lotes');
            $t->integer('diferencia');
            $t->timestamp('created_at');
        });
        DB::table('presentaciones')->orderBy('id')->each(function ($p) {
            $stock = (int) DB::table('ingresos_productores_items')->where('presentacion_id', $p->id)->sum('cantidad_disponible');
            DB::table('conciliaciones_stock_acopio')->insert(['presentacion_id' => $p->id, 'stock_anterior' => $p->stock, 'stock_lotes' => $stock, 'diferencia' => $stock - (int) $p->stock, 'created_at' => now()]);
            DB::table('presentaciones')->where('id', $p->id)->update(['stock' => $stock]);
        });
        Schema::create('tickets_venta', function (Blueprint $t) {
            $t->id();
            $t->uuid('clave')->unique();
            $t->string('payload_hash', 64);
            $t->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $t->enum('metodo_pago', ['efectivo', 'qr', 'credito']);
            $t->string('cliente')->nullable();
            $t->decimal('total', 12, 2);
            $t->timestamps();
        });
        Schema::table('ventas', fn (Blueprint $t) => $t->foreignId('ticket_venta_id')->nullable()->constrained('tickets_venta')->restrictOnDelete());
        Schema::create('movimientos_inventario', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lote_id')->constrained('ingresos_productores_items')->restrictOnDelete();
            $t->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $t->string('tipo', 40);
            $t->integer('cantidad');
            $t->text('motivo');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Migración de acopio irreversible: restaurar el respaldo completo previo. No eliminar productores, tickets ni historial de auditoría.');
    }
};
