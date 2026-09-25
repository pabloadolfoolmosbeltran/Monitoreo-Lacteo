# Inventarios y devoluciones Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Incorporar filtros rápidos por lote, un Inventario general consolidado y un espacio directo para consultar y registrar devoluciones.

**Architecture:** Un servicio de consulta reutilizable aplicará la semántica de búsqueda y construirá consultas Eloquent/Query Builder paginadas. `ComercialController` seguirá coordinando las pantallas y `ComercialService` conservará las transacciones de stock. Las cifras generales se derivan de lotes; no se crea un saldo duplicado.

**Tech Stack:** PHP 8.2, Laravel 12, Eloquent/Query Builder, Blade, CSS existente, PHPUnit 11, SQLite en memoria para pruebas y MySQL en producción.

**Spec:** `docs/superpowers/specs/2026-09-11-inventarios-devoluciones-reactivacion-design.md`

## Global Constraints

- Conservar datos, rutas actuales de ventas y reglas contables.
- Acceso para Administrador y Trabajador mediante `OperadorComercial`.
- Cantidades con tres decimales y operaciones monetarias mediante `App\Support\Decimal`.
- Consultas paginadas, sin peticiones por pulsación y sin dependencias frontend nuevas.
- No modificar cambios del usuario fuera de los archivos enumerados en cada tarea.

---

### Task 1: Búsqueda reutilizable de presentaciones

**Files:**
- Create: `app/Services/BusquedaPresentacion.php`
- Modify: `app/Http/Controllers/PresentacionController.php`
- Test: `tests/Feature/PanelCommercialRefinementTest.php`

**Interfaces:**
- Produces: `BusquedaPresentacion::aplicar(Builder $query, ?string $texto): Builder`.
- Consumes: relaciones `Presentacion::producto()` y campos `nombre`, `sabor`, `envase`, `contenido`, `unidad`.

- [ ] **Step 1: Escribir la prueba que distingue todos los términos**

Agregar una prueba que cree estas presentaciones: Yogurt/Frutilla/2/L, Yogurt/Natural/2/L y Yogurt/Frutilla/1/L. La mutación que debe detectar es ignorar producto, sabor, contenido o unidad.

```php
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
```

- [ ] **Step 2: Ejecutar RED**

Run: `php artisan test --filter=presentation_search_combines_product_flavor_content_and_plural_unit`

Expected: FAIL porque el código actual normaliza `litros` a `l`, pero los datos válidos guardan `L`.

- [ ] **Step 3: Implementar el servicio mínimo y usarlo en PresentacionController**

El método debe recortar el texto, convertir coma decimal a punto, separar número/unidad (`2L`) y mapear `litro`, `litros` y `l` a `L`; `gramo(s)` a `g`, `kilo(s)` a `kg` y `mililitro(s)` a `ml`. Cada término agrega un `where`, mientras los campos textuales de ese término se unen con `orWhere` dentro de un grupo.

```php
final class BusquedaPresentacion
{
    public function aplicar(Builder $query, ?string $texto): Builder
    {
        $normalizado = preg_replace('/(?<=\d)(?=\p{L})/u', ' ', str_replace(',', '.', trim((string) $texto)));
        $terminos = preg_split('/\s+/u', $normalizado, -1, PREG_SPLIT_NO_EMPTY);
        $unidades = ['l'=>'L','litro'=>'L','litros'=>'L','ml'=>'ml','mililitro'=>'ml','mililitros'=>'ml','g'=>'g','gramo'=>'g','gramos'=>'g','kg'=>'kg','kilo'=>'kg','kilos'=>'kg','kilogramo'=>'kg','kilogramos'=>'kg'];

        foreach ($terminos as $termino) {
            $clave = mb_strtolower($termino);
            if (isset($unidades[$clave])) {
                $query->whereRaw('LOWER(unidad) = ?', [mb_strtolower($unidades[$clave])]);
            } elseif (is_numeric($termino)) {
                $query->where('contenido', (float) $termino);
            } else {
                $query->where(function (Builder $q) use ($termino) {
                    $like = '%'.$termino.'%';
                    $q->where('nombre', 'like', $like)->orWhere('sabor', 'like', $like)
                        ->orWhere('envase', 'like', $like)
                        ->orWhereHas('producto', fn (Builder $p) => $p->where('nombre', 'like', $like));
                });
            }
        }
        return $query;
    }
}
```

