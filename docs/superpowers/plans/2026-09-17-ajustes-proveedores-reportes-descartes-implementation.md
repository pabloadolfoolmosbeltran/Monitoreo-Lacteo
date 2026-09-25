# Ajustes, proveedores, reportes y descartes — Plan de implementación

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Eliminar liquidaciones e incorporar ajustes auditados, ventas por proveedor, ranking de productos y descartes sin crear una segunda fuente de inventario.

**Architecture:** Los nuevos módulos operan sobre `ingresos_productores_items` y sincronizan `presentaciones.stock` dentro de transacciones. Controladores y Form Requests se separan por caso de uso; servicios de dominio concentran bloqueos, validaciones y efectos de stock.

**Tech Stack:** Laravel 12, PHP 8.2, Eloquent, Blade, Chart.js, DomPDF, PHPUnit, JavaScript ES modules y Vite.

**Spec:** `docs/superpowers/specs/2026-09-17-ajustes-proveedores-reportes-descartes-design.md`

## Global Constraints

- Tablas y campos de dominio nuevos deben estar en español.
- Cantidades de inventario son enteros; importes son decimales exactos con `App\Support\Decimal`.
- `ingresos_productores_items.cantidad_disponible` es la fuente de verdad y `presentaciones.stock` su agregado sincronizado.
- No modificar autenticación, métodos de pago, ventas históricas, producción ni monitoreo.
- Toda escritura de stock usa transacción y bloquea presentación antes del lote.
- No confiar en precio, proveedor, costo ni disponibilidad enviados por el navegador.

---

### Task 1: Eliminar liquidaciones y normalizar entradas

**Files:**
- Create: `database/migrations/2026_09_17_000003_remove_liquidaciones_completely.php`
- Modify: `app/Models/IngresoProductor.php`
- Delete: `app/Models/Liquidacion.php`
- Modify: `app/Services/ComercialService.php`
- Modify: `app/Http/Controllers/ComercialController.php`
- Modify: `app/Services/InventarioConsulta.php`
- Modify: `app/Http/Requests/FiltroInventarioRequest.php`
- Modify: `resources/views/ingresos/show.blade.php`
- Modify: `resources/views/inventario/index.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/LiquidacionesRemovalTest.php`

**Interfaces:**
- Produces: entradas con estados `abierta` o `cerrada`; ninguna ruta o relación de liquidación.
- Preserves: entradas, lotes, ventas y movimientos existentes.

- [ ] **Step 1: Write the failing test**

```php
public function test_migration_drops_liquidaciones_and_normalizes_entries(): void
{
    $this->assertFalse(Schema::hasTable('liquidaciones'));
    $this->assertSame(0, DB::table('ingresos_productores')->where('estado', 'liquidada')->count());
}

public function test_liquidation_endpoint_no_longer_exists(): void
{
    $this->post('/ingresos-productores/1/liquidacion')->assertNotFound();
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/LiquidacionesRemovalTest.php`
Expected: FAIL porque existe la tabla/ruta o faltan los cambios.

- [ ] **Step 3: Write minimal implementation**

La migración calcula cada estado antes de eliminar datos:

```php
DB::table('ingresos_productores')->where('estado', 'liquidada')->orderBy('id')->each(function ($ingreso): void {
    $stock = DB::table('ingresos_productores_items')->where('ingreso_productor_id', $ingreso->id)->sum('cantidad_disponible');
    DB::table('ingresos_productores')->where('id', $ingreso->id)->update(['estado' => $stock > 0 ? 'abierta' : 'cerrada']);
});
Schema::dropIfExists('liquidaciones');
```

Retirar método, relación, ruta, filtros y UI; conservar el resto del flujo de entradas.

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Feature/LiquidacionesRemovalTest.php tests/Feature/AcopioComercialTest.php tests/Feature/InventarioPorLoteTest.php`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_09_17_000003_remove_liquidaciones_completely.php app/Models app/Services app/Http resources/views/ingresos resources/views/inventario routes/web.php tests/Feature/LiquidacionesRemovalTest.php
git commit -m "refactor: eliminar liquidaciones completamente"
```

