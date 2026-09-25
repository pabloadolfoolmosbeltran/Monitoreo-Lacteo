# Reactivación y depuración Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Eliminar los bloqueos al mostrar/restaurar inactivos, unificar el estado de proveedores y retirar únicamente artefactos locales demostrablemente huérfanos.

**Architecture:** Las acciones administrativas sensibles vuelven a navegación Laravel completa y dejan de depender del reemplazo parcial del panel. Los cuatro CRUD usan `activo` como estado funcional; `Productor` conserva SoftDeletes sólo para leer y recuperar datos históricos. La depuración se limita a un SQLite raíz sin referencias y a cachés regenerables.

**Tech Stack:** PHP 8.2, Laravel 12, Blade, JavaScript ES modules, PHPUnit 11, Node test runner, Git.

**Spec:** `docs/superpowers/specs/2026-09-11-inventarios-devoluciones-reactivacion-design.md`

## Global Constraints

- No eliminar historial, migraciones, imágenes, logs diagnósticos ni pruebas automatizadas válidas.
- No incluir cambios preexistentes ajenos en los commits.
- Sólo Administrador puede listar o restaurar inactivos.
- Formularios de desactivar/restaurar deben funcionar sin JavaScript.

---

### Task 1: Reactivación mediante navegación completa

**Files:**
- Modify: `resources/js/async-panel.js`
- Modify: `resources/views/productos/index.blade.php`
- Modify: `resources/views/presentaciones/index.blade.php`
- Modify: `resources/views/usuarios/index.blade.php`
- Modify: `resources/views/productores/index.blade.php`
- Modify: `tests/js/async-panel.test.mjs`
- Modify: `tests/Feature/AsyncPanelActionsTest.php`

**Interfaces:**
- Produces: atributo `data-full-navigation` y `shouldUsePanelNavigation(link, event, location)`.
- Consumes: navegación normal del navegador y endpoints Laravel existentes.

- [ ] **Step 1: Escribir pruebas RED del límite de navegación**

La mutación que deben detectar es volver a interceptar enlaces o formularios administrativos.

```js
import { shouldUsePanelNavigation } from '../../resources/js/async-panel.js';

test('full-navigation links bypass panel replacement', () => {
    const link = { hasAttribute: (name) => name === 'data-full-navigation', target: '' };
    const event = { defaultPrevented: false, button: 0, metaKey: false, ctrlKey: false, shiftKey: false, altKey: false };
    assert.equal(shouldUsePanelNavigation(link, event), false);
});
```

Agregar prueba Feature:

```php
public function test_deleted_lists_and_mutations_use_full_page_navigation(): void
{
    $this->authenticate();
    $user = User::factory()->create(['rol'=>'Trabajador','activo'=>false]);
    $product = Producto::create(['nombre'=>'Inactivo','temperatura_minima'=>2,'temperatura_maxima'=>8,'activo'=>false]);
    $presentation = Presentacion::create(['producto_id'=>$product->id,'nombre'=>'Inactiva','precio'=>10,'activo'=>false]);
    $producer = Productor::create(['nombres'=>'Proveedor','primer_apellido'=>'Inactivo','activo'=>false]);
    foreach (['/productos', '/presentaciones', '/usuarios', '/productores'] as $url) {
        $html = $this->get($url)->assertOk()->getContent();
        $this->assertStringContainsString('data-full-navigation', $html);
    }
    foreach (['/productos?estado=eliminados','/presentaciones?estado=eliminados','/usuarios?estado=eliminados','/productores?estado=eliminados'] as $url) {
        $html = $this->get($url)->assertOk()->getContent();
        $this->assertStringContainsString('Restablecer', $html);
        $this->assertStringNotContainsString('method="POST" class="d-inline" data-async-action', $html);
    }
}
```

- [ ] **Step 2: Ejecutar RED**

Run: `node --test tests/js/async-panel.test.mjs`

Run: `php artisan test --filter=deleted_lists_and_mutations_use_full_page_navigation`

Expected: FAIL porque no existe el helper ni el atributo.

- [ ] **Step 3: Implementar bypass mínimo**

Extraer la condición del listener:

```js
export function shouldUsePanelNavigation(link, event) {
    return !link.hasAttribute('data-full-navigation') && !event.defaultPrevented && event.button === 0
        && !event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey
        && !link.target && !link.hasAttribute('download');
}
```

El listener de clicks retorna si el helper es falso. Marcar enlaces “Mostrar eliminados” y “Volver” con `data-full-navigation`. Retirar `data-async-action` de los formularios de desactivar/restaurar de los cuatro CRUD. No retirar el atributo de acciones rápidas comerciales o de control que permanecen en la misma pantalla.

- [ ] **Step 4: Ejecutar GREEN**

Run: `node --test tests/js/async-panel.test.mjs`

