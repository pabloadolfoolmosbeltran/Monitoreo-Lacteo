# Eliminación de devoluciones, cantidades enteras y menú unificado

## Objetivo

Eliminar completamente el concepto de devoluciones del sistema, convertir a enteros todas las cantidades que representan unidades físicas comercializables y corregir el menú lateral para que use un único fondo, sin alterar los flujos de producción, ventas, ingresos, inventario, liquidaciones, reportes ni administración.

## Alcance funcional

### Eliminación de devoluciones

El sistema dejará de crear, consultar, mostrar, exportar o relacionar devoluciones. Se eliminarán:

- La tabla `devoluciones`, sus claves foráneas, índices y datos.
- El modelo `Devolucion` y el request `FiltroDevolucionRequest`.
- Las rutas y acciones del controlador dedicadas a devoluciones.
- La lógica transaccional de devolución en `ComercialService`.
- Las consultas de historial y ventas elegibles en `InventarioConsulta`.
- Las relaciones Eloquent hacia devoluciones en ventas, lotes y usuarios.
- La vista de devoluciones, enlaces, formularios y entrada del menú lateral.
- Las secciones de devoluciones en reportes PDF y Excel.
- Las pruebas dedicadas al módulo y los escenarios que dependan de él.

Los efectos históricos que una devolución ya haya producido sobre el stock no se revertirán. La migración elimina el historial de devoluciones, pero conserva los saldos actuales como punto de partida.

Para cerrar una entrada con existencias pendientes, el operador deberá venderlas o registrar una salida/ajuste auditado desde Inventario por lote. El mensaje de validación de liquidación explicará ese flujo y no mencionará devoluciones.

### Cantidades enteras

Se convertirán a enteros los campos que representan conteos de unidades:

- `presentaciones.stock`
- `presentaciones.stock_minimo_alerta`
- `ingresos_productores_items.cantidad_ingresada`
- `ingresos_productores_items.cantidad_disponible`
- `ventas.cantidad_vendida`
- `movimientos_inventario.cantidad` (entero con signo porque los egresos y ajustes pueden ser negativos)
- `conciliaciones_stock_acopio.stock_anterior`
- `conciliaciones_stock_acopio.stock_lotes`
- `conciliaciones_stock_acopio.diferencia` (entero con signo)

No se convertirán a enteros precios, totales monetarios, temperaturas, litros de leche, dosis o stock de cuajo, contenido del envase ni otras magnitudes que legítimamente admiten decimales.

La aplicación validará las cantidades nuevas con reglas `integer`, límites enteros y controles HTML con `step="1"`. Los modelos devolverán estas cantidades como `int`. Los cálculos monetarios seguirán usando `App\Support\Decimal` para evitar errores de coma flotante.

Las operaciones FEFO distribuirán únicamente unidades enteras entre lotes. Los saldos globales y por lote continuarán actualizándose dentro de una única transacción y con los bloqueos existentes.

## Política para datos históricos fraccionarios

Antes de cambiar tipos, la migración inspeccionará cada columna objetivo. Si encuentra un valor con parte fraccionaria, abortará sin modificar el esquema y lanzará un error que identifique tabla, columna e IDs afectados.

No se redondeará, truncará ni recalculará silenciosamente ningún histórico. Después de corregir explícitamente los registros informados, la migración podrá ejecutarse de nuevo.

La comprobación y el cambio de tipos estarán cubiertos tanto en SQLite de pruebas como en MySQL. La migración será ordenada para no dejar un esquema parcialmente convertido si ocurre un error.

## Estrategia de migraciones

Se añadirá una migración terminal para instalaciones existentes. Esta migración:

1. Comprueba que las cantidades objetivo no contienen fracciones.
2. Elimina índices dependientes de devoluciones cuando existan.
3. Elimina la tabla `devoluciones` con sus claves foráneas.
4. Convierte los campos de conteo a enteros conservando nulabilidad, valores predeterminados y signo.

También se limpiarán las migraciones históricas todavía no publicadas como versión estable para que `migrate:fresh` no cree ni transforme devoluciones en ningún momento. La migración terminal seguirá siendo necesaria para bases que ya ejecutaron esas migraciones.

El método `down()` no reconstruirá datos de devoluciones eliminados. Fallará explícitamente indicando que la restauración requiere un respaldo anterior. Esto evita una falsa reversibilidad.

El volcado SQL incluido en el proyecto se actualizará para reflejar el esquema final sin devoluciones y con cantidades enteras, sin inventar ni redondear datos.

## Cambios de backend y frontend

`ComercialController` conservará ventas, ingresos, inventarios, movimientos, regularización y liquidación. Sus validaciones de cantidades aceptarán solamente enteros positivos, salvo ajustes de inventario, que admitirán enteros positivos o negativos distintos de cero.

`ComercialService` eliminará `devolver()`. Las operaciones de ingreso, venta y movimiento trabajarán con cantidades enteras; los precios y subtotales mantendrán precisión decimal monetaria. Los cálculos de liquidación usarán ventas brutas, ya que no existirá una deducción por devoluciones.

`IngresoProductorItem` calculará cantidad vendida, monto del productor y margen sin relaciones ni deducciones de devoluciones. `Venta` dejará de exponer la relación correspondiente.

Las vistas y JavaScript mostrarán cantidades sin decimales (`0` posiciones), usarán `step="1"` y eliminarán todas las acciones, filtros y textos de devoluciones. Los informes comerciales contendrán entradas, ventas, liquidaciones y ajustes por lote.

## Menú lateral

La causa raíz observada es que el contenedor externo `.sidebar` usa `--sidebar-bg: #1e293b`, mientras `.sidebar-container` usa `#112224`. Son dos capas anidadas con fondos distintos; cualquier espacio, altura o desplazamiento visible expone ambos colores. No hay lógica JavaScript que cambie el color.

Se definirá un único token de color para el menú y tanto `#app-sidebar`/`.sidebar` como `.sidebar-container` lo usarán. Se retirará el relleno redundante del contenedor externo y se conservarán la estructura Blade, el desplazamiento independiente, el comportamiento móvil y los estados activo, hover y foco.

## Pruebas y verificación

El trabajo seguirá ciclos TDD para cambios de comportamiento:

- Una prueba de migración fallará primero al encontrar una cantidad fraccionaria y comprobará que el esquema no cambió.
- Una prueba de migración con cantidades enteras comprobará los tipos finales y la ausencia de la tabla `devoluciones`.
- Pruebas HTTP rechazarán fracciones en ingresos y movimientos, además de la validación de ventas enteras ya existente.
- Pruebas de dominio comprobarán FEFO, stock, liquidaciones y reportes sin devoluciones.
- Pruebas de rutas y renderizado comprobarán que no quedan páginas, enlaces ni formularios de devolución.
- La suite completa de PHPUnit, el formateador de PHP y `npm run build` deberán finalizar correctamente.
- El menú se verificará visualmente en escritorio y móvil mediante los estilos calculados del navegador. Por tratarse de una corrección puramente CSS sin infraestructura de navegador automatizada en el repositorio, esta parte usará verificación visual y de compilación en lugar de una aserción frágil sobre texto CSS.

## Compatibilidad y límites

- Se preservarán los cambios locales existentes que no pertenezcan a este alcance.
- No se cambiarán importes monetarios ni magnitudes continuas a enteros.
- No se redondearán datos históricos.
- No se recreará un flujo alternativo de devoluciones.
- No se modificarán las reglas de autenticación ni los roles.
- No se ejecutará la migración destructiva contra una base real sin que la validación previa termine correctamente.