### Task 2: Dominio de ajustes de inventario

**Files:**
- Create: `database/migrations/2026_09_17_000004_create_ajustes_inventario_table.php`
- Create: `app/Models/AjusteInventario.php`
- Create: `app/Policies/AjusteInventarioPolicy.php`
- Create: `app/Http/Requests/GuardarAjusteInventarioRequest.php`
- Create: `app/Http/Requests/ActualizarAjusteInventarioRequest.php`
- Create: `app/Http/Requests/AnularAjusteInventarioRequest.php`
- Create: `app/Services/AjusteInventarioService.php`
- Modify: `app/Models/IngresoProductorItem.php`
- Modify: `app/Models/User.php`
- Test: `tests/Feature/AjusteInventarioTest.php`

**Interfaces:**
- Produces: `AjusteInventarioService::crear(array $datos, User $usuario): AjusteInventario`.
- Produces: `actualizar(AjusteInventario $ajuste, array $datos, User $usuario): AjusteInventario`.
- Produces: `anular(AjusteInventario $ajuste, string $motivo, User $usuario): AjusteInventario`.

- [ ] **Step 1: Write failing transaction and authorization tests**

```php
$ajuste = app(AjusteInventarioService::class)->crear([
    'lote_id' => $lote->id, 'cantidad_nueva' => 7,
    'tipo_motivo' => 'conteo', 'motivo' => 'Conteo físico',
], $trabajador);
$this->assertSame(10, $ajuste->cantidad_anterior);
$this->assertSame(-3, $ajuste->diferencia);
$this->assertSame(7, $lote->fresh()->cantidad_disponible);
$this->assertSame(7, $presentacion->fresh()->stock);
```

