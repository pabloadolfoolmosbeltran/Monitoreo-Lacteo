# Diseño: ajustes, ventas por proveedor, reportes y descartes

## Objetivo

Eliminar completamente el concepto de liquidaciones y ampliar el módulo comercial sin reemplazar la arquitectura existente de entradas, lotes, presentaciones, proveedores y ventas. El inventario seguirá teniendo una única fuente de verdad: `ingresos_productores_items.cantidad_disponible`, sincronizada con `presentaciones.stock` mediante operaciones transaccionales.

## Alcance

La entrega comprende:

- eliminación irreversible del módulo, tabla y datos de liquidaciones;
- reemplazo de “Inventario por Lote” por un CRUD de “Ajustes de Inventario”;
- selección explícita de proveedor en cada línea del carrito;
- reporte de productos más vendidos con gráfico, tabla y CSV;
- CRUD de productos descartados, procesamiento de stock, auditoría, PDF y CSV;
- documentación funcional, changelog y diagrama ER de las tablas nuevas.

No se modificarán la autenticación, los métodos de pago, el cálculo actual de totales del ticket, las ventas históricas, el orden FEFO dentro de un mismo proveedor ni los módulos de producción y monitoreo.

## Decisiones de arquitectura

### Fuente única de inventario

No se creará un inventario paralelo. Los ajustes y descartes actuarán sobre un lote existente (`ingresos_productores_items`) y actualizarán en la misma transacción el saldo del lote y el stock agregado de su presentación. Las ventas seguirán registrándose contra el lote consumido.

Las operaciones que escriben stock mantendrán el orden de bloqueo actual: presentación, cabecera de ingreso y lote. Esto reduce interbloqueos y evita vender, ajustar o descartar simultáneamente las mismas unidades.

### Importes

Los importes monetarios permanecerán como decimales exactos y usarán `App\Support\Decimal`. Las cantidades de unidades permanecerán enteras. No se emplearán operaciones de punto flotante para totales, pérdidas o porcentajes persistidos.

### Autorización

Se conservará el middleware comercial actual. Administradores y trabajadores podrán consultar y crear ajustes y descartes. Solamente un Administrador podrá:

- actualizar o anular un ajuste;
- procesar un descarte;
- editar o eliminar un descarte pendiente.

Las reglas se centralizarán en policies registradas mediante el descubrimiento convencional de Laravel. Los Form Requests validarán autorización y datos.

## 1. Eliminación total de liquidaciones

Se eliminarán:

- `Liquidacion` y todas sus relaciones Eloquent;
- la ruta y acción para liquidar entradas;
- el método de dominio que genera liquidaciones;
- botones, formularios, filtros, columnas y textos de interfaz;
- carga eager y consultas de liquidaciones;
- reportes o cálculos exclusivos de liquidación;
- la tabla `liquidaciones` y todos sus registros.

Una migración irreversible eliminará la tabla y sustituirá el estado `liquidada` en entradas existentes. Una entrada con unidades disponibles pasará a `abierta`; una entrada sin saldo pasará a `cerrada`. Después se ajustará la definición de `estado` para admitir solamente los estados vigentes. La migración no eliminará entradas, lotes, ventas ni movimientos.

El método `down()` explicará que restaurar liquidaciones requiere recuperar un respaldo, porque el usuario autorizó explícitamente eliminar también el historial.

## 2. Ajustes de inventario

### Datos

La tabla `inventory_adjustments` contendrá:

- `id`;
- `lot_id`, referencia restringida al lote;
- `created_by` y `updated_by`, referencias restringidas a usuarios;
- `quantity_before`, `quantity_after` y `delta`, enteros;
- `reason_type`: error de conteo, daño, remanente u otro;
- `reason`, detalle obligatorio;
- `status`: activo o anulado;
- `voided_by`, `voided_at` y `void_reason`, opcionales;
- timestamps.

Se indexarán lote, estado, motivo, creador y fecha. El modelo expondrá relaciones al lote, presentación, proveedor y responsables.

### Operaciones

