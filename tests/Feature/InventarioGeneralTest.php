<?php

namespace Tests\Feature;

use App\Models\IngresoProductor;
use App\Models\IngresoProductorItem;
use App\Models\Presentacion;
use App\Models\Producto;
use App\Models\Productor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InventarioGeneralTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_general_inventory_groups_by_presentation_and_producer_and_separates_stock(): void
    {
        [$admin,$north] = $this->fixtures();
        $this->actingAs($admin)->get('/inventario-general?producto=yogurt+frutilla+2+litros')
            ->assertOk()->assertViewHas('grupos', function ($groups) use ($north) {
                $row = collect($groups->items())->firstWhere('productor_id', $north->id);

                return $row && (float) $row->vendible === 7.0 && (float) $row->proximo === 2.0
                    && (float) $row->no_vendible === 7.0 && (float) $row->fisico === 14.0;
            });
    }

    public function test_general_totals_never_offer_expired_or_closed_stock(): void
    {
        [$admin] = $this->fixtures();
        $this->actingAs($admin)->get('/inventario-general')->assertOk()
            ->assertViewHas('totales', fn ($t) => (float) $t->vendible === 8.0 && (float) $t->no_vendible === 7.0 && (float) $t->fisico === 15.0);
    }

    private function fixtures(): array
    {
        Carbon::setTestNow('2026-09-11 10:00:00');
        $admin = User::factory()->create(['rol' => 'Administrador', 'activo' => true]);
        $north = Productor::create(['nombres' => 'Ana', 'primer_apellido' => 'Mendoza', 'nombre_unidad_productiva' => 'Finca Norte', 'activo' => true]);
        $south = Productor::create(['nombres' => 'Bruno', 'primer_apellido' => 'Rojas', 'nombre_unidad_productiva' => 'Finca Sur', 'activo' => true]);
        $product = Producto::create(['nombre' => 'Yogurt', 'temperatura_minima' => 2, 'temperatura_maxima' => 8, 'activo' => true]);
        $presentation = Presentacion::create(['producto_id' => $product->id, 'nombre' => 'Botella familiar', 'sabor' => 'Frutilla', 'contenido' => 2, 'unidad' => 'L', 'precio' => 30, 'stock' => 15, 'activo' => true]);
        $openNorth = IngresoProductor::create(['productor_id' => $north->id, 'user_id' => $admin->id, 'fecha_ingreso' => '2026-09-10', 'estado' => 'abierta']);
        $closedNorth = IngresoProductor::create(['productor_id' => $north->id, 'user_id' => $admin->id, 'fecha_ingreso' => '2026-09-01', 'estado' => 'cerrada']);
        $openSouth = IngresoProductor::create(['productor_id' => $south->id, 'user_id' => $admin->id, 'fecha_ingreso' => '2026-09-10', 'estado' => 'abierta']);
        foreach ([[$openNorth, 5, '2026-10-10'], [$openNorth, 2, '2026-09-14'], [$openNorth, 3, '2026-09-10'], [$closedNorth, 4, '2026-10-10'], [$openSouth, 1, '2026-10-10']] as [$entry,$qty,$expiry]) {
            IngresoProductorItem::create(['ingreso_productor_id' => $entry->id, 'presentacion_id' => $presentation->id, 'cantidad_ingresada' => $qty, 'cantidad_disponible' => $qty, 'precio_acopio_unitario' => 20, 'precio_venta_unitario' => 30, 'fecha_recepcion' => '2026-09-10', 'fecha_caducidad' => $expiry]);
        }

        return [$admin, $north];
    }
}
