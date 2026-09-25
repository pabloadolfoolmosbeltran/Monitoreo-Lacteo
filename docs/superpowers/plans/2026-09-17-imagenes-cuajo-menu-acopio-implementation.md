# Imágenes, recomendación de cuajo y menú de acopio Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Publicar correctamente las imágenes, convertir el cuajo en una recomendación no bloqueante y organizar el menú comercial en grupos desplegables.

**Architecture:** Se conserva el disco público estándar de Laravel y se repara su enlace web. La existencia de cuajo desaparece del esquema y del dominio; un módulo puro calcula recomendaciones que consumen la vista y el controlador. El sidebar usa Bootstrap Collapse y abre el grupo correspondiente según la ruta activa.

**Tech Stack:** Laravel 12, PHP 8.2+, Blade, Eloquent, Laravel Filesystem, Bootstrap 5, Vite y Node Test Runner.

**Spec:** `docs/superpowers/specs/2026-09-17-imagenes-cuajo-menu-acopio-design.md`

## Global Constraints

- Conservar el disco `public` con raíz `storage/app/public` y publicar mediante `public/storage`.
- Usar directorios relativos `productos` y `presentaciones`; no cambiar las rutas ya almacenadas.
- Eliminar físicamente `productos.stock_cuajo` y no redirigir esa responsabilidad a otro campo.
- Conservar `tipo_cuajo`, `cuajo_por_litro` y `unidad_cuajo`.
- Admitir `ml`, `g`, `kg`, `gotas` y `pastilla`.
- No bloquear ni descontar cuajo al iniciar producción.
- Mantener sin cambios el stock comercial, los lotes, ventas, temperaturas y control ESP32.
- Usar Bootstrap Collapse existente; no agregar dependencias frontend.

---

### Task 1: Publicación y almacenamiento dedicado de imágenes

**Files:**
- Create: `tests/Feature/ImageStorageTest.php`
- Modify: `app/Http/Controllers/ProductoController.php`
- Modify: `app/Http/Controllers/PresentacionController.php`
- Environment: `public/storage`
- Environment: `storage/app/public/productos`
- Environment: `storage/app/public/presentaciones`

**Interfaces:**
- Consumes: `Storage::disk('public')` y rutas relativas almacenadas en los modelos.
- Produces: rutas `productos/<archivo>` y `presentaciones/<archivo>` accesibles desde `/storage/<ruta>`.

- [ ] **Step 1: Escribir pruebas de subida y reemplazo**

Crear `ImageStorageTest` con `RefreshDatabase`, `Storage::fake('public')` y `UploadedFile::fake()->image(...)`. La primera prueba debe publicar un producto y comprobar:

```php
$response = $this->actingAs($admin)->post('/productos', [
    'nombre' => 'Queso de prueba',
    'imagen_referencial' => UploadedFile::fake()->image('producto.png'),
    'cuajo_por_litro' => 0.2,
    'unidad_cuajo' => 'ml',
    'temperatura_minima' => 2,
    'temperatura_maxima' => 8,
    'temperatura_pasteurizacion' => 65,
    'activo' => 1,
]);

$response->assertRedirect('/productos');
$path = Producto::where('nombre', 'Queso de prueba')->value('imagen_referencial');
$this->assertStringStartsWith('productos/', $path);
Storage::disk('public')->assertExists($path);
```

La segunda prueba debe crear una presentación con imagen, actualizarla con otra imagen y afirmar que la ruta nueva empieza por `presentaciones/`, existe y la anterior ya no existe.

- [ ] **Step 2: Ejecutar las pruebas de almacenamiento**

Run: `php artisan test tests/Feature/ImageStorageTest.php`

Expected: las pruebas describen el contrato actual; cualquier fallo debe señalar una ruta, validación o eliminación incorrecta antes de tocar los controladores.

- [ ] **Step 3: Centralizar las constantes de directorio**

En cada controlador declarar una constante privada y usarla para almacenar:

```php
private const IMAGE_DIRECTORY = 'productos';
```

```php
return $request->file('imagen_referencial')->store(self::IMAGE_DIRECTORY, 'public');
```

En `PresentacionController`, usar `private const IMAGE_DIRECTORY = 'presentaciones';` tanto en creación como en reemplazo. Conservar la eliminación de la imagen anterior únicamente cuando llega un archivo nuevo.

- [ ] **Step 4: Reparar el enlace público de forma segura**

Verificar que `public/storage` no contiene archivos. Crear, si faltan, `storage/app/public/productos` y `storage/app/public/presentaciones`. Eliminar únicamente la carpeta vacía `public/storage` y ejecutar:

```powershell
php artisan storage:link
```