Inyectar el servicio en `PresentacionController` y reemplazar el bloque duplicado del buscador con `$this->busqueda->aplicar($consulta, $busqueda)`.

- [ ] **Step 4: Ejecutar GREEN y la regresión existente**

Run: `php artisan test --filter='presentation_search_combines|presentations_can_be_searched'`

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Services/BusquedaPresentacion.php app/Http/Controllers/PresentacionController.php tests/Feature/PanelCommercialRefinementTest.php
git commit -m "feat: unificar busqueda de presentaciones"
```

### Task 2: Filtros de Inventario por lote

**Files:**
- Create: `app/Http/Requests/FiltroInventarioRequest.php`
- Create: `app/Services/InventarioConsulta.php`
- Create: `tests/Feature/InventarioPorLoteTest.php`
- Modify: `app/Http/Controllers/ComercialController.php`
- Modify: `resources/views/inventario/index.blade.php`

**Interfaces:**
- Consumes: `BusquedaPresentacion::aplicar`, `IngresoProductorItem`, `IngresoProductor`, `Productor`.
- Produces: `InventarioConsulta::lotes(array $filtros): LengthAwarePaginator` y `FiltroInventarioRequest::validated()`.

- [ ] **Step 1: Escribir pruebas RED para filtros combinados y fechas inválidas**

Usar este fixture con dos productores, dos presentaciones, entradas abierta/liquidada y fechas distintas. La mutación que debe detectar es omitir cualquiera de los `whereHas`, rangos o estado.

```php
public function test_inventory_lots_combine_product_producer_entry_expiry_and_settlement_filters(): void
{
    [$admin, $visible, $other] = $this->inventoryFixtures();
    $url = '/inventario?producto=yogurt+frutilla+2+litros&productor=Finca+Norte'.
        '&ingreso_desde=2026-09-01&ingreso_hasta=2026-09-30'.
        '&vence_desde=2026-10-01&vence_hasta=2026-10-31&liquidacion=no_liquidada';

    $this->actingAs($admin)->get($url)->assertOk()
        ->assertSee('#'.$visible->id)->assertDontSee('#'.$other->id)
        ->assertSee('value="yogurt frutilla 2 litros"', false);
}

public function test_inventory_rejects_inverted_entry_and_expiry_ranges(): void
{
    $this->actingAs($this->admin())->get('/inventario?ingreso_desde=2026-09-30&ingreso_hasta=2026-09-01')
        ->assertSessionHasErrors('ingreso_hasta');
    $this->get('/inventario?vence_desde=2026-10-30&vence_hasta=2026-10-01')
        ->assertSessionHasErrors('vence_hasta');
}

