<?php

namespace Tests\Feature;

use App\Models\IngresoProductor;
use App\Models\IngresoProductorItem;
use App\Models\Presentacion;
use App\Models\Producto;
use App\Models\Productor;
use App\Models\User;
use App\Services\DescarteProductoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DescarteProductoTest extends TestCase
{
    use RefreshDatabase;

    public function test_pendiente_calcula_perdida_y_procesar_descuenta_stock_una_vez(): void
    {
        [$admin, $lote, $presentacion] = $this->inventario();
        $servicio = app(DescarteProductoService::class);
        $descarte = $servicio->crear(['lote_id' => $lote->id, 'cantidad' => 3, 'tipo_motivo' => 'caducado', 'notas' => 'Control mensual'], $admin);

        $this->assertSame('7.50', $descarte->costo_unitario);
        $this->assertSame('22.50', $descarte->perdida_total);
        $this->assertSame(10, $lote->fresh()->cantidad_disponible);

        $servicio->procesar($descarte, $admin);
        $this->assertSame('procesado', $descarte->fresh()->estado);
        $this->assertSame(7, $lote->fresh()->cantidad_disponible);
        $this->assertSame(7, $presentacion->fresh()->stock);
        $this->assertDatabaseHas('movimientos_inventario', ['lote_id' => $lote->id, 'tipo' => 'descarte', 'cantidad' => -3]);

        $this->expectException(ValidationException::class);
        $servicio->procesar($descarte->fresh(), $admin);
    }

    public function test_no_procesa_mas_unidades_que_el_saldo(): void
    {
        [$admin, $lote] = $this->inventario();
        $servicio = app(DescarteProductoService::class);
        $descarte = $servicio->crear(['lote_id' => $lote->id, 'cantidad' => 11, 'tipo_motivo' => 'dañado'], $admin);

        $this->expectException(ValidationException::class);
        $servicio->procesar($descarte, $admin);
    }

    public function test_no_procesa_si_el_stock_general_es_inconsistente(): void
    {
        [$admin, $lote] = $this->inventario();
        $descarte = app(DescarteProductoService::class)->crear([
            'lote_id' => $lote->id,
            'cantidad' => 3,
            'tipo_motivo' => 'dañado',
        ], $admin);
        $lote->presentacion()->update(['stock' => 2]);

        $this->expectException(ValidationException::class);
        app(DescarteProductoService::class)->procesar($descarte, $admin);
    }

    private function inventario(): array
    {
        $admin = User::factory()->create(['rol' => 'Administrador', 'activo' => true]);
        $productor = Productor::create(['nombres' => 'Ana', 'primer_apellido' => 'Mendoza', 'activo' => true]);
        $producto = Producto::create(['nombre' => 'Queso', 'temperatura_minima' => 2, 'temperatura_maxima' => 8, 'activo' => true]);
        $presentacion = Presentacion::create(['producto_id' => $producto->id, 'nombre' => 'Bloque', 'contenido' => 1, 'unidad' => 'kg', 'precio' => 10, 'stock' => 10, 'activo' => true]);
        $ingreso = IngresoProductor::create(['productor_id' => $productor->id, 'user_id' => $admin->id, 'fecha_ingreso' => today(), 'estado' => 'abierta']);
        $lote = IngresoProductorItem::create(['ingreso_productor_id' => $ingreso->id, 'presentacion_id' => $presentacion->id, 'cantidad_ingresada' => 10, 'cantidad_disponible' => 10, 'precio_acopio_unitario' => '7.50', 'precio_venta_unitario' => 10, 'fecha_recepcion' => today(), 'fecha_caducidad' => today()->subDay()]);

        return [$admin, $lote, $presentacion];
    }
}
