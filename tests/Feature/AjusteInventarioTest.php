<?php

namespace Tests\Feature;

use App\Models\IngresoProductor;
use App\Models\IngresoProductorItem;
use App\Models\Presentacion;
use App\Models\Producto;
use App\Models\Productor;
use App\Models\User;
use App\Services\AjusteInventarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AjusteInventarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_ajuste_sincroniza_lote_presentacion_y_auditoria(): void
    {
        [$admin, $lote, $presentacion] = $this->inventario(10);

        $ajuste = app(AjusteInventarioService::class)->crear([
            'lote_id' => $lote->id,
            'cantidad_nueva' => 7,
            'tipo_motivo' => 'conteo',
            'motivo' => 'Conteo físico de cierre',
        ], $admin);

        $this->assertSame(10, $ajuste->cantidad_anterior);
        $this->assertSame(7, $ajuste->cantidad_nueva);
        $this->assertSame(-3, $ajuste->diferencia);
        $this->assertSame(7, $lote->fresh()->cantidad_disponible);
        $this->assertSame(7, $presentacion->fresh()->stock);
        $this->assertDatabaseHas('movimientos_inventario', [
            'lote_id' => $lote->id,
            'tipo' => 'ajuste_inventario',
            'cantidad' => -3,
        ]);
    }

    public function test_anular_ajuste_aplica_movimiento_inverso_una_sola_vez(): void
    {
        [$admin, $lote, $presentacion] = $this->inventario(10);
        $servicio = app(AjusteInventarioService::class);
        $ajuste = $servicio->crear([
            'lote_id' => $lote->id,
            'cantidad_nueva' => 7,
            'tipo_motivo' => 'conteo',
            'motivo' => 'Conteo físico de cierre',
        ], $admin);

        $servicio->anular($ajuste, 'El conteo correspondía a otro lote', $admin);

        $this->assertSame('anulado', $ajuste->fresh()->estado);
        $this->assertSame(10, $lote->fresh()->cantidad_disponible);
        $this->assertSame(10, $presentacion->fresh()->stock);

        $this->expectException(ValidationException::class);
        $servicio->anular($ajuste->fresh(), 'Segundo intento inválido', $admin);
    }

    public function test_rechaza_un_ajuste_con_saldo_negativo(): void
    {
        [$admin, $lote] = $this->inventario(2);

        $this->expectException(ValidationException::class);
        app(AjusteInventarioService::class)->crear([
            'lote_id' => $lote->id,
            'cantidad_nueva' => -1,
            'tipo_motivo' => 'conteo',
            'motivo' => 'Valor inválido',
        ], $admin);
    }

    private function inventario(int $cantidad): array
    {
        $admin = User::factory()->create(['rol' => 'Administrador', 'activo' => true]);
        $productor = Productor::create(['nombres' => 'Ana', 'primer_apellido' => 'Mendoza', 'activo' => true]);
        $producto = Producto::create(['nombre' => 'Queso', 'temperatura_minima' => 2, 'temperatura_maxima' => 8, 'activo' => true]);
        $presentacion = Presentacion::create([
            'producto_id' => $producto->id, 'nombre' => 'Bloque', 'contenido' => 1,
            'unidad' => 'kg', 'precio' => 10, 'stock' => $cantidad, 'activo' => true,
        ]);
        $ingreso = IngresoProductor::create([
            'productor_id' => $productor->id, 'user_id' => $admin->id,
            'fecha_ingreso' => today(), 'estado' => 'abierta',
        ]);
        $lote = IngresoProductorItem::create([
            'ingreso_productor_id' => $ingreso->id, 'presentacion_id' => $presentacion->id,
            'cantidad_ingresada' => $cantidad, 'cantidad_disponible' => $cantidad,
            'precio_acopio_unitario' => 7, 'precio_venta_unitario' => 10,
            'fecha_recepcion' => today(), 'fecha_caducidad' => today()->addMonth(),
        ]);

        return [$admin, $lote, $presentacion];
    }
}