private function inventoryFixtures(): array
{
    $admin = User::factory()->create(['rol'=>'Administrador','activo'=>true]);
    $north = Productor::create(['nombres'=>'Ana','primer_apellido'=>'Mendoza','nombre_unidad_productiva'=>'Finca Norte','activo'=>true]);
    $south = Productor::create(['nombres'=>'Bruno','primer_apellido'=>'Rojas','nombre_unidad_productiva'=>'Finca Sur','activo'=>true]);
    $yogurt = Producto::create(['nombre'=>'Yogurt','temperatura_minima'=>2,'temperatura_maxima'=>8,'activo'=>true]);
    $cheese = Producto::create(['nombre'=>'Queso','temperatura_minima'=>2,'temperatura_maxima'=>8,'activo'=>true]);
    $bottle = Presentacion::create(['producto_id'=>$yogurt->id,'nombre'=>'Botella familiar','sabor'=>'Frutilla','contenido'=>2,'unidad'=>'L','precio'=>30,'stock'=>5,'activo'=>true]);
    $block = Presentacion::create(['producto_id'=>$cheese->id,'nombre'=>'Bloque','contenido'=>1,'unidad'=>'kg','precio'=>40,'stock'=>1,'activo'=>true]);
    $open = IngresoProductor::create(['productor_id'=>$north->id,'user_id'=>$admin->id,'fecha_ingreso'=>'2026-09-11','estado'=>'abierta']);
    $closed = IngresoProductor::create(['productor_id'=>$south->id,'user_id'=>$admin->id,'fecha_ingreso'=>'2026-08-15','estado'=>'liquidada']);
    $visible = IngresoProductorItem::create(['ingreso_productor_id'=>$open->id,'presentacion_id'=>$bottle->id,'cantidad_ingresada'=>5,'cantidad_disponible'=>5,'precio_acopio_unitario'=>20,'precio_venta_unitario'=>30,'fecha_recepcion'=>'2026-09-11','fecha_caducidad'=>'2026-10-15']);
    $other = IngresoProductorItem::create(['ingreso_productor_id'=>$closed->id,'presentacion_id'=>$block->id,'cantidad_ingresada'=>1,'cantidad_disponible'=>1,'precio_acopio_unitario'=>30,'precio_venta_unitario'=>40,'fecha_recepcion'=>'2026-08-15','fecha_caducidad'=>'2026-09-15']);
    return [$admin, $visible, $other];
}
```

- [ ] **Step 2: Ejecutar RED**

Run: `php artisan test tests/Feature/InventarioPorLoteTest.php`

Expected: FAIL porque `inventario()` aún no recibe ni aplica filtros.

- [ ] **Step 3: Implementar request y consulta mínima**

Validar `producto`/`productor` como texto máximo 100; fechas como `date`; los campos hasta con `after_or_equal` a su desde; `liquidacion` con `in:todas,no_liquidada,liquidada` y valor predeterminado `todas`.

`InventarioConsulta::lotes()` parte de:

```php
$query = IngresoProductorItem::query()->with([
    'presentacion.producto', 'ingresoProductor.productor', 'ingresoProductor.liquidacion',
]);
```

Aplicar producto con `whereHas('presentacion', fn (Builder $p) => $this->busqueda->aplicar($p, $filtros['producto'] ?? null))`. Para productor, agrupar `nombres`, ambos apellidos y `nombre_unidad_productiva` con el mismo `%texto%`. Aplicar los límites individuales con `whereDate('fecha_recepcion', '>=', $desde)`, `whereDate('fecha_recepcion', '<=', $hasta)` y equivalentes sobre `fecha_caducidad`. Para `no_liquidada` usar `whereHas('ingresoProductor', fn ($q) => $q->where('estado','abierta'))`; para `liquidada` usar `whereHas('ingresoProductor', fn ($q) => $q->where('estado','liquidada'))`. Finalizar con `orderByDesc('id')->paginate(30)->withQueryString()`.

Modificar `ComercialController::inventario(FiltroInventarioRequest $request, InventarioConsulta $consulta)` para entregar `lotes`, `movimientos` y `filtros`.

- [ ] **Step 4: Implementar la tarjeta de filtros y columnas acordadas**

El formulario GET debe contener exactamente los siete controles de la especificación, botón Buscar y enlace Limpiar a `route('inventario.index')`. La tabla añade producto/presentación, recepción y badges derivados de `estado_caducidad` y `ingresoProductor.estado`. Mantener formulario de movimientos y enlace a devolución.

- [ ] **Step 5: Ejecutar GREEN**

Run: `php artisan test tests/Feature/InventarioPorLoteTest.php tests/Feature/CommercialPagesTest.php`

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Requests/FiltroInventarioRequest.php app/Services/InventarioConsulta.php app/Http/Controllers/ComercialController.php resources/views/inventario/index.blade.php tests/Feature/InventarioPorLoteTest.php
git commit -m "feat: filtrar inventario por lote"
```

### Task 3: Inventario general agregado

**Files:**
- Modify: `app/Http/Requests/FiltroInventarioRequest.php`
- Modify: `app/Services/InventarioConsulta.php`
- Modify: `app/Http/Controllers/ComercialController.php`
- Modify: `routes/web.php`
- Create: `resources/views/inventario/general.blade.php`
- Create: `tests/Feature/InventarioGeneralTest.php`

