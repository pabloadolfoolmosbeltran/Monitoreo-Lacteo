# GUÍA DEL SISTEMA — Sistema Inteligente de Producción Láctea
> Última actualización: 23 de septiembre de 2026  
> Versión del sistema: [no encontrado en `composer.json` ni `package.json`]  
> Framework: Laravel 12.63.0 · PHP 8.2.12 · Vite 7.0.7

## 1. PROPÓSITO DEL SISTEMA

Aplicación web para administrar una unidad de producción láctea: monitorea procesos térmicos conectados a un ESP32, registra producción y alertas, y gestiona catálogo, acopio, inventario, ventas y reportes. Está orientada a administradores y trabajadores; además expone un catálogo público para consulta de productos.

## 2. ARQUITECTURA GENERAL

```text
ESP32 + sensor térmico
        │ HTTP/JSON
        ▼
routes/api.php ──► app/Http/Controllers/Api/Esp32Controller.php
        │                         │
        │                         ├──► app/Services/ActuadorService.php
        │                         └──► Modelos Eloquent
        ▼
Laravel 12 ──► MySQL ──► Controllers web ──► Blade ──► Vite/CSS/JS ──► navegador
```

- Stack confirmado: Laravel 12, PHP 8.2, MySQL configurado mediante `DB_*`, Eloquent, Blade, Vite 7, Tailwind CSS 4, Bootstrap 5 por CDN, Chart.js por CDN, `barryvdh/laravel-dompdf` y `maatwebsite/excel`.
- IoT: el ESP32 intercambia estado, temperatura, alertas, finalización y pasteurización mediante HTTP con `routes/api.php`. La referencia concreta al sensor **DS18B20 no aparece en el código del repositorio**; por tanto, el modelo físico del sensor queda como `[no encontrado]` en la implementación inspeccionada.
- API: las rutas IoT usan el grupo `api` y `throttle:60,1`; las lecturas del dashboard también tienen rutas web autenticadas.
- Presentación: `resources/views/layouts/app.blade.php` compone navbar, sidebar, contenido y footer. Los assets se generan en `public/build` mediante Vite.
- Dominio comercial: los ingresos crean lotes; el inventario se calcula por lote/presentación; las ventas descuentan existencias mediante FEFO en `app/Services/ComercialService.php`.

### Mapa global de directorios

| Ruta desde la raíz | Contenido |
|---|---|
| `app/Http/Controllers/` | Controllers web y `Api/Esp32Controller.php`. |
| `app/Http/Middleware/` | Restricciones por rol y operador comercial. |
| `app/Http/Requests/` | Validaciones de inventario y descartes. |
| `app/Models/` | Los 20 modelos Eloquent descritos en sección 4. |
| `app/Services/`, `app/Support/` | Lógica de aplicación reutilizable y aritmética decimal. |
| `app/Exports/`, `app/Policies/` | Exportaciones y autorización. |
| `app/Providers/AppServiceProvider.php` | Registro de servicios/policies y arranque de la aplicación. |
| `bootstrap/app.php`, `bootstrap/providers.php`, `bootstrap/cache/.gitignore`, `bootstrap/cache/packages.php`, `bootstrap/cache/services.php` | Arranque, rutas, middleware, providers y cachés generados de paquetes/servicios. |
| `config/` | `app.php`, `auth.php`, `cache.php`, `database.php`, `filesystems.php`, `logging.php`, `mail.php`, `queue.php`, `services.php`, `session.php`. |
| `database/factories/UserFactory.php` | Fábrica de usuarios de prueba. |
| `database/database.sqlite`, `database/.gitignore` | Archivo SQLite local presente y marcador del directorio; que exista no implica que sea la conexión activa. |
| `database/migrations/` | Esquema completo, inventariado en sección 8. |
| `database/seeders/DatabaseSeeder.php` | Datos iniciales. |
| `resources/views/`, `resources/css/`, `resources/js/` | Blade y fuentes frontend, inventariados en secciones 5–7. |
| `routes/web.php`, `routes/api.php`, `routes/console.php` | Rutas web, IoT/API y consola. |
| `public/` | Front controller, storage público y build Vite. |
| `tests/Feature/`, `tests/Unit/`, `tests/js/` | Pruebas PHP y JavaScript. |
| `docs/` | Diagrama ER y planes/especificaciones históricas. |
| `postman/` | Colección y globals para probar `/api/estado`. |
| `9_22_2026_monitoreo_lacteos.sql` | Volcado SQL presente en la raíz. |
| `artisan`, `composer.json`, `composer.lock`, `package.json`, `package-lock.json`, `phpunit.xml`, `vite.config.js` | CLI, dependencias, pruebas y compilación. |
| `.env`, `.env.example`, `.editorconfig`, `.gitattributes`, `.gitignore`, `.phpunit.result.cache` | Entorno local (no publicar secretos), plantilla de entorno, reglas de editor/Git e historial local de PHPUnit. |
| `CHANGELOG.md`, `GUIA_SISTEMA.md` | Historial y esta guía. `README.md`: `[no encontrado]`. |

### Pruebas y documentación auxiliar

- Feature tests: `tests/Feature/AcopioComercialTest.php`, `tests/Feature/AcopioIntegrityTest.php`, `tests/Feature/AcopioMigrationTest.php`, `tests/Feature/AjusteInventarioPagesTest.php`, `tests/Feature/AjusteInventarioTest.php`, `tests/Feature/AsyncPanelActionsTest.php`, `tests/Feature/CommercialPagesTest.php`, `tests/Feature/DescarteProductoPagesTest.php`, `tests/Feature/DescarteProductoTest.php`, `tests/Feature/ExampleTest.php`, `tests/Feature/ImageStorageTest.php`, `tests/Feature/InventarioGeneralTest.php`, `tests/Feature/InventarioPorLoteTest.php`, `tests/Feature/LiquidacionesRemovalTest.php`, `tests/Feature/PanelCommercialRefinementTest.php`, `tests/Feature/ProductosVendidosReportTest.php`, `tests/Feature/RennetRecommendationTest.php`.
- Base/unitarias: `tests/TestCase.php`, `tests/Unit/ExampleTest.php`.
- JavaScript: `tests/js/async-panel.test.mjs`, `tests/js/rennet-recommendation.test.mjs`, `tests/js/supplier-cart.test.mjs`.
- Documentación de datos: `docs/diagrama-er-inventario.md`.
- Planes: `docs/superpowers/plans/2026-09-11-inventarios-devoluciones-implementation.md`, `docs/superpowers/plans/2026-09-11-reactivacion-depuracion-implementation.md`, `docs/superpowers/plans/2026-09-17-ajustes-proveedores-reportes-descartes-implementation.md`, `docs/superpowers/plans/2026-09-17-eliminar-devoluciones-cantidades-enteras-menu-implementation.md`, `docs/superpowers/plans/2026-09-17-imagenes-cuajo-menu-acopio-implementation.md`.
- Especificaciones: `docs/superpowers/specs/2026-09-11-inventarios-devoluciones-reactivacion-design.md`, `docs/superpowers/specs/2026-09-17-ajustes-proveedores-reportes-descartes-design.md`, `docs/superpowers/specs/2026-09-17-eliminar-devoluciones-cantidades-enteras-menu-design.md`, `docs/superpowers/specs/2026-09-17-imagenes-cuajo-menu-acopio-design.md`.
- Postman: `.postman/resources.yaml`, `postman/collections/New Collection/.resources/definition.yaml`, `postman/collections/New Collection/http-127.0.0.1-8000-api-estado.request.yaml`, `postman/collections/New Collection/http-127.0.0.1-8000-api-estado-1.request.yaml` y `postman/globals/workspace.globals.yaml`.
- Marcador de base de datos local: `database/.gitignore`.

## 3. MÓDULOS DEL SISTEMA

### 3.1 Producción

- Qué hace: inicia y finaliza producciones, calcula cuajo requerido, asocia producto/dispositivo, registra temperaturas, métricas, eventos y alertas.
- Quién lo usa: usuarios autenticados (Administrador y Trabajador según la navegación y validaciones del sistema).
- URL principal: `/produccion`.
- Piezas centrales: `ProduccionController`, `Produccion`, `Lectura`, `Alerta`, `Reporte`, `produccion/index.blade.php` y `produccion.js`.

→ Ver archivos en secciones 4, 5, 6, 7 para cross-reference.

### 3.2 POS / Ventas

- Qué hace: presenta el catálogo vendible, mantiene el carrito, cobra en efectivo o QR, registra tickets y genera una vista imprimible del comprobante.
- Quién lo usa: usuarios autenticados permitidos por `OperadorComercial` (Administrador o Trabajador).
- URL principal: `/pos`.
- FEFO: el navegador muestra una estimación, pero la selección autoritativa ocurre en `ComercialService::vender()`, que bloquea y consume lotes disponibles ordenados por `fecha_caducidad` y luego por `id`; no se solicita proveedor por ítem.

→ Ver archivos en secciones 4, 5, 6, 7 para cross-reference.

### 3.3 Inventario

