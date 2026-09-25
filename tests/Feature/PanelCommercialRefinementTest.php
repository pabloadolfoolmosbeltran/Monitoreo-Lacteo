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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PanelCommercialRefinementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['rol' => 'Administrador', 'activo' => true]);
    }

    private function commercialData(): array
    {
        $productor = Productor::create(['nombres' => 'Ana', 'primer_apellido' => 'Mendoza', 'nombre_unidad_productiva' => 'Finca Norte', 'activo' => true]);
        $producto = Producto::create(['nombre' => 'Yogur Natural', 'temperatura_minima' => 2, 'temperatura_maxima' => 8, 'activo' => true]);
        $presentacion = Presentacion::create(['producto_id' => $producto->id, 'nombre' => 'Vaso 250 g', 'precio' => '15.75', 'stock' => '10.000', 'unidad' => 'unidad', 'activo' => true]);

        return [$productor, $producto, $presentacion];
    }

    public function test_panel_marks_internal_navigation_without_replacing_sidebar(): void
    {
        $this->actingAs($this->admin())->get('/dashboard')->assertOk()
            ->assertSee('data-panel-navigation', false)
            ->assertSee('id="app-sidebar"', false)
            ->assertSee('id="app-main"', false);
    }

    public function test_pos_accepts_only_whole_units(): void
    {
        $this->actingAs($this->admin())->postJson('/comercial/ventas', [
            'clave' => (string) Str::uuid(), 'metodo_pago' => 'efectivo',
            'items' => [['presentacion_id' => 1, 'cantidad' => '0.5']],
        ])->assertUnprocessable()->assertJsonValidationErrors('items.0.cantidad');
    }

    public function test_ingreso_uses_compact_producer_select_and_standard_presentation_price(): void
    {
        [$productor, , $presentacion] = $this->commercialData();

        $this->actingAs($this->admin())->get('/ingresos-productores/create')->assertOk()
            ->assertSee('id="producer-id"', false)
            ->assertSee('<option value="'.$productor->id.'"', false)
            ->assertSee('data-standard-price="'.$presentacion->precio.'"', false)
            ->assertDontSee('data-producer-row', false);
    }

    public function test_presentations_can_be_searched_by_product_content_and_unit(): void
    {
        $queso = Producto::create(['nombre' => 'Queso Artesanal', 'temperatura_minima' => 2, 'temperatura_maxima' => 8, 'activo' => true]);
        $yogur = Producto::create(['nombre' => 'Yogur Natural', 'temperatura_minima' => 2, 'temperatura_maxima' => 8, 'activo' => true]);

        Presentacion::create(['producto_id' => $queso->id, 'nombre' => 'Bloque familiar', 'contenido' => 1, 'unidad' => 'kg', 'precio' => 50, 'activo' => true]);
        Presentacion::create(['producto_id' => $queso->id, 'nombre' => 'Porción pequeña', 'contenido' => 500, 'unidad' => 'g', 'precio' => 28, 'activo' => true]);
        Presentacion::create(['producto_id' => $yogur->id, 'nombre' => 'Envase ajeno', 'contenido' => 500, 'unidad' => 'g', 'precio' => 20, 'activo' => true]);

        $this->actingAs($this->admin())->get('/presentaciones?q=Queso+500+g')
            ->assertOk()
            ->assertSee('Porción pequeña')
            ->assertDontSee('Bloque familiar')
            ->assertDontSee('Envase ajeno')
            ->assertSee('value="Queso 500 g"', false);
    }

    public function test_presentation_search_combines_product_flavor_content_and_plural_unit(): void
    {
        $yogurt = Producto::create(['nombre' => 'Yogurt', 'temperatura_minima' => 2, 'temperatura_maxima' => 8, 'activo' => true]);
        Presentacion::create(['producto_id' => $yogurt->id, 'nombre' => 'Botella familiar', 'sabor' => 'Frutilla', 'contenido' => 2, 'unidad' => 'L', 'precio' => 30, 'activo' => true]);
        Presentacion::create(['producto_id' => $yogurt->id, 'nombre' => 'Natural familiar', 'sabor' => 'Natural', 'contenido' => 2, 'unidad' => 'L', 'precio' => 30, 'activo' => true]);
        Presentacion::create(['producto_id' => $yogurt->id, 'nombre' => 'Frutilla pequeña', 'sabor' => 'Frutilla', 'contenido' => 1, 'unidad' => 'L', 'precio' => 20, 'activo' => true]);

        $this->actingAs($this->admin())->get('/presentaciones?q=yogurt+frutilla+2+litros')
            ->assertOk()->assertSee('Botella familiar')
            ->assertDontSee('Natural familiar')->assertDontSee('Frutilla pequeña');
    }

    public function test_supplier_list_can_be_searched_by_name_unit_or_phone(): void
    {
        Productor::create(['nombres' => 'Ana', 'primer_apellido' => 'Mendoza', 'nombre_unidad_productiva' => 'Finca Norte', 'telefono' => '70010020', 'activo' => true]);
        Productor::create(['nombres' => 'Bruno', 'primer_apellido' => 'Rojas', 'nombre_unidad_productiva' => 'Granja Sur', 'telefono' => '71122334', 'activo' => true]);

        $this->actingAs($this->admin())->get('/productores?buscar=Finca+Norte')
            ->assertOk()
            ->assertSee('Ana Mendoza')
            ->assertDontSee('Bruno Rojas')
            ->assertSee('value="Finca Norte"', false);
    }

    public function test_supplier_has_create_show_edit_and_archive_workflow(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get('/productores/create')->assertOk()->assertSee('Nuevo Proveedor');

        $this->post('/productores', [
            'nombres' => 'Carla', 'primer_apellido' => 'Flores', 'segundo_apellido' => 'Vega',
            'telefono' => '72233445', 'direccion' => 'Zona Central',
            'nombre_unidad_productiva' => 'Lácteos El Valle', 'activo' => '1',
        ])->assertRedirect(route('productores.index'));

        $proveedor = Productor::where('nombres', 'Carla')->firstOrFail();
        $this->get(route('productores.show', $proveedor))->assertOk()->assertSee('Carla Flores Vega')->assertSee('Lácteos El Valle');
        $this->get(route('productores.edit', $proveedor))->assertOk()->assertSee('Editar Proveedor');
        $this->put(route('productores.update', $proveedor), [
            'nombres' => 'Carla', 'primer_apellido' => 'Flores', 'segundo_apellido' => 'Vega',
            'telefono' => '72233445', 'direccion' => 'Zona Central',
            'nombre_unidad_productiva' => 'Lácteos Valle Alto', 'activo' => '1',
        ])->assertRedirect(route('productores.index'));
        $this->assertDatabaseHas('productores', ['id' => $proveedor->id, 'nombre_unidad_productiva' => 'Lácteos Valle Alto']);

        $this->delete(route('productores.destroy', $proveedor))->assertRedirect(route('productores.index'));
        $this->assertDatabaseHas('productores', ['id' => $proveedor->id, 'activo' => false, 'deleted_at' => null]);
    }

    public function test_only_admin_can_view_deleted_records_and_restore_them(): void
    {
        $admin = $this->admin();
        $usuario = User::factory()->create(['name' => 'Usuario Eliminado', 'rol' => 'Trabajador', 'activo' => false]);
        $producto = Producto::create(['nombre' => 'Producto Eliminado', 'temperatura_minima' => 2, 'temperatura_maxima' => 8, 'activo' => false]);
        $presentacion = Presentacion::create(['producto_id' => $producto->id, 'nombre' => 'Presentación Eliminada', 'precio' => 10, 'activo' => false]);
        $proveedor = Productor::create(['nombres' => 'Proveedor', 'primer_apellido' => 'Eliminado', 'activo' => true]);
        $proveedor->delete();

        $this->actingAs($admin)->get('/usuarios?estado=eliminados')->assertOk()->assertSee('Usuario Eliminado')->assertSee('Restablecer');
        $this->get('/productos?estado=eliminados')->assertOk()->assertSee('Producto Eliminado')->assertSee('Restablecer');
        $this->get('/presentaciones?estado=eliminados')->assertOk()->assertSee('Presentación Eliminada')->assertSee('Restablecer');
        $this->get('/productores?estado=eliminados')->assertOk()->assertSee('Proveedor Eliminado')->assertSee('Restablecer');

        $this->patch('/usuarios/'.$usuario->id.'/restablecer')->assertRedirect('/usuarios?estado=eliminados');
        $this->patch('/productos/'.$producto->id.'/restablecer')->assertRedirect('/productos?estado=eliminados');
        $this->patch('/presentaciones/'.$presentacion->id.'/restablecer')->assertRedirect('/presentaciones?estado=eliminados');
        $this->patch('/productores/'.$proveedor->id.'/restablecer')->assertRedirect('/productores?estado=eliminados');

        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'activo' => true]);
        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'activo' => true]);
        $this->assertDatabaseHas('presentaciones', ['id' => $presentacion->id, 'activo' => true]);
        $this->assertDatabaseHas('productores', ['id' => $proveedor->id, 'activo' => true, 'deleted_at' => null]);
    }

    public function test_inactive_and_legacy_deleted_suppliers_are_both_restorable(): void
    {
        $admin = $this->admin();
        $inactive = Productor::create(['nombres' => 'Ana', 'primer_apellido' => 'Rojas', 'activo' => false]);
        $legacy = Productor::create(['nombres' => 'Luis', 'primer_apellido' => 'Paz', 'activo' => true]);
        $legacy->delete();
        $this->actingAs($admin)->get('/productores?estado=eliminados')->assertOk()->assertSee('Ana Rojas')->assertSee('Luis Paz');
        $this->patch(route('productores.restore', $inactive->id));
        $this->patch(route('productores.restore', $legacy->id));
        $this->assertDatabaseHas('productores', ['id' => $inactive->id, 'activo' => true, 'deleted_at' => null]);
        $this->assertDatabaseHas('productores', ['id' => $legacy->id, 'activo' => true, 'deleted_at' => null]);
    }

    public function test_worker_cannot_view_or_restore_deleted_records(): void
    {
        $trabajador = User::factory()->create(['rol' => 'Trabajador', 'activo' => true]);
        $producto = Producto::create(['nombre' => 'Producto Oculto', 'temperatura_minima' => 2, 'temperatura_maxima' => 8, 'activo' => false]);
        $presentacion = Presentacion::create(['producto_id' => $producto->id, 'nombre' => 'Presentación Oculta', 'precio' => 10, 'activo' => false]);
        $proveedor = Productor::create(['nombres' => 'Proveedor', 'primer_apellido' => 'Oculto', 'activo' => true]);
        $proveedor->delete();

        $this->actingAs($trabajador)->get('/productos?estado=eliminados')->assertForbidden();
        $this->get('/presentaciones?estado=eliminados')->assertForbidden();
        $this->get('/productores?estado=eliminados')->assertForbidden();
        $this->get('/usuarios?estado=eliminados')->assertForbidden();
        $this->patch('/productos/'.$producto->id.'/restablecer')->assertForbidden();
        $this->patch('/presentaciones/'.$presentacion->id.'/restablecer')->assertForbidden();
        $this->patch('/productores/'.$proveedor->id.'/restablecer')->assertForbidden();
    }

    public function test_worker_does_not_see_deleted_record_controls(): void
    {
        $trabajador = User::factory()->create(['rol' => 'Trabajador', 'activo' => true]);

        $this->actingAs($trabajador)->get('/productos')->assertOk()->assertDontSee('Mostrar eliminados');
        $this->get('/presentaciones')->assertOk()->assertDontSee('Mostrar eliminados');
        $this->get('/productores')->assertOk()->assertDontSee('Mostrar eliminados');
    }

    public function test_deleted_presentation_requires_an_active_parent_product_to_be_restored(): void
    {
        $admin = $this->admin();
        $producto = Producto::create([
            'nombre' => 'Producto Padre Eliminado',
            'temperatura_minima' => 2,
            'temperatura_maxima' => 8,
            'activo' => false,
        ]);
        $presentacion = Presentacion::create([
            'producto_id' => $producto->id,
            'nombre' => 'Presentación Bloqueada',
            'precio' => 10,
            'activo' => false,
        ]);

        $this->actingAs($admin)
            ->patch('/presentaciones/'.$presentacion->id.'/restablecer')
            ->assertRedirect('/presentaciones?estado=eliminados')
            ->assertSessionHas('error', 'Restablezca primero el producto asociado a esta presentación.');

        $this->assertDatabaseHas('presentaciones', ['id' => $presentacion->id, 'activo' => false]);
    }

    public function test_ingreso_persists_the_standard_price_even_if_the_request_is_modified(): void
    {
        [$productor, , $presentacion] = $this->commercialData();

        $this->actingAs($this->admin())->postJson('/ingresos-productores', [
            'productor_id' => $productor->id, 'fecha_ingreso' => now()->toDateTimeString(),
            'items' => [[
                'presentacion_id' => $presentacion->id, 'cantidad_ingresada' => 2,
                'precio_acopio_unitario' => '10.00', 'precio_venta_unitario' => '99.99',
                'fecha_caducidad' => today()->addDays(10)->toDateString(),
            ]],
        ])->assertCreated();

        $this->assertDatabaseHas('ingresos_productores_items', [
            'presentacion_id' => $presentacion->id, 'precio_venta_unitario' => '15.75',
        ]);
    }

    public function test_user_management_cannot_assign_producer_role(): void
    {
        User::factory()->create(['name' => 'Productor heredado', 'email' => 'legacy@example.test', 'rol' => 'Productor', 'activo' => true]);
        $this->actingAs($this->admin())->get('/usuarios/create')->assertOk()->assertDontSee('<option value="Productor"', false);
        $this->get('/usuarios')->assertOk()->assertDontSee('Productor heredado');
        $this->post('/usuarios', ['name' => 'Productor incorrecto', 'email' => 'productor@example.test', 'password' => 'password123', 'password_confirmation' => 'password123', 'rol' => 'Productor'])
            ->assertSessionHasErrors('rol');
    }

    public function test_legacy_producer_user_cannot_log_in_as_internal_user(): void
    {
        User::factory()->create(['email' => 'legacy@example.test', 'password' => 'password123', 'rol' => 'Productor', 'activo' => true]);

        $this->post('/login', ['email' => 'legacy@example.test', 'password' => 'password123'])
            ->assertSessionHas('error');
        $this->assertGuest();
    }

    // ¿Qué hace? Verifica los filtros y detalles de reportes sin recargar la tabla con el nombre de la finca.
    public function test_sales_and_income_reports_support_product_and_producer_filters_and_details(): void
    {
        [$productor, , $presentacion] = $this->commercialData();
        $admin = $this->admin();
        $ingreso = IngresoProductor::create(['productor_id' => $productor->id, 'user_id' => $admin->id, 'fecha_ingreso' => now(), 'estado' => 'abierta']);
        $lote = IngresoProductorItem::create(['ingreso_productor_id' => $ingreso->id, 'presentacion_id' => $presentacion->id, 'cantidad_ingresada' => 10, 'cantidad_disponible' => 9, 'precio_acopio_unitario' => '10.00', 'precio_venta_unitario' => '15.75', 'fecha_recepcion' => today(), 'fecha_caducidad' => today()->addDays(10)]);
        $otroProducto = Producto::create(['nombre' => 'Queso Oculto', 'temperatura_minima' => 2, 'temperatura_maxima' => 8, 'activo' => true]);
        $otraPresentacion = Presentacion::create(['producto_id' => $otroProducto->id, 'nombre' => 'Bloque no filtrado', 'precio' => '20.00', 'stock' => '1.000', 'unidad' => 'unidad', 'activo' => true]);
        IngresoProductorItem::create(['ingreso_productor_id' => $ingreso->id, 'presentacion_id' => $otraPresentacion->id, 'cantidad_ingresada' => 1, 'cantidad_disponible' => 1, 'precio_acopio_unitario' => '12.00', 'precio_venta_unitario' => '20.00', 'fecha_recepcion' => today(), 'fecha_caducidad' => today()->addDays(10)]);
        $ticket = TicketVenta::create(['clave' => (string) Str::uuid(), 'payload_hash' => hash('sha256', 'test'), 'user_id' => $admin->id, 'metodo_pago' => 'efectivo', 'total' => '15.75']);
        Venta::create(['ingreso_productor_item_id' => $lote->id, 'ticket_venta_id' => $ticket->id, 'user_id' => $admin->id, 'cantidad_vendida' => '1.000', 'precio_unitario_venta' => '15.75', 'fecha_venta' => now()]);

        $this->actingAs($admin)->get('/reportes-comerciales?producto=Yogur&productor=Mendoza')->assertOk()
            ->assertSee('Vaso 250 g')->assertDontSee('Finca Norte')->assertSee('data-view-sale="'.$ticket->id.'"', false);
        $this->get('/reportes-comerciales/ventas/'.$ticket->id)->assertOk()
            ->assertJsonPath('ventas.0.lote.ingreso_productor.productor.id', $productor->id);
        $this->get('/reportes-ingresos?producto=Yogur&productor=Mendoza')->assertOk()
            ->assertSee('Vaso 250 g')->assertDontSee('Bloque no filtrado')->assertDontSee('Finca Norte')->assertSee($ingreso->fecha_ingreso->format('d/m/Y H:i'));
    }
}
