<?php

namespace Tests\Feature;

use App\Models\AjusteInventario;
use App\Models\IngresoProductor;
use App\Models\IngresoProductorItem;
use App\Models\Presentacion;
use App\Models\Producto;
use App\Models\Productor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AjusteInventarioPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_trabajador_puede_listar_y_crear_ajuste(): void
    {
        [$trabajador, $lote] = $this->inventario('Trabajador');

        $this->actingAs($trabajador)->get(route('ajustes-inventario.index'))->assertOk();
        $this->post(route('ajustes-inventario.store'), [
            'lote_id' => $lote->id, 'cantidad_nueva' => 4,
            'tipo_motivo' => 'conteo', 'motivo' => 'Conteo físico validado',
        ])->assertRedirect(route('ajustes-inventario.index'));

        $this->assertDatabaseHas('ajustes_inventario', ['lote_id' => $lote->id, 'cantidad_nueva' => 4]);
    }

    public function test_solo_administrador_puede_anular(): void
    {
        [$trabajador, $lote] = $this->inventario('Trabajador');
        $this->actingAs($trabajador)->post(route('ajustes-inventario.store'), [
            'lote_id' => $lote->id, 'cantidad_nueva' => 4,
            'tipo_motivo' => 'conteo', 'motivo' => 'Conteo físico validado',
        ]);
        $ajuste = AjusteInventario::firstOrFail();

        $this->patch(route('ajustes-inventario.anular', $ajuste), [
            'motivo_anulacion' => 'No autorizado',
        ])->assertForbidden();
    }

    public function test_administrador_puede_corregir_un_ajuste_activo_sin_perder_auditoria(): void
    {
        [$administrador, $lote] = $this->inventario('Administrador');
        $this->actingAs($administrador)->post(route('ajustes-inventario.store'), [
            'lote_id' => $lote->id, 'cantidad_nueva' => 3,
            'tipo_motivo' => 'conteo', 'motivo' => 'Conteo inicial',
        ]);
        $ajuste = AjusteInventario::firstOrFail();

        $this->get(route('ajustes-inventario.edit', $ajuste))->assertOk();
        $this->put(route('ajustes-inventario.update', $ajuste), [
            'cantidad_nueva' => 4,
            'tipo_motivo' => 'otro',
            'motivo' => 'Correccion supervisada',
        ])->assertRedirect(route('ajustes-inventario.show', $ajuste));

        $this->assertDatabaseHas('ajustes_inventario', [
            'id' => $ajuste->id,
            'cantidad_anterior' => 5,
            'cantidad_nueva' => 4,
            'diferencia' => -1,
            'actualizado_por' => $administrador->id,
        ]);
        $this->assertDatabaseHas('ingresos_productores_items', [
            'id' => $lote->id,
            'cantidad_disponible' => 4,
        ]);
        $this->assertDatabaseHas('presentaciones', [
            'id' => $lote->presentacion_id,
            'stock' => 4,
        ]);
    }

    private function inventario(string $rol): array
    {
        $usuario = User::factory()->create(['rol' => $rol, 'activo' => true]);
        $productor = Productor::create(['nombres' => 'Ana', 'primer_apellido' => 'Mendoza', 'activo' => true]);
        $producto = Producto::create(['nombre' => 'Queso', 'temperatura_minima' => 2, 'temperatura_maxima' => 8, 'activo' => true]);
        $presentacion = Presentacion::create(['producto_id' => $producto->id, 'nombre' => 'Bloque', 'contenido' => 1, 'unidad' => 'kg', 'precio' => 10, 'stock' => 5, 'activo' => true]);
        $ingreso = IngresoProductor::create(['productor_id' => $productor->id, 'user_id' => $usuario->id, 'fecha_ingreso' => today(), 'estado' => 'abierta']);
        $lote = IngresoProductorItem::create(['ingreso_productor_id' => $ingreso->id, 'presentacion_id' => $presentacion->id, 'cantidad_ingresada' => 5, 'cantidad_disponible' => 5, 'precio_acopio_unitario' => 7, 'precio_venta_unitario' => 10, 'fecha_recepcion' => today(), 'fecha_caducidad' => today()->addMonth()]);

        return [$usuario, $lote];
    }
}