- Qué hace: consulta existencias por lote y consolidadas, filtra productos/proveedores, registra movimientos, regularizaciones, ajustes auditables y descartes.
- Quién lo usa: Administrador y Trabajador mediante `OperadorComercial`; ciertas acciones también se autorizan con policies.
- URLs principales: `/inventario`, `/inventario-general`, `/ajustes-inventario` y `/descartes-productos`.

→ Ver archivos en secciones 4, 5, 6, 7 para cross-reference.

### 3.4 Proveedores

- Qué hace: mantiene el directorio de productores/proveedores y registra ingresos con uno o varios lotes, precios, fechas de elaboración y caducidad.
- Quién lo usa: Administrador y Trabajador mediante `OperadorComercial`; restaurar proveedores requiere rol Administrador.
- URLs principales: `/productores` y `/ingresos-productores`.
- Nota de dominio: las rutas históricas `/consignaciones` y `/mis-consignaciones` son redirecciones de compatibilidad; no existen controllers/modelos actuales de consignación.

→ Ver archivos en secciones 4, 5, 6, 7 para cross-reference.

### 3.5 Reportes

- Qué hace: lista y detalla reportes de producción; exporta PDF/Excel; resume ventas e ingresos y exporta PDF, Excel o CSV; genera comprobantes de descartes.
- Quién lo usa: usuarios autenticados; reportes comerciales requieren `OperadorComercial`.
- URLs principales: `/reportes`, `/reportes-comerciales` y `/reportes-ingresos`.

→ Ver archivos en secciones 4, 5, 6, 7 para cross-reference.

### 3.6 Control ESP32 / IoT

- Qué hace: recibe telemetría, entrega estado de actuadores, registra ping, alertas y pasteurización, permite control manual/automático de motor, ventilador y sensor, y alimenta el dashboard.
- Quién lo usa: el firmware ESP32 mediante `/api/*`; usuarios autenticados mediante `/control` y `/dashboard`.
- URLs principales: `/control`, `/dashboard`, `/api/estado`, `/api/temperatura` y `/api/dashboard`.
- Controller real: `app/Http/Controllers/Api/Esp32Controller.php`.

→ Ver archivos en secciones 4, 5, 6, 7 para cross-reference.

### 3.7 Autenticación y Roles

- Qué hace: inicio/cierre de sesión, limitación de intentos, protección `auth`, gestión de usuarios y control de roles.
- Quién lo usa: todos para iniciar sesión; solo Administrador administra usuarios; Administrador/Trabajador acceden a catálogo interno y operación comercial.
- URLs principales: `/login`, `/logout` y `/usuarios`.
- Middleware: `RolMiddleware` valida una lista de roles y `OperadorComercial` acepta Administrador o Trabajador.

→ Ver archivos en secciones 4, 5, 6, 7 para cross-reference.

## 4. MAPA DE ARCHIVOS — BACKEND

### Controllers

| Archivo | Ruta | Responsabilidad |
|---|---|---|
| `Controller.php` | `app/Http/Controllers/Controller.php` | Clase base abstracta de controllers. |
| `AjusteInventarioController.php` | `app/Http/Controllers/AjusteInventarioController.php` | CRUD parcial de ajustes, detalle y anulación mediante `AjusteInventarioService`. |
| `AlertaController.php` | `app/Http/Controllers/AlertaController.php` | Lista alertas y permite marcarlas como atendidas. |
| `Esp32Controller.php` | `app/Http/Controllers/Api/Esp32Controller.php` | API IoT: estado, temperatura, series, ping, dashboard, alerta, pasteurización y finalización automática. |
| `AuthController.php` | `app/Http/Controllers/AuthController.php` | Renderiza login, autentica con límite de intentos y cierra sesión. |
| `CatalogoController.php` | `app/Http/Controllers/CatalogoController.php` | Catálogo público, detalle de presentación y páginas Nosotros/Contacto. |
| `ComercialController.php` | `app/Http/Controllers/ComercialController.php` | POS, catálogo JSON, venta, ingresos, inventarios, movimientos y regularizaciones. |
| `ControlController.php` | `app/Http/Controllers/ControlController.php` | Panel y comandos de motor, ventilador, sensor y modo automático/manual. |
| `DashboardController.php` | `app/Http/Controllers/DashboardController.php` | Métricas iniciales y vista principal del dashboard. |
| `DescarteProductoController.php` | `app/Http/Controllers/DescarteProductoController.php` | CRUD, procesamiento y exportación PDF/CSV de descartes. |
| `EventoController.php` | `app/Http/Controllers/EventoController.php` | Consulta filtrada de bitácora de eventos. |
| `PresentacionController.php` | `app/Http/Controllers/PresentacionController.php` | CRUD lógico y búsqueda de presentaciones comerciales. |
| `ProduccionController.php` | `app/Http/Controllers/ProduccionController.php` | Pantalla, inicio y finalización de producción y coordinación de actuadores. |
| `ProductoController.php` | `app/Http/Controllers/ProductoController.php` | CRUD lógico de productos e imagen referencial. |
| `ProductorController.php` | `app/Http/Controllers/ProductorController.php` | CRUD lógico y detalle de productores/proveedores. |
| `ReporteComercialController.php` | `app/Http/Controllers/ReporteComercialController.php` | Reportes de ventas/ingresos, ticket JSON, PDF, Excel y CSV. |
| `ReporteController.php` | `app/Http/Controllers/ReporteController.php` | Listado/detalle de producción y exportación PDF/Excel. |
| `UserController.php` | `app/Http/Controllers/UserController.php` | CRUD lógico y restauración de usuarios. |

### Servicios, soporte, middleware y autorización

| Ruta | Responsabilidad |
|---|---|
| `app/Services/ActuadorService.php` | Cambios seguros de estado/modo de actuadores y registro de eventos. |
| `app/Services/AjusteInventarioService.php` | Crea, actualiza y anula ajustes con transacción, bloqueo y trazabilidad. |
| `app/Services/BusquedaPresentacion.php` | Aplica búsqueda reutilizable a queries de presentaciones. |
| `app/Services/ComercialService.php` | Registra ingresos, vende con FEFO, arma tickets y registra movimientos/regularizaciones. |
| `app/Services/ConsultaDescartes.php` | Construye consultas filtradas de descartes. |
| `app/Services/DescarteProductoService.php` | Ciclo transaccional de descartes y sincronización de stock. |
| `app/Services/EventoService.php` | Registra eventos operativos. |
| `app/Services/InventarioConsulta.php` | Consultas por lote, consolidadas, totales y detalle de grupos. |
| `app/Services/ReporteProduccionService.php` | Calcula duración y estadísticas de producción. |
| `app/Services/ReporteVentasService.php` | Calcula ranking de productos vendidos. |
| `app/Support/Decimal.php` | Operaciones decimales exactas con BCMath o alternativa controlada. |
| `app/Exports/ComercialExport.php` | Exportación comercial con múltiples hojas. |
| `app/Exports/ProduccionesExport.php` | Exportación Excel de producciones. |
| `app/Http/Middleware/OperadorComercial.php` | Restringe operación comercial a Administrador/Trabajador. |
| `app/Http/Middleware/RolMiddleware.php` | Restringe por roles pasados al middleware `rol`. |
| `app/Policies/AjusteInventarioPolicy.php` | Autorización de acciones sobre ajustes. |
| `app/Policies/DescarteProductoPolicy.php` | Autorización de acciones sobre descartes. |
| `app/Http/Requests/GuardarAjusteInventarioRequest.php` | Validación de alta de ajustes. |
| `app/Http/Requests/ActualizarAjusteInventarioRequest.php` | Validación de modificación de ajustes. |
| `app/Http/Requests/AnularAjusteInventarioRequest.php` | Validación de anulación. |
| `app/Http/Requests/FiltroInventarioRequest.php` | Validación/normalización de filtros de inventario. |
| `app/Http/Requests/GuardarDescarteProductoRequest.php` | Validación de altas y cambios de descartes. |

### Modelos