Añadir casos de saldo negativo, edición, anulación inversa, doble anulación y permisos.

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/AjusteInventarioTest.php`
Expected: FAIL porque tabla, modelo y servicio no existen.

- [ ] **Step 3: Implement migration, model, requests, policy and service**

Crear `ajustes_inventario` con `lote_id`, `creado_por`, `actualizado_por`, cantidades, motivo, estado y campos de anulación. El servicio bloquea presentación, cabecera y lote; actualiza ambos saldos y registra `movimientos_inventario`.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/AjusteInventarioTest.php`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_09_17_000004_create_ajustes_inventario_table.php app/Models/AjusteInventario.php app/Policies app/Http/Requests app/Services/AjusteInventarioService.php tests/Feature/AjusteInventarioTest.php
git commit -m "feat: agregar ajustes auditados de inventario"
```

### Task 3: CRUD e interfaz de ajustes

**Files:**
- Create: `app/Http/Controllers/AjusteInventarioController.php`
- Create: `resources/views/ajustes-inventario/index.blade.php`
- Create: `resources/views/ajustes-inventario/create.blade.php`
- Create: `resources/views/ajustes-inventario/edit.blade.php`
- Create: `resources/views/ajustes-inventario/show.blade.php`
- Create: `resources/views/ajustes-inventario/_form.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/partials/sidebar.blade.php`
- Test: `tests/Feature/AjusteInventarioPagesTest.php`

**Interfaces:**
- Consumes: métodos de `AjusteInventarioService` de Task 2.
- Produces: rutas `ajustes-inventario.*` y acción `ajustes-inventario.anular`.

- [ ] **Step 1: Write failing page and permission tests**

```php
$this->actingAs($trabajador)->get(route('ajustes-inventario.index'))->assertOk();
$this->actingAs($trabajador)->patch(route('ajustes-inventario.anular', $ajuste), [
    'motivo_anulacion' => 'Corrección administrativa',
])->assertForbidden();
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/AjusteInventarioPagesTest.php`
Expected: FAIL con rutas no definidas.

- [ ] **Step 3: Implement resource controller, routes and Blade views**

La lista muestra antes, después, diferencia, motivo, estado, responsable y fecha. “Eliminar” se rotula “Anular” y solicita motivo; nunca usa `DELETE` físico.

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Feature/AjusteInventarioPagesTest.php tests/Feature/CommercialPagesTest.php`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/AjusteInventarioController.php resources/views/ajustes-inventario routes/web.php resources/views/partials/sidebar.blade.php tests/Feature/AjusteInventarioPagesTest.php
git commit -m "feat: agregar interfaz de ajustes de inventario"
```

### Task 4: Selección de proveedor en carrito

**Files:**
- Create: `app/Http/Requests/GuardarVentaRequest.php`
- Modify: `app/Http/Controllers/ComercialController.php`
- Modify: `app/Services/ComercialService.php`
- Modify: `resources/js/comercial.js`
- Modify: `resources/views/pos/index.blade.php`
- Test: `tests/Feature/VentaPorProveedorTest.php`
- Test: `tests/js/comercial-proveedor.test.mjs`

**Interfaces:**
- Consumes request line: `{presentacion_id:int, productor_id:int, cantidad:int}`.
- Produces catalog provider: `{productor_id:int, nombre:string, precio:string, stock:int, lotes:array}`.

- [ ] **Step 1: Write failing supplier-scoped sale tests**

```php
$response = $this->actingAs($trabajador)->postJson(route('ventas.store'), [
    'clave' => Str::uuid(), 'metodo_pago' => 'efectivo',
    'items' => [['presentacion_id' => $presentacion->id, 'productor_id' => $proveedorB->id, 'cantidad' => 2]],
]);
$response->assertCreated();
$this->assertDatabaseHas('ventas', ['ingreso_productor_item_id' => $loteB->id, 'cantidad_vendida' => 2]);
$this->assertSame(5, $loteA->fresh()->cantidad_disponible);
```

Añadir rechazo de proveedor ajeno, stock insuficiente y prueba de hash idempotente incluyendo proveedor.

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Feature/VentaPorProveedorTest.php`
Expected: FAIL porque el request no acepta o el servicio ignora `productor_id`.

- [ ] **Step 3: Implement server behavior and browser selector**

Agrupar catálogo por proveedor y limitar la consulta FEFO con `whereHas('ingresoProductor', ... productor_id ...)`. Extraer funciones puras de selección y subtotal para probar el comportamiento del carrito sin DOM falso.

- [ ] **Step 4: Run PHP and JavaScript tests**