- Crear: el usuario indica lote, cantidad física final y motivo. El servicio calcula la diferencia; no se aceptan saldos negativos ni ajustes sin cambio.
- Leer: lista paginada con filtros y detalle antes/después.
- Actualizar: solo Administrador y solo mientras el ajuste esté activo. Se revierte el delta anterior y se aplica el nuevo delta atómicamente, validando el saldo resultante.
- Eliminar: significa anular. Se aplica el movimiento inverso y se conservan registro, responsable, fecha y motivo de anulación.

Cada alta, actualización y anulación también generará un `movimientos_inventario` descriptivo. Los ajustes no modificarán `cantidad_ingresada`, precios, costos ni ventas históricas.

## 3. Selector de proveedor en ventas

### Catálogo

La respuesta del catálogo comercial agrupará los lotes vendibles por presentación y proveedor. Cada opción incluirá:

- `productor_id` y nombre;
- precio unitario del proveedor;
- stock vendible agregado;
- lotes ordenados por caducidad;
- tiempo estimado de entrega solamente si el sistema llega a disponer de ese dato; no se inventará un valor.

Cuando exista un solo proveedor se seleccionará automáticamente. Con varios, el usuario deberá elegir uno. El carrito permitirá cambiar la opción y recalculará precio, stock máximo y subtotal.

### Venta

Cada línea enviada al servidor incluirá `presentacion_id`, `productor_id` y `cantidad`. El hash de idempotencia incluirá también el proveedor. El servicio bloqueará y consumirá por FEFO únicamente lotes vigentes, abiertos y pertenecientes al proveedor elegido. El precio de la línea saldrá de esos lotes, no de un valor manipulable enviado por el navegador.

Si el proveedor no está activo, no tiene stock suficiente o sus lotes no comparten un precio unitario coherente para la cantidad solicitada, la transacción completa se revertirá con un mensaje de validación. El ticket seguirá detallando cada venta y lote consumido.

## 4. Productos más vendidos

El reporte de ventas admitirá `desde`, `hasta` y `productor_id`. No se añadirá filtro de categoría porque el sistema no tiene actualmente una entidad de categorías; añadirla estaría fuera del alcance aprobado.

La consulta agrupará ventas por producto y devolverá como máximo quince filas con:

- unidades vendidas;
- monto total vendido;
- porcentaje del monto total filtrado;
- posición.

La vista mostrará:

- gráfico horizontal con Chart.js ya disponible en el frontend;
- colores diferenciados para las tres primeras posiciones;
- tooltip con unidades, monto y porcentaje;
- tabla alternativa accesible;
- botón CSV que reutiliza exactamente los filtros y la consulta del gráfico.

Los filtros del gráfico se integrarán con los filtros existentes del reporte para evitar resultados contradictorios.

## 5. Productos descartados

### Datos

La tabla `product_discards` contendrá:

- `id` y número de referencia derivado del ID;
- `lot_id`, referencia restringida al lote;
- `created_by` y `updated_by`, referencias restringidas a usuarios;
- `quantity`, entero positivo;
- `expiration_date`, copia de la fecha del lote;
- `unit_cost`, copia del precio de acopio al registrar;
- `total_loss`, decimal exacto calculado al registrar;
- `reason_type`: caducado, dañado, deterioro u otro;
- `notes`, opcional;
- `status`: pendiente o procesado;
- `processed_by` y `processed_at`, opcionales;
- timestamps.

Se indexarán lote, estado, motivo, fecha de caducidad, fecha de creación y responsables. Producto, presentación y proveedor se obtendrán mediante el lote, evitando duplicar claves y permitir combinaciones inconsistentes.

### Flujo

- Crear: selecciona un lote existente y captura cantidad, motivo y notas. La fecha, proveedor y costo se derivan del lote. La pérdida se guarda como cantidad por costo unitario.
- Actualizar: solamente mientras esté pendiente. Cambiar de lote recalcula proveedor, fecha, costo y pérdida.
- Eliminar: borrado físico permitido únicamente para registros pendientes, que todavía no afectaron inventario.
- Procesar: descuenta cantidad del lote y de la presentación en una transacción y registra movimiento de inventario. No permite saldo negativo. Un descarte procesado es inmutable.