| Archivo | Ruta | Tabla BD | Relaciones |
|---|---|---|---|
| `Actuador.php` | `app/Models/Actuador.php` | `actuadores` | `belongsTo Dispositivo`. |
| `AjusteInventario.php` | `app/Models/AjusteInventario.php` | `ajustes_inventario` | `belongsTo` lote y usuarios creador, actualizador, anulador. |
| `Alerta.php` | `app/Models/Alerta.php` | `alertas` | `belongsTo Produccion`, `belongsTo Lectura`. |
| `Configuracion.php` | `app/Models/Configuracion.php` | `configuraciones` | Sin relaciones Eloquent; `sistema()` obtiene la configuración principal. |
| `DescarteProducto.php` | `app/Models/DescarteProducto.php` | `descartes_productos` | `belongsTo` lote y usuarios creador, actualizador, procesador. |
| `Dispositivo.php` | `app/Models/Dispositivo.php` | `dispositivos` | `hasMany` sensores, actuadores y producciones. |
| `Evento.php` | `app/Models/Evento.php` | `eventos` | `belongsTo User`, `belongsTo Produccion`. |
| `IngresoProductor.php` | `app/Models/IngresoProductor.php` | `ingresos_productores` | `belongsTo Productor`, `belongsTo User` operador, `hasMany` items. |
| `IngresoProductorItem.php` | `app/Models/IngresoProductorItem.php` | `ingresos_productores_items` | `belongsTo` ingreso/presentación; `hasMany` ventas, ajustes y descartes. |
| `Lectura.php` | `app/Models/Lectura.php` | `lecturas` | `belongsTo Produccion/Sensor`, `hasMany Alerta`. |
| `MovimientoInventario.php` | `app/Models/MovimientoInventario.php` | `movimientos_inventario` | `belongsTo` lote y usuario responsable. |
| `Presentacion.php` | `app/Models/Presentacion.php` | `presentaciones` | `belongsTo Producto`, `hasMany` lotes. |
| `Produccion.php` | `app/Models/Produccion.php` | `producciones` | `belongsTo` usuario/producto/dispositivo; `hasMany` lecturas, alertas, eventos y reportes. |
| `Producto.php` | `app/Models/Producto.php` | `productos` | `hasMany` producciones y presentaciones. |
| `Productor.php` | `app/Models/Productor.php` | `productores` | `hasMany IngresoProductor`; usa `SoftDeletes`. |
| `Reporte.php` | `app/Models/Reporte.php` | `reportes` | `belongsTo Produccion`. |
| `Sensor.php` | `app/Models/Sensor.php` | `sensores` | `belongsTo Dispositivo`, `hasMany Lectura`. |
| `TicketVenta.php` | `app/Models/TicketVenta.php` | `tickets_venta` | `hasMany Venta`, `belongsTo User` vendedor. |
| `User.php` | `app/Models/User.php` | `users` | `hasMany` producciones, eventos, ingresos, ventas, ajustes y descartes creados. |
| `Venta.php` | `app/Models/Venta.php` | `ventas` | `belongsTo` lote/item, ticket y vendedor. |

### Rutas

Tabla generada a partir de `php artisan route:list --json` (la opción `--columns` no está disponible en esta versión).

| Método | URI | Nombre | Controller@método |
|---|---|---|---|
| GET/HEAD | `/` | — | Closure |
| GET/HEAD | `/ajustes-inventario` | `ajustes-inventario.index` | `AjusteInventarioController@index` |
| POST | `/ajustes-inventario` | `ajustes-inventario.store` | `AjusteInventarioController@store` |
| GET/HEAD | `/ajustes-inventario/create` | `ajustes-inventario.create` | `AjusteInventarioController@create` |
| GET/HEAD | `/ajustes-inventario/{ajusteInventario}` | `ajustes-inventario.show` | `AjusteInventarioController@show` |
| PUT | `/ajustes-inventario/{ajusteInventario}` | `ajustes-inventario.update` | `AjusteInventarioController@update` |
| PATCH | `/ajustes-inventario/{ajusteInventario}/anular` | `ajustes-inventario.anular` | `AjusteInventarioController@anular` |
| GET/HEAD | `/ajustes-inventario/{ajusteInventario}/edit` | `ajustes-inventario.edit` | `AjusteInventarioController@edit` |
| GET/HEAD | `/alertas` | — | `AlertaController@index` |
| PATCH | `/alertas/{id}/atender` | `alertas.atender` | `AlertaController@atender` |
| POST | `/api/alerta-pasteurizacion` | — | `Api\\Esp32Controller@alertaPasteurizacion` |
| GET/HEAD | `/api/dashboard` | — | `Api\\Esp32Controller@dashboard` |
| GET/HEAD | `/api/estado` | — | `Api\\Esp32Controller@estado` |
| POST | `/api/finalizar-produccion` | — | `Api\\Esp32Controller@finalizarProduccionAutomatica` |
| POST | `/api/pasteurizacion` | — | `Api\\Esp32Controller@pasteurizacion` |
| POST | `/api/ping` | — | `Api\\Esp32Controller@ping` |
| POST | `/api/temperatura` | — | `Api\\Esp32Controller@temperatura` |
| GET/HEAD | `/api/temperaturas` | — | `Api\\Esp32Controller@temperaturas` |
| GET/HEAD | `/catalogo` | `catalogo.index` | `CatalogoController@index` |
| GET/HEAD | `/catalogo/contacto` | `catalogo.contacto` | `CatalogoController@contacto` |
| GET/HEAD | `/catalogo/nosotros` | `catalogo.nosotros` | `CatalogoController@nosotros` |
| GET/HEAD | `/catalogo/{presentacion}` | `catalogo.detalle` | `CatalogoController@detalle` |
| GET/HEAD | `/comercial/catalogo` | `ventas.catalogo` | `ComercialController@catalogo` |
| POST | `/comercial/ventas` | `ventas.store` | `ComercialController@vender` |
| GET/HEAD/POST/PUT/PATCH/DELETE/OPTIONS | `/consignaciones` | — | `RedirectController` |
| GET/HEAD | `/consignaciones/{id}` | — | Closure de compatibilidad |
| GET/HEAD | `/control` | — | `ControlController@index` |
| POST | `/control/motor/{estado}` | — | `ControlController@motor` |
| POST | `/control/sensor/{estado}` | — | `ControlController@sensor` |
| POST | `/control/ventilador/{estado}` | — | `ControlController@ventilador` |
| POST | `/control/{tipo}/modo` | — | `ControlController@cambiarModo` |
| GET/HEAD | `/dashboard` | `dashboard` | `DashboardController@index` |
| GET/HEAD | `/dashboard/datos` | `dashboard.datos` | `Api\\Esp32Controller@dashboard` |
| GET/HEAD | `/dashboard/temperaturas` | `dashboard.temperaturas` | `Api\\Esp32Controller@temperaturas` |
| GET/HEAD | `/descartes-productos` | `descartes-productos.index` | `DescarteProductoController@index` |
| POST | `/descartes-productos` | `descartes-productos.store` | `DescarteProductoController@store` |
| GET/HEAD | `/descartes-productos/create` | `descartes-productos.create` | `DescarteProductoController@create` |
| GET/HEAD | `/descartes-productos/reporte.csv` | `descartes-productos.csv` | `DescarteProductoController@csv` |
| GET/HEAD | `/descartes-productos/reporte.pdf` | `descartes-productos.reporte.pdf` | `DescarteProductoController@consolidadoPdf` |
| GET/HEAD | `/descartes-productos/{descarteProducto}` | `descartes-productos.show` | `DescarteProductoController@show` |
| PUT/PATCH | `/descartes-productos/{descarteProducto}` | `descartes-productos.update` | `DescarteProductoController@update` |
| DELETE | `/descartes-productos/{descarteProducto}` | `descartes-productos.destroy` | `DescarteProductoController@destroy` |
| GET/HEAD | `/descartes-productos/{descarteProducto}/edit` | `descartes-productos.edit` | `DescarteProductoController@edit` |
| GET/HEAD | `/descartes-productos/{descarteProducto}/pdf` | `descartes-productos.pdf` | `DescarteProductoController@pdf` |
| PATCH | `/descartes-productos/{descarteProducto}/procesar` | `descartes-productos.procesar` | `DescarteProductoController@procesar` |
| GET/HEAD | `/eventos` | — | `EventoController@index` |
| GET/HEAD | `/ingresos-productores` | `ingresos-productores.index` | `ComercialController@ingresos` |
| POST | `/ingresos-productores` | `ingresos-productores.store` | `ComercialController@guardarIngreso` |
| GET/HEAD | `/ingresos-productores/create` | `ingresos-productores.create` | `ComercialController@crearIngreso` |
| GET/HEAD | `/ingresos-productores/{ingreso}` | `ingresos-productores.show` | `ComercialController@detalle` |
| GET/HEAD | `/ingresos-productores/{ingreso}/detalle-json` | `ingresos-productores.detalle-json` | `ComercialController@detalleJson` |
| GET/HEAD | `/ingresos-productores/{ingreso}/excel` | `ingresos-productores.excel` | `ReporteComercialController@excelIngreso` |
| GET/HEAD | `/ingresos-productores/{ingreso}/pdf` | `ingresos-productores.pdf` | `ReporteComercialController@pdfIngreso` |
| GET/HEAD | `/inventario` | `inventario.index` | `ComercialController@inventario` |
| GET/HEAD | `/inventario-general` | `inventario-general.index` | `ComercialController@inventarioGeneral` |
| GET/HEAD | `/login` | `login` | `AuthController@login` |
| POST | `/login` | `login.autenticar` | `AuthController@autenticar` |
| POST | `/logout` | `logout` | `AuthController@logout` |
| POST | `/lotes/{item}/movimientos` | `lotes.movimientos.store` | `ComercialController@movimiento` |
| PATCH | `/lotes/{item}/regularizar` | `lotes.regularizar` | `ComercialController@regularizar` |
| GET/HEAD/POST/PUT/PATCH/DELETE/OPTIONS | `/mis-consignaciones` | — | `RedirectController` |
| GET/HEAD | `/obtenerDatosGrafico` | — | `Api\\Esp32Controller@obtenerDatosGrafico` |
| GET/HEAD | `/pos` | `pos.index` | `ComercialController@pos` |
| GET/HEAD | `/presentaciones` | `presentaciones.index` | `PresentacionController@index` |
| POST | `/presentaciones` | `presentaciones.store` | `PresentacionController@store` |
| GET/HEAD | `/presentaciones/create` | `presentaciones.create` | `PresentacionController@create` |
| PUT | `/presentaciones/{presentacion}` | `presentaciones.update` | `PresentacionController@update` |
| DELETE | `/presentaciones/{presentacion}` | `presentaciones.destroy` | `PresentacionController@destroy` |
| GET/HEAD | `/presentaciones/{presentacion}/edit` | `presentaciones.edit` | `PresentacionController@edit` |
| PATCH | `/presentaciones/{presentacion}/restablecer` | `presentaciones.restore` | `PresentacionController@restore` |
| GET/HEAD | `/produccion` | — | `ProduccionController@index` |
| POST | `/produccion/finalizar` | — | `ProduccionController@finalizar` |
| POST | `/produccion/iniciar` | — | `ProduccionController@iniciar` |
| GET/HEAD | `/productores` | `productores.index` | `ProductorController@index` |
| POST | `/productores` | `productores.store` | `ProductorController@store` |
| GET/HEAD | `/productores/create` | `productores.create` | `ProductorController@create` |
| GET/HEAD | `/productores/{productor}` | `productores.show` | `ProductorController@show` |
| PUT/PATCH | `/productores/{productor}` | `productores.update` | `ProductorController@update` |
| DELETE | `/productores/{productor}` | `productores.destroy` | `ProductorController@destroy` |
| GET/HEAD | `/productores/{productor}/edit` | `productores.edit` | `ProductorController@edit` |
| PATCH | `/productores/{productor}/restablecer` | `productores.restore` | `ProductorController@restore` |
| GET/HEAD | `/productos` | — | `ProductoController@index` |
| POST | `/productos` | — | `ProductoController@store` |
| GET/HEAD | `/productos/create` | — | `ProductoController@create` |
| PUT | `/productos/{producto}` | — | `ProductoController@update` |
| DELETE | `/productos/{producto}` | `productos.destroy` | `ProductoController@destroy` |
| GET/HEAD | `/productos/{producto}/edit` | — | `ProductoController@edit` |
| PATCH | `/productos/{producto}/restablecer` | `productos.restore` | `ProductoController@restore` |
| GET/HEAD | `/reportes` | — | `ReporteController@index` |
| GET/HEAD | `/reportes-comerciales` | `reportes-comerciales.index` | `ReporteComercialController@index` |
| GET/HEAD | `/reportes-comerciales/excel` | `reportes-comerciales.excel` | `ReporteComercialController@excel` |
| GET/HEAD | `/reportes-comerciales/pdf` | `reportes-comerciales.pdf` | `ReporteComercialController@pdf` |
| GET/HEAD | `/reportes-comerciales/productos.csv` | `reportes-comerciales.productos.csv` | `ReporteComercialController@productosCsv` |
| GET/HEAD | `/reportes-comerciales/ventas/{ticket}` | `reportes-comerciales.venta` | `ReporteComercialController@venta` |
| GET/HEAD | `/reportes-ingresos` | `reportes-ingresos.index` | `ReporteComercialController@ingresosIndex` |
| GET/HEAD/POST/PUT/PATCH/DELETE/OPTIONS | `/reportes-inventario` | — | `RedirectController` |
| GET/HEAD | `/reportes/excel` | `reportes.excel` | `ReporteController@excel` |
| GET/HEAD | `/reportes/{id}` | — | `ReporteController@show` |
| GET/HEAD | `/reportes/{id}/pdf` | — | `ReporteController@pdf` |
| GET/HEAD | `/storage/{path}` | `storage.local` | Closure del framework |
| PUT | `/storage/{path}` | `storage.local.upload` | Closure del framework |
| GET/HEAD | `/up` | — | Health check del framework |
| GET/HEAD | `/usuarios` | `usuarios.index` | `UserController@index` |
| POST | `/usuarios` | `usuarios.store` | `UserController@store` |
| GET/HEAD | `/usuarios/create` | `usuarios.create` | `UserController@create` |
| GET/HEAD | `/usuarios/{usuario}` | `usuarios.show` | `UserController@show` |
| PUT/PATCH | `/usuarios/{usuario}` | `usuarios.update` | `UserController@update` |
| DELETE | `/usuarios/{usuario}` | `usuarios.destroy` | `UserController@destroy` |
| GET/HEAD | `/usuarios/{usuario}/edit` | `usuarios.edit` | `UserController@edit` |
| PATCH | `/usuarios/{usuario}/restablecer` | `usuarios.restore` | `UserController@restore` |