Run: `php artisan test tests/Feature/VentaPorProveedorTest.php tests/Feature/AcopioComercialTest.php`
Run: `node --test tests/js/*.test.mjs`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Requests/GuardarVentaRequest.php app/Http/Controllers/ComercialController.php app/Services/ComercialService.php resources/js/comercial.js resources/views/pos/index.blade.php tests/Feature/VentaPorProveedorTest.php tests/js
git commit -m "feat: vender inventario por proveedor seleccionado"
```

### Task 5: Ranking de productos vendidos

**Files:**
- Create: `app/Services/ReporteVentasService.php`
- Create: `app/Exports/ProductosVendidosExport.php`
- Modify: `app/Http/Controllers/ReporteComercialController.php`
- Modify: `resources/views/reportes/ventas.blade.php`
- Create: `resources/js/reporte-ventas.js`
- Modify: `resources/js/app.js`
- Modify: `routes/web.php`
- Test: `tests/Feature/ProductosVendidosReportTest.php`

**Interfaces:**
- Produces: `ReporteVentasService::productosMasVendidos(array $filtros): Collection`.
- Each row: `producto_id`, `producto`, `unidades`, `monto`, `porcentaje`.
- Produces: route `reportes-comerciales.productos.csv`.

- [ ] **Step 1: Write failing aggregate/filter/export test**

```php
$filas = app(ReporteVentasService::class)->productosMasVendidos([
    'desde' => '2026-09-01', 'hasta' => '2026-09-30', 'productor_id' => $productor->id,
]);
$this->assertSame('Queso crema', $filas->first()['producto']);
$this->assertSame(4, $filas->first()['unidades']);
$this->assertSame('40.00', $filas->first()['monto']);
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/ProductosVendidosReportTest.php`
Expected: FAIL porque el servicio y CSV no existen.

- [ ] **Step 3: Implement shared query, controller, CSV, table and chart**

Usar joins hasta productos/productores, `SUM(cantidad_vendida)` y `SUM(cantidad_vendida * precio_unitario_venta)`, máximo quince filas. Serializar datos con `@json` y dibujar barras horizontales; la tabla Blade siempre permanece disponible.

- [ ] **Step 4: Run tests and build**

Run: `php artisan test tests/Feature/ProductosVendidosReportTest.php`
Run: `npm run build`
Expected: PASS y build exitoso.

- [ ] **Step 5: Commit**

```bash
git add app/Services/ReporteVentasService.php app/Exports/ProductosVendidosExport.php app/Http/Controllers/ReporteComercialController.php resources/views/reportes/ventas.blade.php resources/js/reporte-ventas.js resources/js/app.js routes/web.php tests/Feature/ProductosVendidosReportTest.php
git commit -m "feat: mostrar productos mas vendidos"
```

### Task 6: Dominio de descartes de productos

**Files:**
- Create: `database/migrations/2026_09_17_000005_create_descartes_productos_table.php`
- Create: `app/Models/DescarteProducto.php`
- Create: `app/Policies/DescarteProductoPolicy.php`
- Create: `app/Http/Requests/GuardarDescarteProductoRequest.php`
- Create: `app/Http/Requests/ActualizarDescarteProductoRequest.php`
- Create: `app/Services/DescarteProductoService.php`
- Modify: `app/Models/IngresoProductorItem.php`
- Test: `tests/Feature/DescarteProductoTest.php`

**Interfaces:**
- Produces: `crear(array $datos, User $usuario): DescarteProducto`.
- Produces: `actualizar(DescarteProducto $descarte, array $datos, User $usuario): DescarteProducto`.
- Produces: `procesar(DescarteProducto $descarte, User $usuario): DescarteProducto`.

- [ ] **Step 1: Write failing loss and stock tests**

```php
$descarte = app(DescarteProductoService::class)->crear([
    'lote_id' => $lote->id, 'cantidad' => 3,
    'tipo_motivo' => 'caducado', 'notas' => null,
], $trabajador);
$this->assertSame('7.50', $descarte->costo_unitario);
$this->assertSame('22.50', $descarte->perdida_total);
$this->assertSame(10, $lote->fresh()->cantidad_disponible);
app(DescarteProductoService::class)->procesar($descarte, $administrador);
$this->assertSame(7, $lote->fresh()->cantidad_disponible);
```

Añadir casos de saldo insuficiente, doble proceso, edición/eliminación procesada y permisos.

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/DescarteProductoTest.php`
Expected: FAIL porque tabla, modelo y servicio no existen.

- [ ] **Step 3: Implement migration, model, policy, requests and service**

Derivar proveedor, fecha y costo del lote bloqueado. Calcular pérdida con `Decimal::mul`. Procesar una sola vez, descontar ambos saldos y crear movimiento `descarte`.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/DescarteProductoTest.php`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_09_17_000005_create_descartes_productos_table.php app/Models/DescarteProducto.php app/Policies app/Http/Requests app/Services/DescarteProductoService.php tests/Feature/DescarteProductoTest.php
git commit -m "feat: agregar dominio de descartes de productos"
```

### Task 7: CRUD y exportaciones de descartes

