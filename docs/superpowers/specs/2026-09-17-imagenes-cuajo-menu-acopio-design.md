# Imágenes públicas, recomendación de cuajo y menú de acopio

## Objetivo

Corregir la publicación de imágenes de productos y presentaciones, retirar por completo el inventario de cuajo como restricción operativa y simplificar el menú de acopio mediante grupos desplegables, sin alterar los procesos de producción, ventas e inventario que no forman parte de este cambio.

## Almacenamiento de imágenes

Laravel conservará el disco `public` con raíz en `storage/app/public`. Las imágenes de productos se guardarán en `storage/app/public/productos` y las imágenes de presentaciones en `storage/app/public/presentaciones`.

`public/storage` deberá ser el enlace público configurado por Laravel hacia `storage/app/public`. Actualmente es una carpeta física vacía, por lo que se reemplazará por el enlace correcto después de verificar que no contiene archivos. Las carpetas especializadas se crearán de forma idempotente y se conservarán las rutas relativas existentes, como `productos/archivo.png` y `presentaciones/archivo.png`.

La subida, sustitución y visualización seguirán usando `Storage::disk('public')`. No se eliminarán imágenes al desactivar registros, para conservar la restauración lógica existente.

## Cuajo como recomendación, no inventario

Se eliminará físicamente `productos.stock_cuajo` mediante una nueva migración. La eliminación es intencional y no reversible sin respaldo de datos. Se conservarán:

- `tipo_cuajo`: nombre o descripción del insumo.
- `cuajo_por_litro`: dosis configurada por litro de leche.
- `unidad_cuajo`: unidad de la recomendación.

Las unidades admitidas serán `ml`, `g`, `kg`, `gotas` y `pastilla`. Se conserva `pastilla` para no invalidar datos existentes y se añade `kg` por el nuevo requisito.

La pantalla de productos dejará de solicitar y mostrar existencias de cuajo. En su lugar mostrará referencias calculadas para 1, 5 y 10 litros usando `cuajo_por_litro`.

Al iniciar una producción, el sistema calculará `cantidad_leche × cuajo_por_litro`, mostrará el resultado en vivo con la unidad configurada y lo almacenará en `producciones.cantidad_cuajo` como referencia histórica. La producción nunca será bloqueada por cuajo y no se descontará ninguna existencia. Los textos de pantalla, bitácora y producción activa hablarán de cantidad recomendada o requerida, no de stock ni de descuento.

El cálculo definitivo continuará realizándose en el servidor. Los campos calculados que pueda enviar el navegador no serán fuente de verdad.

## Navegación de acopio y ventas

El bloque lateral mantendrá accesos directos a `Ventas` y `Proveedores`. Los demás accesos se organizarán en dos grupos desplegables:

- **Inventario**: Entradas de inventario, Inventario por lote e Inventario general.
- **Reportes**: Reporte de ventas y Reporte de entradas.

Se utilizará el componente Collapse de Bootstrap ya cargado por la aplicación, sin introducir una segunda biblioteca ni un controlador JavaScript paralelo. Cada grupo permanecerá abierto cuando la ruta actual pertenezca a ese grupo, tendrá atributos accesibles y mostrará visualmente su estado. La navegación parcial existente seguirá tratando los enlaces finales como navegación normal del panel; los botones desplegables solo controlarán su submenú.

## Migraciones y compatibilidad

Una nueva migración eliminará `stock_cuajo` únicamente si la columna existe. El método `down` la recreará como nullable para permitir una reversión estructural, aunque los valores históricos eliminados no podrán recuperarse.

Las migraciones antiguas no se reescribirán para instalaciones ya ejecutadas. En una instalación nueva se aplicará el historial y luego la migración terminal, obteniendo el mismo esquema final.

## Pruebas

El trabajo se desarrollará mediante pruebas antes de modificar producción. Se cubrirán como mínimo:

- almacenamiento de productos y presentaciones en sus directorios dedicados;
- sustitución de imágenes sin afectar otros archivos;
- esquema final sin `stock_cuajo`;
- inicio de producción aunque no exista inventario de cuajo;
- cálculo y persistencia de la recomendación con su unidad;
- referencias de 1, 5 y 10 litros;
- ausencia de controles y textos de stock de cuajo;
- estructura, apertura activa y enlaces de los grupos Inventario y Reportes;
- suite PHP, pruebas JavaScript, compilación Vite y revisión visual del menú.

## Fuera de alcance

No se modificarán el stock de presentaciones comerciales, el inventario por lotes, la disponibilidad para ventas, las temperaturas de producción, el control ESP32 ni la lógica de devoluciones desarrollada anteriormente.