## 5. MAPA DE ARCHIVOS — FRONTEND (VISTAS)

### Layouts y Componentes Base

| Archivo | Ruta completa | Descripción |
|---|---|---|
| `app.blade.php` | `resources/views/layouts/app.blade.php` | Layout autenticado; carga CSS/JS Vite, Chart.js, Bootstrap y compone la estructura general. |
| `layout.blade.php` | `resources/views/catalogo/layout.blade.php` | Layout público del catálogo. |
| `navbar.blade.php` | `resources/views/partials/navbar.blade.php` | Cabecera, identidad y sesión. |
| `sidebar.blade.php` | `resources/views/partials/sidebar.blade.php` | Navegación lateral por grupos y roles. |
| `footer.blade.php` | `resources/views/partials/footer.blade.php` | Pie global. |

### Vistas por Módulo

**Autenticación:**

- `resources/views/auth/login.blade.php` → formulario de ingreso y errores de autenticación.

**Dashboard:**

- `resources/views/dashboard/index.blade.php` → métricas, accesos comerciales, estado IoT, alertas y gráfico térmico; publica endpoints en `#dashboard-config`.

**Producción:**

- `resources/views/produccion/index.blade.php` → formulario de inicio, producción activa, cuajo recomendado y finalización.

**Control ESP32:**

- `resources/views/control/index.blade.php` → controles manuales/automáticos de motor, ventilador y sensor.
- `resources/views/alertas/index.blade.php` → listado y atención de alertas.
- `resources/views/eventos/index.blade.php` → bitácora filtrable de eventos.

**POS / Ventas:**

- `resources/views/pos/index.blade.php` → catálogo interno, carrito FEFO, cobro, confirmación y ticket imprimible.

**Inventario y ajustes:**

- `resources/views/inventario/index.blade.php` → inventario detallado por lote.
- `resources/views/inventario/general.blade.php` → inventario consolidado por presentación.
- `resources/views/ajustes-inventario/index.blade.php` → historial de ajustes.
- `resources/views/ajustes-inventario/create.blade.php` → alta de ajuste.
- `resources/views/ajustes-inventario/edit.blade.php` → edición de ajuste vigente.
- `resources/views/ajustes-inventario/show.blade.php` → detalle, trazabilidad y anulación.
- `resources/views/descartes-productos/_form.blade.php` → parcial de formulario de descarte.
- `resources/views/descartes-productos/create.blade.php` → alta de descarte.
- `resources/views/descartes-productos/edit.blade.php` → edición de descarte.
- `resources/views/descartes-productos/index.blade.php` → listado/filtros/exportaciones.
- `resources/views/descartes-productos/show.blade.php` → detalle y procesamiento.
- `resources/views/descartes-productos/pdf.blade.php` → comprobante PDF individual.
- `resources/views/descartes-productos/consolidado-pdf.blade.php` → reporte PDF consolidado.

**Proveedores e ingresos:**

- `resources/views/productores/_form.blade.php` → campos reutilizables del proveedor.
- `resources/views/productores/create.blade.php` → alta de proveedor.
- `resources/views/productores/edit.blade.php` → edición de proveedor.
- `resources/views/productores/index.blade.php` → directorio y búsqueda.
- `resources/views/productores/show.blade.php` → ficha e ingresos relacionados.
- `resources/views/ingresos/_item.blade.php` → fila reutilizable de producto/lote del ingreso.
- `resources/views/ingresos/create.blade.php` → registro de ingreso con múltiples ítems.
- `resources/views/ingresos/index.blade.php` → historial de ingresos.
- `resources/views/ingresos/show.blade.php` → detalle de ingreso y lotes.

**Productos y presentaciones:**

- `resources/views/productos/form.blade.php` → campos reutilizables de producto técnico.
- `resources/views/productos/create.blade.php` → alta de producto.
- `resources/views/productos/edit.blade.php` → edición de producto.
- `resources/views/productos/index.blade.php` → listado, búsqueda y baja/restauración lógica.
- `resources/views/presentaciones/form.blade.php` → campos reutilizables de presentación comercial.
- `resources/views/presentaciones/create.blade.php` → alta de presentación.
- `resources/views/presentaciones/edit.blade.php` → edición de presentación.
- `resources/views/presentaciones/index.blade.php` → listado, búsqueda y estado.

**Catálogo público:**

- `resources/views/catalogo/catalogo.blade.php` → rejilla pública con búsqueda/filtro y carga de `catalogo.js`.
- `resources/views/catalogo/detalle.blade.php` → ficha pública de una presentación.
- `resources/views/catalogo/nosotros.blade.php` → contenido institucional.
- `resources/views/catalogo/contacto.blade.php` → información de contacto.

