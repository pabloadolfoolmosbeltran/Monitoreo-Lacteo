# AUDITORÍA PREVIA DE CÓDIGO MUERTO

> Estado: **CM-001 y CM-002 ejecutados con aprobación; CV-001 y CV-002 siguen pendientes y sin cambios**.  
> Fecha: 24 de septiembre de 2026.  
> Regla aplicada: antes de borrar, se revisaron rutas, controladores, modelos, vistas, imports JavaScript, entradas de Vite y pruebas.

> **Ejecución aprobada posteriormente:** CM-001 y CM-002 fueron ejecutados el 24 de septiembre de 2026. Se retiró la acción GET `usuarios.show`, se eliminó su vista vacía y se eliminó el alias duplicado `Venta::item()`. Los candidatos CV-001 y CV-002 permanecen sin cambios.

## Índice

1. [Resultado ejecutivo](#1-resultado-ejecutivo)
2. [Candidatos recomendados para eliminación](#2-candidatos-recomendados-para-eliminación)
3. [Candidatos que requieren verificación](#3-candidatos-que-requieren-verificación)
4. [Archivos aislados que no deben eliminarse](#4-archivos-aislados-que-no-deben-eliminarse)
5. [Artefactos temporales y generados](#5-artefactos-temporales-y-generados)
6. [Código comprobado como activo](#6-código-comprobado-como-activo)
7. [Procedimiento propuesto después de la aprobación](#7-procedimiento-propuesto-después-de-la-aprobación)
8. [Evidencia de la auditoría](#8-evidencia-de-la-auditoría)

---

## 1. Resultado ejecutivo

La auditoría conservadora encontró:

- **2 candidatos de eliminación con evidencia fuerte**: una vista vacía ligada a una ruta rota y una relación Eloquent duplicada sin consumidores.
- **2 grupos probables**, pero todavía no seguros: un helper JavaScript usado solo por su prueba y un grupo de accessors financieros sin consumidores visibles.
- **1 modelo sin referencias directas**, `Configuracion`, que **no se propone eliminar** porque pertenece al dominio IoT protegido.
- **0 controladores completos** que puedan eliminarse con seguridad.
- **0 servicios completos** que puedan eliminarse con seguridad.
- **0 rutas IoT** candidatas a eliminación.
- **0 modelos IoT** candidatos a eliminación.

En la auditoría inicial no se modificó código IoT. Después de la aprobación se ejecutaron únicamente CM-001 y CM-002. No se añadieron comentarios masivos ni se tocaron los candidatos pendientes CV-001 y CV-002.

---

## 2. Candidatos recomendados para eliminación — ejecutados

### CM-001 — Vista vacía de detalle de usuario y ruta `show` no implementada

| Campo | Evidencia |
|---|---|
| Archivo candidato | `resources/views/usuarios/show.blade.php` |
| Estado | Archivo de **0 bytes**. |
| Ruta relacionada | `GET /usuarios/{usuario}`, generada automáticamente por `Route::resource('usuarios', UserController::class)` en `routes/web.php:101`. |
| Controller esperado | `UserController::show(User $usuario)`. |
| Hallazgo | `UserController` no contiene el método `show()`. |
| Referencias desde GUI | No se encontraron `route('usuarios.show')`, enlaces a `/usuarios/{id}` ni llamadas a `view('usuarios.show')`. |
| Efecto actual | La ruta existe en `php artisan route:list`, pero al invocarla intenta llamar un método inexistente. La vista vacía nunca puede renderizarse por el flujo actual. |
| Riesgo de borrar solo la vista | Medio: dejaría la ruta rota exactamente como está; por eso no debe eliminarse de forma aislada. |
| Acción propuesta tras aprobación | 1) Cambiar el recurso de usuarios para no generar `show`, usando `->except(['show'])` o una lista `->only(...)`; 2) eliminar la vista vacía; 3) probar el CRUD de usuarios. |

**Resultado ejecutado:** la vista fue eliminada y el resource de usuarios ahora usa `->except(['show'])`. Las rutas `index`, `create`, `store`, `edit`, `update`, `destroy` y `restore` permanecen activas.

### CM-002 — Relación duplicada `Venta::item()`

| Campo | Evidencia |
|---|---|
| Archivo | `app/Models/Venta.php` |
| Método candidato | `public function item(): BelongsTo` |
| Duplicación | Devuelve exactamente la misma relación y clave foránea que `Venta::lote()`: `IngresoProductorItem` mediante `ingreso_productor_item_id`. |
| Referencias | No se encontraron `->item`, `with('item')`, `load('item')`, `whereHas('item')` ni llamadas directas en `app`, `routes`, `resources` o `tests`. |
| Relación vigente | Todo el código activo utiliza `lote`: reportes, tickets, ventas, exportaciones y servicios. |
| Riesgo | Bajo dentro del repositorio; podría existir un consumidor externo no versionado que espere el nombre `item`. |
| Acción propuesta tras aprobación | Eliminar solo el método `item()` y ejecutar todas las pruebas de ventas, reportes y acopio. |

**Resultado ejecutado:** se eliminó únicamente `Venta::item()`. La relación vigente `Venta::lote()` permanece sin cambios.

---

## 3. Candidatos que requieren verificación

Estos elementos **no se deben eliminar todavía**. Conforme a la Regla 1, existe una duda razonable y se conservarán hasta que el usuario decida.

### CV-001 — Helper JavaScript de selección de proveedor

```text
// ⚠️ VERIFICAR SI SE USA
resources/js/supplier-cart.js
tests/js/supplier-cart.test.mjs
```

| Comprobación | Resultado |
|---|---|
| Importado por código de producción | No. `comercial.js` no lo importa. |
| Entrada de Vite | No aparece como entrada directa. |
| Referenciado por vistas | No. |
| Referenciado por pruebas | Sí: `tests/js/supplier-cart.test.mjs`. |
| Motivo histórico probable | La interfaz actual ya no solicita proveedor por ítem; el servidor puede filtrar por proveedor, pero el carrito envía normalmente presentación y cantidad. |

**Decisión recomendada:** conservar por ahora. Si se confirma que no se restaurará selección manual de proveedor en el POS, proponer juntos la eliminación del helper y de su prueba exclusiva. No es código totalmente desconectado porque una prueba todavía lo importa.

### CV-002 — Accessors financieros de acopio sin consumidor visible

```text
// ⚠️ VERIFICAR SI SE USA
app/Models/IngresoProductor.php
app/Models/IngresoProductorItem.php
```

Métodos bajo revisión:

- `IngresoProductor::getMontoProductorAttribute()`
- `IngresoProductor::getMargenEncargadoAttribute()`
- `IngresoProductor::sumarItems()`
- `IngresoProductorItem::getCantidadVendidaAttribute()`
- `IngresoProductorItem::getCantidadVendidaNetaAttribute()`
- `IngresoProductorItem::getMontoProductorAttribute()`
- `IngresoProductorItem::getMontoProductorNetaAttribute()`
- `IngresoProductorItem::getMargenEncargadoAttribute()`
- `IngresoProductorItem::getMargenEncargadoNetoAttribute()`
- `IngresoProductorItem::ventasParaCalculo()`

Evidencia:

- No se encontraron lecturas de `monto_productor`, `margen_encargado`, `cantidad_vendida_neta`, `monto_productor_neta` o `margen_encargado_neto` fuera de este grupo de métodos.
- Esos atributos no están incluidos en `$appends` del modelo; por tanto, no se serializan automáticamente.
- El grupo conserva nombres como “neta”, posiblemente heredados de la versión que manejaba devoluciones.
- Aun así, Eloquent permite acceder dinámicamente a estos atributos desde código externo o desde una vista futura sin una llamada de método textual fácilmente detectable.

**Decisión recomendada:** no eliminar en la primera limpieza. Primero añadir pruebas o confirmar que ningún cliente/API externo consume esos atributos. Si se aprueba retirarlos, hacerlo como un bloque y verificar reportes, detalle de entradas y serialización JSON.

---

## 4. Archivos aislados que no deben eliminarse

### 4.1 `Configuracion` — protegido por la Regla 2

```text
// ⚠️ VERIFICAR SI SE USA
app/Models/Configuracion.php
```

No se encontraron referencias directas a `Configuracion::sistema()` ni al modelo desde el código activo. Sin embargo:

- representa la tabla IoT `configuraciones`;
- contiene estados y umbrales de motor, ventilador, sensor y pasteurización;
- la Regla 2 prohíbe modificar o eliminar modelos IoT;
- puede ser consumido por firmware, comandos, código no incluido o una integración futura.

**Decisión:** excluido de toda eliminación y refactorización. En la etapa de comentarios solo podría recibir comentarios encima de métodos, sin cambiar su lógica.

### 4.2 `Reporte` y tabla `reportes`

`app/Models/Reporte.php` no es usado directamente por `ReporteController`, pero está conectado mediante `Produccion::reportes()` y una clave foránea del esquema. No se considera muerto.

### 4.3 Migraciones históricas

Las migraciones de consignaciones, devoluciones y liquidaciones no son código muerto aunque las tablas finales hayan cambiado. Laravel necesita el historial para construir o migrar una base desde cero. No deben eliminarse.

### 4.4 Rutas antiguas de consignación

Las rutas `/consignaciones`, `/mis-consignaciones` y `/consignaciones/{id}` siguen conectadas como redirecciones de compatibilidad hacia entradas de inventario. No son rutas muertas.

### 4.5 Policies sin referencia textual explícita

`DescarteProductoPolicy` puede resolverse por descubrimiento automático de policies de Laravel. Su baja cantidad de referencias textuales no demuestra que esté muerta; además, los controladores llaman a `authorize()`. No se elimina.

### 4.6 Archivos compilados de Vite

Todos los archivos actuales de `public/build/assets` aparecen en `public/build/manifest.json`. El directorio está ignorado por Git y se regenera con `npm run build`. No se propone borrar archivos individuales.

---

## 5. Artefactos temporales y generados

Estos archivos no participan en rutas, controladores, vistas, modelos, imports de frontend ni configuración de ejecución. No se clasifican como código de aplicación; parecen artefactos de generación documental.

### 5.1 Candidatos opcionales de limpieza

| Ruta | Tipo | Referencia en ejecución | Recomendación |
|---|---|---|---|
| `tmp/pdfs/final_render/*.png` | Render temporal de páginas | Ninguna | Puede eliminarse después de confirmar que la revisión visual terminó. |
| `tmp/pdfs/modelo/*.png` | Render del documento modelo | Ninguna | Conservar si todavía se compara el diseño; de lo contrario, limpiar. |
| `tmp/pdfs/modelo/DocumentosPruebas.txt` | Extracción temporal | Ninguna | Revisar antes de eliminar porque puede contener texto fuente útil. |
| `tmp/pdfs/generate_qa_pdf.py` | Script generador documental | Ninguna desde la aplicación | No eliminar automáticamente; puede ser necesario para regenerar el PDF. |
| `output/pdf/Documentacion_Pruebas_14_MDs_Monitoreo_Lacteo.pdf` | Salida generada | Ninguna desde la aplicación | Es una entrega, no código muerto. Conservar o mover a documentación. |

### 5.2 Duplicado aparente de PDF

Existen:

- `output/pdf/Documentacion_Pruebas_14_MDs_Monitoreo_Lacteo.pdf`
- `Documentacion_Pruebas_14_MDs_Monitoreo_Lacteo.pdf` en la raíz

Ambos tienen 126633 bytes, pero antes de declarar duplicado se debe comparar hash. No se propone eliminar ninguno sin esa verificación y aprobación.

### 5.3 Archivo vacío no relacionado con código

`public/favicon.ico` tiene 0 bytes. Los navegadores pueden solicitarlo implícitamente, por lo que no se considera código muerto; la acción correcta sería reemplazarlo por un favicon válido, no borrarlo durante esta limpieza.

---

## 6. Código comprobado como activo

La siguiente revisión evita falsos positivos:

- Todos los controllers, salvo la acción faltante `UserController::show`, están conectados a rutas activas.
- Todos los Services tienen al menos un consumidor desde controllers, otros services o exportaciones.
- `ingresos/_item.blade.php` está incluido dos veces por `ingresos/create.blade.php` (fila inicial y template dinámico).
- `usuarios/form.blade.php` está incluido por las vistas create/edit.
- `partials/footer.blade.php` está incluido por `layouts/app.blade.php`.
- `rennet-recommendation.js` es importado por `produccion.js` y tiene pruebas.
- `reporte-ventas.js`, `responsive-tables.js`, `async-panel.js` y `bootstrap.js` son importados por `app.js`.
- Las hojas CSS están registradas como entradas de Vite y cargadas por el layout.
- `Reporte` está conectado por la relación `Produccion::reportes()`.
- Las rutas de consignación son redirecciones intencionales.
- Los componentes IoT fueron únicamente inspeccionados y quedan protegidos.

---

## 7. Procedimiento propuesto después de la aprobación

No ejecutar hasta recibir autorización explícita.

### Grupo A — Limpieza segura recomendada

1. Cambiar solo la declaración `Route::resource('usuarios', ...)` para excluir `show`.
2. Eliminar `resources/views/usuarios/show.blade.php`, actualmente vacío.
3. Eliminar únicamente `Venta::item()` y mantener `Venta::lote()`.
4. Ejecutar `php artisan route:list` y confirmar que las demás rutas de usuarios permanecen.
5. Ejecutar pruebas de usuarios, ventas, acopio y reportes.

### Grupo B — Mantener en observación

1. Añadir `// ⚠️ VERIFICAR SI SE USA` sobre el grupo de accessors financieros y documentar sus posibles consumidores externos.
2. Mantener `supplier-cart.js` hasta decidir si la selección manual de proveedor regresará al POS.
3. No modificar `Configuracion` ni ningún componente IoT.

### Grupo C — Temporales

1. Comparar hashes de PDFs antes de declarar duplicados.
2. Confirmar si el script generador y renders temporales siguen siendo necesarios.
3. Limpiar temporales solo con una aprobación separada y targets exactos.

---

## 8. Evidencia de la auditoría

Se usaron comprobaciones de solo lectura:

- `php artisan route:list --json` para conexiones ruta → controller → método.
- Búsqueda de clases, métodos, relaciones, nombres de vista y rutas con `rg`.
- Revisión de imports ES Modules en `resources/js`.
- Revisión de entradas en `vite.config.js`, llamadas `@vite` y `public/build/manifest.json`.
- Revisión de referencias en `app`, `routes`, `resources` y `tests`.
- Revisión de archivos de 0 bytes.
- Revisión de `git status` para no confundir cambios previos del usuario con cambios de esta auditoría.

### Limitaciones

El análisis estático no puede detectar con certeza consumidores externos no incluidos en el repositorio, llamadas construidas dinámicamente, firmware externo o integraciones que lean atributos Eloquent por nombre. Por esa razón los casos dudosos no se incluyen en la eliminación recomendada.

---

## Decisión solicitada

Para la siguiente fase se requiere aprobación específica de uno o más grupos:

- **Aprobar CM-001:** retirar ruta `show` de usuarios y borrar la vista vacía.
- **Aprobar CM-002:** eliminar la relación duplicada `Venta::item()`.
- **Mantener CV-001 y CV-002:** recomendación conservadora actual.
- **Revisar temporales:** decisión separada; no afecta el código de ejecución.

La decisión fue recibida con la instrucción “Ejecuta”. CM-001 y CM-002 quedaron ejecutados; cualquier eliminación adicional continúa requiriendo una aprobación nueva.
