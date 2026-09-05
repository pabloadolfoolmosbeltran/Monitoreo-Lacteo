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