La lista tendrá filtros por proveedor, rango de fechas, estado y motivo, además del total filtrado y subtotales por proveedor.

### Exportaciones

- PDF individual con referencia, producto, presentación, proveedor, lote, cantidad, caducidad, costo, pérdida, motivo, notas, responsables y fecha de generación.
- PDF consolidado con el filtro vigente, detalle, subtotales por proveedor y total general.
- CSV consolidado compatible con Excel.

Las exportaciones compartirán un servicio de consulta para que vista, PDF y CSV produzcan los mismos importes.

## Componentes

Se crearán controladores separados para ajustes y descartes, Form Requests por operación, policies, modelos Eloquent y servicios de dominio transaccionales. `ComercialController` conservará entradas, catálogo y ventas; no absorberá los nuevos CRUD.

`ReporteComercialController` delegará el agregado de productos vendidos y las exportaciones a un servicio de reporte. Las vistas seguirán usando `layouts.app` y `partials.sidebar`, y el menú desplegable de inventario añadirá Ajustes y Descartes sin duplicar barras laterales.

## Manejo de errores

Los conflictos de stock y estado producirán errores de validación legibles y revertirán toda la transacción. Las acciones sobre registros inexistentes usarán model binding y 404. Las policies producirán 403. Los formularios conservarán los datos previos y mostrarán los errores con el patrón existente.

No se redondearán cantidades inválidas, no se aceptarán identificadores de proveedor que no correspondan al lote y no se confiará en precios enviados por el cliente.

## Migraciones y compatibilidad

Las migraciones nuevas se ejecutarán después de las migraciones ya existentes. El orden será:

1. eliminar liquidaciones y normalizar estados;
2. crear `inventory_adjustments`;
3. crear `product_discards`;
4. añadir índices adicionales solamente si no existen.

La base instalada y `migrate:fresh` deberán terminar con el mismo esquema. El volcado SQL de referencia se actualizará al finalizar.

## Pruebas

Se seguirá TDD y cada comportamiento nuevo se observará fallar antes de implementar. La cobertura incluirá:

- migración sin tabla, rutas, modelo ni textos de liquidación;
- normalización de estados sin perder entradas, lotes o ventas;
- creación, edición y anulación transaccional de ajustes;
- rechazo de saldo negativo y permisos por rol;
- selector de proveedor, preselección, cambio y validación de stock específico;
- consumo FEFO limitado al proveedor y conservación del carrito actual;
- ranking, filtros, porcentajes y CSV;
- cálculo exacto de pérdidas;
- creación, edición, eliminación pendiente y procesamiento de descartes;
- inmutabilidad posterior al procesamiento;
- PDF individual, PDF consolidado y CSV;
- regresión completa de pruebas Laravel y JavaScript existentes.

La verificación final incluirá PHPUnit, pruebas JavaScript, Pint sobre archivos modificados, compilación Vite, `git diff --check` y una prueba manual de los flujos principales en navegador.

## Documentación entregable

Se actualizarán el changelog y la guía del sistema. El diagrama ER documentará:

```text
users ──< inventory_adjustments >── ingresos_productores_items
users ──< product_discards      >── ingresos_productores_items
                                      │
                                      ├── presentaciones ── productos
                                      └── ingresos_productores ── productores
```

## Criterios de aceptación

- No existe funcionalidad ni dato de liquidaciones.
- Todo cambio manual de stock queda auditado y puede anularse sin borrarse.
- Una venta consume exclusivamente stock del proveedor elegido.
- Reportes muestra top de productos consistente en gráfico, tabla y CSV.
- Un descarte procesado descuenta stock una sola vez y conserva su pérdida histórica.
- Ninguna operación permite cantidades fraccionarias o stock negativo.
- Las funcionalidades existentes de entradas, carrito, tickets, autenticación y permisos continúan operativas.