**Files:**
- Create: `app/Http/Controllers/DescarteProductoController.php`
- Create: `app/Http/Controllers/ReporteDescarteController.php`
- Create: `app/Services/ConsultaDescartes.php`
- Create: `app/Exports/DescartesExport.php`
- Create: `resources/views/descartes-productos/index.blade.php`
- Create: `resources/views/descartes-productos/create.blade.php`
- Create: `resources/views/descartes-productos/edit.blade.php`
- Create: `resources/views/descartes-productos/show.blade.php`
- Create: `resources/views/descartes-productos/_form.blade.php`
- Create: `resources/views/descartes-productos/pdf.blade.php`
- Create: `resources/views/descartes-productos/consolidado-pdf.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/partials/sidebar.blade.php`
- Test: `tests/Feature/DescarteProductoPagesTest.php`

**Interfaces:**
- Consumes: servicio de Task 6.
- Produces: resource routes `descartes-productos.*`, `procesar`, `pdf`, `reporte.pdf`, `reporte.csv`.

- [ ] **Step 1: Write failing CRUD/filter/export tests**

```php
$this->actingAs($trabajador)->get(route('descartes-productos.index', [
    'productor_id' => $productor->id, 'estado' => 'pendiente',
]))->assertOk()->assertSee('22,50');
$this->actingAs($administrador)->get(route('descartes-productos.pdf', $descarte))
    ->assertOk()->assertHeader('content-type', 'application/pdf');
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/DescarteProductoPagesTest.php`
Expected: FAIL con rutas no definidas.

- [ ] **Step 3: Implement controllers, shared query, views and exports**

La consulta común aplica proveedor, fechas, estado y motivo; calcula total y subtotales. PDF y CSV reciben esa misma colección. La interfaz oculta acciones incompatibles con estado o rol.

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Feature/DescarteProductoPagesTest.php tests/Feature/CommercialPagesTest.php`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/DescarteProductoController.php app/Http/Controllers/ReporteDescarteController.php app/Services/ConsultaDescartes.php app/Exports/DescartesExport.php resources/views/descartes-productos routes/web.php resources/views/partials/sidebar.blade.php tests/Feature/DescarteProductoPagesTest.php
git commit -m "feat: agregar gestion y reportes de descartes"
```

### Task 8: Documentación, SQL y verificación integral

**Files:**
- Modify: `GUIA_SISTEMA.md`
- Create: `CHANGELOG.md`
- Create: `docs/diagrama-er-inventario.md`
- Modify: `9_11_2026_monitoreo_lacteo_sistema_ventas_funcional_v2.sql`
- Modify: tests impacted by intentional labels/routes.

**Interfaces:**
- Consumes: esquema y rutas finales de Tasks 1–7.
- Produces: guía, changelog, diagrama ER y volcado coherentes con migraciones.

- [ ] **Step 1: Update documentation and SQL dump**

Documentar `ajustes_inventario` como correcciones auditadas y `descartes_productos` como bajas controladas con pérdida económica. Eliminar liquidaciones del diagrama y del volcado.

- [ ] **Step 2: Run complete automated verification**

Run: `php artisan test`
Expected: todas las pruebas PASS.

Run: `node --test tests/js/*.test.mjs`
Expected: todas las pruebas PASS.

Run: `npm run build`
Expected: Vite termina sin errores.

Run: `vendor/bin/pint --test <archivos PHP modificados>`
Expected: PASS.

Run: `git diff --check`
Expected: sin salida.

- [ ] **Step 3: Run browser smoke tests**

Verificar autenticación, creación/anulación de ajuste, proveedor en carrito, ranking, creación/proceso de descarte y descarga de PDFs/CSV. Confirmar que no aparece “Liquidación”.

- [ ] **Step 4: Commit**

```bash
git add GUIA_SISTEMA.md CHANGELOG.md docs/diagrama-er-inventario.md 9_11_2026_monitoreo_lacteo_sistema_ventas_funcional_v2.sql tests
git commit -m "docs: documentar ajustes ventas y descartes"
```
