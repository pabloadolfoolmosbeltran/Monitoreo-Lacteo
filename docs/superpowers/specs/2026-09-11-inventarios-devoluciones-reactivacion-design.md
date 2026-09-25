# Inventarios, devoluciones y reactivación estable

## Objetivo

Mejorar el módulo comercial sin alterar sus cálculos ni perder historial. El trabajador y el administrador podrán consultar inventario por lote, obtener una vista consolidada de existencias, revisar y registrar devoluciones desde un acceso directo y reactivar registros sin depender de la navegación parcial que actualmente puede dejar la interfaz bloqueada.

## Alcance

La mejora incluye:

- filtros combinables en Inventario por lote;
- una nueva pantalla llamada Inventario general;
- una nueva pantalla accesible llamada Devoluciones, con consulta y registro;
- acceso directo a ambas pantallas desde el menú lateral;
- simplificación de la desactivación y reactivación de productos, presentaciones, usuarios y proveedores;
- optimización de consultas e índices para las búsquedas nuevas;
- depuración conservadora de artefactos de prueba o caché que estén versionados, duplicados o sin referencias.

No se reemplaza el flujo de ventas, no se duplican saldos en tablas de resumen, no se elimina historial comercial y no se modifica la regla contable de liquidación.

## Usuarios y permisos

Los roles Administrador y Trabajador pueden:

- consultar Inventario por lote e Inventario general;
- consultar el historial de devoluciones;
- registrar devoluciones válidas de cliente o productor.

Sólo el Administrador puede:

- ver registros inactivos;
- desactivar y reactivar productos, presentaciones, usuarios y proveedores;
- realizar ajustes de inventario reservados actualmente a ese rol.

Se mantienen los middleware y autorizaciones existentes. Las nuevas rutas comerciales quedan bajo `OperadorComercial`.

## Navegación

La sección “Acopio y ventas” del menú lateral mostrará accesos directos a:

1. Ventas
2. Entradas de Inventario
3. Proveedores
4. Inventario por Lote
5. Inventario General
6. Devoluciones
7. Reporte de ventas
8. Reporte de entradas

Los enlaces activos deben distinguir correctamente las tres pantallas de inventario/devoluciones.

## Inventario por lote

La ruta actual de inventario conserva su responsabilidad: mostrar una fila por lote físico y permitir acceder a sus movimientos.

### Filtros

La pantalla acepta filtros GET combinables:

- `producto`: búsqueda textual sobre producto, presentación, sabor, contenido y unidad;
- `productor`: nombres, apellidos o unidad productiva;
- `ingreso_desde` y `ingreso_hasta`: rango inclusivo de fecha de recepción/ingreso;
- `vence_desde` y `vence_hasta`: rango inclusivo de fecha de caducidad;
- `liquidacion`: `todas`, `no_liquidada` o `liquidada`.

Las búsquedas toleran espacios adicionales y coma decimal. La búsqueda de presentación separa términos numéricos y unidades, de modo que expresiones como “yogurt frutilla 2 litros” puedan coincidir con el producto, sabor, contenido y unidad correspondientes.

Cada par de fechas debe cumplir `desde <= hasta`. Si no se cumple, Laravel devuelve un error de validación comprensible y conserva los filtros introducidos. La paginación conserva todos los parámetros válidos.

### Resultado

Cada fila muestra:

- número de lote y entrada;
- producto y presentación;
- productor y unidad productiva;
- existencia física;
- precio de venta;
- fecha de recepción y fecha de caducidad;
- estado de liquidación;
- condición de inventario: vendible, próximo a vencer, vencido, sin fecha o bloqueado;
- acciones existentes de movimiento y enlaces a trazabilidad y devolución.

El resultado se pagina a 30 filas y precarga sólo las relaciones utilizadas.

## Inventario general

Inventario general es una pantalla de consulta separada. No guarda un segundo saldo: deriva sus cifras de `ingresos_productores_items`, que continúa siendo la fuente física por lote.

### Definiciones de stock