**Interfaces:**
- Produces: `InventarioConsulta::grupos(array $filtros): LengthAwarePaginator` y `InventarioConsulta::totales(array $filtros): object`.
- Route: GET `/inventario-general`, name `inventario-general.index`.

- [ ] **Step 1: Escribir pruebas RED para clasificación y agrupación**

Congelar el tiempo con `Carbon::setTestNow('2026-09-11 10:00:00')`. Crear para una misma presentación/productor saldos 5 vigente, 2 que vence en tres días, 3 vencido y 4 en ingreso liquidado; crear otro productor con saldo independiente.

```php
public function test_general_inventory_groups_by_presentation_and_producer_and_separates_stock(): void
{
    [$admin, $ids] = $this->stockFixtures();
    $response = $this->actingAs($admin)->get('/inventario-general?producto=yogurt+frutilla+2+litros');

    $response->assertOk()->assertViewHas('grupos', function ($groups) use ($ids) {
        $row = collect($groups->items())->firstWhere('productor_id', $ids['norte']);
        return $row->vendible === '7.000' && $row->proximo === '2.000'
            && $row->no_vendible === '7.000' && $row->fisico === '14.000';
    });
}

public function test_expired_missing_date_and_closed_lots_never_count_as_sellable(): void
{
    [$admin] = $this->stockFixtures();
    $this->actingAs($admin)->get('/inventario-general')->assertViewHas('totales', fn ($t) =>
        $t->vendible === '8.000' && $t->no_vendible === '7.000' && $t->fisico === '15.000');
}

private function stockFixtures(): array
{
    Carbon::setTestNow('2026-09-11 10:00:00');
    $admin = User::factory()->create(['rol'=>'Administrador','activo'=>true]);
    $north = Productor::create(['nombres'=>'Ana','primer_apellido'=>'Mendoza','nombre_unidad_productiva'=>'Finca Norte','activo'=>true]);
    $south = Productor::create(['nombres'=>'Bruno','primer_apellido'=>'Rojas','nombre_unidad_productiva'=>'Finca Sur','activo'=>true]);
    $product = Producto::create(['nombre'=>'Yogurt','temperatura_minima'=>2,'temperatura_maxima'=>8,'activo'=>true]);
    $presentation = Presentacion::create(['producto_id'=>$product->id,'nombre'=>'Botella familiar','sabor'=>'Frutilla','contenido'=>2,'unidad'=>'L','precio'=>30,'stock'=>15,'activo'=>true]);
    $openNorth = IngresoProductor::create(['productor_id'=>$north->id,'user_id'=>$admin->id,'fecha_ingreso'=>'2026-09-10','estado'=>'abierta']);
    $closedNorth = IngresoProductor::create(['productor_id'=>$north->id,'user_id'=>$admin->id,'fecha_ingreso'=>'2026-09-01','estado'=>'liquidada']);
    $openSouth = IngresoProductor::create(['productor_id'=>$south->id,'user_id'=>$admin->id,'fecha_ingreso'=>'2026-09-10','estado'=>'abierta']);
    foreach ([[$openNorth,5,'2026-10-10'],[$openNorth,2,'2026-09-14'],[$openNorth,3,'2026-09-10'],[$closedNorth,4,'2026-10-10'],[$openSouth,1,'2026-10-10']] as [$entry,$quantity,$expires]) {
        IngresoProductorItem::create(['ingreso_productor_id'=>$entry->id,'presentacion_id'=>$presentation->id,'cantidad_ingresada'=>$quantity,'cantidad_disponible'=>$quantity,'precio_acopio_unitario'=>20,'precio_venta_unitario'=>30,'fecha_recepcion'=>'2026-09-10','fecha_caducidad'=>$expires]);
    }
    return [$admin, ['norte'=>$north->id,'sur'=>$south->id]];
}
```

- [ ] **Step 2: Ejecutar RED**

Run: `php artisan test tests/Feature/InventarioGeneralTest.php`

Expected: FAIL con ruta inexistente.

- [ ] **Step 3: Implementar las agregaciones SQL**

Construir una consulta base con joins a ingreso, productor, presentación y producto. Usar `SUM(CASE WHEN ...)` con bindings de `today()->toDateString()` y `today()->addDays(7)->toDateString()`:

