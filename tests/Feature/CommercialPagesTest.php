<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommercialPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_render_every_commercial_workspace_and_export_empty_reports(): void
    {
        $this->actingAs(User::factory()->create(['rol' => 'Administrador', 'activo' => true]));

        foreach (['/pos', '/productores', '/ingresos-productores', '/ingresos-productores/create', '/inventario', '/reportes-comerciales'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get('/reportes-comerciales/pdf')->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get('/reportes-comerciales/excel')->assertOk();
    }

    public function test_commercial_pages_and_dashboard_share_the_main_panel_navigation(): void
    {
        $this->actingAs(User::factory()->create(['rol' => 'Administrador', 'activo' => true]));

        $commercialLinks = [
            route('pos.index'),
            route('ingresos-productores.index'),
            route('productores.index'),
            route('ajustes-inventario.index'),
            route('reportes-comerciales.index'),
        ];

        foreach (['/pos', '/productores', '/ingresos-productores', '/inventario', '/reportes-comerciales'] as $url) {
            $response = $this->get($url)
                ->assertOk()
                ->assertSee('data-layout="panel-principal"', false);

            foreach ($commercialLinks as $link) {
                $response->assertSee('href="'.$link.'"', false);
            }
        }

        $dashboard = $this->get('/dashboard')->assertOk();

        foreach ($commercialLinks as $link) {
            $dashboard->assertSee('href="'.$link.'"', false);
        }
    }

    public function test_authenticated_panel_uses_one_fixed_shell_and_one_sidebar(): void
    {
        $this->actingAs(User::factory()->create(['rol' => 'Administrador', 'activo' => true]));

        foreach (['/dashboard', '/control', '/productos', '/pos', '/productores'] as $url) {
            $response = $this->get($url)->assertOk();
            $html = $response->getContent();

            $response->assertSee('class="panel-layout"', false)
                ->assertSee('id="app-shell"', false)
                ->assertSee('id="app-main"', false);
            $this->assertSame(1, substr_count($html, 'id="app-sidebar"'));
            $this->assertStringNotContainsString('commercial-shortcuts', $html);
        }
    }

    public function test_commercial_shell_exposes_inventories_without_returns(): void
    {
        $this->actingAs(User::factory()->create(['rol' => 'Trabajador', 'activo' => true]));
        foreach (['/inventario', '/inventario-general'] as $url) {
            $this->get($url)->assertOk()->assertSee('Ajustes de Inventario')->assertSee('Inventario General')->assertDontSee('Devoluciones');
        }

        $this->get('/devoluciones')->assertNotFound();
        $this->post('/lotes/1/devoluciones', [])->assertNotFound();
        $this->get('/reportes-comerciales')->assertOk()->assertDontSee('Devoluciones');
    }

    public function test_navigation_uses_collapsible_operations_inventory_and_report_groups(): void
    {
        $this->actingAs(User::factory()->create(['rol' => 'Administrador', 'activo' => true]));

        $operations = $this->get('/produccion')->assertOk()
            ->assertSee('data-bs-target="#operations-menu"', false)
            ->assertSee('id="operations-menu" class="collapse show sidebar-collapse"', false)
            ->assertSee('href="'.url('/produccion').'"', false)
            ->assertSee('href="'.url('/control').'"', false);

        $this->assertSame(1, substr_count($operations->getContent(), 'data-bs-target="#operations-menu"'));

        $inventory = $this->get('/inventario-general')->assertOk()
            ->assertSee('data-bs-target="#inventory-menu"', false)
            ->assertSee('id="inventory-menu" class="collapse show sidebar-collapse"', false)
            ->assertSee('href="'.route('ingresos-productores.index').'"', false)
            ->assertSee('href="'.route('ajustes-inventario.index').'"', false)
            ->assertSee('href="'.route('descartes-productos.index').'"', false)
            ->assertSee('href="'.route('inventario-general.index').'"', false)
            ->assertSee('href="'.route('pos.index').'"', false)
            ->assertSee('href="'.route('productores.index').'"', false);

        $this->assertSame(1, substr_count($inventory->getContent(), 'data-bs-target="#inventory-menu"'));

        $this->get('/reportes-ingresos')->assertOk()
            ->assertSee('data-bs-target="#commercial-reports-menu"', false)
            ->assertSee('id="commercial-reports-menu" class="collapse show sidebar-collapse"', false)
            ->assertSee('href="'.route('reportes-comerciales.index').'"', false)
            ->assertSee('href="'.route('reportes-ingresos.index').'"', false);
    }
}
