# Eliminación de devoluciones, cantidades enteras y menú unificado Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Eliminar devoluciones, restringir a enteros los conteos de inventario y ventas, y unificar el fondo del menú lateral.

**Architecture:** Una migración terminal hará primero una prevalidación completa de fracciones y solo después eliminará devoluciones y cambiará tipos. El dominio comercial conservará FEFO, transacciones y cálculos monetarios decimales, pero manejará conteos como enteros. La interfaz retirará devoluciones y compartirá un único token CSS para las dos capas existentes del sidebar.

**Tech Stack:** PHP 8.2, Laravel 12, Eloquent/Query Builder, Blade, JavaScript ES modules, CSS, PHPUnit 11, SQLite de pruebas y MySQL local.

**Spec:** `docs/superpowers/specs/2026-09-17-eliminar-devoluciones-cantidades-enteras-menu-design.md`

## Global Constraints

- No redondear ni truncar datos históricos fraccionarios.
- Mantener decimales en dinero, precios, temperaturas, leche, cuajo y contenido del envase.
- Preservar los cambios locales preexistentes fuera del alcance.
- No ejecutar la migración destructiva sobre la base local durante las pruebas; usar SQLite en memoria.
- Mantener autenticación, roles, FEFO y transacciones de stock.

---

### Task 1: Contrato de esquema sin devoluciones y con cantidades enteras

**Files:**
- Modify: `tests/Feature/AcopioMigrationTest.php`
- Create: `database/migrations/2026_09_17_000001_remove_returns_and_integer_quantities.php`
- Modify: `database/migrations/2026_09_05_000000_create_consignacion_y_ventas_tables.php`
- Delete: `database/migrations/2026_09_08_000003_add_venta_id_to_devoluciones_table.php`
- Delete: `database/migrations/2026_09_08_000004_update_devoluciones_for_cliente_productor_flow.php`
- Modify: `database/migrations/2026_09_10_000001_refactor_acopio.php`
- Modify: `database/migrations/2026_09_11_000001_add_inventory_search_indexes.php`

**Interfaces:**
- Produces: esquema final sin `devoluciones` y con conteos `integer`.
- Produces: excepción `RuntimeException` que enumera `tabla.columna` e IDs fraccionarios antes de cualquier DDL.

- [ ] **Step 1: Escribir pruebas RED de esquema y prevalidación**

Añadir pruebas que verifiquen la ausencia de la tabla y los tipos finales:

```php
public function test_fresh_schema_has_no_returns_and_uses_integer_unit_counts(): void
{
    $this->artisan('migrate:fresh')->assertExitCode(0);
    $this->assertFalse(Schema::hasTable('devoluciones'));
    foreach ([
        ['presentaciones', 'stock'],
        ['presentaciones', 'stock_minimo_alerta'],
        ['ingresos_productores_items', 'cantidad_ingresada'],
        ['ingresos_productores_items', 'cantidad_disponible'],
        ['ventas', 'cantidad_vendida'],
        ['movimientos_inventario', 'cantidad'],
    ] as [$table, $column]) {
        $this->assertSame('integer', Schema::getColumnType($table, $column));
    }
}

public function test_integer_migration_rejects_fractional_history_before_dropping_returns(): void
{
    Schema::dropAllTables();
    Schema::create('presentaciones', function (Blueprint $table) {
        $table->id();
        $table->decimal('stock', 12, 3)->default(0);
        $table->decimal('stock_minimo_alerta', 12, 3)->default(0);
    });
    Schema::create('ingresos_productores_items', function (Blueprint $table) {
        $table->id();
        $table->decimal('cantidad_ingresada', 10, 3);
        $table->decimal('cantidad_disponible', 10, 3);
    });
    Schema::create('ventas', function (Blueprint $table) {
        $table->id();
        $table->decimal('cantidad_vendida', 10, 3);
    });
    Schema::create('movimientos_inventario', function (Blueprint $table) {
        $table->id();
        $table->decimal('cantidad', 12, 3);
    });
    Schema::create('conciliaciones_stock_acopio', function (Blueprint $table) {
        $table->id();
        $table->decimal('stock_anterior', 12, 3);
        $table->decimal('stock_lotes', 12, 3);
        $table->decimal('diferencia', 12, 3);
    });
    Schema::create('devoluciones', fn (Blueprint $table) => $table->id());

    DB::table('presentaciones')->insert(['id'=>1, 'stock'=>'1.000', 'stock_minimo_alerta'=>'0.000']);
    DB::table('ingresos_productores_items')->insert(['id'=>9, 'cantidad_ingresada'=>'2.000', 'cantidad_disponible'=>'1.500']);
    DB::table('ventas')->insert(['id'=>1, 'cantidad_vendida'=>'1.000']);
    DB::table('movimientos_inventario')->insert(['id'=>1, 'cantidad'=>'-1.000']);
    DB::table('conciliaciones_stock_acopio')->insert(['id'=>1, 'stock_anterior'=>'2.000', 'stock_lotes'=>'1.000', 'diferencia'=>'-1.000']);

    try {
        (require database_path('migrations/2026_09_17_000001_remove_returns_and_integer_quantities.php'))->up();
        $this->fail('La migración debía rechazar cantidades fraccionarias.');
    } catch (RuntimeException $exception) {
        $this->assertStringContainsString('ingresos_productores_items.cantidad_disponible: IDs 9', $exception->getMessage());
    }

    $this->assertTrue(Schema::hasTable('devoluciones'));
    $this->assertSame('decimal', Schema::getColumnType('ingresos_productores_items', 'cantidad_disponible'));
}
```