- **Stock vendible:** suma de `cantidad_disponible` positiva de lotes cuyo ingreso está `abierta`, cuya fecha de caducidad existe y es igual o posterior al día actual.
- **Próximo a vencer:** parte del stock vendible con vencimiento entre hoy y los próximos siete días, inclusive.
- **Stock no vendible:** saldo físico positivo de lotes vencidos, sin fecha válida o pertenecientes a un ingreso que ya no está abierto.
- **Stock físico:** stock vendible más stock no vendible.

Las cifras se calculan con precisión decimal de tres posiciones. Ningún saldo vencido o bloqueado se presenta como disponible para venta.

### Búsqueda y agrupación

La pantalla acepta:

- `producto`, con la misma semántica de búsqueda de producto/presentación/sabor/contenido/unidad;
- `productor`;
- `vence_desde` y `vence_hasta`.

Los resultados se agrupan por presentación y productor. Cada grupo muestra producto, presentación, productor, stock vendible, próximo a vencer, no vendible, stock físico y cantidad de lotes. El total general de las tarjetas superiores respeta los filtros actuales.

La interfaz permite expandir un grupo para consultar los lotes que forman el saldo sin abandonar la pantalla. El servidor pagina los grupos; no carga todos los lotes ni todas las presentaciones en el navegador.

## Devoluciones

La pantalla Devoluciones contiene dos áreas visibles: registro e historial.

### Selección y registro

Para evitar desplegables masivos, el usuario empieza buscando por producto/presentación, productor, lote o número de venta. Los resultados elegibles se paginan. Al seleccionar un lote se muestra un formulario asociado a ese lote.

El formulario contiene:

- tipo: `cliente` o `productor`;
- venta, obligatoria y limitada a ventas del lote cuando el tipo es cliente;
- cantidad devuelta;
- observaciones/motivo;
- resumen del producto, productor, lote, saldo y vencimiento antes de confirmar.

Desde Inventario por lote e Inventario general existe una acción “Registrar devolución” que abre esta pantalla con el lote preseleccionado.

Las reglas actuales se mantienen dentro de una transacción:

- una devolución de cliente no supera la cantidad aún no devuelta de esa venta y reintegra stock al lote;
- una devolución al productor no supera el saldo del lote y descuenta stock;
- la venta seleccionada debe pertenecer al lote;
- el ingreso debe permanecer abierto;
- el stock global de la presentación y el saldo del lote se actualizan juntos;
- los reintentos o errores no dejan actualizaciones parciales.

### Historial

El historial acepta filtros GET por producto, productor, tipo, fecha de devolución desde/hasta y vencimiento del lote desde/hasta. Se pagina y muestra:

- fecha e identificador;
- tipo;
- producto y presentación;
- productor;
- lote y venta cuando corresponda;
- cantidad;
- importe revertido cuando corresponda;
- responsable y observaciones.

## Reactivación estable

La investigación inicial confirma que los endpoints de servidor para listar y restaurar registros funcionan en las pruebas automatizadas. El bloqueo reportado se concentra en la capa de navegación parcial: los formularios de restauración se interceptan con `data-async-action`, siguen redirecciones y reemplazan el contenido principal sin una navegación completa.

Para las pantallas administrativas, “Mostrar eliminados”, desactivar y restablecer usarán navegación y formularios Laravel normales. Durante el envío, el navegador realizará una recarga completa y mostrará el mensaje de sesión. Esto elimina estados parciales, controladores de navegación pendientes y botones que permanecen deshabilitados.

El estado funcional se expresa como `activo = 1` o `activo = 0` en productos, presentaciones, usuarios y proveedores. Para proveedores:

- las nuevas desactivaciones establecen `activo = 0` y preservan el registro;
- el listado de inactivos también reconoce proveedores históricos con `deleted_at` no nulo;
- al reactivar, se establece `activo = 1` y se limpia `deleted_at` si existía;
- los ingresos relacionados continúan visibles y no cambian de propietario.

La presentación sólo puede reactivarse cuando su producto padre está activo. Un usuario no puede desactivarse a sí mismo. Los controles siguen ocultos y prohibidos para Trabajador.

