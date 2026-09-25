<?php

namespace Tests\Feature;

use App\Models\{Presentacion, Producto, Productor, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsyncPanelActionsTest extends TestCase
{
    use RefreshDatabase;

    private function authenticate(): void
    {
        $this->actingAs(User::factory()->create(['rol' => 'Administrador', 'activo' => true]));
    }

    public function test_same_screen_quick_actions_are_marked_for_partial_refresh(): void
    {
        $this->authenticate();

        $control = $this->get('/control')->assertOk()->getContent();
        $this->assertSame(8, substr_count($control, 'data-async-action'));

        $this->get('/produccion')->assertOk()->assertSee('data-async-action', false);
        Productor::create(['nombres' => 'Ana', 'primer_apellido' => 'Mendoza', 'activo' => true]);
        $this->get('/productores')->assertOk()->assertSee('data-full-navigation', false)->assertDontSee('data-async-action', false);
    }

    public function test_forms_that_change_screen_remain_normal_laravel_navigation(): void
    {
        $this->authenticate();

        $this->get('/productos/create')->assertOk()->assertDontSee('data-async-action', false);
        $this->get('/presentaciones/create')->assertOk()->assertDontSee('data-async-action', false);
        $this->get('/usuarios/create')->assertOk()->assertDontSee('data-async-action', false);
    }

    public function test_deleted_lists_and_mutations_use_full_page_navigation(): void
    {
        $this->authenticate();
        $user=User::factory()->create(['rol'=>'Trabajador','activo'=>false]);
        $product=Producto::create(['nombre'=>'Inactivo','temperatura_minima'=>2,'temperatura_maxima'=>8,'activo'=>false]);
        Presentacion::create(['producto_id'=>$product->id,'nombre'=>'Inactiva','precio'=>10,'activo'=>false]);
        Productor::create(['nombres'=>'Proveedor','primer_apellido'=>'Inactivo','activo'=>false]);
        foreach(['/productos','/presentaciones','/usuarios','/productores'] as $url) $this->get($url)->assertOk()->assertSee('data-full-navigation',false);
        foreach(['/productos?estado=eliminados','/presentaciones?estado=eliminados','/usuarios?estado=eliminados','/productores?estado=eliminados'] as $url) {
            $html=$this->get($url)->assertOk()->assertSee('Restablecer')->getContent();
            $this->assertStringNotContainsString('data-async-action',substr($html,strpos($html,'Restablecer')-350,700));
        }
    }
}