```sql
SUM(CASE WHEN ingresos_productores.estado = 'abierta'
 AND fecha_caducidad IS NOT NULL AND fecha_caducidad >= ?
 THEN cantidad_disponible ELSE 0 END) AS vendible
```

`proximo` usa vencimiento entre hoy y +7; `no_vendible` usa el complemento de la condición vendible; `fisico` suma todo saldo positivo. Filtrar siempre `cantidad_disponible > 0`, agrupar por IDs y nombres necesarios, ordenar producto/presentación/productor y paginar 20 grupos. `totales()` envuelve la misma consulta filtrada como subconsulta y suma las cuatro columnas, evitando sumar páginas parciales.

- [ ] **Step 4: Añadir controlador, ruta y vista**

`inventarioGeneral(FiltroInventarioRequest $request, InventarioConsulta $consulta)` entrega `grupos`, `totales` y `filtros`. La vista muestra tarjetas Vendible/Próximo/No vendible/Físico, filtros de producto/productor/vencimiento, tabla agrupada y un `<details>` por grupo con lotes limitados a ese grupo. Task 4 añadirá el enlace de devolución cuando exista la ruta correspondiente.

- [ ] **Step 5: Ejecutar GREEN y revisar consultas**

Run: `php artisan test tests/Feature/InventarioGeneralTest.php`

Expected: PASS. Agregar `DB::enableQueryLog()` en una prueba con 25 filas del mismo grupo y afirmar que renderizar usa como máximo 8 consultas; quitar el log al terminar con `DB::disableQueryLog()`.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Requests/FiltroInventarioRequest.php app/Services/InventarioConsulta.php app/Http/Controllers/ComercialController.php routes/web.php resources/views/inventario/general.blade.php tests/Feature/InventarioGeneralTest.php
git commit -m "feat: agregar inventario general"
```

### Task 4: Espacio directo de devoluciones

**Files:**
- Create: `app/Http/Requests/FiltroDevolucionRequest.php`
- Modify: `app/Services/InventarioConsulta.php`
- Modify: `app/Http/Controllers/ComercialController.php`
- Modify: `routes/web.php`
- Create: `resources/views/devoluciones/index.blade.php`
- Modify: `resources/views/inventario/index.blade.php`
- Modify: `resources/views/inventario/general.blade.php`
- Create: `tests/Feature/DevolucionesWorkspaceTest.php`

**Interfaces:**
- Produces: `InventarioConsulta::devoluciones(array): LengthAwarePaginator`, `InventarioConsulta::lotesParaDevolver(array): LengthAwarePaginator` y `InventarioConsulta::seleccionarLote(?int): ?IngresoProductorItem`.
- Route: GET `/devoluciones`, name `devoluciones.index`.
- Consumes: POST existente `lotes.devoluciones.store` y `ComercialService::devolver(IngresoProductorItem $original, array $datos, int $operador): void`.

- [ ] **Step 1: Escribir pruebas RED para acceso, filtros y selección segura**

```php
public function test_worker_can_open_returns_and_filter_history(): void
{
    [$worker, $visible, $hidden] = $this->returnFixtures();
    $this->actingAs($worker)->get('/devoluciones?producto=Yogurt&productor=Norte&tipo=cliente')
        ->assertOk()->assertSee('#'.$visible->id)->assertDontSee('#'.$hidden->id);
}

public function test_selected_lot_offers_only_its_sales_with_remaining_return_quantity(): void
{
    [$admin, $lot, $eligibleSale, $otherSale] = $this->saleFixtures();
    $this->actingAs($admin)->get('/devoluciones?lote='.$lot->id.'&tipo=cliente')
        ->assertOk()->assertSee('value="'.$eligibleSale->id.'"', false)
        ->assertDontSee('value="'.$otherSale->id.'"', false);
}

public function test_direct_customer_return_updates_stock_and_appears_in_history(): void
{
    [$admin, $lot, $sale] = $this->saleFixtures();
    $this->actingAs($admin)->post(route('lotes.devoluciones.store', $lot), [
        'tipo' => 'cliente', 'venta_id' => $sale->id,
        'cantidad_devuelta' => '1.0', 'observaciones' => 'Envase sin abrir',
    ])->assertRedirect(route('devoluciones.index', ['lote' => $lot->id]));
    $this->assertDatabaseHas('devoluciones', ['venta_id' => $sale->id, 'cantidad_devuelta' => '1.000']);
}

