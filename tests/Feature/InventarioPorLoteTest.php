<?php

namespace Tests\Feature;

use App\Models\IngresoProductor;
use App\Models\IngresoProductorItem;
use App\Models\Presentacion;
use App\Models\Producto;
use App\Models\Productor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventarioPorLoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_lots_combine_all_filters_and_keep_values(): void
    {
        [$admin, $visible, $other] = $this->fixtures();
        $url = '/inventario?producto=yogurt+frutilla+2+litros&productor=Finca+Norte'
            .'&ingreso_desde=2026-09-01&ingreso_hasta=2026-09-30'
            .'&vence_desde=2026-10-01&vence_hasta=2026-10-31';

        $this->actingAs($admin)->get($url)->assertOk()
            ->assertSee('Lote #'.$visible->id)
            ->assertDontSee('Lote #'.$other->id)
            ->assertSee('value="yogurt frutilla 2 litros"', false)
            ->assertSee('value="2026-10-31"', false);
    }

    public function test_inventory_rejects_inverted_date_ranges(): void
    {
        $admin = User::factory()->create(['rol' => 'Administrador', 'activo' => true]);
        $this->actingAs($admin)->from('/inventario')
            ->get('/inventario?ingreso_desde=2026-09-30&ingreso_hasta=2026-09-01')
            ->assertRedirect('/inventario')->assertSessionHasErrors('ingreso_hasta');
        $this->from('/inventario')->get('/inventario?vence_desde=2026-10-30&vence_hasta=2026-10-01')
            ->assertRedirect('/inventario')->assertSessionHasErrors('vence_hasta');
    }

    public function test_inventory_accepts_single_date_limits(): void
    {
        $admin = User::factory()->create(['rol' => 'Administrador', 'activo' => true]);
        $this->actingAs($admin)->get('/inventario?ingreso_hasta=2026-09-30&vence_hasta=2026-10-31')->assertOk();
    }

    private function fixtures(): array
    {
        $admin = User::factory()->create(['rol' => 'Administrador', 'activo' => true]);
        $north = Productor::create(['nombres' => 'Ana', 'primer_apellido' => 'Mendoza', 'nombre_unidad_productiva' => 'Finca Norte', 'activo' => true]);
        $south = Productor::create(['nombres' => 'Bruno', 'primer_apellido' => 'Rojas', 'nombre_unidad_productiva' => 'Finca Sur', 'activo' => true]);
        $yogurt = Producto::create(['nombre' => 'Yogurt', 'temperatura_minima' => 2, 'temperatura_maxima' => 8, 'activo' => true]);
        $cheese = Producto::create(['nombre' => 'Queso', 'temperatura_minima' => 2, 'temperatura_maxima' => 8, 'activo' => true]);
        $bottle = Presentacion::create(['producto_id' => $yogurt->id, 'nombre' => 'Botella familiar', 'sabor' => 'Frutilla', 'contenido' => 2, 'unidad' => 'L', 'precio' => 30, 'stock' => 5, 'activo' => true]);
        $block = Presentacion::create(['producto_id' => $cheese->id, 'nombre' => 'Bloque', 'contenido' => 1, 'unidad' => 'kg', 'precio' => 40, 'stock' => 1, 'activo' => true]);
        $open = IngresoProductor::create(['productor_id' => $north->id, 'user_id' => $admin->id, 'fecha_ingreso' => '2026-09-11', 'estado' => 'abierta']);
        $closed = IngresoProductor::create(['productor_id' => $south->id, 'user_id' => $admin->id, 'fecha_ingreso' => '2026-08-15', 'estado' => 'cerrada']);
        $visible = IngresoProductorItem::create(['ingreso_productor_id' => $open->id, 'presentacion_id' => $bottle->id, 'cantidad_ingresada' => 5, 'cantidad_disponible' => 5, 'precio_acopio_unitario' => 20, 'precio_venta_unitario' => 30, 'fecha_recepcion' => '2026-09-11', 'fecha_caducidad' => '2026-10-15']);
        $other = IngresoProductorItem::create(['ingreso_productor_id' => $closed->id, 'presentacion_id' => $block->id, 'cantidad_ingresada' => 1, 'cantidad_disponible' => 1, 'precio_acopio_unitario' => 30, 'precio_venta_unitario' => 40, 'fecha_recepcion' => '2026-08-15', 'fecha_caducidad' => '2026-09-15']);

        return [$admin, $visible, $other];
    }
}
