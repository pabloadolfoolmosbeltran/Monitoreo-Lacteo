<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LiquidacionesRemovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_schema_and_routes_have_no_liquidations(): void
    {
        $this->assertFalse(Schema::hasTable('liquidaciones'));
        $this->assertFalse(Route::has('ingresos-productores.liquidar'));

        $administrador = User::factory()->create(['rol' => 'Administrador', 'activo' => true]);

        $this->actingAs($administrador)
            ->post('/ingresos-productores/1/liquidacion')
            ->assertNotFound();
    }

    public function test_commercial_pages_do_not_expose_liquidation_controls(): void
    {
        $administrador = User::factory()->create(['rol' => 'Administrador', 'activo' => true]);

        $this->actingAs($administrador)
            ->get(route('inventario.index'))
            ->assertOk()
            ->assertDontSee('Liquidación')
            ->assertDontSee('liquidacion', false);
    }
}