Comprobar que `Resolve-Path public/storage` y `Resolve-Path storage/app/public` apuntan al mismo contenido y que las dos imágenes existentes responden desde `/storage/productos/...` y `/storage/presentaciones/...`.

- [ ] **Step 5: Ejecutar pruebas y registrar el cambio**

Run: `php artisan test tests/Feature/ImageStorageTest.php`

Expected: PASS.

Commit only files owned by this task if they do not include pre-existing user work:

```bash
git add tests/Feature/ImageStorageTest.php app/Http/Controllers/ProductoController.php app/Http/Controllers/PresentacionController.php
git commit -m "fix: publicar imagenes comerciales por directorio"
```

---

### Task 2: Eliminar el stock de cuajo y mostrar referencias por volumen

**Files:**
- Create: `database/migrations/2026_09_17_000002_drop_stock_cuajo_from_productos.php`
- Create: `tests/Feature/RennetRecommendationTest.php`
- Modify: `app/Models/Producto.php`
- Modify: `app/Http/Controllers/ProductoController.php`
- Modify: `resources/views/productos/form.blade.php`
- Modify: `resources/views/productos/index.blade.php`

**Interfaces:**
- Consumes: `Producto::cuajo_por_litro` como decimal y `Producto::unidad_cuajo` como unidad.
- Produces: esquema sin `stock_cuajo` y referencias literales para 1, 5 y 10 litros.

- [ ] **Step 1: Escribir pruebas que fallen por el stock actual**

En `RennetRecommendationTest` agregar:

```php
public function test_final_schema_removes_rennet_stock(): void
{
    $this->artisan('migrate:fresh')->assertExitCode(0);
    $this->assertFalse(Schema::hasColumn('productos', 'stock_cuajo'));
}
```

Agregar otra prueba autenticada que cree un producto con `cuajo_por_litro = 0.2`, unidad `g`, solicite `/productos` y afirme que aparecen `1 L: 0,20 g`, `5 L: 1,00 g` y `10 L: 2,00 g`, y que no aparecen `Stock del cuajo` ni `Stock Actual de Cuajo`.

- [ ] **Step 2: Confirmar RED**

Run: `php artisan test tests/Feature/RennetRecommendationTest.php`

Expected: FAIL porque `stock_cuajo` todavía existe y la vista aún muestra inventario.

- [ ] **Step 3: Crear la migración terminal**

Implementar:

```php
public function up(): void
{
    if (Schema::hasColumn('productos', 'stock_cuajo')) {
        Schema::table('productos', fn (Blueprint $table) => $table->dropColumn('stock_cuajo'));
    }
}

public function down(): void
{
    if (! Schema::hasColumn('productos', 'stock_cuajo')) {
        Schema::table('productos', fn (Blueprint $table) => $table->decimal('stock_cuajo', 10, 2)->nullable()->after('unidad_cuajo'));
    }
}
```

- [ ] **Step 4: Retirar el campo del dominio y validar unidades**

Eliminar `stock_cuajo` de `$fillable`, `$casts`, reglas, valores predeterminados, formulario y tabla. Cambiar la regla de unidad a:

```php
'unidad_cuajo' => 'nullable|string|in:ml,g,kg,gotas,pastilla',
```

Añadir la opción `kg` al formulario y conservar `pastilla`.

- [ ] **Step 5: Renderizar las tres referencias**

En la columna de dosis del listado, calcular con el valor configurado:

```blade
@foreach([1, 5, 10] as $litros)
    <span>{{ $litros }} L: {{ number_format($producto->cuajo_por_litro * $litros, 2, ',', '.') }} {{ $producto->unidad_cuajo }}</span>
@endforeach
```

Mostrar estas referencias solo cuando exista una dosis; de lo contrario mostrar `No configurado`.

- [ ] **Step 6: Confirmar GREEN**

Run: `php artisan test tests/Feature/RennetRecommendationTest.php`

Expected: PASS.

Commit only isolated files when safe:

```bash
git add database/migrations/2026_09_17_000002_drop_stock_cuajo_from_productos.php tests/Feature/RennetRecommendationTest.php app/Models/Producto.php app/Http/Controllers/ProductoController.php resources/views/productos/form.blade.php resources/views/productos/index.blade.php
git commit -m "refactor: convertir cuajo en recomendacion"
```

---

### Task 3: Iniciar producción sin bloquear y calcular recomendación en servidor y navegador

**Files:**
- Create: `resources/js/rennet-recommendation.js`
- Create: `tests/js/rennet-recommendation.test.mjs`
- Modify: `app/Http/Controllers/ProduccionController.php`
- Modify: `resources/js/produccion.js`
- Modify: `resources/views/produccion/index.blade.php`
- Modify: `resources/views/dashboard/index.blade.php`
- Modify: `resources/views/reportes/show.blade.php`
- Modify: `resources/views/reportes/pdf.blade.php`