private function saleFixtures(): array
{
    $admin = User::factory()->create(['rol'=>'Administrador','activo'=>true]);
    $producer = Productor::create(['nombres'=>'Ana','primer_apellido'=>'Mendoza','nombre_unidad_productiva'=>'Finca Norte','activo'=>true]);
    $product = Producto::create(['nombre'=>'Yogurt','temperatura_minima'=>2,'temperatura_maxima'=>8,'activo'=>true]);
    $presentation = Presentacion::create(['producto_id'=>$product->id,'nombre'=>'Botella','sabor'=>'Frutilla','contenido'=>2,'unidad'=>'L','precio'=>30,'stock'=>7,'activo'=>true]);
    $entry = IngresoProductor::create(['productor_id'=>$producer->id,'user_id'=>$admin->id,'fecha_ingreso'=>today(),'estado'=>'abierta']);
    $lot = IngresoProductorItem::create(['ingreso_productor_id'=>$entry->id,'presentacion_id'=>$presentation->id,'cantidad_ingresada'=>10,'cantidad_disponible'=>7,'precio_acopio_unitario'=>20,'precio_venta_unitario'=>30,'fecha_recepcion'=>today(),'fecha_caducidad'=>today()->addMonth()]);
    $otherLot = IngresoProductorItem::create(['ingreso_productor_id'=>$entry->id,'presentacion_id'=>$presentation->id,'cantidad_ingresada'=>2,'cantidad_disponible'=>1,'precio_acopio_unitario'=>20,'precio_venta_unitario'=>30,'fecha_recepcion'=>today(),'fecha_caducidad'=>today()->addMonth()]);
    $ticket = TicketVenta::create(['clave'=>(string) Str::uuid(),'payload_hash'=>hash('sha256','fixture'),'user_id'=>$admin->id,'metodo_pago'=>'efectivo','total'=>120]);
    $eligible = Venta::create(['ticket_venta_id'=>$ticket->id,'ingreso_productor_item_id'=>$lot->id,'user_id'=>$admin->id,'cantidad_vendida'=>3,'precio_unitario_venta'=>30,'fecha_venta'=>now()]);
    $other = Venta::create(['ticket_venta_id'=>$ticket->id,'ingreso_productor_item_id'=>$otherLot->id,'user_id'=>$admin->id,'cantidad_vendida'=>1,'precio_unitario_venta'=>30,'fecha_venta'=>now()]);
    return [$admin, $lot, $eligible, $other];
}

