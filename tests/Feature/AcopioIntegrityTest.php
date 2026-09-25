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

class AcopioIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private function setupStock(): array
    {
        $this->actingAs(User::factory()->create(['rol' => 'Administrador', 'activo' => true]));
        $producer = Productor::create(['nombres' => 'Ana', 'primer_apellido' => 'Perez', 'activo' => true]);
        $product = Producto::create(['nombre' => 'Leche', 'temperatura_minima' => 4, 'temperatura_maxima' => 8, 'activo' => true]);
        $p = Presentacion::create(['producto_id' => $product->id, 'nombre' => 'Natural', 'precio' => '1.01', 'stock' => 0, 'unidad' => 'L', 'activo' => true]);

        return [$producer, $p];
    }

    private function receive($producer, $p, $price = '0.50')
    {
        return $this->postJson('/ingresos-productores', ['productor_id' => $producer->id, 'fecha_ingreso' => today()->toDateString(),
            'items' => [['presentacion_id' => $p->id, 'cantidad_ingresada' => 1, 'precio_acopio_unitario' => $price,
                'precio_venta_unitario' => '1.01', 'fecha_caducidad' => today()->addDays(5)->toDateString()]]]);
    }

    public function test_nullable_acopio_does_not_produce_server_error(): void
    {
        [$producer,$p] = $this->setupStock();
        $this->receive($producer, $p, null)->assertCreated();
    }

    public function test_second_product_shortage_rolls_back_first_product_and_ticket(): void
    {
        [$producer,$p] = $this->setupStock();
        $this->receive($producer, $p)->assertCreated();
        $q = $p->replicate();
        $q->stock = 0;
        $q->save();
        $this->postJson('/comercial/ventas', ['clave' => (string) Str::uuid(), 'metodo_pago' => 'qr', 'items' => [
            ['presentacion_id' => $q->id, 'cantidad' => 1], ['presentacion_id' => $p->id, 'cantidad' => 1]]])->assertUnprocessable();
        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('tickets_venta', 0);
        $this->assertSame(1, $p->fresh()->stock);
        $this->assertEquals(1, DB::table('ingresos_productores_items')->sum('cantidad_disponible'));
    }

    public function test_archived_producer_history_remains_saleable_and_inactive_presentation_does_not(): void
    {
        [$producer,$p] = $this->setupStock();
        $this->receive($producer, $p)->assertCreated();
        $producer->delete();
        $payload = ['clave' => (string) Str::uuid(), 'metodo_pago' => 'efectivo', 'items' => [['presentacion_id' => $p->id, 'cantidad' => 1]]];
        $this->postJson('/comercial/ventas', $payload)->assertCreated()->assertJsonPath('ventas.0.productor', 'Ana Perez');
        $p->update(['activo' => false]);
        $payload['clave'] = (string) Str::uuid();
        $this->postJson('/comercial/ventas', $payload)->assertUnprocessable();
        $this->assertDatabaseCount('ventas', 1);
    }

    public function test_fefo_preserves_whole_units_without_stock_loss(): void
    {
        [$producer,$p] = $this->setupStock();
        $this->receive($producer, $p)->assertCreated();
        $this->receive($producer, $p)->assertCreated();
        $this->postJson('/comercial/ventas', ['clave' => (string) Str::uuid(), 'metodo_pago' => 'efectivo', 'items' => [['presentacion_id' => $p->id, 'cantidad' => 1]]])
            ->assertCreated()->assertJsonPath('ventas.0.cantidad', 1)->assertJsonPath('total', '1.01');
        $this->assertSame(1, $p->fresh()->stock);
    }

    public function test_presentation_metadata_update_cannot_overwrite_lot_stock(): void
    {
        [$producer,$p] = $this->setupStock();
        $this->receive($producer, $p)->assertCreated();
        $this->putJson('/presentaciones/'.$p->id, ['producto_id' => $p->producto_id, 'nombre' => 'Renombrado', 'precio' => '1.01', 'stock' => 900]);
        $this->assertSame(1, $p->fresh()->stock);
    }

    public function test_admin_can_remove_whole_stock_with_audited_movement(): void
    {
        [$producer,$p] = $this->setupStock();
        $id = $this->receive($producer, $p)->assertCreated()->json('id');
        $lot = DB::table('ingresos_productores_items')->where('ingreso_productor_id', $id)->first();
        $this->postJson('/lotes/'.$lot->id.'/movimientos', ['tipo' => 'ajuste', 'cantidad' => -1, 'motivo' => 'Baja de unidad'])->assertSuccessful();
        $this->assertSame(0, $p->fresh()->stock);
        $this->assertDatabaseHas('movimientos_inventario', ['lote_id' => $lot->id, 'cantidad' => -1]);
    }

    public function test_regularization_unblocks_missing_price_and_expiry_and_preserves_known_values(): void
    {
        [$producer,$p] = $this->setupStock();
        $id = $this->receive($producer, $p, null)->assertCreated()->json('id');
        $lot = DB::table('ingresos_productores_items')->where('ingreso_productor_id', $id)->first();
        DB::table('ingresos_productores_items')->where('id', $lot->id)->update(['fecha_caducidad' => null]);
        $patch = ['fecha_caducidad' => today()->addDays(4)->toDateString(), 'precio_acopio_unitario' => '0.50', 'motivo' => 'Verificado con productor'];
        $this->patchJson('/lotes/'.$lot->id.'/regularizar', $patch)->assertSuccessful();
        $this->assertDatabaseHas('movimientos_inventario', ['lote_id' => $lot->id, 'tipo' => 'regularizacion', 'cantidad' => 0]);
        $patch['precio_acopio_unitario'] = '0.60';
        $this->patchJson('/lotes/'.$lot->id.'/regularizar', $patch)->assertUnprocessable();
        $this->assertDatabaseHas('ingresos_productores_items', ['id' => $lot->id, 'precio_acopio_unitario' => '0.50']);
        $this->postJson('/comercial/ventas', ['clave' => (string) Str::uuid(), 'metodo_pago' => 'efectivo', 'items' => [['presentacion_id' => $p->id, 'cantidad' => 1]]])->assertCreated();
        $this->assertSame(0, $p->fresh()->stock);
    }
}