**Reportes:**

- `resources/views/reportes/index.blade.php` → listado de producciones reportables.
- `resources/views/reportes/show.blade.php` → vista detallada de una producción.
- `resources/views/reportes/pdf.blade.php` → plantilla PDF de producción.
- `resources/views/reportes/ventas.blade.php` → tablero de ventas, tabla, filtros, ranking y exportaciones.
- `resources/views/reportes/ingresos.blade.php` → reporte de ingresos de productores.
- `resources/views/reportes/comercial-pdf.blade.php` → plantilla PDF comercial.

**Usuarios:**

- `resources/views/usuarios/form.blade.php` → campos reutilizables de usuario/rol.
- `resources/views/usuarios/create.blade.php` → alta de usuario.
- `resources/views/usuarios/edit.blade.php` → edición de usuario.
- `resources/views/usuarios/index.blade.php` → listado, filtros y acciones administrativas.
- `resources/views/usuarios/show.blade.php` → detalle de usuario.

## 6. MAPA DE ARCHIVOS — CSS

### CSS Fuente

| Archivo | Ruta | Qué estilos contiene | Usado en |
|---|---|---|---|
| `app.css` | `resources/css/app.css` | Variables globales, layout autenticado, navbar/sidebar, panel asíncrono, botones y utilidades responsivas de tabla. | `layouts/app.blade.php` |
| `catalogo.css` | `resources/css/catalogo.css` | Layout público, tarjetas, filtros, detalle y responsive. | `catalogo/layout.blade.php` |
| `comercial.css` | `resources/css/comercial.css` | POS, carrito, ticket/impresión, ingresos, proveedores, inventario y reportes comerciales. | `layouts/app.blade.php` |
| `control.css` | `resources/css/control.css` | Panel HMI, actuadores, modos y estados. | `layouts/app.blade.php` |
| `dashboard.css` | `resources/css/dashboard.css` | Tarjetas métricas, panel IoT, alertas y gráfico. | `layouts/app.blade.php` |
| `eventos.css` | `resources/css/eventos.css` | Filtros y tabla de bitácora. | `layouts/app.blade.php` |
| `login.css` | `resources/css/login.css` | Pantalla y formulario de acceso. | `auth/login.blade.php` |
| `presentaciones.css` | `resources/css/presentaciones.css` | Tabla, formularios y acciones de presentaciones. | `layouts/app.blade.php` |
| `produccion.css` | `resources/css/produccion.css` | Formulario, estado, tarjetas y controles de producción. | `layouts/app.blade.php` |
| `productos.css` | `resources/css/productos.css` | Listado, buscador, formulario y acciones de productos. | `layouts/app.blade.php` |
| `reportes-show.css` | `resources/css/reportes-show.css` | Detalle, métricas, tablas y acciones de reporte de producción. | `layouts/app.blade.php` |
| `usuarios.css` | `resources/css/usuarios.css` | Listado, formularios, badges y responsive de usuarios. | `layouts/app.blade.php` |

### CSS Compilado / Público

| Archivo | Ruta en `/public` | Generado desde |
|---|---|---|
| `app-BCp9CTlH.css` | `public/build/assets/app-BCp9CTlH.css` | `resources/css/app.css` |
| `catalogo-BYPYJSqY.css` | `public/build/assets/catalogo-BYPYJSqY.css` | `resources/css/catalogo.css` |
| `comercial-DpzHwb7K.css` | `public/build/assets/comercial-DpzHwb7K.css` | `resources/css/comercial.css` |
| `control-BwtisnPf.css` | `public/build/assets/control-BwtisnPf.css` | `resources/css/control.css` |
| `dashboard-BRJeantm.css` | `public/build/assets/dashboard-BRJeantm.css` | `resources/css/dashboard.css` |
| `eventos-BQAESbJN.css` | `public/build/assets/eventos-BQAESbJN.css` | `resources/css/eventos.css` |
| `login-CNXflR8z.css` | `public/build/assets/login-CNXflR8z.css` | `resources/css/login.css` |
| `presentaciones-wCAgIfIR.css` | `public/build/assets/presentaciones-wCAgIfIR.css` | `resources/css/presentaciones.css` |
| `produccion-DR_XFG93.css` | `public/build/assets/produccion-DR_XFG93.css` | `resources/css/produccion.css` |
| `productos-SS3GEg7S.css` | `public/build/assets/productos-SS3GEg7S.css` | `resources/css/productos.css` |
| `reportes-show-q_k0dt8o.css` | `public/build/assets/reportes-show-q_k0dt8o.css` | `resources/css/reportes-show.css` |
| `usuarios-DM7z9E7i.css` | `public/build/assets/usuarios-DM7z9E7i.css` | `resources/css/usuarios.css` |

No existe `public/css` con CSS propio fuera del build de Vite.

### Clases Tailwind Personalizadas

- `tailwind.config.js`: `[no encontrado]`.
- Tailwind 4 se integra con `@tailwindcss/vite` en `vite.config.js`.
- `resources/css/app.css` define manualmente compatibilidad para `hidden` y `md:table-cell`, utilizada para ocultar columnas secundarias en pantallas pequeñas.
- La identidad visual se concentra en variables CSS propias; no se encontró una extensión de tema Tailwind declarativa.

## 7. MAPA DE ARCHIVOS — JAVASCRIPT

### JS por Vista/Módulo

**`resources/js/app.js`**

- Usado en: `resources/views/layouts/app.blade.php`, `resources/views/catalogo/layout.blade.php` y `resources/views/auth/login.blade.php` mediante `@vite`.
- Funciones principales: arranca Bootstrap/Axios, instala navegación asíncrona del panel y confirmaciones; importa ranking de ventas y tablas responsivas.
- Conecta con: delega las solicitudes a `async-panel.js`; no fija una URL propia.
- Dependencias: `bootstrap.js`, `async-panel.js`, `reporte-ventas.js`, `responsive-tables.js` y Bootstrap global para componentes visuales.

**`resources/js/bootstrap.js`**

- Usado en: importado por `resources/js/app.js`.
- Funciones principales: expone `window.axios` y configura `X-Requested-With: XMLHttpRequest`.
- Conecta con: ningún endpoint directamente.
- Dependencias: paquete `axios`.

**`resources/js/async-panel.js`**

- Usado en: importado por `resources/js/app.js`.
- Funciones principales: `responseStaysInCurrentPanel`, `replaceMainContent`, `replacePanelPage`, `syncCollapsibleGroup`, `syncSidebarState`, `navigatePanel`, `submitAsyncAction`, `showError`, `shouldUsePanelNavigation`, `installAsyncPanelActions`.
- Conecta con: ejecuta `fetch` GET a enlaces internos y `fetch` con el método/URL de formularios marcados para navegación asíncrona; el controller depende dinámicamente de la ruta del enlace/formulario.
- Dependencias: DOM, History API, `fetch`, `FormData` y estructura `[data-main-panel]`/sidebar del layout.

**`resources/js/catalogo.js`**

- Usado en: `resources/views/catalogo/catalogo.blade.php` mediante `@vite`.
- Funciones principales: filtra tarjetas del catálogo por texto/categoría, actualiza conteo y mensaje vacío.
- Conecta con: no realiza HTTP; opera sobre datos ya renderizados por `CatalogoController@index`.
- Dependencias: DOM del catálogo público.

**`resources/js/comercial.js`**

- Usado en: cargado por `resources/views/layouts/app.blade.php`; se activa según los elementos presentes en POS, ingresos y reportes.
- Funciones principales: solicitud JSON común, filas dinámicas de ingreso, catálogo/carrito FEFO, cantidades, cobro, modal de venta, vista/impresión de ticket y carga de detalles comerciales.
- Conecta con: `GET /comercial/catalogo` → `ComercialController@catalogo`; `POST /comercial/ventas` → `ComercialController@vender`; `GET /ingresos-productores/{id}/detalle-json` → `ComercialController@detalleJson`; `GET /reportes-comerciales/ventas/{ticket}` → `ReporteComercialController@venta`.
- Dependencias: `fetch`, CSRF del documento, Bootstrap Modal global y atributos `data-*` de las vistas.

**`resources/js/control.js`**

- Usado en: cargado por `layouts/app.blade.php`; inicializa `resources/views/control/index.blade.php` cuando existe su raíz.
- Funciones principales: `initializeControl`; sincroniza controles de modo y envía formularios de actuación.
- Conecta con: URLs de formularios `POST /control/motor/{estado}`, `/control/ventilador/{estado}`, `/control/sensor/{estado}` y `/control/{tipo}/modo` → `ControlController`.
- Dependencias: DOM y formularios Blade con token CSRF.

**`resources/js/dashboard.js`**