private function returnFixtures(): array
{
    [$admin, $lot, $sale] = $this->saleFixtures();
    $worker = User::factory()->create(['rol'=>'Trabajador','activo'=>true]);
    $visible = Devolucion::create(['ingreso_productor_item_id'=>$lot->id,'venta_id'=>$sale->id,'user_id'=>$worker->id,'cantidad_devuelta'=>1,'precio_unitario_venta'=>30,'fecha_devolucion'=>now(),'tipo'=>'cliente']);
    $otherProduct = Producto::create(['nombre'=>'Queso','temperatura_minima'=>2,'temperatura_maxima'=>8,'activo'=>true]);
    $otherPresentation = Presentacion::create(['producto_id'=>$otherProduct->id,'nombre'=>'Bloque','contenido'=>1,'unidad'=>'kg','precio'=>40,'stock'=>0,'activo'=>true]);
    $otherLot = IngresoProductorItem::create(['ingreso_productor_id'=>$lot->ingreso_productor_id,'presentacion_id'=>$otherPresentation->id,'cantidad_ingresada'=>1,'cantidad_disponible'=>0,'precio_acopio_unitario'=>30,'precio_venta_unitario'=>40,'fecha_recepcion'=>today(),'fecha_caducidad'=>today()->addMonth()]);
    $hidden = Devolucion::create(['ingreso_productor_item_id'=>$otherLot->id,'user_id'=>$admin->id,'cantidad_devuelta'=>1,'fecha_devolucion'=>now(),'tipo'=>'productor']);
    return [$worker, $visible, $hidden];
}
```

- [ ] **Step 2: Ejecutar RED**

Run: `php artisan test tests/Feature/DevolucionesWorkspaceTest.php`

Expected: FAIL con ruta inexistente y redirección actual a la pantalla anterior.

- [ ] **Step 3: Implementar filtros y selección**

Validar historial: producto/productor texto máximo 100, `tipo` en `todos,cliente,productor`, fechas de devolución y vencimiento con pares ordenados. Validar selector: `buscar_lote` texto máximo 100 y `lote` entero existente.

El historial precarga `lote.presentacion.producto`, `lote.ingresoProductor.productor`, `venta` y `responsable`; aplica filtros mediante `whereHas`; ordena por `fecha_devolucion DESC, id DESC`; pagina 20.

El selector limita lotes a ingresos abiertos. Si `tipo=cliente`, cargar ventas del lote con `withSum(['devoluciones as cantidad_devuelta_cliente' => fn ($q) => $q->where('tipo','cliente')], 'cantidad_devuelta')` y conservar sólo ventas cuyo vendido sea mayor que lo devuelto. No aceptar `venta_id` aportado para otro lote: la validación transaccional existente continúa como defensa final.

- [ ] **Step 4: Hacer que el POST regrese al espacio de devoluciones**

Tras `ComercialService::devolver`, las solicitudes HTML redirigen explícitamente a `route('devoluciones.index', ['lote' => $item->id])` con éxito. Para llamadas JSON conservar la respuesta `['message'=>'Devolución registrada.']`. No cambiar la transacción ni los cálculos del servicio.

- [ ] **Step 5: Crear vista accesible y rápida**

Arriba: búsqueda de lote/producto/productor/venta. Cuando exista lote seleccionado: resumen y formulario con tipo, venta elegible, cantidad y observación. Abajo: filtros del historial y tabla paginada. Incluir errores junto al formulario y estados vacíos diferenciados. Evitar cargar ventas cuando no hay lote elegido. Añadir en ambas vistas de inventario enlaces `route('devoluciones.index', ['lote'=>$item->id])` para preseleccionar el lote.

- [ ] **Step 6: Ejecutar GREEN y regresiones contables**

Run: `php artisan test tests/Feature/DevolucionesWorkspaceTest.php tests/Feature/AcopioComercialTest.php tests/Feature/AcopioIntegrityTest.php`

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Requests/FiltroDevolucionRequest.php app/Services/InventarioConsulta.php app/Http/Controllers/ComercialController.php routes/web.php resources/views/devoluciones/index.blade.php resources/views/inventario tests/Feature/DevolucionesWorkspaceTest.php
git commit -m "feat: centralizar devoluciones comerciales"
```

### Task 5: Navegación y acabado visual

**Files:**
- Modify: `resources/views/partials/sidebar.blade.php`
- Modify: `resources/views/inventario/index.blade.php`
- Modify: `resources/views/inventario/general.blade.php`
- Modify: `resources/views/devoluciones/index.blade.php`
- Modify: `resources/css/comercial.css`
- Modify: `tests/Feature/CommercialPagesTest.php`

**Interfaces:**
- Consumes: rutas `inventario.index`, `inventario-general.index`, `devoluciones.index`.
- Produces: navegación visible y estilos `.filter-grid`, `.metric-grid`, `.stock-state`.

- [ ] **Step 1: Escribir prueba RED del menú y páginas responsive**

```php
public function test_commercial_shell_exposes_lot_general_inventory_and_returns(): void
{
    $this->actingAs(User::factory()->create(['rol' => 'Trabajador', 'activo' => true]));
    foreach (['/inventario', '/inventario-general', '/devoluciones'] as $url) {
        $this->get($url)->assertOk()
            ->assertSee('Inventario por Lote')->assertSee('Inventario General')->assertSee('Devoluciones');
    }
}
```

- [ ] **Step 2: Ejecutar RED**

Run: `php artisan test --filter=commercial_shell_exposes_lot_general_inventory_and_returns`

Expected: FAIL porque faltan dos enlaces.

