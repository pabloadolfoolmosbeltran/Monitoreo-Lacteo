<?php

namespace Tests\Feature;

use App\Models\Dispositivo;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RennetRecommendationTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_schema_removes_rennet_stock(): void
    {
        $this->assertFalse(Schema::hasColumn('productos', 'stock_cuajo'));
    }

    public function test_product_list_shows_reference_amounts_without_rennet_stock(): void
    {
        $admin = User::factory()->create(['rol' => 'Administrador', 'activo' => true]);
        Producto::create([
            'nombre' => 'Queso recomendado',
            'temperatura_minima' => 2,
            'temperatura_maxima' => 8,
            'temperatura_pasteurizacion' => 65,
            'tipo_cuajo' => 'Cuajo en polvo',
            'cuajo_por_litro' => 0.2,
            'unidad_cuajo' => 'g',
            'activo' => true,
        ]);

        $this->actingAs($admin)->get('/productos')
            ->assertOk()
            ->assertSee('1 L: 0,20 g')
            ->assertSee('5 L: 1,00 g')
            ->assertSee('10 L: 2,00 g')
            ->assertDontSee('Stock del cuajo')
            ->assertDontSee('Stock Actual de Cuajo');
    }

    public function test_production_starts_and_persists_recommendation_without_rennet_inventory(): void
    {
        $admin = User::factory()->create(['rol' => 'Administrador', 'activo' => true]);
        $producto = Producto::create([
            'nombre' => 'Queso sin inventario de cuajo',
            'temperatura_minima' => 2,
            'temperatura_maxima' => 8,
            'temperatura_pasteurizacion' => 65,
            'tipo_cuajo' => 'Cuajo vegetal',
            'cuajo_por_litro' => 0.25,
            'unidad_cuajo' => 'g',
            'activo' => true,
        ]);
        Dispositivo::create([
            'nombre' => 'ESP32 de prueba',
            'mac_address' => 'AA:BB:CC:DD:EE:01',
            'estado' => 'Activo',
        ]);

        $this->actingAs($admin)->post('/produccion/iniciar', [
            'user_id' => $admin->id,
            'producto_id' => $producto->id,
            'cantidad_leche' => 12,
            'temperatura_objetivo' => 38,
        ])->assertRedirect('/produccion')->assertSessionHasNoErrors();

        $this->assertDatabaseHas('producciones', [
            'producto_id' => $producto->id,
            'cantidad_leche' => '12.00',
            'tipo_cuajo' => 'Cuajo vegetal',
            'cantidad_cuajo' => '3.00',
            'estado' => 'En proceso',
        ]);
    }
}