- Usado en: cargado por `layouts/app.blade.php`; solo arranca si existe `#dashboard-config` en `dashboard/index.blade.php`.
- Funciones principales: `reproducirAlarmaSonora`, `silenciarAlarma`, `aceptarAlertaPanel`, `inicializarBotonesAlerta`, `inicializarGrafico`, `actualizarDashboard`, `actualizarGrafico`, `inicializarDashboard`, `cargarConfiguracionDashboard`.
- Conecta con: `GET /dashboard/datos` → `Api\\Esp32Controller@dashboard` y `GET /dashboard/temperaturas` → `Api\\Esp32Controller@temperaturas`. Los valores por defecto del archivo son `/api/dashboard` y `/api/temperaturas`, pero la vista autenticada los reemplaza mediante `data-*`.
- Dependencias: `fetch`, Chart.js global, Bootstrap global, Web Audio API y DOM del dashboard.

**`resources/js/produccion.js`**

- Usado en: cargado por `layouts/app.blade.php`; actúa sobre `produccion/index.blade.php`.
- Funciones principales: `initializeProductionForm`; actualiza datos del producto, recomendación/rangos de cuajo, validaciones y resumen.
- Conecta con: no usa `fetch`; los formularios Blade envían `POST /produccion/iniciar` y `POST /produccion/finalizar` a `ProduccionController`.
- Dependencias: `rennet-recommendation.js` y datos JSON/atributos renderizados por Blade.

**`resources/js/productos.js`**

- Usado en: cargado por `layouts/app.blade.php`; actúa en formularios de productos.
- Funciones principales: `initializeProductForms`; limita/sanea campos numéricos y técnicos.
- Conecta con: no usa HTTP; el envío lo hacen los formularios a `ProductoController`.
- Dependencias: DOM de `productos/form.blade.php`.

**`resources/js/rennet-recommendation.js`**

- Usado en: importado por `resources/js/produccion.js`.
- Funciones principales: `requiredRennet` calcula cuajo requerido; `rennetReferences` calcula referencias de mínimo/recomendado/máximo.
- Conecta con: ningún endpoint.
- Dependencias: ninguna externa.

**`resources/js/reporte-ventas.js`**

- Usado en: importado por `resources/js/app.js`; consume el nodo JSON `#top-products-data` de `resources/views/reportes/ventas.blade.php`.
- Funciones principales: `initializeSalesRanking`; construye/destruye el gráfico de productos más vendidos.
- Conecta con: no hace HTTP; los datos los renderiza `ReporteComercialController@index`.
- Dependencias: Chart.js global y JSON embebido no ejecutable.

**`resources/js/responsive-tables.js`**

- Usado en: importado por `resources/js/app.js`.
- Funciones principales: normaliza encabezados, detecta estado/acciones/nombre principal y `makeTablesResponsive` asigna `hidden md:table-cell` a columnas secundarias.
- Conecta con: ningún endpoint.
- Dependencias: DOM; se reaplica tras navegación asíncrona mediante evento del panel.

**`resources/js/supplier-cart.js`**

- Usado en: `[no encontrado]` en imports o entradas Vite de producción; existe como helper propio y puede ser usado por pruebas JS.
- Funciones principales: `chooseSupplier`, `selectedSupplier`, `supplierStock`, `supplierPrice`.
- Conecta con: ningún endpoint.
- Dependencias: estructura de producto/proveedores pasada como objeto. Es un helper heredado; la venta real usa FEFO en servidor.

**Scripts embebidos en Blade:**

- `resources/views/reportes/ventas.blade.php` contiene `script[type="application/json"]#top-products-data`: datos JSON, no código ejecutable; lo consume `reporte-ventas.js`.
- `resources/views/layouts/app.blade.php` y `resources/views/catalogo/layout.blade.php` cargan librerías CDN (Chart.js/Bootstrap); no contienen lógica JS propia adicional.
- No se encontraron otros bloques ejecutables `<script>` propios dentro de vistas Blade.
- Archivos TypeScript propios: `[no encontrado]`. Directorio `public/js`: `[no encontrado]`.

### JS Compilado

| Archivo en `/public` | Generado desde | Incluido en |
|---|---|---|
| `public/build/assets/app-BplKg5Fw.js` | `resources/js/app.js` y sus imports | Layout autenticado, layout de catálogo y login. |
| `public/build/assets/catalogo-BGwNBJmq.js` | `resources/js/catalogo.js` | Vista `catalogo/catalogo.blade.php`. |
| `public/build/assets/comercial-ZpzQ71Lm.js` | `resources/js/comercial.js` | Layout autenticado. |
| `public/build/assets/control-_9koPk9L.js` | `resources/js/control.js` | Layout autenticado. |
| `public/build/assets/dashboard-COfueUf7.js` | `resources/js/dashboard.js` | Layout autenticado. |
| `public/build/assets/produccion-CfIr7sOu.js` | `resources/js/produccion.js` | Layout autenticado. |
| `public/build/assets/productos-17WyFMhu.js` | `resources/js/productos.js` | Layout autenticado. |

La correspondencia exacta hash/fuente está registrada en `public/build/manifest.json` y cambia al ejecutar `npm run build`.

## 8. BASE DE DATOS

### Tablas

| Tabla | Migración | Modelo | Descripción |
|---|---|---|---|
| `users` | `database/migrations/0001_01_01_000000_create_users_table.php` y `2026_09_08_000001_add_productor_role_to_users_table.php` | `User` | Cuentas, rol y estado. |
| `password_reset_tokens` | `database/migrations/0001_01_01_000000_create_users_table.php` | `[sin modelo]` | Tokens de recuperación. |
| `sessions` | `database/migrations/0001_01_01_000000_create_users_table.php` | `[sin modelo]` | Sesiones si `SESSION_DRIVER=database`. |
| `cache`, `cache_locks` | `database/migrations/0001_01_01_000001_create_cache_table.php` | `[sin modelo]` | Caché y bloqueos. |
| `jobs`, `job_batches`, `failed_jobs` | `database/migrations/0001_01_01_000002_create_jobs_table.php` | `[sin modelo]` | Colas y fallos. |
| `productos` | `database/migrations/2026_07_08_150320_create_productos_table.php` y migraciones `add_*_to_productos`/baja lógica | `Producto` | Producto lácteo y parámetros técnicos. |
| `dispositivos` | `database/migrations/2026_07_08_150429_create_dispositivos_table.php` | `Dispositivo` | Equipos IoT. |
| `sensores` | `database/migrations/2026_07_08_150531_create_sensores_table.php` y `2026_07_29_175807_add_temperatura_actual_to_sensores_table.php` | `Sensor` | Sensores asociados al dispositivo. |
| `actuadores` | `database/migrations/2026_07_08_150720_create_actuadores_table.php` | `Actuador` | Motor, ventilador y otros actuadores. |
| `producciones` | `database/migrations/2026_07_08_150744_create_producciones_table.php` y migraciones de temperatura/cuajo | `Produccion` | Lotes de producción y métricas. |
| `lecturas` | `database/migrations/2026_07_08_150851_create_lecturas_table.php` | `Lectura` | Series térmicas por producción/sensor. |
| `alertas` | `database/migrations/2026_07_08_150909_create_alertas_table.php` y `2026_07_16_171351_make_lectura_id_nullable_in_alertas_table.php` | `Alerta` | Alertas térmicas/operativas. |
| `reportes` | `database/migrations/2026_07_08_150925_create_reportes_table.php` | `Reporte` | Reportes ligados a producción. |
| `configuraciones` | `database/migrations/2026_07_08_150948_create_configuraciones_table.php` y migraciones de control/pasteurización | `Configuracion` | Umbrales y configuración del sistema. |
| `eventos` | `database/migrations/2026_07_14_190918_create_eventos_table.php` y `2026_07_16_141301_add_user_id_to_eventos_table.php` | `Evento` | Auditoría de operaciones. |
| `presentaciones` | `database/migrations/2026_08_25_211829_create_presentaciones_table.php` y migraciones de catálogo/stock | `Presentacion` | Formatos comerciales del producto. |
| `productores` | `database/migrations/2026_09_10_000001_refactor_acopio.php` | `Productor` | Proveedores/productores. |
| `ingresos_productores` | `database/migrations/2026_09_10_000001_refactor_acopio.php` | `IngresoProductor` | Cabecera de recepción de inventario. |
| `ingresos_productores_items` | `database/migrations/2026_09_10_000001_refactor_acopio.php` y migraciones de fechas/índices/enteros | `IngresoProductorItem` | Lotes recibidos, precios, stock y caducidad. |
| `ventas` | `database/migrations/2026_09_05_000000_create_consignacion_y_ventas_tables.php` y refactorizaciones posteriores | `Venta` | Líneas vendidas por lote y ticket. |
| `tickets_venta` | `database/migrations/2026_09_10_000001_refactor_acopio.php` | `TicketVenta` | Cabecera de comprobante/forma de pago. |
| `movimientos_inventario` | `database/migrations/2026_09_10_000001_refactor_acopio.php` | `MovimientoInventario` | Kardex y trazabilidad de cambios. |
| `conciliaciones_stock_acopio` | `database/migrations/2026_09_10_000001_refactor_acopio.php` | `[sin modelo]` | Conciliación técnica durante el refactor de acopio. |
| `ajustes_inventario` | `database/migrations/2026_09_17_000004_create_ajustes_inventario_table.php` | `AjusteInventario` | Ajustes, reversos y auditoría. |
| `descartes_productos` | `database/migrations/2026_09_17_000005_create_descartes_productos_table.php` | `DescarteProducto` | Producto retirado y su procesamiento. |