- [ ] **Step 3: Añadir enlaces y estilos mínimos**

Agregar los dos enlaces junto a Inventario por Lote y usar `routeIs()` independiente. Crear grid de filtros con `grid-template-columns: repeat(auto-fit, minmax(180px, 1fr))`, métricas con mínimo 210px y badges con variantes `sellable`, `warning`, `blocked`. En `@media (max-width: 768px)`, botones al ancho y tablas dentro de `.table-wrap` con `overflow-x:auto`.

- [ ] **Step 4: Ejecutar GREEN y compilar**

Run: `php artisan test tests/Feature/CommercialPagesTest.php tests/Feature/InventarioPorLoteTest.php tests/Feature/InventarioGeneralTest.php tests/Feature/DevolucionesWorkspaceTest.php`

Run: `npm run build`

Expected: todas PASS y Vite termina con exit code 0.

- [ ] **Step 5: Commit**

```bash
git add resources/views/partials/sidebar.blade.php resources/views/inventario resources/views/devoluciones resources/css/comercial.css tests/Feature/CommercialPagesTest.php
git commit -m "style: integrar inventarios y devoluciones al panel"
```

### Task 6: Índices aditivos y verificación integral

**Files:**
- Create: `database/migrations/2026_09_11_000001_add_inventory_search_indexes.php`
- Modify: `tests/Feature/AcopioMigrationTest.php`

**Interfaces:**
- Produces: índices `ingresos_productor_estado_fecha_idx`, `lotes_presentacion_vencimiento_saldo_idx`, `lotes_recepcion_idx`, `devoluciones_tipo_fecha_idx`, `productores_activo_idx`.

- [ ] **Step 1: Escribir prueba RED de índices**

Usar `Schema::getIndexes($table)` y comparar nombres literales:

```php
public function test_inventory_filter_indexes_exist(): void
{
    $this->assertContains('ingresos_productor_estado_fecha_idx', array_column(Schema::getIndexes('ingresos_productores'), 'name'));
    $this->assertContains('lotes_presentacion_vencimiento_saldo_idx', array_column(Schema::getIndexes('ingresos_productores_items'), 'name'));
    $this->assertContains('lotes_recepcion_idx', array_column(Schema::getIndexes('ingresos_productores_items'), 'name'));
    $this->assertContains('devoluciones_tipo_fecha_idx', array_column(Schema::getIndexes('devoluciones'), 'name'));
    $this->assertContains('productores_activo_idx', array_column(Schema::getIndexes('productores'), 'name'));
}
```

- [ ] **Step 2: Ejecutar RED**

Run: `php artisan test --filter=inventory_filter_indexes_exist`

Expected: FAIL porque los índices nombrados no existen.

- [ ] **Step 3: Crear migración reversible**

En `up()` añadir:

```php
Schema::table('ingresos_productores', fn (Blueprint $t) => $t->index(['productor_id','estado','fecha_ingreso'], 'ingresos_productor_estado_fecha_idx'));
Schema::table('ingresos_productores_items', function (Blueprint $t) {
    $t->index(['presentacion_id','fecha_caducidad','cantidad_disponible'], 'lotes_presentacion_vencimiento_saldo_idx');
    $t->index('fecha_recepcion', 'lotes_recepcion_idx');
});
Schema::table('devoluciones', fn (Blueprint $t) => $t->index(['tipo','fecha_devolucion'], 'devoluciones_tipo_fecha_idx'));
Schema::table('productores', fn (Blueprint $t) => $t->index('activo', 'productores_activo_idx'));
```

`down()` elimina exactamente esos cinco índices por nombre.

- [ ] **Step 4: Ejecutar GREEN, migración reversible y suite**

Run: `php artisan test --filter=inventory_filter_indexes_exist`

Run contra la base de prueba desechable: `php artisan migrate:fresh --env=testing`

Run: `php artisan test`

Run: `node --test tests/js/async-panel.test.mjs`

Run: `npm run build`

Expected: todos los comandos terminan con exit code 0.

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_09_11_000001_add_inventory_search_indexes.php tests/Feature/AcopioMigrationTest.php
git commit -m "perf: indexar consultas de inventario"
```