- [ ] **Step 2: Ejecutar RED**

Run: `php artisan test tests/Feature/AcopioMigrationTest.php`

Expected: FAIL porque existe `devoluciones`, los campos siguen siendo `decimal` y no existe la migración terminal.

- [ ] **Step 3: Implementar la migración terminal y limpiar migraciones históricas**

La migración terminal inspeccionará cada columna con una consulta equivalente a:

```php
$ids = DB::table($table)
    ->whereRaw("$column <> CAST($column AS INTEGER)")
    ->limit(20)
    ->pluck('id');

if ($ids->isNotEmpty()) {
    $errors[] = "$table.$column: IDs ".$ids->implode(', ');
}
```

Solo si `$errors` queda vacío eliminará `devoluciones` y cambiará los campos mediante `Schema::table(...)->integer(...)->change()`. Las migraciones históricas crearán directamente cantidades enteras y no crearán, renombrarán ni indexarán devoluciones.

- [ ] **Step 4: Ejecutar GREEN**

Run: `php artisan test tests/Feature/AcopioMigrationTest.php`

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add tests/Feature/AcopioMigrationTest.php database/migrations
git commit -m "refactor: eliminar devoluciones y usar cantidades enteras"
```

### Task 2: Dominio comercial entero y sin devoluciones

**Files:**
- Modify: `tests/Feature/AcopioComercialTest.php`
- Modify: `tests/Feature/AcopioIntegrityTest.php`
- Modify: `tests/Feature/PanelCommercialRefinementTest.php`
- Modify: `app/Http/Controllers/ComercialController.php`
- Modify: `app/Services/ComercialService.php`
- Modify: `app/Services/InventarioConsulta.php`
- Modify: `app/Models/IngresoProductor.php`
- Modify: `app/Models/IngresoProductorItem.php`
- Modify: `app/Models/Presentacion.php`
- Modify: `app/Models/Venta.php`
- Modify: `app/Models/User.php`
- Modify: `app/Models/MovimientoInventario.php`
- Delete: `app/Models/Devolucion.php`
- Delete: `app/Http/Requests/FiltroDevolucionRequest.php`

**Interfaces:**
- `ComercialService::ingreso`, `vender` y `movimiento` consumen cantidades enteras.
- `IngresoProductorItem::$casts` y modelos relacionados exponen conteos como `integer`.
- La liquidación calcula importes sobre ventas sin deducciones de devoluciones.

- [ ] **Step 1: Reescribir pruebas como contrato RED del nuevo comportamiento**

Sustituir el escenario fraccionario/devolución por pruebas que rechacen fracciones y liquiden después de una salida auditada:

```php
public function test_ingreso_and_inventory_movements_reject_fractional_units(): void
{
    [$productor, $presentacion] = $this->base();
    $this->postJson('/ingresos-productores', [
        'productor_id'=>$productor,
        'fecha_ingreso'=>now()->format('Y-m-d H:i:s'),
        'items'=>[[
            'presentacion_id'=>$presentacion->id,
            'cantidad_ingresada'=>'1.5',
            'precio_acopio_unitario'=>'8.00',
            'precio_venta_unitario'=>'12.00',
            'fecha_caducidad'=>now()->addDays(10)->toDateString(),
        ]],
    ])
        ->assertUnprocessable()->assertJsonValidationErrors('items.0.cantidad_ingresada');
}

public function test_stock_can_be_closed_with_audited_integer_output_and_liquidated(): void
{
    [$productor, $presentacion] = $this->base();
    $ingreso = $this->ingreso($productor, $presentacion, '2');
    $lote = DB::table('ingresos_productores_items')->where('ingreso_productor_id', $ingreso)->first();
    $this->postJson("/lotes/{$lote->id}/movimientos", ['tipo'=>'salida','cantidad'=>2,'motivo'=>'Retiro autorizado'])->assertSuccessful();
    $this->postJson("/ingresos-productores/{$ingreso}/liquidacion", [])->assertSuccessful();
}
```

- [ ] **Step 2: Ejecutar RED**

Run: `php artisan test tests/Feature/AcopioComercialTest.php tests/Feature/AcopioIntegrityTest.php tests/Feature/PanelCommercialRefinementTest.php`

Expected: FAIL porque ingresos y movimientos aún admiten fracciones y el dominio todavía depende de devoluciones.

- [ ] **Step 3: Implementar el dominio mínimo**

Cambiar validaciones a `integer`, casts a `integer`, escalas de cantidad a `0`, eliminar `devolver()` y relaciones. `cantidad_vendida_neta` será igual a la suma de ventas y la liquidación cargará únicamente `ventas`.

- [ ] **Step 4: Ejecutar GREEN**

Run: `php artisan test tests/Feature/AcopioComercialTest.php tests/Feature/AcopioIntegrityTest.php tests/Feature/PanelCommercialRefinementTest.php`

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app tests/Feature/AcopioComercialTest.php tests/Feature/AcopioIntegrityTest.php tests/Feature/PanelCommercialRefinementTest.php
git commit -m "refactor: simplificar dominio comercial sin devoluciones"
```

