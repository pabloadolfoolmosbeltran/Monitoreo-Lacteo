<?php

namespace Tests\Feature;

use App\Models\DescarteProducto;
use App\Models\IngresoProductor;
use App\Models\IngresoProductorItem;
use App\Models\Presentacion;
use App\Models\Producto;
use App\Models\Productor;
use App\Models\User;
use App\Services\DescarteProductoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DescarteProductoPagesTest extends TestCase
{
    use RefreshDatabase;

    // ¿Qué hace? Verifica que las notas del descarte sean visibles desde el listado y que el flujo pueda procesarse y exportarse.
    public function test_flujo_web_crea_lista_procesa_y_exporta_descarte(): void
    {
        [$admin, $lote, $productor] = $this->inventario();
        $this->actingAs($admin)->post(route('descartes-productos.store'), [
            'lote_id' => $lote->id, 'cantidad' => 2, 'tipo_motivo' => 'caducado', 'notas' => 'Control semanal',
        ])->assertRedirect(route('descartes-productos.index'));
        $descarte = DescarteProducto::firstOrFail();

        $this->get(route('descartes-productos.index', ['productor_id' => $productor->id]))
            ->assertOk()->assertSee('15,00')->assertSee('Control semanal');
        $this->get(route('descartes-productos.pdf', $descarte))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get(route('descartes-productos.csv'))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->patch(route('descartes-productos.procesar', $descarte))->assertRedirect(route('descartes-productos.show', $descarte));
        $this->assertSame('procesado', $descarte->fresh()->estado);
    }

    // ¿Qué hace? Verifica que un administrador pueda actualizar las notas y eliminar un descarte todavía pendiente.
    public function test_administrador_puede_editar_y_eliminar_un_descarte_pendiente(): void
    {
        [$admin, $lote] = $this->inventario();
        $descarte = DescarteProducto::create([
            'lote_id' => $lote->id,
            'creado_por' => $admin->id,
            'actualizado_por' => $admin->id,
            'cantidad' => 1,
            'fecha_caducidad' => $lote->fecha_caducidad,
            'costo_unitario' => '7.50',
            'perdida_total' => '7.50',
            'tipo_motivo' => 'caducado',
            'estado' => 'pendiente',
        ]);

        $this->actingAs($admin)->put(route('descartes-productos.update', $descarte), [
            'lote_id' => $lote->id,
            'cantidad' => 2,
            'tipo_motivo' => 'deterioro',
            'notas' => 'Validado por supervisión',
        ])->assertRedirect(route('descartes-productos.show', $descarte));

        $this->assertDatabaseHas('descartes_productos', [
            'id' => $descarte->id,
            'cantidad' => 2,
            'perdida_total' => '15.00',
            'notas' => 'Validado por supervisión',
        ]);
        $this->get(route('descartes-productos.index'))->assertOk()->assertSee('Validado por supervisión');
        $this->get(route('descartes-productos.show', $descarte))->assertOk()->assertSee('Validado por supervisión');
        $this->delete(route('descartes-productos.destroy', $descarte))
            ->assertRedirect(route('descartes-productos.index'));
        $this->assertDatabaseMissing('descartes_productos', ['id' => $descarte->id]);
        $this->assertSame(10, $lote->fresh()->cantidad_disponible);
    }

    public function test_edicion_conserva_visible_el_lote_original_aunque_se_agote(): void
    {
        [$admin, $lote] = $this->inventario();
        $descarte = app(DescarteProductoService::class)->crear([
            'lote_id' => $lote->id,
            'cantidad' => 1,
            'tipo_motivo' => 'caducado',
        ], $admin);
        $lote->update(['cantidad_disponible' => 0]);

        $this->actingAs($admin)->get(route('descartes-productos.edit', $descarte))
            ->assertOk()
            ->assertSee('<option value="'.$lote->id.'"', false);
    }

    private function inventario(): array
    {
        $admin = User::factory()->create(['rol' => 'Administrador', 'activo' => true]);
        $productor = Productor::create(['nombres' => 'Ana', 'primer_apellido' => 'Mendoza', 'activo' => true]);
        $producto = Producto::create(['nombre' => 'Queso', 'temperatura_minima' => 2, 'temperatura_maxima' => 8, 'activo' => true]);
        $presentacion = Presentacion::create(['producto_id' => $producto->id, 'nombre' => 'Bloque', 'contenido' => 1, 'unidad' => 'kg', 'precio' => 10, 'stock' => 10, 'activo' => true]);
        $ingreso = IngresoProductor::create(['productor_id' => $productor->id, 'user_id' => $admin->id, 'fecha_ingreso' => today(), 'estado' => 'abierta']);
        $lote = IngresoProductorItem::create(['ingreso_productor_id' => $ingreso->id, 'presentacion_id' => $presentacion->id, 'cantidad_ingresada' => 10, 'cantidad_disponible' => 10, 'precio_acopio_unitario' => '7.50', 'precio_venta_unitario' => 10, 'fecha_recepcion' => today(), 'fecha_caducidad' => today()->subDay()]);

        return [$admin, $lote, $productor];
    }
}