Run: `php artisan test tests/Feature/AsyncPanelActionsTest.php tests/Feature/PanelCommercialRefinementTest.php`

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add resources/js/async-panel.js resources/views/productos/index.blade.php resources/views/presentaciones/index.blade.php resources/views/usuarios/index.blade.php resources/views/productores/index.blade.php tests/js/async-panel.test.mjs tests/Feature/AsyncPanelActionsTest.php
git commit -m "fix: estabilizar reactivacion de registros"
```

### Task 2: Proveedores con estado activo simple y compatibilidad histórica

**Files:**
- Modify: `app/Http/Controllers/ProductorController.php`
- Modify: `resources/views/productores/index.blade.php`
- Modify: `tests/Feature/PanelCommercialRefinementTest.php`

**Interfaces:**
- Produces: desactivación nueva con `activo = false`; restauración compatible mediante `Productor::withTrashed()->findOrFail($id)`.
- Consumes: `SoftDeletes` sólo para registros heredados.

- [ ] **Step 1: Escribir pruebas RED del nuevo estado y del legado**

```php
public function test_new_supplier_deactivation_only_sets_active_false(): void
{
    $admin = $this->admin();
    $supplier = Productor::create(['nombres'=>'Ana','primer_apellido'=>'Rojas','activo'=>true]);
    $this->actingAs($admin)->delete(route('productores.destroy', $supplier))->assertRedirect(route('productores.index'));
    $this->assertDatabaseHas('productores', ['id'=>$supplier->id,'activo'=>false,'deleted_at'=>null]);
    $this->get('/productores?estado=eliminados')->assertOk()->assertSee('Ana Rojas');
}

public function test_legacy_soft_deleted_supplier_is_listed_and_restored(): void
{
    $admin = $this->admin();
    $supplier = Productor::create(['nombres'=>'Luis','primer_apellido'=>'Paz','activo'=>true]);
    $supplier->delete();
    $this->actingAs($admin)->get('/productores?estado=eliminados')->assertSee('Luis Paz');
    $this->patch(route('productores.restore', $supplier->id));
    $this->assertDatabaseHas('productores', ['id'=>$supplier->id,'activo'=>true,'deleted_at'=>null]);
}
```

- [ ] **Step 2: Ejecutar RED**

Run: `php artisan test --filter='new_supplier_deactivation|legacy_soft_deleted_supplier'`

Expected: la primera FAIL porque `destroy()` llena `deleted_at`.

- [ ] **Step 3: Implementar consulta y mutaciones mínimas**

En activos usar `Productor::query()->where('activo', true)`. En eliminados usar:

```php
Productor::withTrashed()->where(function (Builder $q) {
    $q->where('activo', false)->orWhereNotNull('deleted_at');
});
```

`destroy()` ejecuta `$productor->update(['activo' => false])`. `restore()` obtiene con `withTrashed`, llama `restore()` sólo si `trashed()` y finalmente actualiza `activo` a true. Mantener `withCount('ingresos')` y filtros existentes.

- [ ] **Step 4: Ejecutar GREEN y permisos**

Run: `php artisan test --filter='supplier_deactivation|soft_deleted_supplier|only_admin_can_view_deleted|worker_cannot_view_or_restore'`

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/ProductorController.php resources/views/productores/index.blade.php tests/Feature/PanelCommercialRefinementTest.php
git commit -m "fix: simplificar estado de proveedores"
```

### Task 3: Depuración recuperable y verificación final

**Files:**
- Delete: `monitoreo_lacteos`
- Inspect only: `.postman/resources.yaml`, `postman/`, `.gitignore`, `phpunit.xml`

**Interfaces:**
- Produces: repositorio sin la base SQLite raíz heredada.
- Consumes: Git como mecanismo de recuperación.

- [ ] **Step 1: Repetir la evidencia antes de eliminar**

Run: `Format-Hex -Path monitoreo_lacteos -Count 16`

Expected: encabezado `SQLite format 3`.

Run: `rg -n "monitoreo_lacteos" . -g '!vendor/**' -g '!node_modules/**' -g '!monitoreo_lacteos'`

Expected: ninguna referencia a la ruta del archivo. El `.env` puede usar `monitoreo_lacteos` como nombre de base MySQL; eso no referencia el archivo raíz. `phpunit.xml` debe continuar usando SQLite `:memory:`.

- [ ] **Step 2: Mantener Postman y pruebas**

Confirmar que `.postman/resources.yaml` registra los recursos bajo `postman/`; por tanto no son duplicados y no se eliminan. Mantener toda la suite PHP/JS. Confirmar que `.phpunit.result.cache` está ignorado y no rastreado con `git check-ignore -v .phpunit.result.cache` y `git ls-files -- .phpunit.result.cache`.

- [ ] **Step 3: Eliminar sólo el SQLite raíz**

Run: `git rm -- monitoreo_lacteos`

El archivo es recuperable desde Git (`ede1bf6`) y no se utiliza por aplicación ni pruebas.

- [ ] **Step 4: Limpiar cachés regenerables con Laravel**

Run: `php artisan optimize:clear`

Expected: configuración, rutas, eventos, vistas y caché compilada informan limpieza correcta; no se toca la base de datos.

- [ ] **Step 5: Verificación integral antes de afirmar finalización**

Run: `php artisan test`

Run: `node --test tests/js/async-panel.test.mjs`

Run: `npm run build`

Run: `git diff --check`

Expected: suites PASS, build con exit code 0 y ningún error de whitespace.

- [ ] **Step 6: Commit**

```bash
git add -- monitoreo_lacteos
git commit -m "chore: retirar base sqlite heredada"
```