**Interfaces:**
- Produces: `requiredRennet(liters: number, perLiter: number): number` y `rennetReferences(perLiter: number): Array<{liters:number, amount:number}>`.
- Consumes: Task 2 garantiza que el producto no tiene `stock_cuajo` y que la unidad configurada sigue disponible.

- [ ] **Step 1: Escribir la prueba JavaScript del cálculo puro**

```js
import test from 'node:test';
import assert from 'node:assert/strict';
import { requiredRennet, rennetReferences } from '../../resources/js/rennet-recommendation.js';

test('calculates live and reference rennet amounts', () => {
    assert.equal(requiredRennet(12, 0.25), 3);
    assert.deepEqual(rennetReferences(0.25), [
        { liters: 1, amount: 0.25 },
        { liters: 5, amount: 1.25 },
        { liters: 10, amount: 2.5 },
    ]);
});
```

- [ ] **Step 2: Confirmar RED de JavaScript**

Run: `node --test tests/js/rennet-recommendation.test.mjs`

Expected: FAIL porque el módulo todavía no existe.

- [ ] **Step 3: Implementar el módulo mínimo**

Normalizar entradas no finitas o negativas a cero y redondear a dos decimales:

```js
export function requiredRennet(liters, perLiter) {
    const safeLiters = Number.isFinite(Number(liters)) && Number(liters) > 0 ? Number(liters) : 0;
    const safeDose = Number.isFinite(Number(perLiter)) && Number(perLiter) > 0 ? Number(perLiter) : 0;
    return Math.round(safeLiters * safeDose * 100) / 100;
}

export function rennetReferences(perLiter) {
    return [1, 5, 10].map(liters => ({ liters, amount: requiredRennet(liters, perLiter) }));
}
```

- [ ] **Step 4: Escribir la prueba HTTP no bloqueante**

En `RennetRecommendationTest`, crear usuario, dispositivo y producto con dosis `0.25 g/L`. Enviar 12 litros a `/produccion/iniciar` y afirmar redirección sin errores, una producción con `cantidad_cuajo = 3.00`, y que el producto no se modifica. Esta prueba debe fallar contra el controlador actual porque intenta validar y descontar `stock_cuajo`.

- [ ] **Step 5: Confirmar RED de Laravel**

Run: `php artisan test tests/Feature/RennetRecommendationTest.php --filter=production`

Expected: FAIL por la dependencia de `stock_cuajo`.

- [ ] **Step 6: Retirar bloqueo y descuento del controlador**

Eliminar `validarStockInsumo()`, el `decrement()`, el bloqueo pesimista innecesario y cualquier mensaje de descuento. Conservar `calcularInsumoRequerido()` como fuente de verdad del servidor. Cambiar el detalle de bitácora a:

```php
return " Recomendación de insumo: {$insumoTotalRequerido} {$unidad}.";
```

- [ ] **Step 7: Simplificar la pantalla dinámica**

Importar `requiredRennet` y `rennetReferences` en `produccion.js`. Eliminar lecturas de `stock_cuajo`, stock restante, alerta de insuficiencia y deshabilitación del botón. Renderizar la cantidad para los litros ingresados y una lista de referencias 1, 5 y 10 L. En Blade, sustituir “Aplicado y Descontado” por “Recomendación de cuajo” y eliminar todos los nodos `stock-actual-val`, `stock-restante-val` y `alerta-stock-insuficiente`.

Actualizar dashboard y reportes para mostrar la unidad real del producto en vez del texto fijo `ml` o `ml / g`.

- [ ] **Step 8: Confirmar GREEN**

Run: `node --test tests/js/*.test.mjs`

Run: `php artisan test tests/Feature/RennetRecommendationTest.php`

Expected: todas las pruebas pasan.

Commit only isolated files when safe:

```bash
git add resources/js/rennet-recommendation.js tests/js/rennet-recommendation.test.mjs app/Http/Controllers/ProduccionController.php resources/js/produccion.js resources/views/produccion/index.blade.php resources/views/dashboard/index.blade.php resources/views/reportes/show.blade.php resources/views/reportes/pdf.blade.php tests/Feature/RennetRecommendationTest.php
git commit -m "feat: recomendar cuajo sin bloquear produccion"
```

---

### Task 4: Agrupar Inventario y Reportes en el sidebar

**Files:**
- Modify: `resources/views/partials/sidebar.blade.php`
- Modify: `resources/css/app.css`
- Modify: `tests/Feature/CommercialPagesTest.php`

