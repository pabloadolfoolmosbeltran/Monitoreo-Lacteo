<?php

namespace Tests\Feature;

use App\Models\Presentacion;
use App\Models\Venta;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class AcopioMigrationTest extends TestCase
{
    public function test_fresh_schema_has_no_returns_and_uses_integer_unit_counts(): void
    {
        $this->artisan('migrate:fresh')->assertExitCode(0);

        $this->assertFalse(Schema::hasTable('devoluciones'));

        foreach ([
            ['presentaciones', 'stock'],
            ['presentaciones', 'stock_minimo_alerta'],
            ['ingresos_productores_items', 'cantidad_ingresada'],
            ['ingresos_productores_items', 'cantidad_disponible'],
            ['ventas', 'cantidad_vendida'],
            ['movimientos_inventario', 'cantidad'],
            ['conciliaciones_stock_acopio', 'stock_anterior'],
            ['conciliaciones_stock_acopio', 'stock_lotes'],
            ['conciliaciones_stock_acopio', 'diferencia'],
        ] as [$table, $column]) {
            $this->assertSame('integer', Schema::getColumnType($table, $column), "$table.$column debe ser integer");
        }
    }

    public function test_integer_migration_rejects_fractional_history_before_dropping_returns(): void
    {
        Schema::dropAllTables();
        Schema::create('presentaciones', function (Blueprint $table) {
            $table->id();
            $table->decimal('stock', 12, 3)->default(0);
            $table->decimal('stock_minimo_alerta', 12, 3)->default(0);
        });
        Schema::create('ingresos_productores_items', function (Blueprint $table) {
            $table->id();
            $table->decimal('cantidad_ingresada', 10, 3);
            $table->decimal('cantidad_disponible', 10, 3);
        });
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->decimal('cantidad_vendida', 10, 3);
        });
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->decimal('cantidad', 12, 3);
        });
        Schema::create('conciliaciones_stock_acopio', function (Blueprint $table) {
            $table->id();
            $table->decimal('stock_anterior', 12, 3);
            $table->decimal('stock_lotes', 12, 3);
            $table->decimal('diferencia', 12, 3);
        });
        Schema::create('devoluciones', fn (Blueprint $table) => $table->id());

        DB::table('presentaciones')->insert(['id' => 1, 'stock' => '1.000', 'stock_minimo_alerta' => '0.000']);
        DB::table('ingresos_productores_items')->insert(['id' => 9, 'cantidad_ingresada' => '2.000', 'cantidad_disponible' => '1.500']);
        DB::table('ventas')->insert(['id' => 1, 'cantidad_vendida' => '1.000']);
        DB::table('movimientos_inventario')->insert(['id' => 1, 'cantidad' => '-1.000']);
        DB::table('conciliaciones_stock_acopio')->insert(['id' => 1, 'stock_anterior' => '2.000', 'stock_lotes' => '1.000', 'diferencia' => '-1.000']);

        try {
            (require database_path('migrations/2026_09_17_000001_remove_returns_and_integer_quantities.php'))->up();
            $this->fail('La migración debía rechazar cantidades fraccionarias.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('ingresos_productores_items.cantidad_disponible: IDs 9', $exception->getMessage());
        }

        $this->assertTrue(Schema::hasTable('devoluciones'));
        $this->assertNotSame('integer', Schema::getColumnType('ingresos_productores_items', 'cantidad_disponible'));
    }

    public function test_empty_fresh_install_succeeds(): void
    {
        $this->artisan('migrate:fresh')->assertExitCode(0);
        $this->assertTrue(Schema::hasTable('ingresos_productores_items'));
        $this->assertTrue(Schema::hasTable('tickets_venta'));
        $this->assertDatabaseCount('productores', 0);
        $this->assertDatabaseCount('conciliaciones_stock_acopio', 0);
    }

    public function test_inventory_filter_indexes_exist(): void
    {
        $this->artisan('migrate:fresh')->assertExitCode(0);
        $this->assertContains('ingresos_productor_estado_fecha_idx', array_column(Schema::getIndexes('ingresos_productores'), 'name'));
        $this->assertContains('lotes_presentacion_vencimiento_saldo_idx', array_column(Schema::getIndexes('ingresos_productores_items'), 'name'));
        $this->assertContains('lotes_recepcion_idx', array_column(Schema::getIndexes('ingresos_productores_items'), 'name'));
        $this->assertFalse(Schema::hasTable('devoluciones'));
        $this->assertContains('productores_activo_idx', array_column(Schema::getIndexes('productores'), 'name'));
    }

    public function test_populated_legacy_history_is_preserved_except_liquidations(): void
    {
        $this->artisan('migrate:fresh', ['--path' => 'database/migrations/0001_01_01_000000_create_users_table.php'])->assertExitCode(0);
        Schema::create('presentaciones', function ($t) {
            $t->id();
            $t->integer('stock')->default(0);
        });
        (require database_path('migrations/2026_09_05_000000_create_consignacion_y_ventas_tables.php'))->up();
        DB::table('users')->insert(['id' => 7, 'name' => 'Nombre Histórico Completo', 'email' => 'legacy@example.test', 'password' => 'x', 'rol' => 'Productor']);
        DB::table('presentaciones')->insert(['id' => 4, 'stock' => 999]);
        DB::table('consignaciones')->insert(['id' => 15, 'user_id' => 7, 'fecha_entrada' => '2026-01-01', 'estado' => 'abierta']);
        DB::table('consignacion_items')->insert(['id' => 21, 'consignacion_id' => 15, 'presentacion_id' => 4, 'cantidad_recibida' => 3, 'cantidad_disponible' => 2, 'precio_productor' => '5.25', 'precio_venta' => '7.50']);
        DB::table('ventas')->insert(['id' => 31, 'consignacion_item_id' => 21, 'user_id' => 7, 'cantidad_vendida' => '1.000', 'precio_unitario_venta' => '7.50']);
        DB::table('liquidaciones')->insert(['id' => 41, 'consignacion_id' => 15, 'user_id' => 7, 'fecha_liquidacion' => '2026-02-01', 'monto_liquidado' => '5.25']);
        (require database_path('migrations/2026_09_10_000001_refactor_acopio.php'))->up();
        (require database_path('migrations/2026_09_17_000003_remove_liquidaciones_completely.php'))->up();
        $this->assertFalse(Schema::hasTable('consignaciones'));
        $this->assertDatabaseHas('productores', ['legacy_user_id' => 7, 'nombres' => 'Nombre Histórico Completo', 'primer_apellido' => '']);
        $this->assertDatabaseHas('ingresos_productores', ['id' => 15, 'user_id' => null, 'fecha_ingreso' => '2026-01-01']);
        $this->assertDatabaseHas('ingresos_productores_items', ['id' => 21, 'ingreso_productor_id' => 15, 'cantidad_ingresada' => 3, 'cantidad_disponible' => 2, 'fecha_caducidad' => null]);
        $this->assertDatabaseHas('ventas', ['id' => 31, 'ingreso_productor_item_id' => 21, 'ticket_venta_id' => null]);
        $this->assertFalse(Schema::hasTable('devoluciones'));
        $this->assertFalse(Schema::hasTable('liquidaciones'));
        $this->assertDatabaseHas('ingresos_productores', ['id' => 15, 'estado' => 'abierta']);
        $this->assertDatabaseHas('presentaciones', ['id' => 4, 'stock' => 2]);
        $this->assertDatabaseHas('conciliaciones_stock_acopio', ['presentacion_id' => 4, 'stock_anterior' => 999, 'stock_lotes' => 2, 'diferencia' => -997]);
        $this->assertSame([], DB::select('PRAGMA foreign_key_check'));
        $venta = Venta::findOrFail(31);
        $this->assertSame('7.50', $venta->total_venta);
        $this->assertSame('2.25', $venta->margen_obtenido);
        $this->assertSame(15, $venta->lote->ingresoProductor->id);
        $this->assertSame(3, $venta->lote->cantidad_ingresada);
        DB::table('ingresos_productores_items')->where('id', 21)->update(['precio_acopio_unitario' => null]);
        $this->assertNull(DB::table('ingresos_productores_items')->where('id', 21)->value('precio_acopio_unitario'));
        DB::table('ingresos_productores_items')->where('id', 21)->update(['precio_acopio_unitario' => '5.25']);
        $this->assertSame(2, Presentacion::findOrFail(4)->stock);
        $productor = $venta->lote->ingresoProductor->productor;
        $productor->delete();
        $this->assertNotNull($venta->lote->ingresoProductor->fresh()->productor);
        $this->expectException(RuntimeException::class);
        (require database_path('migrations/2026_09_10_000001_refactor_acopio.php'))->down();
    }
}
