-- Eliminación lógica para la base de datos existente monitoreo_lacteos
-- Ejecutar una sola vez en phpMyAdmin o MySQL antes de usar el código actualizado.
USE `monitoreo_lacteos`;

-- La tabla productos ya posee el campo activo en las migraciones del proyecto.
-- Estas instrucciones agregan los campos faltantes sin borrar datos.
ALTER TABLE `users`
    ADD COLUMN `activo` TINYINT(1) NOT NULL DEFAULT 1 AFTER `rol`,
    ADD INDEX `users_activo_index` (`activo`);

ALTER TABLE `presentaciones`
    ADD COLUMN `activo` TINYINT(1) NOT NULL DEFAULT 1 AFTER `imagen_comercial`,
    ADD INDEX `presentaciones_activo_index` (`activo`);

-- Índice para acelerar los listados de productos activos.
ALTER TABLE `productos`
    ADD INDEX `productos_activo_index` (`activo`);

-- Los registros existentes permanecen visibles.
UPDATE `users` SET `activo` = 1 WHERE `activo` IS NULL;
UPDATE `productos` SET `activo` = 1 WHERE `activo` IS NULL;
UPDATE `presentaciones` SET `activo` = 1 WHERE `activo` IS NULL;

-- Ejemplos de comprobación:
SELECT * FROM `users` WHERE `activo` = 1;
SELECT * FROM `productos` WHERE `activo` = 1;
SELECT * FROM `presentaciones` WHERE `activo` = 1;