Las tablas históricas `consignaciones`, `consignacion_items`, `liquidaciones` y `devoluciones` aparecen en migraciones de transición, pero son renombradas o eliminadas por las migraciones posteriores; no forman parte del esquema final esperado.

**Inventario completo de migraciones:**

| Ruta | Cambio principal |
|---|---|
| `database/migrations/0001_01_01_000000_create_users_table.php` | Crea usuarios, tokens de contraseña y sesiones. |
| `database/migrations/0001_01_01_000001_create_cache_table.php` | Crea caché y bloqueos. |
| `database/migrations/0001_01_01_000002_create_jobs_table.php` | Crea infraestructura de colas. |
| `database/migrations/2026_07_08_150320_create_productos_table.php` | Crea productos. |
| `database/migrations/2026_07_08_150429_create_dispositivos_table.php` | Crea dispositivos. |
| `database/migrations/2026_07_08_150531_create_sensores_table.php` | Crea sensores. |
| `database/migrations/2026_07_08_150720_create_actuadores_table.php` | Crea actuadores. |
| `database/migrations/2026_07_08_150744_create_producciones_table.php` | Crea producciones. |
| `database/migrations/2026_07_08_150851_create_lecturas_table.php` | Crea lecturas. |
| `database/migrations/2026_07_08_150909_create_alertas_table.php` | Crea alertas. |
| `database/migrations/2026_07_08_150925_create_reportes_table.php` | Crea reportes. |
| `database/migrations/2026_07_08_150948_create_configuraciones_table.php` | Crea configuraciones. |
| `database/migrations/2026_07_09_095126_add_control_fields_to_configuraciones_table.php` | Añade parámetros de control. |
| `database/migrations/2026_07_14_190918_create_eventos_table.php` | Crea eventos. |
| `database/migrations/2026_07_16_141301_add_user_id_to_eventos_table.php` | Relaciona eventos con usuarios. |
| `database/migrations/2026_07_16_164440_add_temperaturas_to_producciones_table.php` | Añade temperaturas a producción. |
| `database/migrations/2026_07_16_165633_add_estadisticas_temperatura_to_producciones_table.php` | Añade estadísticas térmicas. |
| `database/migrations/2026_07_16_171351_make_lectura_id_nullable_in_alertas_table.php` | Hace opcional la lectura de una alerta. |
| `database/migrations/2026_07_16_215818_add_temperatura_pasteurizacion_to_productos_table.php` | Añade temperatura objetivo al producto. |
| `database/migrations/2026_07_16_222029_add_temperatura_pasteurizacion_to_configuraciones_table.php` | Añade configuración de pasteurización. |
| `database/migrations/2026_07_16_223419_add_temperatura_pasteurizacion_to_configuraciones_table.php` | Ajusta/asegura configuración de pasteurización. |
| `database/migrations/2026_07_29_175807_add_temperatura_actual_to_sensores_table.php` | Añade última temperatura al sensor. |
| `database/migrations/2026_08_25_211718_add_datos_tecnicos_to_productos_table.php` | Añade datos técnicos de producto. |
| `database/migrations/2026_08_25_211829_create_presentaciones_table.php` | Crea presentaciones. |
| `database/migrations/2026_08_26_004012_add_tipo_cuajo_to_productos_table.php` | Añade tipo/dosis de cuajo. |
| `database/migrations/2026_08_26_104749_add_stock_y_unidad_to_productos_table.php` | Añade stock/unidad históricos. |
| `database/migrations/2026_08_26_121058_add_cuajo_to_producciones_table.php` | Registra cuajo en producción. |
| `database/migrations/2026_08_27_100000_add_activo_for_logical_deletes.php` | Añade estado activo para bajas lógicas. |
| `database/migrations/2026_08_28_091959_add_catalog_fields_to_presentaciones_table.php` | Añade campos de catálogo y venta. |
| `database/migrations/2026_09_05_000000_create_consignacion_y_ventas_tables.php` | Crea esquema comercial histórico y ventas. |
| `database/migrations/2026_09_08_000001_add_productor_role_to_users_table.php` | Añade rol histórico Productor. |
| `database/migrations/2026_09_08_000002_add_fechas_to_consignacion_items_table.php` | Añade elaboración/caducidad a lotes históricos. |
| `database/migrations/2026_09_10_000001_refactor_acopio.php` | Migra consignaciones a acopio, productores, tickets, movimientos y conciliación. |
| `database/migrations/2026_09_11_000001_add_inventory_search_indexes.php` | Añade índices de búsqueda de inventario. |
| `database/migrations/2026_09_17_000001_remove_returns_and_integer_quantities.php` | Elimina devoluciones y normaliza cantidades enteras. |
| `database/migrations/2026_09_17_000002_drop_stock_cuajo_from_productos.php` | Elimina stock de cuajo del producto. |
| `database/migrations/2026_09_17_000003_remove_liquidaciones_completely.php` | Elimina liquidaciones y referencias. |
| `database/migrations/2026_09_17_000004_create_ajustes_inventario_table.php` | Crea ajustes de inventario. |
| `database/migrations/2026_09_17_000005_create_descartes_productos_table.php` | Crea descartes de productos. |

### Relaciones Clave

```text
Dispositivo 1 ── N Sensor 1 ── N Lectura N ── 1 Produccion
      │                                  │
      ├── 1 ── N Actuador               ├── 1 ── N Alerta
      └── 1 ── N Produccion             ├── 1 ── N Evento
                                         └── 1 ── N Reporte

Producto 1 ── N Produccion
Producto 1 ── N Presentacion 1 ── N IngresoProductorItem (lote)
Productor 1 ── N IngresoProductor 1 ── N IngresoProductorItem
IngresoProductorItem 1 ── N Venta/AjusteInventario/DescarteProducto/MovimientoInventario
TicketVenta 1 ── N Venta
User 1 ── N Produccion/Evento/IngresoProductor/TicketVenta/Venta/Ajuste/Descarte
```

## 9. CONEXIONES Y DEPENDENCIAS

### Quién llama a quién (Frontend → Backend)

| Vista / JS | Llama a ruta | Controller@método | Respuesta |
|---|---|---|---|
| `auth/login.blade.php` | `POST /login` | `AuthController@autenticar` | Redirect con sesión o errores. |
| Navbar/layout | `POST /logout` | `AuthController@logout` | Invalida sesión y redirige. |
| `catalogo/*.blade.php` | `GET /catalogo`, `/catalogo/{presentacion}`, páginas informativas | `CatalogoController` | HTML Blade. |
| `dashboard.js` | `GET /dashboard/datos` | `Api\\Esp32Controller@dashboard` | JSON de estado/producción/alerta. |
| `dashboard.js` | `GET /dashboard/temperaturas` | `Api\\Esp32Controller@temperaturas` | JSON de serie térmica. |
| Firmware ESP32 | `GET /api/estado` | `Api\\Esp32Controller@estado` | JSON de estado y consignas. |
| Firmware ESP32 | `POST /api/temperatura` | `Api\\Esp32Controller@temperatura` | JSON de registro/control automático. |
| Firmware ESP32 | `POST /api/ping` | `Api\\Esp32Controller@ping` | JSON de disponibilidad. |
| Firmware ESP32 | `POST /api/alerta-pasteurizacion` | `Api\\Esp32Controller@alertaPasteurizacion` | JSON de alerta. |
| Firmware ESP32 | `POST /api/finalizar-produccion` | `Api\\Esp32Controller@finalizarProduccionAutomatica` | JSON de cierre. |
| Firmware ESP32 | `POST /api/pasteurizacion` | `Api\\Esp32Controller@pasteurizacion` | JSON de estado térmico. |
| `produccion/index.blade.php` | `POST /produccion/iniciar`, `/produccion/finalizar` | `ProduccionController@iniciar/finalizar` | Redirect con estado/errores. |
| `control/index.blade.php` + `control.js` | `POST /control/*` | `ControlController@motor/ventilador/sensor/cambiarModo` | Redirect/resultado de comando. |
| `alertas/index.blade.php` | `PATCH /alertas/{id}/atender` | `AlertaController@atender` | Redirect. |
| CRUD de productos | `/productos*` | `ProductoController` | HTML/redirect. |
| CRUD de presentaciones | `/presentaciones*` | `PresentacionController` | HTML/redirect. |
| CRUD de usuarios | `/usuarios*` | `UserController` | HTML/redirect. |
| CRUD de proveedores | `/productores*` | `ProductorController` | HTML/redirect. |
| `ingresos/create.blade.php` | `POST /ingresos-productores` | `ComercialController@guardarIngreso` | Redirect al detalle/listado. |
| `comercial.js` | `GET /ingresos-productores/{ingreso}/detalle-json` | `ComercialController@detalleJson` | JSON de ingreso/lotes. |
| `pos/index.blade.php` + `comercial.js` | `GET /comercial/catalogo` | `ComercialController@catalogo` | JSON de presentaciones y lotes disponibles. |
| `pos/index.blade.php` + `comercial.js` | `POST /comercial/ventas` | `ComercialController@vender` | JSON de ticket y líneas vendidas. |
| Inventario por lote | `POST /lotes/{item}/movimientos` | `ComercialController@movimiento` | Redirect. |
| Inventario por lote | `PATCH /lotes/{item}/regularizar` | `ComercialController@regularizar` | Redirect. |
| Ajustes Blade | `/ajustes-inventario*` | `AjusteInventarioController` | HTML/redirect. |
| Descartes Blade | `/descartes-productos*` | `DescarteProductoController` | HTML, redirect, PDF o CSV. |
| Reportes producción | `GET /reportes*` | `ReporteController` | HTML, PDF o Excel. |
| Reportes comerciales | `GET /reportes-comerciales*` | `ReporteComercialController` | HTML, JSON, PDF, Excel o CSV. |
| `comercial.js` | `GET /reportes-comerciales/ventas/{ticket}` | `ReporteComercialController@venta` | JSON del comprobante. |
| `async-panel.js` | URL del enlace/formulario interno seleccionado | Controller resuelto por `routes/web.php` | HTML parcial, redirect o JSON según ruta. |