## Rendimiento

Las listas usan consultas SQL filtradas, agregadas y paginadas. Se evita calcular totales recorriendo colecciones completas en PHP y se evitan accesos N+1 mediante relaciones precargadas o uniones explícitas.

Una migración aditiva añade índices orientados a los filtros, después de comprobar los índices ya existentes:

- ingreso: productor, estado y fecha de ingreso;
- lote: presentación, fecha de recepción, fecha de caducidad y cantidad disponible;
- devolución: tipo y fecha de devolución, conservando el índice existente por lote/fecha;
- proveedor: activo y campos de identificación cuando el motor lo permita sin índices de texto sobredimensionados.

No se introduce caché de resultados ni tabla resumida. Esas opciones podrían devolver cifras obsoletas y no son necesarias para el volumen esperado.

## Experiencia visual y accesibilidad

Las nuevas vistas reutilizan el sistema visual verde del módulo comercial. Los filtros aparecen en una tarjeta compacta y responsive; los botones Buscar y Limpiar permanecen visibles. Los estados usan texto e icono además de color. Las tablas conservan encabezados claros y desplazamiento horizontal en pantallas pequeñas.

No se realizan peticiones por cada tecla. La búsqueda se ejecuta al enviar el formulario. No se agregan animaciones pesadas ni dependencias de interfaz.

## Manejo de errores

- Los errores de validación muestran el primer mensaje junto al formulario correspondiente.
- Las selecciones de lote o venta inexistentes, incompatibles o ya cerradas se rechazan en el servidor.
- Los conflictos de stock conservan la transacción completa y explican qué cantidad es insuficiente.
- Las pantallas vacías explican si no existen datos o si los filtros no encontraron coincidencias.
- Los parámetros desconocidos no modifican el alcance de la consulta.

## Depuración conservadora

Antes de retirar un archivo se debe verificar que no esté referenciado por Composer, Vite, rutas, vistas, pruebas, documentación ni Git. Se pueden eliminar cachés de resultados, salidas generadas y colecciones de prueba verdaderamente duplicadas o huérfanas. Las pruebas automatizadas válidas no se eliminan: no participan en las solicitudes web y protegen contra regresiones.

La limpieza de cachés de Laravel se hace mediante los comandos oficiales de optimización. No se eliminan migraciones aplicadas, datos, imágenes de usuarios, logs necesarios para diagnosticar el problema ni archivos modificados por el usuario sin relación demostrada con la mejora.

## Pruebas y criterios de aceptación

La implementación seguirá TDD. Como mínimo debe demostrar:

1. Inventario por lote combina producto, productor, fechas de ingreso, fechas de vencimiento y liquidación, y conserva filtros al paginar.
2. “yogurt frutilla 2 litros” localiza la presentación correcta sin incluir presentaciones que incumplan alguno de los términos.
3. Inventario general agrupa por presentación/productor y separa exactamente stock vendible, próximo a vencer y no vendible.
4. Los lotes vencidos, sin fecha o liquidados nunca incrementan el stock vendible.
5. Administrador y Trabajador acceden a las nuevas pantallas; los controles administrativos mantienen sus restricciones.
6. La devolución de cliente selecciona sólo ventas del lote y no supera el pendiente; la devolución a productor no supera el saldo.
7. Una devolución válida modifica lote y stock global atómicamente y aparece en el historial filtrado.
8. Las cuatro pantallas de inactivos se abren mediante navegación completa y restauran registros mediante envío normal, sin `data-async-action`.
9. Proveedores nuevos usan `activo = 0/1` y los archivados históricos siguen siendo recuperables.
10. Las consultas de las listas no crecen linealmente por cada fila mostrada y los resultados permanecen paginados.
11. La suite existente continúa pasando y la compilación de frontend termina sin errores.

La verificación final incluye pruebas PHP focalizadas, suite completa, pruebas JavaScript existentes, compilación de Vite y revisión manual de las nuevas vistas en tamaños de escritorio y móvil.
