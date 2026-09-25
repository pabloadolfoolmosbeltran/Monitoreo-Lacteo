<?php

namespace Tests\Feature;

use App\Models\IngresoProductor;
use App\Models\IngresoProductorItem;
use App\Models\Presentacion;
use App\Models\Producto;
use App\Models\Productor;
use App\Models\TicketVenta;
use App\Models\User;
use App\Models\Venta;
use App\Services\ReporteVentasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductosVendidosReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_ranking_suma_unidades_montos_y_filtra_proveedor(): void
    {
        [$admin, $proveedor, $otro, $lote] = $this->datos();

        $filas = app(ReporteVentasService::class)->productosMasVendidos([
            'desde' => '2026-09-01', 'hasta' => '2026-09-30', 'productor_id' => $proveedor->id,
        ]);

        $this->assertCount(1, $filas);
        $this->assertSame('Queso crema', $filas->first()['producto']);
        $this->assertSame(4, $filas->first()['unidades']);
        $this->assertSame('40.00', $filas->first()['monto']);
        $this->assertSame('100.00', $filas->first()['porcentaje']);
    }

    public function test_reporte_muestra_grafico_tabla_y_exporta_csv(): void
    {
        [$admin, $proveedor] = $this->datos();

        $this->actingAs($admin)->get(route('reportes-comerciales.index', ['productor_id' => $proveedor->id]))
            ->assertOk()->assertSee('Productos más vendidos')->assertSee('id="top-products-chart"', false)
            ->assertSee('class="sales-ranking-chart"', false)
            ->assertSee('href="'.route('reportes-comerciales.pdf', ['productor_id' => $proveedor->id]).'"', false)
            ->assertSee('data-full-navigation', false)
            ->assertSee('Queso crema');

        $this->get(route('reportes-comerciales.productos.csv', ['productor_id' => $proveedor->id]))
            ->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    private function datos(): array
    {
        $admin = User::factory()->create(['rol' => 'Administrador', 'activo' => true]);
        $proveedor = Productor::create(['nombres' => 'Ana', 'primer_apellido' => 'Mendoza', 'activo' => true]);
        $otro = Productor::create(['nombres' => 'Bruno', 'primer_apellido' => 'Rojas', 'activo' => true]);
        $producto = Producto::create(['nombre' => 'Queso crema', 'temperatura_minima' => 2, 'temperatura_maxima' => 8, 'activo' => true]);
        $presentacion = Presentacion::create(['producto_id' => $producto->id, 'nombre' => 'Pote', 'contenido' => 1, 'unidad' => 'unidad', 'precio' => 10, 'stock' => 0, 'activo' => true]);
        $ingreso = IngresoProductor::create(['productor_id' => $proveedor->id, 'user_id' => $admin->id, 'fecha_ingreso' => '2026-09-01', 'estado' => 'abierta']);
        $lote = IngresoProductorItem::create(['ingreso_productor_id' => $ingreso->id, 'presentacion_id' => $presentacion->id, 'cantidad_ingresada' => 4, 'cantidad_disponible' => 0, 'precio_acopio_unitario' => 7, 'precio_venta_unitario' => 10, 'fecha_recepcion' => '2026-09-01', 'fecha_caducidad' => '2026-10-01']);
        $ticket = TicketVenta::create(['clave' => fake()->uuid(), 'payload_hash' => str_repeat('a', 64), 'user_id' => $admin->id, 'metodo_pago' => 'efectivo', 'total' => 40]);
        Venta::create(['ticket_venta_id' => $ticket->id, 'ingreso_productor_item_id' => $lote->id, 'user_id' => $admin->id, 'cantidad_vendida' => 4, 'precio_unitario_venta' => 10, 'fecha_venta' => '2026-09-10 10:00:00']);

        return [$admin, $proveedor, $otro, $lote];
    }
}