**Interfaces:**
- Consumes: Bootstrap Collapse cargado desde `resources/js/bootstrap.js`.
- Produces: `#inventory-menu` y `#commercial-reports-menu`, abiertos según `request()->routeIs(...)`.

- [ ] **Step 1: Escribir prueba de estructura y ruta activa**

Agregar una prueba que solicite `/inventario-general` y compruebe:

```php
->assertSee('data-bs-target="#inventory-menu"', false)
->assertSee('id="inventory-menu" class="collapse show"', false)
->assertSee('href="'.route('ingresos-productores.index').'"', false)
->assertSee('href="'.route('inventario.index').'"', false)
->assertSee('href="'.route('inventario-general.index').'"', false);
```

Solicitar `/reportes-ingresos` y comprobar el grupo `#commercial-reports-menu` abierto y sus dos enlaces. Afirmar que `Ventas` y `Proveedores` siguen siendo enlaces directos.

- [ ] **Step 2: Confirmar RED**

Run: `php artisan test tests/Feature/CommercialPagesTest.php --filter=collapsible`

Expected: FAIL porque todavía no existen los grupos.

- [ ] **Step 3: Implementar los grupos Bootstrap**

Calcular al principio del bloque comercial:

```blade
@php
    $inventoryOpen = request()->routeIs('ingresos-productores.*', 'inventario.*', 'inventario-general.*');
    $reportsOpen = request()->routeIs('reportes-comerciales.*', 'reportes-ingresos.*');
@endphp
```

Usar botones con `data-bs-toggle="collapse"`, `aria-expanded`, `aria-controls` y un icono chevron. Los enlaces finales conservan `nav-link` y sus condiciones activas actuales.

- [ ] **Step 4: Añadir estilos contenidos**

Agregar `.sidebar-group-toggle`, `.sidebar-submenu` y `.sidebar-chevron`. Indentar enlaces secundarios, reducir ligeramente su tamaño y rotar el chevron cuando `aria-expanded="true"`. Reutilizar las variables actuales del sidebar.

- [ ] **Step 5: Confirmar GREEN**

Run: `php artisan test tests/Feature/CommercialPagesTest.php`

Expected: PASS.

Commit only isolated files when safe:

```bash
git add resources/views/partials/sidebar.blade.php resources/css/app.css tests/Feature/CommercialPagesTest.php
git commit -m "feat: agrupar inventario y reportes en sidebar"
```

---

### Task 5: Aplicar migración y verificación integral

**Files:**
- Verify: all files modified by Tasks 1-4
- Verify: `public/storage`

**Interfaces:**
- Consumes: todos los entregables anteriores.
- Produces: aplicación migrada, compilada y verificada visualmente.

- [ ] **Step 1: Aplicar la migración de cuajo**

Run: `php artisan migrate --force`

Expected: la nueva migración elimina `productos.stock_cuajo`. Si sigue pendiente la migración anterior de cantidades enteras por los registros fraccionarios ya identificados, ejecutar únicamente la ruta de la migración de cuajo y documentar el bloqueo separado sin alterar esos datos.

- [ ] **Step 2: Ejecutar formato y pruebas completas**

Run: `vendor/bin/pint --test app/Http/Controllers/ProductoController.php app/Http/Controllers/PresentacionController.php app/Http/Controllers/ProduccionController.php app/Models/Producto.php database/migrations/2026_09_17_000002_drop_stock_cuajo_from_productos.php tests/Feature/ImageStorageTest.php tests/Feature/RennetRecommendationTest.php tests/Feature/CommercialPagesTest.php`

Run: `php artisan test`

Run: `node --test tests/js/*.test.mjs`

Run: `npm run build`

Run: `git diff --check`

Expected: Pint pasa en los archivos tocados, todas las pruebas pasan, Vite compila y no hay errores de espacios.

- [ ] **Step 3: Verificar imágenes por HTTP**

Levantar el servidor local y abrir las dos URLs existentes bajo `/storage/productos/` y `/storage/presentaciones/`. Esperar HTTP 200 y contenido de imagen.

- [ ] **Step 4: Verificar visualmente producción y sidebar**

Acceder con un usuario temporal de QA, comprobar que el producto muestra referencias 1/5/10 L, que producción calcula la dosis para un valor libre sin alertas ni botón deshabilitado y que los grupos Inventario y Reportes abren, cierran y permanecen abiertos en sus rutas. Eliminar el usuario temporal al terminar.

- [ ] **Step 5: Revisar alcance final**

Confirmar con búsquedas que `stock_cuajo` solo aparece en migraciones históricas y la nueva migración de eliminación; que no quedan textos “stock insuficiente” asociados al cuajo; y que no cambiaron las reglas de stock comercial o control ESP32.