### Relaciones entre Modelos

- `Actuador`: pertenece a `Dispositivo`.
- `AjusteInventario`: pertenece a `IngresoProductorItem` como lote y a tres `User` (creador, actualizador y anulador).
- `Alerta`: pertenece a `Produccion` y opcionalmente a `Lectura`.
- `Configuracion`: no declara relaciones Eloquent.
- `DescarteProducto`: pertenece a lote y a usuarios creador, actualizador y procesador.
- `Dispositivo`: tiene muchos sensores, actuadores y producciones.
- `Evento`: pertenece a usuario y producción.
- `IngresoProductor`: pertenece a productor y operador; tiene muchos ítems.
- `IngresoProductorItem`: pertenece a ingreso y presentación; tiene muchas ventas, ajustes y descartes.
- `Lectura`: pertenece a producción y sensor; tiene muchas alertas.
- `MovimientoInventario`: pertenece a lote y responsable.
- `Presentacion`: pertenece a producto; tiene muchos lotes.
- `Produccion`: pertenece a usuario, producto y dispositivo; tiene muchas lecturas, alertas, eventos y reportes.
- `Producto`: tiene muchas producciones y presentaciones.
- `Productor`: tiene muchos ingresos.
- `Reporte`: pertenece a producción.
- `Sensor`: pertenece a dispositivo y tiene muchas lecturas.
- `TicketVenta`: pertenece al vendedor y tiene muchas ventas.
- `User`: tiene muchas producciones, eventos, ingresos registrados, ventas, ajustes creados y descartes creados.
- `Venta`: pertenece al lote (también expuesto como `item`), al ticket y al vendedor.

## 10. ZONA PROTEGIDA — NO MODIFICAR

Estos archivos contienen lógica IoT o térmica crítica. No editar sin pruebas de integración con el dispositivo y revisión explícita:

- `app/Http/Controllers/Api/Esp32Controller.php` — controller IoT real.
- `app/Services/ActuadorService.php` — transiciones manuales/automáticas y eventos.
- `app/Services/EventoService.php` — bitácora usada por operaciones IoT.
- `app/Http/Controllers/ControlController.php` — órdenes del panel de control.
- `app/Http/Controllers/ProduccionController.php` — inicio/finalización y coordinación de actuadores.
- `app/Models/Actuador.php`, `app/Models/Sensor.php`, `app/Models/Dispositivo.php`, `app/Models/Lectura.php`, `app/Models/Produccion.php`, `app/Models/Alerta.php`, `app/Models/Configuracion.php`.
- `routes/api.php` — contrato HTTP del firmware ESP32.
- `routes/web.php` — contiene rutas autenticadas del dashboard/control; proteger específicamente las entradas IoT.
- `resources/js/dashboard.js`, `resources/js/control.js`, `resources/views/dashboard/index.blade.php`, `resources/views/control/index.blade.php`.
- Migraciones de dispositivos, sensores, actuadores, producciones, lecturas, alertas y configuraciones bajo `database/migrations/`.

Archivos pedidos por nombre pero inexistentes en la estructura actual:

- `app/Http/Controllers/Esp32Controller.php` → `[no encontrado]`; el archivo real está en `app/Http/Controllers/Api/Esp32Controller.php`.
- `app/Http/Controllers/SensorController.php` → `[no encontrado]`.
- `app/Http/Controllers/ActuadorController.php` → `[no encontrado]`.

## 11. ASSETS Y RECURSOS ESTÁTICOS

| Archivo | Ruta en `/public` | Descripción |
|---|---|---|
| `.htaccess` | `public/.htaccess` | Reglas Apache para enrutar al front controller. |
| `index.php` | `public/index.php` | Front controller de Laravel. |
| `robots.txt` | `public/robots.txt` | Directivas para rastreadores. |
| `favicon.ico` | `public/favicon.ico` | Favicon; el archivo inspeccionado tiene 0 bytes. |
| `manifest.json` | `public/build/manifest.json` | Mapa Vite de entradas fuente a assets versionados. |
| Assets CSS/JS | `public/build/assets/*` | Salida compilada detallada en secciones 6 y 7. |
| Imagen de presentación | `public/storage/presentaciones/izwBtREUpiHNmwUa5nn2gUmB9d7XJeVXrjPQb64h.png` | Archivo cargado para una presentación comercial. |
| Imagen de producto | `public/storage/productos/jVqBRlhIs0HJPNStgz74SVbTT2oJDuDeKACBQpMW.png` | Imagen referencial cargada para producto. |
| Marcador de storage | `public/storage/.gitignore` | Control de archivos del almacenamiento público. |

No se encontraron fuentes locales ni directorios propios `public/js` o `public/css`. Bootstrap Icons, Bootstrap y Chart.js se consumen desde CDN en los layouts.

## 12. CONFIGURACIÓN DEL ENTORNO

### Variables relevantes de `.env`

Documentar/configurar sin publicar valores secretos:

- Aplicación: `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`, `APP_LOCALE`, `APP_FALLBACK_LOCALE`, `APP_FAKER_LOCALE`.
- Base de datos: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_SOCKET`, `DB_CHARSET`, `DB_COLLATION`.
- Correo: `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`.
- Persistencia/ejecución: `FILESYSTEM_DISK`, `SESSION_DRIVER`, `SESSION_LIFETIME`, `SESSION_DOMAIN`, `CACHE_STORE`, `QUEUE_CONNECTION`.
- Registro: `LOG_CHANNEL`, `LOG_STACK`, `LOG_DEPRECATIONS_CHANNEL`, `LOG_LEVEL`.
- Servicios opcionales presentes en configuración estándar: `REDIS_CLIENT`, `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_USE_PATH_STYLE_ENDPOINT`, `VITE_APP_NAME`.
- `.env.example` existe y sirve de plantilla sin secretos; propone SQLite por defecto, mientras que el despliegue MySQL se configura con las mismas variables `DB_*`.

### Vite

- Archivo: `vite.config.js`; no existe `webpack.mix.js`.
- Plugins: `laravel-vite-plugin` y `@tailwindcss/vite`.
- Entradas CSS: los 12 archivos de `resources/css/` descritos en sección 6.
- Entradas JS: `app.js`, `catalogo.js`, `dashboard.js`, `produccion.js`, `control.js`, `productos.js`, `comercial.js`.
- Salida: `public/build/manifest.json` y `public/build/assets/*`.
- Desarrollo: `npm run dev`; producción: `npm run build`.
- Watch excluye `storage/framework/views`.

### Composer

- `php ^8.2`, `laravel/framework ^12.0`, `laravel/tinker ^2.10.1`.
- `barryvdh/laravel-dompdf ^3.1` para PDF.
- `maatwebsite/excel ^3.1` para Excel.
- Desarrollo: Faker, Pail, Pint, Sail, Mockery, Collision y PHPUnit 11.
- Scripts importantes: `composer setup`, `composer dev` y `composer test`.

### NPM

- `vite ^7.0.7`, `laravel-vite-plugin ^2.0.0`.
- `tailwindcss ^4.0.0`, `@tailwindcss/vite ^4.0.0`.
- `axios ^1.11.0`, `concurrently ^9.0.1`.
- `package.json` es privado y usa módulos ES (`"type": "module"`).

### Archivos de configuración y arranque relevantes

- `bootstrap/app.php` registra rutas web/API, health check y aliases de middleware.
- `routes/web.php` define navegación, autenticación, producción, catálogo interno, acopio, inventario y reportes.
- `routes/api.php` define el contrato ESP32.
- `config/app.php`, `config/auth.php`, `config/cache.php`, `config/database.php`, `config/filesystems.php`, `config/logging.php`, `config/mail.php`, `config/queue.php`, `config/services.php`, `config/session.php` consumen las variables anteriores y configuran cada subsistema.
- `artisan` es la entrada CLI; `composer.json` y `package.json` definen dependencias y scripts.