### Task 3: Rutas, pantallas y reportes sin devoluciones

**Files:**
- Modify: `tests/Feature/CommercialPagesTest.php`
- Delete: `tests/Feature/DevolucionesWorkspaceTest.php`
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/ReporteComercialController.php`
- Modify: `resources/views/partials/sidebar.blade.php`
- Modify: `resources/views/ingresos/show.blade.php`
- Modify: `resources/views/inventario/index.blade.php`
- Modify: `resources/views/inventario/general.blade.php`
- Modify: `resources/views/reportes/comercial-pdf.blade.php`
- Modify: `resources/views/reportes/ingresos.blade.php`
- Delete: `resources/views/devoluciones/index.blade.php`
- Modify: `resources/js/comercial.js`
- Modify: `app/Exports/ComercialExport.php`

**Interfaces:**
- GET `/devoluciones` y POST `/lotes/{item}/devoluciones` dejan de existir.
- Los reportes comerciales producen Entradas, Ventas, Liquidaciones y Ajustes por Lote.

- [ ] **Step 1: Escribir prueba RED de superficie pública**

```php
public function test_commercial_shell_and_reports_do_not_expose_returns(): void
{
    $user = User::factory()->create(['rol'=>'Administrador','activo'=>true]);
    $this->actingAs($user)->get('/dashboard')->assertOk()->assertDontSee('Devoluciones');
    $this->get('/devoluciones')->assertNotFound();
    $this->post('/lotes/1/devoluciones', [])->assertNotFound();
    $this->get('/reportes-comerciales')->assertOk()->assertDontSee('Devoluciones');
}
```

- [ ] **Step 2: Ejecutar RED**

Run: `php artisan test tests/Feature/CommercialPagesTest.php`

Expected: FAIL porque las rutas y enlaces siguen presentes.

- [ ] **Step 3: Retirar superficie de devoluciones y mostrar enteros**

Eliminar rutas, imports, vistas, botones y bloques de informe. Cambiar formatos de cantidades a cero decimales, `step="1"` y textos de liquidación/salida coherentes.

- [ ] **Step 4: Ejecutar GREEN y pruebas de rutas**

Run: `php artisan test tests/Feature/CommercialPagesTest.php tests/Feature/InventarioPorLoteTest.php tests/Feature/InventarioGeneralTest.php`

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add routes app/Http/Controllers/ReporteComercialController.php app/Exports/ComercialExport.php resources tests/Feature/CommercialPagesTest.php tests/Feature/DevolucionesWorkspaceTest.php
git commit -m "refactor: retirar interfaz y reportes de devoluciones"
```

### Task 4: Fondo único del menú y artefactos finales

**Files:**
- Modify: `resources/css/app.css`
- Modify: `9_11_2026_monitoreo_lacteo_sistema_ventas_funcional_v2.sql`

**Interfaces:**
- `--sidebar-bg` es la única fuente de color de fondo del menú.
- `.sidebar` y `.sidebar-container` consumen el mismo token.

- [ ] **Step 1: Confirmar la causa raíz antes del cambio**

En el navegador, inspeccionar `#app-sidebar` y `.sidebar-container`; registrar que sus fondos calculados actuales son distintos (`rgb(30, 41, 59)` y `rgb(17, 34, 36)`).

- [ ] **Step 2: Aplicar la corrección CSS mínima**

```css
:root { --sidebar-bg: #112224; }
.sidebar { background-color: var(--sidebar-bg); padding-top: 0; }
.sidebar-container { background-color: var(--sidebar-bg); }
```

- [ ] **Step 3: Actualizar el volcado SQL sin redondear**

Eliminar definición, datos, índices y FKs de devoluciones. Cambiar tipos de conteo solo si todos los valores del volcado son enteros; si aparece una fracción, documentarla y no alterar silenciosamente ese dato.

- [ ] **Step 4: Verificar visualmente y ejecutar la suite completa**

Run:

```bash
vendor/bin/pint --test
php artisan test
npm run build
```

Expected: Pint sin errores, 0 pruebas fallidas y Vite con código de salida 0. En escritorio y móvil, ambos contenedores del menú deben tener el mismo color calculado.

- [ ] **Step 5: Commit**

```bash
git add resources/css/app.css 9_11_2026_monitoreo_lacteo_sistema_ventas_funcional_v2.sql
git commit -m "fix: unificar fondo del menu lateral"
```
