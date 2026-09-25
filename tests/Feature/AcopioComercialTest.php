<?php

namespace Tests\Feature;

use App\Models\Presentacion;
use App\Models\Producto;
use App\Models\Productor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AcopioComercialTest extends TestCase
{
    use RefreshDatabase;

    private function operador(): User
    {
        return User::factory()->create(['rol' => 'Administrador', 'activo' => true]);
    }

    public function test_catalogo_comercial_exige_autenticacion(): void
    {
        $this->getJson('/comercial/catalogo')->assertUnauthorized();
    }

    public function test_productor_no_puede_operar_caja(): void
    {
        $this->actingAs(User::factory()->create(['rol' => 'Productor', 'activo' => true]))
            ->postJson('/comercial/ventas', [])->assertForbidden();
    }

    public function test_registro_de_ingreso_solo_ofrece_productores_activos_en_selector(): void
    {
        $this->actingAs($this->operador());
        $activo = Productor::create([
            'nombres' => 'Ana Activa',
            'primer_apellido' => 'Mendoza',
            'telefono' => '04120000001',
            'nombre_unidad_productiva' => 'Finca El Prado',
            'activo' => true,
        ]);
        Productor::create([
            'nombres' => 'Bruno Inactivo',
            'primer_apellido' => 'Rojas',
            'activo' => false,
        ]);

        $this->get('/ingresos-productores/create')
            ->assertOk()
            ->assertSee('Ana Activa')
            ->assertSee('Finca El Prado')
            ->assertSee('<option value="'.$activo->id.'"', false)
            ->assertDontSee('Bruno Inactivo');
    }

    public function test_registro_de_ingreso_rechaza_productores_inactivos(): void
    {
        $this->actingAs($this->operador());
        $productor = Productor::create([
            'nombres' => 'Inactivo',
            'primer_apellido' => 'Archivado',
            'activo' => false,
        ]);
        $producto = Producto::create([
            'nombre' => 'Queso',
            'temperatura_minima' => 2,
            'temperatura_maxima' => 8,
            'activo' => true,
        ]);
        $presentacion = Presentacion::create([
            'producto_id' => $producto->id,
            'nombre' => 'Queso 500 g',
            'precio' => '20.00',
            'stock' => 0,
            'unidad' => 'unidad',
            'activo' => true,
        ]);

        $this->postJson('/ingresos-productores', [
            'productor_id' => $productor->id,
            'fecha_ingreso' => now()->format('Y-m-d H:i:s'),
            'items' => [[
                'presentacion_id' => $presentacion->id,
                'cantidad_ingresada' => 1,
                'precio_acopio_unitario' => '12.00',
                'precio_venta_unitario' => '20.00',
                'fecha_caducidad' => now()->addDays(10)->toDateString(),
            ]],
        ])->assertUnprocessable()->assertJsonValidationErrors('productor_id');

        $this->assertDatabaseCount('ingresos_productores', 0);
    }

    private function base(): array
    {
        $this->actingAs($this->operador());
        $productor = $this->postJson('/productores', ['nombres' => 'María', 'primer_apellido' => 'Quispe', 'activo' => true])
            ->assertCreated()->json('id');
        $producto = Producto::create(['nombre' => 'Yogurt', 'temperatura_minima' => 4, 'temperatura_maxima' => 8, 'activo' => true]);
        $p = Presentacion::create(['producto_id' => $producto->id, 'nombre' => 'Yogurt natural 1 L', 'precio' => '12.00', 'stock' => 0, 'unidad' => 'L', 'activo' => true]);

        return [$productor, $p];
    }

    private function ingreso(int $productor, Presentacion $p, string $cantidad, int $dias = 10, string $precio = '12.00'): int
    {
        return $this->postJson('/ingresos-productores', [
            'productor_id' => $productor, 'fecha_ingreso' => now()->format('Y-m-d H:i:s'),
            'items' => [['presentacion_id' => $p->id, 'cantidad_ingresada' => $cantidad,
                'precio_acopio_unitario' => '8.00', 'precio_venta_unitario' => $precio, 'fecha_caducidad' => now()->addDays($dias)->toDateString()]],
        ])->assertCreated()->json('id');
    }

    public function test_ingreso_entero_y_venta_fefo_preservan_stock_y_trazabilidad(): void
    {
        [$productor,$p] = $this->base();
        $tarde = $this->ingreso($productor, $p, '2', 20);
        $pronto = $this->ingreso($productor, $p, '1', 3, '10.00');
        $this->assertSame(3, $p->fresh()->stock);
        $r = $this->postJson('/comercial/ventas', ['clave' => (string) Str::uuid(), 'metodo_pago' => 'efectivo', 'items' => [['presentacion_id' => $p->id, 'cantidad' => 2]]])->assertCreated();
        $r->assertJsonPath('total', '24.00')->assertJsonCount(2, 'ventas');
        $lote = DB::table('ingresos_productores_items')->where('ingreso_productor_id', $pronto)->first();
        $r->assertJsonPath('ventas.0.lote_id', $lote->id);
        $this->assertEquals(0, DB::table('ingresos_productores_items')->where('id', $lote->id)->value('cantidad_disponible'));
        $this->assertSame(1, $p->fresh()->stock);
    }

    public function test_venta_consume_solo_lotes_del_proveedor_seleccionado(): void
    {
        [$productorA, $presentacion] = $this->base();
        $productorB = $this->postJson('/productores', [
            'nombres' => 'Juana', 'primer_apellido' => 'Rojas', 'activo' => true,
        ])->assertCreated()->json('id');
        $this->ingreso($productorA, $presentacion, '3', 3, '11.00');
        $ingresoB = $this->ingreso($productorB, $presentacion, '4', 5, '13.00');
        $loteB = DB::table('ingresos_productores_items')->where('ingreso_productor_id', $ingresoB)->first();

        $catalogo = $this->getJson('/comercial/catalogo')->assertOk();
        $catalogo->assertJsonPath('0.proveedores.1.productor_id', $productorB);

        $this->postJson('/comercial/ventas', [
            'clave' => (string) Str::uuid(), 'metodo_pago' => 'efectivo',
            'items' => [['presentacion_id' => $presentacion->id, 'productor_id' => $productorB, 'cantidad' => 2]],
        ])->assertCreated()->assertJsonPath('total', '24.00');

        $this->assertDatabaseHas('ventas', ['ingreso_productor_item_id' => $loteB->id, 'cantidad_vendida' => 2]);
        $this->assertSame(2, (int) DB::table('ingresos_productores_items')->where('id', $loteB->id)->value('cantidad_disponible'));
    }

    public function test_venta_selecciona_fefo_automaticamente_entre_proveedores(): void
    {
        [$productorA, $presentacion] = $this->base();
        $productorB = $this->postJson('/productores', [
            'nombres' => 'Juana', 'primer_apellido' => 'Rojas', 'activo' => true,
        ])->assertCreated()->json('id');
        $this->ingreso($productorA, $presentacion, '2', 10);
        $ingresoB = $this->ingreso($productorB, $presentacion, '2', 5);
        $loteB = DB::table('ingresos_productores_items')->where('ingreso_productor_id', $ingresoB)->first();

        $this->postJson('/comercial/ventas', [
            'clave' => (string) Str::uuid(), 'metodo_pago' => 'efectivo',
            'items' => [['presentacion_id' => $presentacion->id, 'cantidad' => 1]],
        ])->assertCreated()->assertJsonPath('ventas.0.lote_id', $loteB->id)
            ->assertJsonPath('ventas.0.producto', 'Yogurt · Yogurt natural 1 L')
            ->assertJsonPath('cliente', null)->assertJsonStructure(['ticket_id', 'fecha', 'metodo_pago', 'total']);

        $this->assertDatabaseHas('ventas', ['ingreso_productor_item_id' => $loteB->id, 'cantidad_vendida' => 1]);
    }

    public function test_ingreso_y_movimientos_rechazan_unidades_fraccionarias(): void
    {
        [$productor,$p] = $this->base();
        $this->postJson('/ingresos-productores', [
            'productor_id' => $productor,
            'fecha_ingreso' => now()->format('Y-m-d H:i:s'),
            'items' => [['presentacion_id' => $p->id, 'cantidad_ingresada' => '1.5',
                'precio_acopio_unitario' => '8.00', 'precio_venta_unitario' => '12.00',
                'fecha_caducidad' => now()->addDays(10)->toDateString()]],
        ])->assertUnprocessable()->assertJsonValidationErrors('items.0.cantidad_ingresada');

        $ingreso = $this->ingreso($productor, $p, '2');
        $lote = DB::table('ingresos_productores_items')->where('ingreso_productor_id', $ingreso)->first();
        $this->postJson('/lotes/'.$lote->id.'/movimientos', [
            'tipo' => 'salida', 'cantidad' => '1.5', 'motivo' => 'Salida fraccionaria',
        ])->assertUnprocessable()->assertJsonValidationErrors('cantidad');
    }

    public function test_faltante_revierte_carrito_entero_y_clave_no_duplica_venta(): void
    {
        [$productor,$p] = $this->base();
        $this->ingreso($productor, $p, '2');
        $this->postJson('/comercial/ventas', ['clave' => (string) Str::uuid(), 'metodo_pago' => 'qr', 'items' => [['presentacion_id' => $p->id, 'cantidad' => 3]]])->assertUnprocessable();
        $this->assertDatabaseCount('ventas', 0);
        $this->assertSame(2, $p->fresh()->stock);
        $payload = ['clave' => (string) Str::uuid(), 'metodo_pago' => 'qr', 'items' => [['presentacion_id' => $p->id, 'cantidad' => 1]]];
        $this->postJson('/comercial/ventas', $payload)->assertCreated();
        $this->postJson('/comercial/ventas', $payload)->assertSuccessful();
        $this->assertDatabaseCount('ventas', 1);
        $this->assertSame(1, $p->fresh()->stock);
        $payload['items'][0]['cantidad'] = 2;
        $this->postJson('/comercial/ventas', $payload)->assertUnprocessable();
    }

    public function test_lote_vencido_no_se_vende_y_precision_invalida_no_se_redondea(): void
    {
        [$productor,$p] = $this->base();
        $id = $this->ingreso($productor, $p, '2');
        DB::table('ingresos_productores_items')->where('ingreso_productor_id', $id)->update(['fecha_caducidad' => now()->subDay()->toDateString()]);
        $this->postJson('/comercial/ventas', ['clave' => (string) Str::uuid(), 'metodo_pago' => 'efectivo', 'items' => [['presentacion_id' => $p->id, 'cantidad' => 1]]])->assertUnprocessable();
        $this->postJson('/comercial/ventas', ['clave' => (string) Str::uuid(), 'metodo_pago' => 'efectivo', 'items' => [['presentacion_id' => $p->id, 'cantidad' => '0.15']]])->assertUnprocessable();
        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_salida_auditada_entera_retiro_stock_sin_liquidaciones(): void
    {
        [$productor,$p] = $this->base();
        $id = $this->ingreso($productor, $p, '2');
        $lote = DB::table('ingresos_productores_items')->where('ingreso_productor_id', $id)->first();
        $this->postJson('/lotes/'.$lote->id.'/movimientos', ['tipo' => 'salida', 'cantidad' => 2, 'motivo' => 'Retiro autorizado'])->assertSuccessful();
        $this->assertDatabaseHas('movimientos_inventario', ['lote_id' => $lote->id, 'cantidad' => -2]);
        $this->assertDatabaseMissing('ingresos_productores_items', ['id' => $lote->id, 'cantidad_disponible' => 2]);
    }
}
