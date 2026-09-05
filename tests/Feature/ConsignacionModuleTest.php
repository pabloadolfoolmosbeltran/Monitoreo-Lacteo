<?php

namespace Tests\Feature;

use App\Models\Consignacion;
use App\Models\ConsignacionItem;
use App\Models\Presentacion;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsignacionModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_venta_descuenta_disponible_y_calcula_margenes_del_ejemplo(): void
    {
        $escenario = $this->crearEscenarioConsignado();

        $this->actingAs($escenario['vendedor'])
            ->post(route('consignacion-items.ventas.store', $escenario['item']), [
                'cantidad_vendida' => '15.000',
                'precio_unitario_venta' => '30.00',
            ])
            ->assertRedirect(route('consignaciones.show', $escenario['consignacion']));

        $item = $escenario['item']->refresh();
        $venta = Venta::query()->firstOrFail();

        $this->assertSame('5.000', $item->cantidad_disponible);
        $this->assertSame('4.00', $item->margen_unitario);
        $this->assertSame('450.00', $venta->total_venta);
        $this->assertSame('60.00', $venta->margen_obtenido);
        $this->assertSame('390.00', $item->fresh('ventas')->monto_productor);
        $this->assertSame('60.00', $item->fresh('ventas')->margen_encargado);

        $this->actingAs($escenario['vendedor'])
            ->get(route('consignaciones.show', $escenario['consignacion']))
            ->assertOk()
            ->assertSee('Yogurt de Frutilla');
    }

    public function test_venta_no_puede_superar_cantidad_disponible(): void
    {
        $escenario = $this->crearEscenarioConsignado();

        $this->actingAs($escenario['vendedor'])
            ->from(route('consignaciones.show', $escenario['consignacion']))
            ->post(route('consignacion-items.ventas.store', $escenario['item']), [
                'cantidad_vendida' => '21.000',
                'precio_unitario_venta' => '30.00',
            ])
            ->assertRedirect(route('consignaciones.show', $escenario['consignacion']))
            ->assertSessionHasErrors('cantidad_vendida');

        $this->assertDatabaseCount('ventas', 0);
        $this->assertSame('20.000', $escenario['item']->refresh()->cantidad_disponible);
    }

    public function test_consignacion_rechaza_precio_de_venta_menor_al_precio_productor(): void
    {
        $escenario = $this->crearBaseComercial();

        $this->actingAs($escenario['vendedor'])
            ->from(route('consignaciones.create'))
            ->post(route('consignaciones.store'), [
                'user_id' => $escenario['productor']->id,
                'fecha_entrada' => '2026-09-05',
                'items' => [[
                    'presentacion_id' => $escenario['presentacion']->id,
                    'cantidad_recibida' => '2.000',
                    'precio_productor' => '26.00',
                    'precio_venta' => '25.99',
                ]],
            ])
            ->assertRedirect(route('consignaciones.create'))
            ->assertSessionHasErrors('items.0.precio_venta');

        $this->assertDatabaseCount('consignaciones', 0);
        $this->assertDatabaseCount('consignacion_items', 0);
    }

    public function test_liquidacion_calcula_monto_productor_y_bloquea_nuevas_ventas(): void
    {
        $escenario = $this->crearEscenarioConsignado();

        $this->actingAs($escenario['vendedor'])
            ->post(route('consignacion-items.ventas.store', $escenario['item']), [
                'cantidad_vendida' => '15.000',
                'precio_unitario_venta' => '30.00',
            ]);

        $this->actingAs($escenario['vendedor'])
            ->post(route('consignaciones.liquidaciones.store', $escenario['consignacion']), [
                'fecha_liquidacion' => '2026-09-05',
            ])
            ->assertRedirect(route('consignaciones.show', $escenario['consignacion']));

        $this->assertDatabaseHas('liquidaciones', [
            'consignacion_id' => $escenario['consignacion']->id,
            'monto_liquidado' => '390.00',
        ]);
        $this->assertSame('liquidada', $escenario['consignacion']->refresh()->estado);

        $this->actingAs($escenario['vendedor'])
            ->from(route('consignaciones.show', $escenario['consignacion']))
            ->post(route('consignacion-items.ventas.store', $escenario['item']), [
                'cantidad_vendida' => '1.000',
                'precio_unitario_venta' => '30.00',
            ])
            ->assertSessionHasErrors('consignacion');
    }

    /**
     * @return array{productor: User, vendedor: User, producto: Producto, presentacion: Presentacion}
     */
    private function crearBaseComercial(): array
    {
        $productor = User::factory()->create([
            'rol' => 'Trabajador',
            'activo' => true,
            'nombre_unidad_productiva' => 'Unidad A',
        ]);
        $vendedor = User::factory()->create([
            'rol' => 'Administrador',
            'activo' => true,
        ]);
        $producto = Producto::create([
            'nombre' => 'Yogurt de Frutilla',
            'descripcion' => 'Yogurt natural con fruta',
            'temperatura_minima' => '4.00',
            'temperatura_maxima' => '8.00',
            'temperatura_pasteurizacion' => '72.00',
            'tipo_cuajo' => null,
            'cuajo_por_litro' => null,
            'unidad_cuajo' => 'ml',
            'stock_cuajo' => '0.00',
            'activo' => true,
        ]);
        $presentacion = Presentacion::create([
            'producto_id' => $producto->id,
            'nombre' => 'Yogurt de Frutilla 1 L',
            'envase' => 'Botella',
            'sabor' => 'Frutilla',
            'contenido' => '1.00',
            'unidad' => 'L',
            'precio' => '30.00',
            'stock' => 20,
            'con_fruta' => true,
            'activo' => true,
        ]);

        return compact('productor', 'vendedor', 'producto', 'presentacion');
    }

    /**
     * @return array{productor: User, vendedor: User, producto: Producto, presentacion: Presentacion, consignacion: Consignacion, item: ConsignacionItem}
     */
    private function crearEscenarioConsignado(): array
    {
        $base = $this->crearBaseComercial();

        $consignacion = Consignacion::create([
            'user_id' => $base['productor']->id,
            'fecha_entrada' => '2026-09-05',
            'estado' => 'abierta',
        ]);
        $item = ConsignacionItem::create([
            'consignacion_id' => $consignacion->id,
            'presentacion_id' => $base['presentacion']->id,
            'cantidad_recibida' => '20.000',
            'cantidad_disponible' => '20.000',
            'precio_productor' => '26.00',
            'precio_venta' => '30.00',
        ]);

        return [
            ...$base,
            'consignacion' => $consignacion,
            'item' => $item,
        ];
    }
}
