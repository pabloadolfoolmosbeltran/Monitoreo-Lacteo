<?php

namespace Tests\Feature;

use App\Models\Presentacion;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_image_is_stored_in_its_public_directory(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['rol' => 'Administrador', 'activo' => true]);

        $this->actingAs($admin)->post('/productos', [
            'nombre' => 'Queso de prueba',
            'imagen_referencial' => UploadedFile::fake()->image('producto.png'),
            'cuajo_por_litro' => 0.2,
            'unidad_cuajo' => 'ml',
            'temperatura_minima' => 2,
            'temperatura_maxima' => 8,
            'temperatura_pasteurizacion' => 65,
            'activo' => 1,
        ])->assertRedirect('/productos');

        $path = Producto::where('nombre', 'Queso de prueba')->value('imagen_referencial');

        $this->assertStringStartsWith('productos/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_replacing_presentation_image_removes_only_the_previous_file(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['rol' => 'Administrador', 'activo' => true]);
        $producto = Producto::create([
            'nombre' => 'Yogur de prueba',
            'temperatura_minima' => 2,
            'temperatura_maxima' => 8,
            'temperatura_pasteurizacion' => 65,
            'activo' => true,
        ]);

        $this->actingAs($admin)->post(route('presentaciones.store'), [
            'producto_id' => $producto->id,
            'nombre' => 'Botella inicial',
            'precio' => 20,
            'imagen_comercial' => UploadedFile::fake()->image('inicial.png'),
        ])->assertRedirect(route('presentaciones.index'));

        $presentacion = Presentacion::where('nombre', 'Botella inicial')->firstOrFail();
        $previousPath = $presentacion->imagen_comercial;
        Storage::disk('public')->put('productos/no-relacionada.png', 'contenido');

        $this->actingAs($admin)->put(route('presentaciones.update', $presentacion), [
            'producto_id' => $producto->id,
            'nombre' => 'Botella actualizada',
            'precio' => 22,
            'imagen_comercial' => UploadedFile::fake()->image('actualizada.png'),
        ])->assertRedirect(route('presentaciones.index'));

        $newPath = $presentacion->fresh()->imagen_comercial;
        $this->assertStringStartsWith('presentaciones/', $newPath);
        Storage::disk('public')->assertExists($newPath);
        Storage::disk('public')->assertMissing($previousPath);
        Storage::disk('public')->assertExists('productos/no-relacionada.png');
    }
}
