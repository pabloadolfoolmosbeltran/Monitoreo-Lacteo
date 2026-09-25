-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-09-2026 a las 21:36:24
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `monitoreo_lacteos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actuadores`
--

CREATE TABLE `actuadores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dispositivo_id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `tipo` enum('Motor','Ventilador') NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 0,
  `modo` enum('Manual','Automatico') NOT NULL DEFAULT 'Automatico',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `actuadores`
--

INSERT INTO `actuadores` (`id`, `dispositivo_id`, `nombre`, `tipo`, `estado`, `modo`, `created_at`, `updated_at`) VALUES
(1, 1, 'Motor Batidor', 'Motor', 0, 'Manual', '2026-07-09 10:58:03', '2026-09-09 01:23:11'),
(2, 1, 'Ventilador', 'Ventilador', 0, 'Manual', '2026-07-09 10:58:13', '2026-09-08 21:18:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ajustes_inventario`
--

CREATE TABLE `ajustes_inventario` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lote_id` bigint(20) UNSIGNED NOT NULL,
  `creado_por` bigint(20) UNSIGNED NOT NULL,
  `actualizado_por` bigint(20) UNSIGNED NOT NULL,
  `cantidad_anterior` int(10) UNSIGNED NOT NULL,
  `cantidad_nueva` int(10) UNSIGNED NOT NULL,
  `diferencia` int(11) NOT NULL,
  `tipo_motivo` varchar(40) NOT NULL,
  `motivo` text NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'activo',
  `anulado_por` bigint(20) UNSIGNED DEFAULT NULL,
  `anulado_en` timestamp NULL DEFAULT NULL,
  `motivo_anulacion` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ajustes_inventario`
--

INSERT INTO `ajustes_inventario` (`id`, `lote_id`, `creado_por`, `actualizado_por`, `cantidad_anterior`, `cantidad_nueva`, `diferencia`, `tipo_motivo`, `motivo`, `estado`, `anulado_por`, `anulado_en`, `motivo_anulacion`, `created_at`, `updated_at`) VALUES
(1, 6, 1, 1, 20, 1, -19, 'daño', 'Dañado', 'anulado', 1, '2026-09-17 19:52:55', 'Error', '2026-09-17 19:51:42', '2026-09-17 19:52:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alertas`
--

CREATE TABLE `alertas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `produccion_id` bigint(20) UNSIGNED NOT NULL,
  `lectura_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo` varchar(50) NOT NULL,
  `mensaje` text NOT NULL,
  `atendida` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `alertas`
--

INSERT INTO `alertas` (`id`, `produccion_id`, `lectura_id`, `tipo`, `mensaje`, `atendida`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Pasteurizacion', '¡ALERTA CRÍTICA! La leche alcanzó los 70°C.', 1, '2026-07-23 19:12:23', '2026-07-23 19:56:23'),
(2, 2, NULL, 'Pasteurizacion', '¡ALERTA CRÍTICA! La leche alcanzó los 70°C.', 1, '2026-07-23 19:57:46', '2026-07-23 19:58:12'),
(3, 3, NULL, 'Pasteurizacion', '¡ALERTA CRÍTICA! La leche alcanzó los 70°C.', 1, '2026-07-23 20:02:46', '2026-07-23 20:03:33'),
(4, 4, NULL, 'Pasteurizacion', '¡ALERTA CRÍTICA! La leche alcanzó los 70°C.', 1, '2026-07-23 20:09:57', '2026-07-23 20:27:25'),
(5, 5, NULL, 'Pasteurizacion', '¡ALERTA CRÍTICA! La leche alcanzó los 70°C.', 1, '2026-07-23 20:27:50', '2026-07-23 20:28:44'),
(6, 7, NULL, 'Pasteurizacion', '¡ALERTA CRÍTICA! La leche alcanzó los 70°C.', 1, '2026-07-23 20:38:21', '2026-07-23 20:59:05'),
(7, 9, NULL, 'Pasteurizacion', '¡ALERTA CRÍTICA! La leche alcanzó los 70°C.', 1, '2026-07-23 21:34:46', '2026-07-23 22:09:31'),
(8, 11, NULL, 'Pasteurizacion', '¡ALERTA CRÍTICA! La leche alcanzó los 70°C.', 1, '2026-07-23 22:08:54', '2026-07-23 22:09:29'),
(9, 12, NULL, 'Pasteurizacion', '¡ALERTA CRÍTICA! La leche alcanzó los 70°C.', 1, '2026-07-23 22:15:48', '2026-07-24 01:56:26'),
(10, 38, NULL, 'Pasteurizacion', '¡ALERTA CRÍTICA! La leche alcanzó los 70°C.', 1, '2026-08-20 14:44:28', '2026-08-20 14:48:07'),
(11, 42, NULL, 'Pasteurizacion', '¡ALERTA CRÍTICA! La leche alcanzó los 70°C.', 1, '2026-08-22 00:12:19', '2026-08-26 13:07:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba', 'i:1;', 1790104777),
('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1790104776;', 1790104777);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuraciones`
--

CREATE TABLE `configuraciones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `intervalo_lectura` int(11) NOT NULL DEFAULT 5,
  `temperatura_ventilador` decimal(5,2) NOT NULL DEFAULT 45.00,
  `temperatura_pasteurizacion` decimal(5,2) NOT NULL DEFAULT 70.00,
  `motor_automatico` tinyint(1) NOT NULL DEFAULT 1,
  `motor_encendido` tinyint(1) NOT NULL DEFAULT 0,
  `ventilador_automatico` tinyint(1) NOT NULL DEFAULT 1,
  `ventilador_encendido` tinyint(1) NOT NULL DEFAULT 0,
  `sensor_activo` tinyint(1) NOT NULL DEFAULT 1,
  `nombre_sistema` varchar(255) NOT NULL DEFAULT 'Sistema Inteligente de Producción Láctea',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `descartes_productos`
--

CREATE TABLE `descartes_productos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lote_id` bigint(20) UNSIGNED NOT NULL,
  `creado_por` bigint(20) UNSIGNED NOT NULL,
  `actualizado_por` bigint(20) UNSIGNED NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL,
  `fecha_caducidad` date DEFAULT NULL,
  `costo_unitario` decimal(12,2) NOT NULL,
  `perdida_total` decimal(14,2) NOT NULL,
  `tipo_motivo` varchar(40) NOT NULL,
  `notas` text DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'pendiente',
  `procesado_por` bigint(20) UNSIGNED DEFAULT NULL,
  `procesado_en` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `descartes_productos`
--

INSERT INTO `descartes_productos` (`id`, `lote_id`, `creado_por`, `actualizado_por`, `cantidad`, `fecha_caducidad`, `costo_unitario`, `perdida_total`, `tipo_motivo`, `notas`, `estado`, `procesado_por`, `procesado_en`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 12, '2026-09-20', 15.00, 180.00, 'caducado', 'Se caduco', 'procesado', 1, '2026-09-22 19:22:24', '2026-09-22 19:20:36', '2026-09-22 19:22:24'),
(3, 2, 1, 1, 19, '2026-09-20', 20.00, 380.00, 'caducado', 'Caducado', 'procesado', 1, '2026-09-22 19:24:36', '2026-09-22 19:24:29', '2026-09-22 19:24:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispositivos`
--

CREATE TABLE `dispositivos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `mac_address` varchar(255) NOT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `estado` enum('Activo','Inactivo','Mantenimiento') NOT NULL DEFAULT 'Activo',
  `ultima_conexion` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `dispositivos`
--

INSERT INTO `dispositivos` (`id`, `nombre`, `mac_address`, `ubicacion`, `estado`, `ultima_conexion`, `created_at`, `updated_at`) VALUES
(1, 'ESP32 Principal', '80:F3:DA:53:71:30', 'Planta de Producción', 'Activo', '2026-09-08 21:18:24', '2026-07-09 10:57:47', '2026-09-08 21:18:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `produccion_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id`, `produccion_id`, `user_id`, `tipo`, `descripcion`, `fecha_hora`, `created_at`, `updated_at`) VALUES
(1, NULL, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-07-23 18:06:37', '2026-07-23 18:06:37', '2026-07-23 18:06:37'),
(2, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-07-23 18:06:40', '2026-07-23 18:06:40', '2026-07-23 18:06:40'),
(3, NULL, 1, 'Sensor', 'Sensor activado.', '2026-07-23 18:06:43', '2026-07-23 18:06:43', '2026-07-23 18:06:43'),
(4, NULL, 1, 'Sensor', 'Sensor activado.', '2026-07-23 18:06:44', '2026-07-23 18:06:44', '2026-07-23 18:06:44'),
(5, NULL, 1, 'Sensor', 'Sensor desactivado.', '2026-07-23 18:06:46', '2026-07-23 18:06:46', '2026-07-23 18:06:46'),
(6, 1, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-23 18:17:00', '2026-07-23 18:17:00', '2026-07-23 18:17:00'),
(7, 1, 1, 'Sensor', 'Sensor activado.', '2026-07-23 18:17:22', '2026-07-23 18:17:22', '2026-07-23 18:17:22'),
(8, 1, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-07-23 18:18:33', '2026-07-23 18:18:33', '2026-07-23 18:18:33'),
(9, 1, 1, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-07-23 18:18:36', '2026-07-23 18:18:36', '2026-07-23 18:18:36'),
(10, 1, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-07-23 18:19:06', '2026-07-23 18:19:06', '2026-07-23 18:19:06'),
(11, 1, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-07-23 18:19:07', '2026-07-23 18:19:07', '2026-07-23 18:19:07'),
(12, 1, 1, 'Motor', 'Motor encendido manualmente.', '2026-07-23 18:19:09', '2026-07-23 18:19:09', '2026-07-23 18:19:09'),
(13, 1, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-07-23 18:19:12', '2026-07-23 18:19:12', '2026-07-23 18:19:12'),
(14, 1, 1, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-07-23 18:19:13', '2026-07-23 18:19:13', '2026-07-23 18:19:13'),
(15, 1, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-07-23 18:19:15', '2026-07-23 18:19:15', '2026-07-23 18:19:15'),
(16, 1, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-07-23 18:19:38', '2026-07-23 18:19:38', '2026-07-23 18:19:38'),
(17, 1, 1, 'Motor', 'Motor apagado manualmente.', '2026-07-23 18:19:41', '2026-07-23 18:19:41', '2026-07-23 18:19:41'),
(18, 1, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-07-23 18:19:42', '2026-07-23 18:19:42', '2026-07-23 18:19:42'),
(19, 1, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-07-23 18:19:43', '2026-07-23 18:19:43', '2026-07-23 18:19:43'),
(20, 1, 1, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-07-23 18:19:45', '2026-07-23 18:19:45', '2026-07-23 18:19:45'),
(21, 1, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-07-23 18:19:46', '2026-07-23 18:19:46', '2026-07-23 18:19:46'),
(22, 1, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-23 19:12:46', '2026-07-23 19:12:46', '2026-07-23 19:12:46'),
(23, 2, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-23 19:57:11', '2026-07-23 19:57:11', '2026-07-23 19:57:11'),
(24, 2, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-23 19:57:11', '2026-07-23 19:57:11', '2026-07-23 19:57:11'),
(25, 2, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-23 19:57:11', '2026-07-23 19:57:11', '2026-07-23 19:57:11'),
(26, 2, 1, 'Motor', 'Motor apagado automáticamente.', '2026-07-23 19:58:46', '2026-07-23 19:58:46', '2026-07-23 19:58:46'),
(27, 2, 1, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-07-23 19:58:46', '2026-07-23 19:58:46', '2026-07-23 19:58:46'),
(28, 2, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-23 19:58:46', '2026-07-23 19:58:46', '2026-07-23 19:58:46'),
(29, 3, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-23 20:02:11', '2026-07-23 20:02:11', '2026-07-23 20:02:11'),
(30, 3, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-23 20:02:11', '2026-07-23 20:02:11', '2026-07-23 20:02:11'),
(31, 3, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-23 20:02:11', '2026-07-23 20:02:11', '2026-07-23 20:02:11'),
(32, 3, 1, 'Motor', 'Motor apagado automáticamente.', '2026-07-23 20:03:12', '2026-07-23 20:03:12', '2026-07-23 20:03:12'),
(33, 3, 1, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-07-23 20:03:12', '2026-07-23 20:03:12', '2026-07-23 20:03:12'),
(34, 3, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-23 20:03:12', '2026-07-23 20:03:12', '2026-07-23 20:03:12'),
(35, 4, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-23 20:08:51', '2026-07-23 20:08:51', '2026-07-23 20:08:51'),
(36, 4, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-23 20:08:51', '2026-07-23 20:08:51', '2026-07-23 20:08:51'),
(37, 4, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-23 20:08:51', '2026-07-23 20:08:51', '2026-07-23 20:08:51'),
(38, 4, 1, 'Motor', 'Motor apagado automáticamente.', '2026-07-23 20:10:21', '2026-07-23 20:10:21', '2026-07-23 20:10:21'),
(39, 4, 1, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-07-23 20:10:21', '2026-07-23 20:10:21', '2026-07-23 20:10:21'),
(40, 4, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-23 20:10:21', '2026-07-23 20:10:21', '2026-07-23 20:10:21'),
(41, 5, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-23 20:16:57', '2026-07-23 20:16:57', '2026-07-23 20:16:57'),
(42, 5, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-23 20:16:57', '2026-07-23 20:16:57', '2026-07-23 20:16:57'),
(43, 5, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-23 20:16:57', '2026-07-23 20:16:57', '2026-07-23 20:16:57'),
(44, 5, 1, 'Motor', 'Motor apagado automáticamente.', '2026-07-23 20:28:55', '2026-07-23 20:28:55', '2026-07-23 20:28:55'),
(45, 5, 1, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-07-23 20:28:55', '2026-07-23 20:28:55', '2026-07-23 20:28:55'),
(46, 5, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-23 20:28:55', '2026-07-23 20:28:55', '2026-07-23 20:28:55'),
(47, 6, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-23 20:36:17', '2026-07-23 20:36:17', '2026-07-23 20:36:17'),
(48, 6, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-23 20:36:17', '2026-07-23 20:36:17', '2026-07-23 20:36:17'),
(49, 6, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-23 20:36:17', '2026-07-23 20:36:17', '2026-07-23 20:36:17'),
(50, 6, 1, 'Producción', 'La producción fue finalizada correctamente.', '2026-07-23 20:36:24', '2026-07-23 20:36:24', '2026-07-23 20:36:24'),
(51, 6, 1, 'Motor', 'Motor apagado automáticamente al finalizar producción.', '2026-07-23 20:36:24', '2026-07-23 20:36:24', '2026-07-23 20:36:24'),
(52, 6, 1, 'Ventilador', 'Ventilador apagado automáticamente al finalizar producción.', '2026-07-23 20:36:24', '2026-07-23 20:36:24', '2026-07-23 20:36:24'),
(53, 7, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-23 20:37:24', '2026-07-23 20:37:24', '2026-07-23 20:37:24'),
(54, 7, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-23 20:37:24', '2026-07-23 20:37:24', '2026-07-23 20:37:24'),
(55, 7, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-23 20:37:24', '2026-07-23 20:37:24', '2026-07-23 20:37:24'),
(56, 7, 1, 'Motor', 'Motor apagado automáticamente.', '2026-07-23 21:01:34', '2026-07-23 21:01:34', '2026-07-23 21:01:34'),
(57, 7, 1, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-07-23 21:01:34', '2026-07-23 21:01:34', '2026-07-23 21:01:34'),
(58, 7, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-23 21:01:34', '2026-07-23 21:01:34', '2026-07-23 21:01:34'),
(59, 8, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-23 21:08:14', '2026-07-23 21:08:14', '2026-07-23 21:08:14'),
(60, 8, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-23 21:08:14', '2026-07-23 21:08:14', '2026-07-23 21:08:14'),
(61, 8, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-23 21:08:14', '2026-07-23 21:08:14', '2026-07-23 21:08:14'),
(62, 8, 1, 'Motor', 'Motor apagado automáticamente.', '2026-07-23 21:33:31', '2026-07-23 21:33:31', '2026-07-23 21:33:31'),
(63, 8, 1, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-07-23 21:33:31', '2026-07-23 21:33:31', '2026-07-23 21:33:31'),
(64, 8, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-23 21:33:31', '2026-07-23 21:33:31', '2026-07-23 21:33:31'),
(65, 9, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-23 21:34:24', '2026-07-23 21:34:24', '2026-07-23 21:34:24'),
(66, 9, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-23 21:34:24', '2026-07-23 21:34:24', '2026-07-23 21:34:24'),
(67, 9, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-23 21:34:24', '2026-07-23 21:34:24', '2026-07-23 21:34:24'),
(68, 9, 1, 'Motor', 'Motor apagado automáticamente.', '2026-07-23 21:35:24', '2026-07-23 21:35:24', '2026-07-23 21:35:24'),
(69, 9, 1, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-07-23 21:35:24', '2026-07-23 21:35:24', '2026-07-23 21:35:24'),
(70, 9, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-23 21:35:24', '2026-07-23 21:35:24', '2026-07-23 21:35:24'),
(71, 10, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-23 21:42:14', '2026-07-23 21:42:14', '2026-07-23 21:42:14'),
(72, 10, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-23 21:42:14', '2026-07-23 21:42:14', '2026-07-23 21:42:14'),
(73, 10, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-23 21:42:14', '2026-07-23 21:42:14', '2026-07-23 21:42:14'),
(74, 10, 1, 'Motor', 'Motor apagado automáticamente.', '2026-07-23 21:42:33', '2026-07-23 21:42:33', '2026-07-23 21:42:33'),
(75, 10, 1, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-07-23 21:42:33', '2026-07-23 21:42:33', '2026-07-23 21:42:33'),
(76, 10, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-23 21:42:33', '2026-07-23 21:42:33', '2026-07-23 21:42:33'),
(77, 11, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-23 22:08:38', '2026-07-23 22:08:38', '2026-07-23 22:08:38'),
(78, 11, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-23 22:08:38', '2026-07-23 22:08:38', '2026-07-23 22:08:38'),
(79, 11, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-23 22:08:38', '2026-07-23 22:08:38', '2026-07-23 22:08:38'),
(80, 11, 1, 'Motor', 'Motor apagado automáticamente.', '2026-07-23 22:09:07', '2026-07-23 22:09:07', '2026-07-23 22:09:07'),
(81, 11, 1, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-07-23 22:09:07', '2026-07-23 22:09:07', '2026-07-23 22:09:07'),
(82, 11, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-23 22:09:07', '2026-07-23 22:09:07', '2026-07-23 22:09:07'),
(83, 12, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-23 22:15:38', '2026-07-23 22:15:38', '2026-07-23 22:15:38'),
(84, 12, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-23 22:15:38', '2026-07-23 22:15:38', '2026-07-23 22:15:38'),
(85, 12, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-23 22:15:38', '2026-07-23 22:15:38', '2026-07-23 22:15:38'),
(86, 12, 1, 'Motor', 'Motor apagado automáticamente.', '2026-07-23 22:16:05', '2026-07-23 22:16:05', '2026-07-23 22:16:05'),
(87, 12, 1, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-07-23 22:16:05', '2026-07-23 22:16:05', '2026-07-23 22:16:05'),
(88, 12, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-23 22:16:05', '2026-07-23 22:16:05', '2026-07-23 22:16:05'),
(89, NULL, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-07-24 00:23:10', '2026-07-24 00:23:10', '2026-07-24 00:23:10'),
(90, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-07-24 00:23:10', '2026-07-24 00:23:10', '2026-07-24 00:23:10'),
(91, NULL, 1, 'Sensor', 'Sensor desactivado.', '2026-07-24 00:23:12', '2026-07-24 00:23:12', '2026-07-24 00:23:12'),
(92, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-07-24 00:23:14', '2026-07-24 00:23:14', '2026-07-24 00:23:14'),
(93, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-07-24 00:23:24', '2026-07-24 00:23:24', '2026-07-24 00:23:24'),
(94, NULL, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-07-24 00:23:31', '2026-07-24 00:23:31', '2026-07-24 00:23:31'),
(95, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-07-24 00:23:33', '2026-07-24 00:23:33', '2026-07-24 00:23:33'),
(96, NULL, 1, 'Sensor', 'Sensor activado.', '2026-07-24 00:23:35', '2026-07-24 00:23:35', '2026-07-24 00:23:35'),
(97, 13, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-24 00:23:56', '2026-07-24 00:23:56', '2026-07-24 00:23:56'),
(98, 13, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-24 00:23:56', '2026-07-24 00:23:56', '2026-07-24 00:23:56'),
(99, 13, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-24 00:23:56', '2026-07-24 00:23:56', '2026-07-24 00:23:56'),
(100, 13, 1, 'Motor', 'Motor apagado automáticamente.', '2026-07-24 00:23:59', '2026-07-24 00:23:59', '2026-07-24 00:23:59'),
(101, 13, 1, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-07-24 00:23:59', '2026-07-24 00:23:59', '2026-07-24 00:23:59'),
(102, 13, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-24 00:23:59', '2026-07-24 00:23:59', '2026-07-24 00:23:59'),
(103, 14, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-24 00:25:12', '2026-07-24 00:25:12', '2026-07-24 00:25:12'),
(104, 14, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-24 00:25:12', '2026-07-24 00:25:12', '2026-07-24 00:25:12'),
(105, 14, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-24 00:25:12', '2026-07-24 00:25:12', '2026-07-24 00:25:12'),
(106, 14, 1, 'Motor', 'Motor apagado automáticamente.', '2026-07-24 00:25:14', '2026-07-24 00:25:14', '2026-07-24 00:25:14'),
(107, 14, 1, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-07-24 00:25:14', '2026-07-24 00:25:14', '2026-07-24 00:25:14'),
(108, 14, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-24 00:25:15', '2026-07-24 00:25:15', '2026-07-24 00:25:15'),
(109, NULL, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-07-24 00:26:21', '2026-07-24 00:26:21', '2026-07-24 00:26:21'),
(110, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-07-24 00:26:22', '2026-07-24 00:26:22', '2026-07-24 00:26:22'),
(111, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-07-24 00:26:24', '2026-07-24 00:26:24', '2026-07-24 00:26:24'),
(112, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-07-24 00:26:25', '2026-07-24 00:26:25', '2026-07-24 00:26:25'),
(113, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-07-24 00:29:16', '2026-07-24 00:29:16', '2026-07-24 00:29:16'),
(114, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-07-24 00:29:18', '2026-07-24 00:29:18', '2026-07-24 00:29:18'),
(115, NULL, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-07-24 00:29:23', '2026-07-24 00:29:23', '2026-07-24 00:29:23'),
(116, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-07-24 00:29:24', '2026-07-24 00:29:24', '2026-07-24 00:29:24'),
(117, 15, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-24 00:29:41', '2026-07-24 00:29:41', '2026-07-24 00:29:41'),
(118, 15, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-24 00:29:41', '2026-07-24 00:29:41', '2026-07-24 00:29:41'),
(119, 15, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-24 00:29:41', '2026-07-24 00:29:41', '2026-07-24 00:29:41'),
(120, 15, 1, 'Motor', 'Motor apagado automáticamente.', '2026-07-24 00:29:43', '2026-07-24 00:29:43', '2026-07-24 00:29:43'),
(121, 15, 1, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-07-24 00:29:43', '2026-07-24 00:29:43', '2026-07-24 00:29:43'),
(122, 15, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-24 00:29:43', '2026-07-24 00:29:43', '2026-07-24 00:29:43'),
(123, 16, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-24 00:30:14', '2026-07-24 00:30:14', '2026-07-24 00:30:14'),
(124, 16, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-24 00:30:14', '2026-07-24 00:30:14', '2026-07-24 00:30:14'),
(125, 16, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-24 00:30:14', '2026-07-24 00:30:14', '2026-07-24 00:30:14'),
(126, 16, 1, 'Motor', 'Motor apagado automáticamente.', '2026-07-24 00:30:15', '2026-07-24 00:30:15', '2026-07-24 00:30:15'),
(127, 16, 1, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-07-24 00:30:15', '2026-07-24 00:30:15', '2026-07-24 00:30:15'),
(128, 16, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-24 00:30:15', '2026-07-24 00:30:15', '2026-07-24 00:30:15'),
(129, 17, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-24 00:31:00', '2026-07-24 00:31:00', '2026-07-24 00:31:00'),
(130, 17, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-24 00:31:00', '2026-07-24 00:31:00', '2026-07-24 00:31:00'),
(131, 17, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-24 00:31:00', '2026-07-24 00:31:00', '2026-07-24 00:31:00'),
(132, 17, 1, 'Motor', 'Motor apagado automáticamente.', '2026-07-24 00:31:04', '2026-07-24 00:31:04', '2026-07-24 00:31:04'),
(133, 17, 1, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-07-24 00:31:04', '2026-07-24 00:31:04', '2026-07-24 00:31:04'),
(134, 17, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-24 00:31:04', '2026-07-24 00:31:04', '2026-07-24 00:31:04'),
(135, NULL, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-07-24 00:31:48', '2026-07-24 00:31:48', '2026-07-24 00:31:48'),
(136, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-07-24 00:31:50', '2026-07-24 00:31:50', '2026-07-24 00:31:50'),
(137, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-07-24 00:31:52', '2026-07-24 00:31:52', '2026-07-24 00:31:52'),
(138, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-07-24 00:31:54', '2026-07-24 00:31:54', '2026-07-24 00:31:54'),
(139, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-07-24 00:34:19', '2026-07-24 00:34:19', '2026-07-24 00:34:19'),
(140, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-07-24 00:34:20', '2026-07-24 00:34:20', '2026-07-24 00:34:20'),
(141, NULL, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-07-24 00:34:22', '2026-07-24 00:34:22', '2026-07-24 00:34:22'),
(142, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-07-24 00:34:24', '2026-07-24 00:34:24', '2026-07-24 00:34:24'),
(143, 18, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-24 00:34:38', '2026-07-24 00:34:38', '2026-07-24 00:34:38'),
(144, 18, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-24 00:34:38', '2026-07-24 00:34:38', '2026-07-24 00:34:38'),
(145, 18, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-24 00:34:38', '2026-07-24 00:34:38', '2026-07-24 00:34:38'),
(146, 18, 1, 'Motor', 'Motor apagado automáticamente.', '2026-07-24 00:34:41', '2026-07-24 00:34:41', '2026-07-24 00:34:41'),
(147, 18, 1, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-07-24 00:34:41', '2026-07-24 00:34:41', '2026-07-24 00:34:41'),
(148, 18, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-24 00:34:41', '2026-07-24 00:34:41', '2026-07-24 00:34:41'),
(149, NULL, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-07-24 00:35:18', '2026-07-24 00:35:18', '2026-07-24 00:35:18'),
(150, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-07-24 00:35:19', '2026-07-24 00:35:19', '2026-07-24 00:35:19'),
(151, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-07-24 00:35:21', '2026-07-24 00:35:21', '2026-07-24 00:35:21'),
(152, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-07-24 00:35:23', '2026-07-24 00:35:23', '2026-07-24 00:35:23'),
(153, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-07-24 00:35:55', '2026-07-24 00:35:55', '2026-07-24 00:35:55'),
(154, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-07-24 00:36:00', '2026-07-24 00:36:00', '2026-07-24 00:36:00'),
(155, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-07-24 00:36:05', '2026-07-24 00:36:05', '2026-07-24 00:36:05'),
(156, NULL, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-07-24 00:36:08', '2026-07-24 00:36:08', '2026-07-24 00:36:08'),
(157, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-07-24 00:36:09', '2026-07-24 00:36:09', '2026-07-24 00:36:09'),
(158, 19, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-24 00:36:22', '2026-07-24 00:36:22', '2026-07-24 00:36:22'),
(159, 19, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-24 00:36:22', '2026-07-24 00:36:22', '2026-07-24 00:36:22'),
(160, 19, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-24 00:36:22', '2026-07-24 00:36:22', '2026-07-24 00:36:22'),
(161, 19, 1, 'Motor', 'Motor apagado automáticamente.', '2026-07-24 00:36:24', '2026-07-24 00:36:24', '2026-07-24 00:36:24'),
(162, 19, 1, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-07-24 00:36:24', '2026-07-24 00:36:24', '2026-07-24 00:36:24'),
(163, 19, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-24 00:36:24', '2026-07-24 00:36:24', '2026-07-24 00:36:24'),
(164, NULL, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-07-24 00:36:35', '2026-07-24 00:36:35', '2026-07-24 00:36:35'),
(165, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-07-24 00:36:36', '2026-07-24 00:36:36', '2026-07-24 00:36:36'),
(166, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-07-24 00:36:39', '2026-07-24 00:36:39', '2026-07-24 00:36:39'),
(167, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-07-24 00:36:42', '2026-07-24 00:36:42', '2026-07-24 00:36:42'),
(168, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-07-24 00:37:21', '2026-07-24 00:37:21', '2026-07-24 00:37:21'),
(169, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-07-24 00:37:22', '2026-07-24 00:37:22', '2026-07-24 00:37:22'),
(170, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-07-24 00:37:30', '2026-07-24 00:37:30', '2026-07-24 00:37:30'),
(171, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-07-24 00:37:35', '2026-07-24 00:37:35', '2026-07-24 00:37:35'),
(172, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-07-24 00:37:38', '2026-07-24 00:37:38', '2026-07-24 00:37:38'),
(173, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-07-24 00:37:44', '2026-07-24 00:37:44', '2026-07-24 00:37:44'),
(174, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-07-24 00:37:49', '2026-07-24 00:37:49', '2026-07-24 00:37:49'),
(175, 20, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-24 00:38:00', '2026-07-24 00:38:00', '2026-07-24 00:38:00'),
(176, 20, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-24 00:38:05', '2026-07-24 00:38:05', '2026-07-24 00:38:05'),
(177, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-07-24 00:38:16', '2026-07-24 00:38:16', '2026-07-24 00:38:16'),
(178, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-07-24 00:38:18', '2026-07-24 00:38:18', '2026-07-24 00:38:18'),
(179, 21, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-24 00:38:46', '2026-07-24 00:38:46', '2026-07-24 00:38:46'),
(180, 21, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-24 00:38:47', '2026-07-24 00:38:47', '2026-07-24 00:38:47'),
(181, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-07-24 00:39:04', '2026-07-24 00:39:04', '2026-07-24 00:39:04'),
(182, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-07-24 00:39:06', '2026-07-24 00:39:06', '2026-07-24 00:39:06'),
(183, 22, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-24 00:53:28', '2026-07-24 00:53:28', '2026-07-24 00:53:28'),
(184, 22, 1, 'Producción', 'Producción finalizada automáticamente.', '2026-07-24 00:53:29', '2026-07-24 00:53:29', '2026-07-24 00:53:29'),
(185, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-07-24 01:13:08', '2026-07-24 01:13:08', '2026-07-24 01:13:08'),
(186, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-07-24 01:13:12', '2026-07-24 01:13:12', '2026-07-24 01:13:12'),
(187, 23, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-24 01:14:15', '2026-07-24 01:14:15', '2026-07-24 01:14:15'),
(188, 23, 1, 'Motor', 'Motor apagado manualmente.', '2026-07-24 01:17:55', '2026-07-24 01:17:55', '2026-07-24 01:17:55'),
(189, 23, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-07-24 01:17:56', '2026-07-24 01:17:56', '2026-07-24 01:17:56'),
(190, 23, 1, 'Producción', 'Producción finalizada: Se alcanzó la temperatura máxima del producto (40.1875°C).', '2026-07-24 01:18:00', '2026-07-24 01:18:00', '2026-07-24 01:18:00'),
(191, NULL, 1, 'Sensor', 'Sensor desactivado.', '2026-07-24 01:27:27', '2026-07-24 01:27:27', '2026-07-24 01:27:27'),
(192, 24, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-24 01:29:52', '2026-07-24 01:29:52', '2026-07-24 01:29:52'),
(193, 24, 1, 'Sensor', 'Sensor activado.', '2026-07-24 01:30:15', '2026-07-24 01:30:15', '2026-07-24 01:30:15'),
(194, 24, 1, 'Producción', 'La producción fue finalizada correctamente.', '2026-07-24 01:31:43', '2026-07-24 01:31:43', '2026-07-24 01:31:43'),
(195, 24, 1, 'Motor', 'Motor apagado automáticamente al finalizar producción.', '2026-07-24 01:31:43', '2026-07-24 01:31:43', '2026-07-24 01:31:43'),
(196, 24, 1, 'Ventilador', 'Ventilador apagado automáticamente al finalizar producción.', '2026-07-24 01:31:43', '2026-07-24 01:31:43', '2026-07-24 01:31:43'),
(197, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-07-24 01:32:02', '2026-07-24 01:32:02', '2026-07-24 01:32:02'),
(198, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-07-24 01:32:05', '2026-07-24 01:32:05', '2026-07-24 01:32:05'),
(199, NULL, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-07-24 01:32:07', '2026-07-24 01:32:07', '2026-07-24 01:32:07'),
(200, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-07-24 01:32:08', '2026-07-24 01:32:08', '2026-07-24 01:32:08'),
(201, 25, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-24 01:32:31', '2026-07-24 01:32:31', '2026-07-24 01:32:31'),
(202, 25, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-24 01:32:31', '2026-07-24 01:32:31', '2026-07-24 01:32:31'),
(203, 25, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-24 01:32:31', '2026-07-24 01:32:31', '2026-07-24 01:32:31'),
(204, 25, 1, 'Producción', 'La producción fue finalizada correctamente.', '2026-07-24 01:36:31', '2026-07-24 01:36:31', '2026-07-24 01:36:31'),
(205, 25, 1, 'Motor', 'Motor apagado automáticamente al finalizar producción.', '2026-07-24 01:36:31', '2026-07-24 01:36:31', '2026-07-24 01:36:31'),
(206, 25, 1, 'Ventilador', 'Ventilador apagado automáticamente al finalizar producción.', '2026-07-24 01:36:31', '2026-07-24 01:36:31', '2026-07-24 01:36:31'),
(207, NULL, 1, 'Sensor', 'Sensor desactivado.', '2026-07-24 01:39:24', '2026-07-24 01:39:24', '2026-07-24 01:39:24'),
(208, NULL, 2, 'Sensor', 'Sensor activado.', '2026-07-28 01:13:17', '2026-07-28 01:13:17', '2026-07-28 01:13:17'),
(209, 26, 2, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-28 01:13:51', '2026-07-28 01:13:51', '2026-07-28 01:13:51'),
(210, 26, 2, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-28 01:13:51', '2026-07-28 01:13:51', '2026-07-28 01:13:51'),
(211, 26, 2, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-28 01:13:51', '2026-07-28 01:13:51', '2026-07-28 01:13:51'),
(212, 26, 2, 'Producción', 'La producción fue finalizada correctamente.', '2026-07-28 01:14:40', '2026-07-28 01:14:40', '2026-07-28 01:14:40'),
(213, 26, 2, 'Motor', 'Motor apagado automáticamente al finalizar producción.', '2026-07-28 01:14:40', '2026-07-28 01:14:40', '2026-07-28 01:14:40'),
(214, 26, 2, 'Ventilador', 'Ventilador apagado automáticamente al finalizar producción.', '2026-07-28 01:14:40', '2026-07-28 01:14:40', '2026-07-28 01:14:40'),
(215, NULL, 2, 'Motor', 'Motor cambiado a modo Manual.', '2026-07-28 01:16:15', '2026-07-28 01:16:15', '2026-07-28 01:16:15'),
(216, NULL, 2, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-07-28 01:16:17', '2026-07-28 01:16:17', '2026-07-28 01:16:17'),
(217, NULL, 2, 'Ventilador', 'Ventilador encendido manualmente.', '2026-07-28 01:16:20', '2026-07-28 01:16:20', '2026-07-28 01:16:20'),
(218, NULL, 2, 'Ventilador', 'Ventilador apagado manualmente.', '2026-07-28 01:16:58', '2026-07-28 01:16:58', '2026-07-28 01:16:58'),
(219, NULL, 2, 'Ventilador', 'Ventilador encendido manualmente.', '2026-07-28 01:17:32', '2026-07-28 01:17:32', '2026-07-28 01:17:32'),
(220, NULL, 2, 'Motor', 'Motor encendido manualmente.', '2026-07-28 01:17:52', '2026-07-28 01:17:52', '2026-07-28 01:17:52'),
(221, NULL, 2, 'Motor', 'Motor apagado manualmente.', '2026-07-28 01:23:38', '2026-07-28 01:23:38', '2026-07-28 01:23:38'),
(222, NULL, 2, 'Ventilador', 'Ventilador apagado manualmente.', '2026-07-28 01:23:40', '2026-07-28 01:23:40', '2026-07-28 01:23:40'),
(223, NULL, 2, 'Motor', 'Motor encendido manualmente.', '2026-07-28 01:24:14', '2026-07-28 01:24:14', '2026-07-28 01:24:14'),
(224, NULL, 2, 'Motor', 'Motor apagado manualmente.', '2026-07-28 01:24:23', '2026-07-28 01:24:23', '2026-07-28 01:24:23'),
(225, NULL, 2, 'Ventilador', 'Ventilador encendido manualmente.', '2026-07-28 01:24:33', '2026-07-28 01:24:33', '2026-07-28 01:24:33'),
(226, NULL, 2, 'Ventilador', 'Ventilador encendido manualmente.', '2026-07-28 01:24:33', '2026-07-28 01:24:33', '2026-07-28 01:24:33'),
(227, NULL, 2, 'Motor', 'Motor encendido manualmente.', '2026-07-28 01:24:42', '2026-07-28 01:24:42', '2026-07-28 01:24:42'),
(228, NULL, 2, 'Motor', 'Motor apagado manualmente.', '2026-07-28 01:24:54', '2026-07-28 01:24:54', '2026-07-28 01:24:54'),
(229, NULL, 2, 'Ventilador', 'Ventilador apagado manualmente.', '2026-07-28 01:24:58', '2026-07-28 01:24:58', '2026-07-28 01:24:58'),
(230, 27, 2, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-28 01:27:43', '2026-07-28 01:27:43', '2026-07-28 01:27:43'),
(231, 27, 2, 'Motor', 'Motor cambiado a modo Automatico.', '2026-07-28 01:27:56', '2026-07-28 01:27:56', '2026-07-28 01:27:56'),
(232, 27, 2, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-07-28 01:27:58', '2026-07-28 01:27:58', '2026-07-28 01:27:58'),
(233, 27, 2, 'Motor', 'Motor cambiado a modo Manual.', '2026-07-28 01:31:28', '2026-07-28 01:31:28', '2026-07-28 01:31:28'),
(234, 27, 2, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-07-28 01:31:30', '2026-07-28 01:31:30', '2026-07-28 01:31:30'),
(235, 27, 2, 'Motor', 'Motor encendido manualmente.', '2026-07-28 01:31:31', '2026-07-28 01:31:31', '2026-07-28 01:31:31'),
(236, 27, 2, 'Ventilador', 'Ventilador encendido manualmente.', '2026-07-28 01:31:32', '2026-07-28 01:31:32', '2026-07-28 01:31:32'),
(237, 27, 2, 'Motor', 'Motor cambiado a modo Automatico.', '2026-07-28 01:31:35', '2026-07-28 01:31:35', '2026-07-28 01:31:35'),
(238, 27, 2, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-07-28 01:31:36', '2026-07-28 01:31:36', '2026-07-28 01:31:36'),
(239, 27, 2, 'Motor', 'Motor apagado automáticamente.', '2026-07-28 01:32:10', '2026-07-28 01:32:10', '2026-07-28 01:32:10'),
(240, 27, 2, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-07-28 01:32:10', '2026-07-28 01:32:10', '2026-07-28 01:32:10'),
(241, 27, 2, 'Producción', 'Producción finalizada: Se alcanzó la temperatura máxima del producto (20°C).', '2026-07-28 01:32:10', '2026-07-28 01:32:10', '2026-07-28 01:32:10'),
(242, NULL, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-07-28 03:57:18', '2026-07-28 03:57:18', '2026-07-28 03:57:18'),
(243, NULL, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-07-28 03:57:22', '2026-07-28 03:57:22', '2026-07-28 03:57:22'),
(244, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-07-28 03:57:25', '2026-07-28 03:57:25', '2026-07-28 03:57:25'),
(245, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-07-28 03:57:29', '2026-07-28 03:57:29', '2026-07-28 03:57:29'),
(246, NULL, 1, 'Sensor', 'Sensor desactivado.', '2026-07-28 03:57:31', '2026-07-28 03:57:31', '2026-07-28 03:57:31'),
(247, NULL, 1, 'Sensor', 'Sensor activado.', '2026-07-29 21:32:19', '2026-07-29 21:32:19', '2026-07-29 21:32:19'),
(248, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-07-29 21:33:03', '2026-07-29 21:33:03', '2026-07-29 21:33:03'),
(249, NULL, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-07-29 21:33:08', '2026-07-29 21:33:08', '2026-07-29 21:33:08'),
(250, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-07-29 21:33:10', '2026-07-29 21:33:10', '2026-07-29 21:33:10'),
(251, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-07-29 21:46:58', '2026-07-29 21:46:58', '2026-07-29 21:46:58'),
(252, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-07-29 21:47:02', '2026-07-29 21:47:02', '2026-07-29 21:47:02'),
(253, NULL, 1, 'Sensor', 'Sensor desactivado.', '2026-07-29 22:03:29', '2026-07-29 22:03:29', '2026-07-29 22:03:29'),
(254, NULL, 1, 'Sensor', 'Sensor activado.', '2026-07-29 22:03:32', '2026-07-29 22:03:32', '2026-07-29 22:03:32'),
(255, 28, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-29 22:50:32', '2026-07-29 22:50:32', '2026-07-29 22:50:32'),
(256, 28, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-29 22:50:32', '2026-07-29 22:50:32', '2026-07-29 22:50:32'),
(257, 28, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-07-29 22:50:44', '2026-07-29 22:50:44', '2026-07-29 22:50:44'),
(258, 28, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-07-29 22:57:33', '2026-07-29 22:57:33', '2026-07-29 22:57:33'),
(259, 28, 1, 'Motor', 'Motor encendido manualmente.', '2026-07-29 22:57:36', '2026-07-29 22:57:36', '2026-07-29 22:57:36'),
(260, 28, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-07-29 22:57:38', '2026-07-29 22:57:38', '2026-07-29 22:57:38'),
(261, 28, 1, 'Producción', 'La producción fue finalizada correctamente.', '2026-07-29 22:59:00', '2026-07-29 22:59:00', '2026-07-29 22:59:00'),
(262, 28, 1, 'Motor', 'Motor apagado automáticamente al finalizar producción.', '2026-07-29 22:59:00', '2026-07-29 22:59:00', '2026-07-29 22:59:00'),
(263, 28, 1, 'Ventilador', 'Ventilador apagado automáticamente al finalizar producción.', '2026-07-29 22:59:00', '2026-07-29 22:59:00', '2026-07-29 22:59:00'),
(264, 29, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-29 23:09:06', '2026-07-29 23:09:06', '2026-07-29 23:09:06'),
(265, 29, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-29 23:09:06', '2026-07-29 23:09:06', '2026-07-29 23:09:06'),
(266, 29, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-29 23:09:06', '2026-07-29 23:09:06', '2026-07-29 23:09:06'),
(267, 29, 1, 'Producción', 'La producción fue finalizada correctamente.', '2026-07-29 23:10:07', '2026-07-29 23:10:07', '2026-07-29 23:10:07'),
(268, 29, 1, 'Motor', 'Motor apagado automáticamente al finalizar producción.', '2026-07-29 23:10:07', '2026-07-29 23:10:07', '2026-07-29 23:10:07'),
(269, 29, 1, 'Ventilador', 'Ventilador apagado automáticamente al finalizar producción.', '2026-07-29 23:10:07', '2026-07-29 23:10:07', '2026-07-29 23:10:07'),
(270, 30, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-07-30 03:44:47', '2026-07-30 03:44:47', '2026-07-30 03:44:47'),
(271, 30, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-07-30 03:44:47', '2026-07-30 03:44:47', '2026-07-30 03:44:47'),
(272, 30, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-07-30 03:44:47', '2026-07-30 03:44:47', '2026-07-30 03:44:47'),
(273, 30, 1, 'Sensor', 'Sensor desactivado.', '2026-07-30 03:45:33', '2026-07-30 03:45:33', '2026-07-30 03:45:33'),
(274, 30, 2, 'Producción', 'La producción fue finalizada correctamente.', '2026-07-30 03:49:52', '2026-07-30 03:49:52', '2026-07-30 03:49:52'),
(275, 30, 2, 'Motor', 'Motor apagado automáticamente al finalizar producción.', '2026-07-30 03:49:52', '2026-07-30 03:49:52', '2026-07-30 03:49:52'),
(276, 30, 2, 'Ventilador', 'Ventilador apagado automáticamente al finalizar producción.', '2026-07-30 03:49:52', '2026-07-30 03:49:52', '2026-07-30 03:49:52'),
(277, NULL, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-08-02 03:58:46', '2026-08-02 03:58:46', '2026-08-02 03:58:46'),
(278, NULL, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-08-02 03:58:49', '2026-08-02 03:58:49', '2026-08-02 03:58:49'),
(279, 31, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-08-02 04:03:14', '2026-08-02 04:03:14', '2026-08-02 04:03:14'),
(280, 31, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-08-02 04:03:14', '2026-08-02 04:03:14', '2026-08-02 04:03:14'),
(281, 31, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-08-02 04:03:14', '2026-08-02 04:03:14', '2026-08-02 04:03:14'),
(282, 31, 1, 'Sensor', 'Sensor activado.', '2026-08-02 04:03:43', '2026-08-02 04:03:43', '2026-08-02 04:03:43'),
(283, 31, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-08-02 04:03:47', '2026-08-02 04:03:47', '2026-08-02 04:03:47'),
(284, 31, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-08-02 04:03:48', '2026-08-02 04:03:48', '2026-08-02 04:03:48'),
(285, 31, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-02 04:03:49', '2026-08-02 04:03:49', '2026-08-02 04:03:49'),
(286, 31, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-02 04:03:50', '2026-08-02 04:03:50', '2026-08-02 04:03:50'),
(287, 31, 1, 'Producción', 'La producción fue finalizada correctamente.', '2026-08-02 04:04:25', '2026-08-02 04:04:25', '2026-08-02 04:04:25'),
(288, 31, 1, 'Motor', 'Motor apagado automáticamente al finalizar producción.', '2026-08-02 04:04:25', '2026-08-02 04:04:25', '2026-08-02 04:04:25'),
(289, 31, 1, 'Ventilador', 'Ventilador apagado automáticamente al finalizar producción.', '2026-08-02 04:04:25', '2026-08-02 04:04:25', '2026-08-02 04:04:25'),
(290, NULL, 2, 'Sensor', 'Sensor desactivado.', '2026-08-02 04:06:14', '2026-08-02 04:06:14', '2026-08-02 04:06:14'),
(291, NULL, 2, 'Motor', 'Motor cambiado a modo Automatico.', '2026-08-02 04:06:17', '2026-08-02 04:06:17', '2026-08-02 04:06:17'),
(292, NULL, 2, 'Motor', 'Motor cambiado a modo Manual.', '2026-08-02 04:06:19', '2026-08-02 04:06:19', '2026-08-02 04:06:19'),
(293, NULL, 2, 'Motor', 'Motor cambiado a modo Automatico.', '2026-08-02 04:06:27', '2026-08-02 04:06:27', '2026-08-02 04:06:27'),
(294, NULL, 2, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-08-02 04:06:29', '2026-08-02 04:06:29', '2026-08-02 04:06:29'),
(295, NULL, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-08-12 19:55:27', '2026-08-12 19:55:27', '2026-08-12 19:55:27'),
(296, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-08-12 19:55:29', '2026-08-12 19:55:29', '2026-08-12 19:55:29'),
(297, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-12 19:55:32', '2026-08-12 19:55:32', '2026-08-12 19:55:32'),
(298, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-12 19:55:38', '2026-08-12 19:55:38', '2026-08-12 19:55:38'),
(299, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-12 19:55:59', '2026-08-12 19:55:59', '2026-08-12 19:55:59'),
(300, NULL, 1, 'Sensor', 'Sensor activado.', '2026-08-12 19:56:20', '2026-08-12 19:56:20', '2026-08-12 19:56:20'),
(301, NULL, 1, 'Sensor', 'Sensor desactivado.', '2026-08-12 19:56:22', '2026-08-12 19:56:22', '2026-08-12 19:56:22'),
(302, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-12 19:56:23', '2026-08-12 19:56:23', '2026-08-12 19:56:23'),
(303, 32, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-08-12 19:56:43', '2026-08-12 19:56:43', '2026-08-12 19:56:43'),
(304, 32, 1, 'Sensor', 'Sensor activado.', '2026-08-12 19:56:55', '2026-08-12 19:56:55', '2026-08-12 19:56:55'),
(305, 32, 1, 'Producción', 'Producción finalizada: Se alcanzó la temperatura máxima del producto (18.9375°C).', '2026-08-12 19:57:02', '2026-08-12 19:57:02', '2026-08-12 19:57:02'),
(306, NULL, 1, 'Sensor', 'Sensor desactivado.', '2026-08-13 18:36:37', '2026-08-13 18:36:37', '2026-08-13 18:36:37'),
(307, 33, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-08-13 18:39:00', '2026-08-13 18:39:00', '2026-08-13 18:39:00'),
(308, 33, 1, 'Sensor', 'Sensor activado.', '2026-08-13 18:39:14', '2026-08-13 18:39:14', '2026-08-13 18:39:14'),
(309, 33, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-08-13 18:39:56', '2026-08-13 18:39:56', '2026-08-13 18:39:56'),
(310, 33, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-08-13 18:39:58', '2026-08-13 18:39:58', '2026-08-13 18:39:58'),
(311, 33, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-13 18:39:59', '2026-08-13 18:39:59', '2026-08-13 18:39:59'),
(312, 33, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-13 18:40:01', '2026-08-13 18:40:01', '2026-08-13 18:40:01'),
(313, 33, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-08-13 18:40:04', '2026-08-13 18:40:04', '2026-08-13 18:40:04'),
(314, 33, 1, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-08-13 18:40:06', '2026-08-13 18:40:06', '2026-08-13 18:40:06'),
(315, 33, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-08-13 18:41:14', '2026-08-13 18:41:14', '2026-08-13 18:41:14'),
(316, 33, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-13 18:41:17', '2026-08-13 18:41:17', '2026-08-13 18:41:17'),
(317, 33, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-13 18:45:53', '2026-08-13 18:45:53', '2026-08-13 18:45:53'),
(318, 33, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-13 18:46:30', '2026-08-13 18:46:30', '2026-08-13 18:46:30'),
(319, 33, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-13 18:46:32', '2026-08-13 18:46:32', '2026-08-13 18:46:32'),
(320, 33, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-08-13 18:46:41', '2026-08-13 18:46:41', '2026-08-13 18:46:41'),
(321, 33, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-13 18:46:43', '2026-08-13 18:46:43', '2026-08-13 18:46:43'),
(322, 33, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-13 18:46:46', '2026-08-13 18:46:46', '2026-08-13 18:46:46'),
(323, 33, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-08-13 19:10:45', '2026-08-13 19:10:45', '2026-08-13 19:10:45'),
(324, 33, 1, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-08-13 19:10:49', '2026-08-13 19:10:49', '2026-08-13 19:10:49'),
(325, 33, 1, 'Motor', 'Motor apagado automáticamente.', '2026-08-13 19:20:13', '2026-08-13 19:20:13', '2026-08-13 19:20:13'),
(326, 33, 1, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-08-13 19:20:13', '2026-08-13 19:20:13', '2026-08-13 19:20:13'),
(327, 33, 1, 'Producción', 'Producción finalizada: Se alcanzó la temperatura máxima del producto (19.9375°C).', '2026-08-13 19:20:13', '2026-08-13 19:20:13', '2026-08-13 19:20:13'),
(328, NULL, 1, 'Sensor', 'Sensor desactivado.', '2026-08-13 19:22:31', '2026-08-13 19:22:31', '2026-08-13 19:22:31'),
(329, NULL, 1, 'Sensor', 'Sensor activado.', '2026-08-13 19:22:36', '2026-08-13 19:22:36', '2026-08-13 19:22:36'),
(330, NULL, 1, 'Sensor', 'Sensor desactivado.', '2026-08-13 19:22:39', '2026-08-13 19:22:39', '2026-08-13 19:22:39'),
(331, NULL, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-08-16 02:19:49', '2026-08-16 02:19:49', '2026-08-16 02:19:49'),
(332, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-08-16 02:19:52', '2026-08-16 02:19:52', '2026-08-16 02:19:52'),
(333, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-16 02:19:56', '2026-08-16 02:19:56', '2026-08-16 02:19:56'),
(334, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-16 02:20:16', '2026-08-16 02:20:16', '2026-08-16 02:20:16'),
(335, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-16 02:20:17', '2026-08-16 02:20:17', '2026-08-16 02:20:17'),
(336, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-16 02:25:29', '2026-08-16 02:25:29', '2026-08-16 02:25:29'),
(337, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-16 02:25:30', '2026-08-16 02:25:30', '2026-08-16 02:25:30'),
(338, 34, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-08-16 02:28:19', '2026-08-16 02:28:19', '2026-08-16 02:28:19'),
(339, 34, 1, 'Sensor', 'Sensor activado.', '2026-08-16 02:28:28', '2026-08-16 02:28:28', '2026-08-16 02:28:28'),
(340, 34, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-16 02:28:31', '2026-08-16 02:28:31', '2026-08-16 02:28:31'),
(341, 34, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-16 02:28:34', '2026-08-16 02:28:34', '2026-08-16 02:28:34'),
(342, 34, 1, 'Producción', 'La producción fue finalizada correctamente.', '2026-08-16 02:36:32', '2026-08-16 02:36:32', '2026-08-16 02:36:32'),
(343, 34, 1, 'Motor', 'Motor apagado automáticamente al finalizar producción.', '2026-08-16 02:36:32', '2026-08-16 02:36:32', '2026-08-16 02:36:32'),
(344, 34, 1, 'Ventilador', 'Ventilador apagado automáticamente al finalizar producción.', '2026-08-16 02:36:32', '2026-08-16 02:36:32', '2026-08-16 02:36:32'),
(345, NULL, 2, 'Sensor', 'Sensor desactivado.', '2026-08-17 18:14:04', '2026-08-17 18:14:04', '2026-08-17 18:14:04'),
(346, NULL, 2, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-17 18:15:37', '2026-08-17 18:15:37', '2026-08-17 18:15:37'),
(347, NULL, 2, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-17 18:15:57', '2026-08-17 18:15:57', '2026-08-17 18:15:57'),
(348, NULL, 2, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-17 18:16:01', '2026-08-17 18:16:01', '2026-08-17 18:16:01'),
(349, NULL, 2, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-17 18:16:06', '2026-08-17 18:16:06', '2026-08-17 18:16:06'),
(350, NULL, 2, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-17 18:17:10', '2026-08-17 18:17:10', '2026-08-17 18:17:10'),
(351, NULL, 2, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-17 18:18:02', '2026-08-17 18:18:02', '2026-08-17 18:18:02'),
(352, NULL, 2, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-17 18:18:11', '2026-08-17 18:18:11', '2026-08-17 18:18:11'),
(353, NULL, 2, 'Motor', 'Motor encendido manualmente.', '2026-08-17 18:18:41', '2026-08-17 18:18:41', '2026-08-17 18:18:41'),
(354, NULL, 2, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-17 18:18:58', '2026-08-17 18:18:58', '2026-08-17 18:18:58'),
(355, NULL, 2, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-17 18:19:03', '2026-08-17 18:19:03', '2026-08-17 18:19:03'),
(356, NULL, 2, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-17 18:21:42', '2026-08-17 18:21:42', '2026-08-17 18:21:42'),
(357, NULL, 2, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-17 18:24:53', '2026-08-17 18:24:53', '2026-08-17 18:24:53'),
(358, NULL, 2, 'Sensor', 'Sensor activado.', '2026-08-17 18:25:32', '2026-08-17 18:25:32', '2026-08-17 18:25:32'),
(359, NULL, 2, 'Sensor', 'Sensor activado.', '2026-08-17 18:25:34', '2026-08-17 18:25:34', '2026-08-17 18:25:34'),
(360, NULL, 2, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-17 18:31:21', '2026-08-17 18:31:21', '2026-08-17 18:31:21'),
(361, NULL, 2, 'Sensor', 'Sensor desactivado.', '2026-08-17 18:31:25', '2026-08-17 18:31:25', '2026-08-17 18:31:25'),
(362, NULL, 2, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-17 18:32:26', '2026-08-17 18:32:26', '2026-08-17 18:32:26'),
(363, NULL, 2, 'Motor', 'Motor apagado manualmente.', '2026-08-17 18:39:08', '2026-08-17 18:39:08', '2026-08-17 18:39:08'),
(364, NULL, 2, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-17 18:39:09', '2026-08-17 18:39:09', '2026-08-17 18:39:09'),
(365, NULL, 2, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-17 18:45:38', '2026-08-17 18:45:38', '2026-08-17 18:45:38'),
(366, NULL, 2, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-17 18:45:54', '2026-08-17 18:45:54', '2026-08-17 18:45:54'),
(367, 35, 2, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-08-17 18:47:37', '2026-08-17 18:47:37', '2026-08-17 18:47:37'),
(368, 35, 2, 'Sensor', 'Sensor activado.', '2026-08-17 18:47:53', '2026-08-17 18:47:53', '2026-08-17 18:47:53'),
(369, 35, 2, 'Motor', 'Motor encendido manualmente.', '2026-08-17 18:48:28', '2026-08-17 18:48:28', '2026-08-17 18:48:28'),
(370, 35, 2, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-17 18:48:30', '2026-08-17 18:48:30', '2026-08-17 18:48:30'),
(371, 35, 2, 'Motor', 'Motor cambiado a modo Automatico.', '2026-08-17 18:48:33', '2026-08-17 18:48:33', '2026-08-17 18:48:33');
INSERT INTO `eventos` (`id`, `produccion_id`, `user_id`, `tipo`, `descripcion`, `fecha_hora`, `created_at`, `updated_at`) VALUES
(372, 35, 2, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-08-17 18:48:35', '2026-08-17 18:48:35', '2026-08-17 18:48:35'),
(373, 35, 2, 'Motor', 'Motor apagado automáticamente.', '2026-08-17 19:25:50', '2026-08-17 19:25:50', '2026-08-17 19:25:50'),
(374, 35, 2, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-08-17 19:25:50', '2026-08-17 19:25:50', '2026-08-17 19:25:50'),
(375, 35, 2, 'Producción', 'Producción finalizada: Se alcanzó la temperatura máxima del producto (27.9375°C).', '2026-08-17 19:25:50', '2026-08-17 19:25:50', '2026-08-17 19:25:50'),
(376, NULL, 2, 'Sensor', 'Sensor desactivado.', '2026-08-17 19:26:48', '2026-08-17 19:26:48', '2026-08-17 19:26:48'),
(377, NULL, 1, 'Sensor', 'Sensor activado.', '2026-08-19 00:52:30', '2026-08-19 00:52:30', '2026-08-19 00:52:30'),
(378, NULL, 1, 'Sensor', 'Sensor desactivado.', '2026-08-19 01:04:19', '2026-08-19 01:04:19', '2026-08-19 01:04:19'),
(379, NULL, 1, 'Sensor', 'Sensor activado.', '2026-08-19 01:18:25', '2026-08-19 01:18:25', '2026-08-19 01:18:25'),
(380, 36, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-08-19 01:22:09', '2026-08-19 01:22:09', '2026-08-19 01:22:09'),
(381, 36, 1, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-08-19 01:22:09', '2026-08-19 01:22:09', '2026-08-19 01:22:09'),
(382, 36, 1, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-08-19 01:22:09', '2026-08-19 01:22:09', '2026-08-19 01:22:09'),
(383, 36, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-08-19 01:22:20', '2026-08-19 01:22:20', '2026-08-19 01:22:20'),
(384, 36, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-19 01:22:24', '2026-08-19 01:22:24', '2026-08-19 01:22:24'),
(385, 36, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-08-19 01:22:25', '2026-08-19 01:22:25', '2026-08-19 01:22:25'),
(386, 36, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-19 01:22:28', '2026-08-19 01:22:28', '2026-08-19 01:22:28'),
(387, 36, 1, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-08-19 01:22:32', '2026-08-19 01:22:32', '2026-08-19 01:22:32'),
(388, 36, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-08-19 01:22:34', '2026-08-19 01:22:34', '2026-08-19 01:22:34'),
(389, 36, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-19 01:26:14', '2026-08-19 01:26:14', '2026-08-19 01:26:14'),
(390, 36, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-19 01:26:15', '2026-08-19 01:26:15', '2026-08-19 01:26:15'),
(391, 36, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-08-19 01:26:21', '2026-08-19 01:26:21', '2026-08-19 01:26:21'),
(392, 36, 1, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-08-19 01:26:22', '2026-08-19 01:26:22', '2026-08-19 01:26:22'),
(393, 36, 1, 'Motor', 'Motor apagado automáticamente.', '2026-08-19 02:00:35', '2026-08-19 02:00:35', '2026-08-19 02:00:35'),
(394, 36, 1, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-08-19 02:00:35', '2026-08-19 02:00:35', '2026-08-19 02:00:35'),
(395, 36, 1, 'Producción', 'Producción finalizada: Se alcanzó la temperatura máxima del producto (28°C).', '2026-08-19 02:00:35', '2026-08-19 02:00:35', '2026-08-19 02:00:35'),
(396, NULL, 2, 'Motor', 'Motor cambiado a modo Manual.', '2026-08-19 02:06:15', '2026-08-19 02:06:15', '2026-08-19 02:06:15'),
(397, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-19 02:07:02', '2026-08-19 02:07:02', '2026-08-19 02:07:02'),
(398, NULL, 2, 'Motor', 'Motor encendido manualmente.', '2026-08-19 02:07:08', '2026-08-19 02:07:08', '2026-08-19 02:07:08'),
(399, NULL, 2, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-08-19 02:07:19', '2026-08-19 02:07:19', '2026-08-19 02:07:19'),
(400, NULL, 2, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-19 02:07:25', '2026-08-19 02:07:25', '2026-08-19 02:07:25'),
(401, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-19 02:07:39', '2026-08-19 02:07:39', '2026-08-19 02:07:39'),
(402, NULL, 2, 'Motor', 'Motor apagado manualmente.', '2026-08-19 02:08:03', '2026-08-19 02:08:03', '2026-08-19 02:08:03'),
(403, NULL, 2, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-19 02:08:19', '2026-08-19 02:08:19', '2026-08-19 02:08:19'),
(404, NULL, 2, 'Sensor', 'Sensor desactivado.', '2026-08-19 02:09:46', '2026-08-19 02:09:46', '2026-08-19 02:09:46'),
(405, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-20 12:13:43', '2026-08-20 12:13:43', '2026-08-20 12:13:43'),
(406, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-20 12:13:53', '2026-08-20 12:13:53', '2026-08-20 12:13:53'),
(407, NULL, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-08-20 12:23:57', '2026-08-20 12:23:57', '2026-08-20 12:23:57'),
(408, NULL, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-08-20 12:24:00', '2026-08-20 12:24:00', '2026-08-20 12:24:00'),
(409, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-20 12:24:25', '2026-08-20 12:24:25', '2026-08-20 12:24:25'),
(410, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-20 12:24:27', '2026-08-20 12:24:27', '2026-08-20 12:24:27'),
(411, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-20 12:24:33', '2026-08-20 12:24:33', '2026-08-20 12:24:33'),
(412, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-20 13:27:11', '2026-08-20 13:27:11', '2026-08-20 13:27:11'),
(413, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-20 13:27:14', '2026-08-20 13:27:14', '2026-08-20 13:27:14'),
(414, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-20 13:27:18', '2026-08-20 13:27:18', '2026-08-20 13:27:18'),
(415, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-20 14:07:26', '2026-08-20 14:07:26', '2026-08-20 14:07:26'),
(416, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-20 14:07:33', '2026-08-20 14:07:33', '2026-08-20 14:07:33'),
(417, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-20 14:07:41', '2026-08-20 14:07:41', '2026-08-20 14:07:41'),
(418, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-20 14:07:43', '2026-08-20 14:07:43', '2026-08-20 14:07:43'),
(419, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-20 14:08:57', '2026-08-20 14:08:57', '2026-08-20 14:08:57'),
(420, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-20 14:09:05', '2026-08-20 14:09:05', '2026-08-20 14:09:05'),
(421, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-20 14:09:10', '2026-08-20 14:09:10', '2026-08-20 14:09:10'),
(422, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-20 14:09:23', '2026-08-20 14:09:23', '2026-08-20 14:09:23'),
(423, NULL, 1, 'Sensor', 'Sensor activado.', '2026-08-20 14:09:36', '2026-08-20 14:09:36', '2026-08-20 14:09:36'),
(424, 37, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-08-20 14:12:48', '2026-08-20 14:12:48', '2026-08-20 14:12:48'),
(425, 37, 1, 'Producción', 'Producción finalizada: Se alcanzó la temperatura máxima del producto (23.875°C).', '2026-08-20 14:12:53', '2026-08-20 14:12:53', '2026-08-20 14:12:53'),
(426, 38, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-08-20 14:19:37', '2026-08-20 14:19:37', '2026-08-20 14:19:37'),
(427, 38, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-20 14:23:06', '2026-08-20 14:23:06', '2026-08-20 14:23:06'),
(428, 38, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-20 14:23:31', '2026-08-20 14:23:31', '2026-08-20 14:23:31'),
(429, 38, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-20 14:23:47', '2026-08-20 14:23:47', '2026-08-20 14:23:47'),
(430, 38, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-20 14:45:44', '2026-08-20 14:45:44', '2026-08-20 14:45:44'),
(431, 38, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-08-20 14:45:47', '2026-08-20 14:45:47', '2026-08-20 14:45:47'),
(432, 38, 1, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-08-20 14:45:49', '2026-08-20 14:45:49', '2026-08-20 14:45:49'),
(433, 38, 1, 'Motor', 'Motor apagado automáticamente.', '2026-08-20 15:18:23', '2026-08-20 15:18:23', '2026-08-20 15:18:23'),
(434, 38, 1, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-08-20 15:18:23', '2026-08-20 15:18:23', '2026-08-20 15:18:23'),
(435, 38, 1, 'Producción', 'Producción finalizada: Se alcanzó la temperatura máxima del producto (41.3125°C).', '2026-08-20 15:18:23', '2026-08-20 15:18:23', '2026-08-20 15:18:23'),
(436, NULL, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-08-20 15:20:52', '2026-08-20 15:20:52', '2026-08-20 15:20:52'),
(437, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-20 15:20:54', '2026-08-20 15:20:54', '2026-08-20 15:20:54'),
(438, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-08-20 15:20:57', '2026-08-20 15:20:57', '2026-08-20 15:20:57'),
(439, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-20 15:20:58', '2026-08-20 15:20:58', '2026-08-20 15:20:58'),
(440, NULL, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-08-20 15:21:00', '2026-08-20 15:21:00', '2026-08-20 15:21:00'),
(441, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-08-20 15:21:03', '2026-08-20 15:21:03', '2026-08-20 15:21:03'),
(442, NULL, 1, 'Sensor', 'Sensor desactivado.', '2026-08-20 15:29:33', '2026-08-20 15:29:33', '2026-08-20 15:29:33'),
(443, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-08-21 13:35:54', '2026-08-21 13:35:54', '2026-08-21 13:35:54'),
(444, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-21 13:36:31', '2026-08-21 13:36:31', '2026-08-21 13:36:31'),
(445, NULL, 1, 'Sensor', 'Sensor activado.', '2026-08-21 13:37:02', '2026-08-21 13:37:02', '2026-08-21 13:37:02'),
(446, NULL, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-08-21 13:37:07', '2026-08-21 13:37:07', '2026-08-21 13:37:07'),
(447, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-21 13:37:13', '2026-08-21 13:37:13', '2026-08-21 13:37:13'),
(448, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-21 13:38:20', '2026-08-21 13:38:20', '2026-08-21 13:38:20'),
(449, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-21 13:38:21', '2026-08-21 13:38:21', '2026-08-21 13:38:21'),
(450, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-21 13:38:25', '2026-08-21 13:38:25', '2026-08-21 13:38:25'),
(451, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-21 13:38:40', '2026-08-21 13:38:40', '2026-08-21 13:38:40'),
(452, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-21 14:13:14', '2026-08-21 14:13:14', '2026-08-21 14:13:14'),
(453, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-21 14:13:20', '2026-08-21 14:13:20', '2026-08-21 14:13:20'),
(454, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-21 14:13:24', '2026-08-21 14:13:24', '2026-08-21 14:13:24'),
(455, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-21 14:13:46', '2026-08-21 14:13:46', '2026-08-21 14:13:46'),
(456, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-21 14:13:51', '2026-08-21 14:13:51', '2026-08-21 14:13:51'),
(457, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-21 14:15:20', '2026-08-21 14:15:20', '2026-08-21 14:15:20'),
(458, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-21 14:15:27', '2026-08-21 14:15:27', '2026-08-21 14:15:27'),
(459, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-21 14:19:03', '2026-08-21 14:19:03', '2026-08-21 14:19:03'),
(460, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-21 14:19:08', '2026-08-21 14:19:08', '2026-08-21 14:19:08'),
(461, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-21 14:20:38', '2026-08-21 14:20:38', '2026-08-21 14:20:38'),
(462, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-21 14:20:44', '2026-08-21 14:20:44', '2026-08-21 14:20:44'),
(463, NULL, 2, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-21 16:50:44', '2026-08-21 16:50:44', '2026-08-21 16:50:44'),
(464, NULL, 2, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-21 16:51:14', '2026-08-21 16:51:14', '2026-08-21 16:51:14'),
(465, NULL, 2, 'Motor', 'Motor encendido manualmente.', '2026-08-21 16:51:18', '2026-08-21 16:51:18', '2026-08-21 16:51:18'),
(466, NULL, 2, 'Motor', 'Motor encendido manualmente.', '2026-08-21 16:51:29', '2026-08-21 16:51:29', '2026-08-21 16:51:29'),
(467, NULL, 2, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-21 16:53:20', '2026-08-21 16:53:20', '2026-08-21 16:53:20'),
(468, NULL, 2, 'Motor', 'Motor apagado manualmente.', '2026-08-21 16:53:25', '2026-08-21 16:53:25', '2026-08-21 16:53:25'),
(469, NULL, 2, 'Sensor', 'Sensor desactivado.', '2026-08-21 16:53:29', '2026-08-21 16:53:29', '2026-08-21 16:53:29'),
(470, NULL, 2, 'Motor', 'Motor encendido manualmente.', '2026-08-21 20:10:35', '2026-08-21 20:10:35', '2026-08-21 20:10:35'),
(471, NULL, 2, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-21 20:10:40', '2026-08-21 20:10:40', '2026-08-21 20:10:40'),
(472, NULL, 2, 'Sensor', 'Sensor activado.', '2026-08-21 20:10:45', '2026-08-21 20:10:45', '2026-08-21 20:10:45'),
(473, NULL, 2, 'Motor', 'Motor apagado manualmente.', '2026-08-21 20:11:19', '2026-08-21 20:11:19', '2026-08-21 20:11:19'),
(474, NULL, 2, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-08-21 20:11:21', '2026-08-21 20:11:21', '2026-08-21 20:11:21'),
(475, NULL, 2, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-08-21 20:11:27', '2026-08-21 20:11:27', '2026-08-21 20:11:27'),
(476, NULL, 2, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-21 20:11:31', '2026-08-21 20:11:31', '2026-08-21 20:11:31'),
(477, NULL, 2, 'Sensor', 'Sensor desactivado.', '2026-08-21 20:11:34', '2026-08-21 20:11:34', '2026-08-21 20:11:34'),
(478, NULL, 2, 'Motor', 'Motor encendido manualmente.', '2026-08-21 21:50:11', '2026-08-21 21:50:11', '2026-08-21 21:50:11'),
(479, NULL, 2, 'Motor', 'Motor apagado manualmente.', '2026-08-21 21:50:21', '2026-08-21 21:50:21', '2026-08-21 21:50:21'),
(480, NULL, 2, 'Motor', 'Motor encendido manualmente.', '2026-08-21 22:51:12', '2026-08-21 22:51:12', '2026-08-21 22:51:12'),
(481, NULL, 2, 'Motor', 'Motor apagado manualmente.', '2026-08-21 22:51:24', '2026-08-21 22:51:24', '2026-08-21 22:51:24'),
(482, NULL, 2, 'Sensor', 'Sensor activado.', '2026-08-21 23:02:29', '2026-08-21 23:02:29', '2026-08-21 23:02:29'),
(483, NULL, 2, 'Sensor', 'Sensor activado.', '2026-08-21 23:02:39', '2026-08-21 23:02:39', '2026-08-21 23:02:39'),
(484, NULL, 2, 'Motor', 'Motor encendido manualmente.', '2026-08-21 23:06:11', '2026-08-21 23:06:11', '2026-08-21 23:06:11'),
(485, NULL, 2, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-21 23:07:24', '2026-08-21 23:07:24', '2026-08-21 23:07:24'),
(486, NULL, 2, 'Motor', 'Motor apagado manualmente.', '2026-08-21 23:19:39', '2026-08-21 23:19:39', '2026-08-21 23:19:39'),
(487, NULL, 2, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-21 23:19:46', '2026-08-21 23:19:46', '2026-08-21 23:19:46'),
(488, NULL, 2, 'Motor', 'Motor encendido manualmente.', '2026-08-21 23:20:02', '2026-08-21 23:20:02', '2026-08-21 23:20:02'),
(489, 39, 2, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-08-21 23:31:45', '2026-08-21 23:31:45', '2026-08-21 23:31:45'),
(490, 39, 2, 'Producción', 'Producción finalizada: Se alcanzó la temperatura máxima del producto (34.125°C).', '2026-08-21 23:31:46', '2026-08-21 23:31:46', '2026-08-21 23:31:46'),
(491, 40, 2, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-08-21 23:35:38', '2026-08-21 23:35:38', '2026-08-21 23:35:38'),
(492, 40, 2, 'Producción', 'Producción finalizada: Se alcanzó la temperatura máxima del producto (36.4375°C).', '2026-08-21 23:35:42', '2026-08-21 23:35:42', '2026-08-21 23:35:42'),
(493, NULL, 2, 'Motor', 'Motor cambiado a modo Automatico.', '2026-08-21 23:36:18', '2026-08-21 23:36:18', '2026-08-21 23:36:18'),
(494, NULL, 2, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-08-21 23:36:20', '2026-08-21 23:36:20', '2026-08-21 23:36:20'),
(495, 41, 2, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-08-21 23:36:54', '2026-08-21 23:36:54', '2026-08-21 23:36:54'),
(496, 41, 2, 'Motor', 'Motor encendido automáticamente al iniciar producción.', '2026-08-21 23:36:54', '2026-08-21 23:36:54', '2026-08-21 23:36:54'),
(497, 41, 2, 'Ventilador', 'Ventilador encendido automáticamente al iniciar producción.', '2026-08-21 23:36:54', '2026-08-21 23:36:54', '2026-08-21 23:36:54'),
(498, 41, 2, 'Motor', 'Motor apagado automáticamente.', '2026-08-21 23:36:57', '2026-08-21 23:36:57', '2026-08-21 23:36:57'),
(499, 41, 2, 'Ventilador', 'Ventilador apagado automáticamente.', '2026-08-21 23:36:57', '2026-08-21 23:36:57', '2026-08-21 23:36:57'),
(500, 41, 2, 'Producción', 'Producción finalizada: Se alcanzó la temperatura máxima del producto (36.6875°C).', '2026-08-21 23:36:57', '2026-08-21 23:36:57', '2026-08-21 23:36:57'),
(501, NULL, 2, 'Motor', 'Motor cambiado a modo Manual.', '2026-08-21 23:39:32', '2026-08-21 23:39:32', '2026-08-21 23:39:32'),
(502, NULL, 2, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-08-21 23:39:34', '2026-08-21 23:39:34', '2026-08-21 23:39:34'),
(503, NULL, 2, 'Motor', 'Motor encendido manualmente.', '2026-08-21 23:39:37', '2026-08-21 23:39:37', '2026-08-21 23:39:37'),
(504, NULL, 2, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-21 23:39:40', '2026-08-21 23:39:40', '2026-08-21 23:39:40'),
(505, 42, 2, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-08-21 23:42:41', '2026-08-21 23:42:41', '2026-08-21 23:42:41'),
(506, 42, 2, 'Motor', 'Motor cambiado a modo Automatico.', '2026-08-21 23:43:02', '2026-08-21 23:43:02', '2026-08-21 23:43:02'),
(507, 42, 2, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-08-21 23:43:06', '2026-08-21 23:43:06', '2026-08-21 23:43:06'),
(508, 42, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-08-22 00:01:39', '2026-08-22 00:01:39', '2026-08-22 00:01:39'),
(509, 42, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-22 00:01:42', '2026-08-22 00:01:42', '2026-08-22 00:01:42'),
(510, 42, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-22 00:09:55', '2026-08-22 00:09:55', '2026-08-22 00:09:55'),
(511, 42, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-08-22 00:10:00', '2026-08-22 00:10:00', '2026-08-22 00:10:00'),
(512, 42, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-08-22 00:28:34', '2026-08-22 00:28:34', '2026-08-22 00:28:34'),
(513, 42, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-08-22 00:30:51', '2026-08-22 00:30:51', '2026-08-22 00:30:51'),
(514, 42, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-22 00:30:55', '2026-08-22 00:30:55', '2026-08-22 00:30:55'),
(515, 42, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-22 00:30:57', '2026-08-22 00:30:57', '2026-08-22 00:30:57'),
(516, 42, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-22 00:31:05', '2026-08-22 00:31:05', '2026-08-22 00:31:05'),
(517, 42, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-08-22 00:31:10', '2026-08-22 00:31:10', '2026-08-22 00:31:10'),
(518, 42, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-22 00:31:15', '2026-08-22 00:31:15', '2026-08-22 00:31:15'),
(519, 42, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-22 00:37:03', '2026-08-22 00:37:03', '2026-08-22 00:37:03'),
(520, 42, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-22 00:37:08', '2026-08-22 00:37:08', '2026-08-22 00:37:08'),
(521, 42, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-08-22 00:37:12', '2026-08-22 00:37:12', '2026-08-22 00:37:12'),
(522, 42, 1, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-08-22 00:37:18', '2026-08-22 00:37:18', '2026-08-22 00:37:18'),
(523, 42, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-08-22 00:55:43', '2026-08-22 00:55:43', '2026-08-22 00:55:43'),
(524, 42, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-22 00:55:46', '2026-08-22 00:55:46', '2026-08-22 00:55:46'),
(525, 42, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-22 00:55:51', '2026-08-22 00:55:51', '2026-08-22 00:55:51'),
(526, 42, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-22 00:58:53', '2026-08-22 00:58:53', '2026-08-22 00:58:53'),
(527, 42, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-22 01:08:55', '2026-08-22 01:08:55', '2026-08-22 01:08:55'),
(528, 42, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-08-22 01:09:00', '2026-08-22 01:09:00', '2026-08-22 01:09:00'),
(529, 42, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-08-22 01:13:16', '2026-08-22 01:13:16', '2026-08-22 01:13:16'),
(530, 42, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-22 01:13:20', '2026-08-22 01:13:20', '2026-08-22 01:13:20'),
(531, 42, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-08-22 01:13:26', '2026-08-22 01:13:26', '2026-08-22 01:13:26'),
(532, 42, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-22 01:13:29', '2026-08-22 01:13:29', '2026-08-22 01:13:29'),
(533, 42, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-08-22 01:19:45', '2026-08-22 01:19:45', '2026-08-22 01:19:45'),
(534, 42, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-22 01:28:47', '2026-08-22 01:28:47', '2026-08-22 01:28:47'),
(535, 42, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-22 01:28:53', '2026-08-22 01:28:53', '2026-08-22 01:28:53'),
(536, 42, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-08-22 01:38:02', '2026-08-22 01:38:02', '2026-08-22 01:38:02'),
(537, 42, 1, 'Ventilador', 'Ventilador cambiado a modo Automatico.', '2026-08-22 01:38:07', '2026-08-22 01:38:07', '2026-08-22 01:38:07'),
(538, 42, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-08-22 01:40:01', '2026-08-22 01:40:01', '2026-08-22 01:40:01'),
(539, 42, 1, 'Motor', 'Motor apagado manualmente.', '2026-08-22 01:40:04', '2026-08-22 01:40:04', '2026-08-22 01:40:04'),
(540, 42, 1, 'Motor', 'Motor encendido manualmente.', '2026-08-22 01:42:27', '2026-08-22 01:42:27', '2026-08-22 01:42:27'),
(541, 42, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-08-22 01:51:20', '2026-08-22 01:51:20', '2026-08-22 01:51:20'),
(542, 42, 1, 'Producción', 'La producción fue finalizada correctamente.', '2026-08-22 01:52:32', '2026-08-22 01:52:32', '2026-08-22 01:52:32'),
(543, 42, 1, 'Motor', 'Motor apagado automáticamente al finalizar producción.', '2026-08-22 01:52:32', '2026-08-22 01:52:32', '2026-08-22 01:52:32'),
(544, 42, 1, 'Ventilador', 'Ventilador apagado automáticamente al finalizar producción.', '2026-08-22 01:52:32', '2026-08-22 01:52:32', '2026-08-22 01:52:32'),
(545, NULL, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-08-26 04:32:58', '2026-08-26 04:32:58', '2026-08-26 04:32:58'),
(546, NULL, 1, 'Ventilador', 'Ventilador cambiado a modo Manual.', '2026-08-26 04:32:59', '2026-08-26 04:32:59', '2026-08-26 04:32:59'),
(547, 43, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-08-26 04:58:34', '2026-08-26 04:58:34', '2026-08-26 04:58:34'),
(548, 43, 1, 'Producción', 'La producción fue finalizada correctamente.', '2026-08-26 04:58:41', '2026-08-26 04:58:41', '2026-08-26 04:58:41'),
(549, 43, 1, 'Motor', 'Motor apagado automáticamente al finalizar producción.', '2026-08-26 04:58:41', '2026-08-26 04:58:41', '2026-08-26 04:58:41'),
(550, 43, 1, 'Ventilador', 'Ventilador apagado automáticamente al finalizar producción.', '2026-08-26 04:58:41', '2026-08-26 04:58:41', '2026-08-26 04:58:41'),
(551, 44, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-08-26 05:06:14', '2026-08-26 05:06:14', '2026-08-26 05:06:14'),
(552, 44, 2, 'Producción', 'La producción fue finalizada correctamente.', '2026-08-26 13:05:04', '2026-08-26 13:05:04', '2026-08-26 13:05:04'),
(553, 44, 2, 'Motor', 'Motor apagado automáticamente al finalizar producción.', '2026-08-26 13:05:04', '2026-08-26 13:05:04', '2026-08-26 13:05:04'),
(554, 44, 2, 'Ventilador', 'Ventilador apagado automáticamente al finalizar producción.', '2026-08-26 13:05:04', '2026-08-26 13:05:04', '2026-08-26 13:05:04'),
(555, 45, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción.', '2026-08-26 15:32:39', '2026-08-26 15:32:39', '2026-08-26 15:32:39'),
(556, 45, 1, 'Producción', 'La producción fue finalizada correctamente.', '2026-08-26 15:33:23', '2026-08-26 15:33:23', '2026-08-26 15:33:23'),
(557, 45, 1, 'Motor', 'Motor apagado automáticamente al finalizar producción.', '2026-08-26 15:33:23', '2026-08-26 15:33:23', '2026-08-26 15:33:23'),
(558, 45, 1, 'Ventilador', 'Ventilador apagado automáticamente al finalizar producción.', '2026-08-26 15:33:23', '2026-08-26 15:33:23', '2026-08-26 15:33:23'),
(559, 46, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción. Se descontaron 900 ml de insumo.', '2026-08-26 15:39:40', '2026-08-26 15:39:40', '2026-08-26 15:39:40'),
(560, 46, 1, 'Producción', 'La producción fue finalizada correctamente.', '2026-08-26 15:40:01', '2026-08-26 15:40:01', '2026-08-26 15:40:01'),
(561, 46, 1, 'Motor', 'Motor apagado automáticamente al finalizar producción.', '2026-08-26 15:40:01', '2026-08-26 15:40:01', '2026-08-26 15:40:01'),
(562, 46, 1, 'Ventilador', 'Ventilador apagado automáticamente al finalizar producción.', '2026-08-26 15:40:01', '2026-08-26 15:40:01', '2026-08-26 15:40:01'),
(563, 47, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción. Se descontaron 20 pastilla de insumo.', '2026-08-26 15:43:15', '2026-08-26 15:43:15', '2026-08-26 15:43:15'),
(564, 47, 1, 'Producción', 'La producción fue finalizada correctamente.', '2026-08-26 15:43:42', '2026-08-26 15:43:42', '2026-08-26 15:43:42'),
(565, 47, 1, 'Motor', 'Motor apagado automáticamente al finalizar producción.', '2026-08-26 15:43:42', '2026-08-26 15:43:42', '2026-08-26 15:43:42'),
(566, 47, 1, 'Ventilador', 'Ventilador apagado automáticamente al finalizar producción.', '2026-08-26 15:43:42', '2026-08-26 15:43:42', '2026-08-26 15:43:42'),
(567, 48, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción. Se descontaron 2 pastilla de insumo.', '2026-08-26 15:48:41', '2026-08-26 15:48:41', '2026-08-26 15:48:41'),
(568, 48, 1, 'Producción', 'La producción fue finalizada correctamente.', '2026-08-26 16:23:11', '2026-08-26 16:23:11', '2026-08-26 16:23:11'),
(569, 48, 1, 'Motor', 'Motor apagado automáticamente al finalizar producción.', '2026-08-26 16:23:11', '2026-08-26 16:23:11', '2026-08-26 16:23:11'),
(570, 48, 1, 'Ventilador', 'Ventilador apagado automáticamente al finalizar producción.', '2026-08-26 16:23:11', '2026-08-26 16:23:11', '2026-08-26 16:23:11'),
(571, 49, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción. Se descontaron 2 pastilla de insumo.', '2026-08-26 16:23:56', '2026-08-26 16:23:56', '2026-08-26 16:23:56'),
(572, 49, 1, 'Producción', 'La producción fue finalizada correctamente.', '2026-08-26 16:24:48', '2026-08-26 16:24:48', '2026-08-26 16:24:48'),
(573, 49, 1, 'Motor', 'Motor apagado automáticamente al finalizar producción.', '2026-08-26 16:24:48', '2026-08-26 16:24:48', '2026-08-26 16:24:48'),
(574, 49, 1, 'Ventilador', 'Ventilador apagado automáticamente al finalizar producción.', '2026-08-26 16:24:48', '2026-08-26 16:24:48', '2026-08-26 16:24:48'),
(575, 50, 1, 'Producción', 'Producción iniciada. Etapa actual: Producción. Se descontaron 2 pastilla de insumo.', '2026-08-26 16:48:24', '2026-08-26 16:48:24', '2026-08-26 16:48:24'),
(576, 50, 1, 'Producción', 'La producción fue finalizada correctamente.', '2026-08-26 16:49:06', '2026-08-26 16:49:06', '2026-08-26 16:49:06'),
(577, 50, 1, 'Motor', 'Motor apagado automáticamente al finalizar producción.', '2026-08-26 16:49:06', '2026-08-26 16:49:06', '2026-08-26 16:49:06'),
(578, 50, 1, 'Ventilador', 'Ventilador apagado automáticamente al finalizar producción.', '2026-08-26 16:49:06', '2026-08-26 16:49:06', '2026-08-26 16:49:06'),
(579, NULL, 1, 'Sensor', 'Sensor desactivado.', '2026-08-27 13:13:03', '2026-08-27 13:13:03', '2026-08-27 13:13:03'),
(580, 51, 2, 'Producción', 'Producción iniciada. Etapa actual: Producción. Se descontaron 4500 ml de insumo.', '2026-09-04 01:33:35', '2026-09-04 01:33:35', '2026-09-04 01:33:35'),
(581, 51, 2, 'Producción', 'La producción fue finalizada correctamente.', '2026-09-04 01:33:42', '2026-09-04 01:33:42', '2026-09-04 01:33:42'),
(582, 51, 2, 'Motor', 'Motor apagado automáticamente al finalizar producción.', '2026-09-04 01:33:42', '2026-09-04 01:33:42', '2026-09-04 01:33:42'),
(583, 51, 2, 'Ventilador', 'Ventilador apagado automáticamente al finalizar producción.', '2026-09-04 01:33:42', '2026-09-04 01:33:42', '2026-09-04 01:33:42'),
(584, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-09-08 18:40:06', '2026-09-08 18:40:06', '2026-09-08 18:40:06'),
(585, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-09-08 18:43:24', '2026-09-08 18:43:24', '2026-09-08 18:43:24'),
(586, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-09-08 18:43:33', '2026-09-08 18:43:33', '2026-09-08 18:43:33'),
(587, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-09-08 18:43:38', '2026-09-08 18:43:38', '2026-09-08 18:43:38'),
(588, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-09-08 18:43:58', '2026-09-08 18:43:58', '2026-09-08 18:43:58'),
(589, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-09-08 18:44:28', '2026-09-08 18:44:28', '2026-09-08 18:44:28'),
(590, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-09-08 18:44:31', '2026-09-08 18:44:31', '2026-09-08 18:44:31'),
(591, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-09-08 18:44:35', '2026-09-08 18:44:35', '2026-09-08 18:44:35'),
(592, NULL, 1, 'Sensor', 'Sensor activado.', '2026-09-08 18:44:49', '2026-09-08 18:44:49', '2026-09-08 18:44:49'),
(593, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-09-08 19:05:56', '2026-09-08 19:05:56', '2026-09-08 19:05:56'),
(594, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-09-08 19:06:50', '2026-09-08 19:06:50', '2026-09-08 19:06:50'),
(595, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-09-08 19:06:54', '2026-09-08 19:06:54', '2026-09-08 19:06:54'),
(596, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-09-08 19:07:11', '2026-09-08 19:07:11', '2026-09-08 19:07:11'),
(597, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-09-08 19:07:17', '2026-09-08 19:07:17', '2026-09-08 19:07:17'),
(598, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-09-08 19:07:39', '2026-09-08 19:07:39', '2026-09-08 19:07:39'),
(599, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-09-08 19:07:55', '2026-09-08 19:07:55', '2026-09-08 19:07:55'),
(600, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-09-08 19:11:12', '2026-09-08 19:11:12', '2026-09-08 19:11:12'),
(601, NULL, 1, 'Sensor', 'Sensor activado.', '2026-09-08 19:11:17', '2026-09-08 19:11:17', '2026-09-08 19:11:17'),
(602, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-09-08 19:14:44', '2026-09-08 19:14:44', '2026-09-08 19:14:44'),
(603, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-09-08 19:14:50', '2026-09-08 19:14:50', '2026-09-08 19:14:50'),
(604, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-09-08 19:14:54', '2026-09-08 19:14:54', '2026-09-08 19:14:54'),
(605, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-09-08 19:14:58', '2026-09-08 19:14:58', '2026-09-08 19:14:58'),
(606, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-09-08 20:44:06', '2026-09-08 20:44:06', '2026-09-08 20:44:06'),
(607, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-09-08 20:50:07', '2026-09-08 20:50:07', '2026-09-08 20:50:07'),
(608, NULL, 1, 'Motor', 'Motor cambiado a modo Automatico.', '2026-09-08 20:50:11', '2026-09-08 20:50:11', '2026-09-08 20:50:11'),
(609, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-09-08 20:50:15', '2026-09-08 20:50:15', '2026-09-08 20:50:15'),
(610, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-09-08 20:50:23', '2026-09-08 20:50:23', '2026-09-08 20:50:23'),
(611, NULL, 1, 'Motor', 'Motor cambiado a modo Manual.', '2026-09-08 20:50:34', '2026-09-08 20:50:34', '2026-09-08 20:50:34'),
(612, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-09-08 20:50:38', '2026-09-08 20:50:38', '2026-09-08 20:50:38'),
(613, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-09-08 21:02:59', '2026-09-08 21:02:59', '2026-09-08 21:02:59'),
(614, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-09-08 21:03:02', '2026-09-08 21:03:02', '2026-09-08 21:03:02'),
(615, NULL, 1, 'Sensor', 'Sensor desactivado.', '2026-09-08 21:03:16', '2026-09-08 21:03:16', '2026-09-08 21:03:16'),
(616, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-09-08 21:17:39', '2026-09-08 21:17:39', '2026-09-08 21:17:39'),
(617, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-09-08 21:17:45', '2026-09-08 21:17:45', '2026-09-08 21:17:45'),
(618, NULL, 1, 'Motor', 'Motor encendido manualmente.', '2026-09-08 21:17:59', '2026-09-08 21:17:59', '2026-09-08 21:17:59'),
(619, NULL, 1, 'Ventilador', 'Ventilador encendido manualmente.', '2026-09-08 21:18:04', '2026-09-08 21:18:04', '2026-09-08 21:18:04'),
(620, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-09-08 21:18:21', '2026-09-08 21:18:21', '2026-09-08 21:18:21'),
(621, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-09-08 21:18:23', '2026-09-08 21:18:23', '2026-09-08 21:18:23'),
(622, NULL, 3, 'Motor', 'Motor cambiado a modo Automatico.', '2026-09-09 01:23:07', '2026-09-09 01:23:07', '2026-09-09 01:23:07'),
(623, NULL, 3, 'Motor', 'Motor cambiado a modo Manual.', '2026-09-09 01:23:11', '2026-09-09 01:23:11', '2026-09-09 01:23:11'),
(624, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-09-10 17:42:55', '2026-09-10 17:42:55', '2026-09-10 17:42:55'),
(625, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-09-10 17:42:59', '2026-09-10 17:42:59', '2026-09-10 17:42:59'),
(626, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-09-10 18:28:05', '2026-09-10 18:28:05', '2026-09-10 18:28:05'),
(627, NULL, 1, 'Sensor', 'Sensor desactivado.', '2026-09-10 18:28:08', '2026-09-10 18:28:08', '2026-09-10 18:28:08'),
(628, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-09-10 18:28:10', '2026-09-10 18:28:10', '2026-09-10 18:28:10'),
(629, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-09-10 22:45:49', '2026-09-10 22:45:49', '2026-09-10 22:45:49'),
(630, NULL, 1, 'Ventilador', 'Ventilador apagado manualmente.', '2026-09-10 22:45:51', '2026-09-10 22:45:51', '2026-09-10 22:45:51'),
(631, NULL, 1, 'Sensor', 'Sensor desactivado.', '2026-09-10 22:45:53', '2026-09-10 22:45:53', '2026-09-10 22:45:53'),
(632, NULL, 1, 'Motor', 'Motor apagado manualmente.', '2026-09-17 15:26:58', '2026-09-17 15:26:58', '2026-09-17 15:26:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingresos_productores`
--

CREATE TABLE `ingresos_productores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fecha_ingreso` date NOT NULL,
  `observaciones` text DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'abierta',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `productor_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ingresos_productores`
--

INSERT INTO `ingresos_productores` (`id`, `user_id`, `fecha_ingreso`, `observaciones`, `estado`, `created_at`, `updated_at`, `productor_id`) VALUES
(1, 1, '2026-09-17', NULL, 'abierta', '2026-09-17 17:49:17', '2026-09-17 17:49:17', 2),
(2, 1, '2026-09-17', NULL, 'abierta', '2026-09-17 18:05:15', '2026-09-17 18:05:15', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingresos_productores_items`
--

CREATE TABLE `ingresos_productores_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ingreso_productor_id` bigint(20) UNSIGNED NOT NULL,
  `presentacion_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad_ingresada` int(10) UNSIGNED NOT NULL,
  `cantidad_disponible` int(10) UNSIGNED NOT NULL,
  `precio_acopio_unitario` decimal(10,2) DEFAULT NULL,
  `precio_venta_unitario` decimal(10,2) NOT NULL,
  `fecha_recepcion` date DEFAULT NULL,
  `fecha_caducidad` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

--
-- Volcado de datos para la tabla `ingresos_productores_items`
--

INSERT INTO `ingresos_productores_items` (`id`, `ingreso_productor_id`, `presentacion_id`, `cantidad_ingresada`, `cantidad_disponible`, `precio_acopio_unitario`, `precio_venta_unitario`, `fecha_recepcion`, `fecha_caducidad`, `created_at`, `updated_at`) VALUES
(1, 1, 10, 20, 0, 15.00, 17.50, '2026-09-17', '2026-09-20', '2026-09-17 17:49:17', '2026-09-22 19:22:24'),
(2, 1, 5, 20, 0, 20.00, 26.00, '2026-09-17', '2026-09-20', '2026-09-17 17:49:17', '2026-09-22 19:24:36'),
(3, 1, 6, 21, 20, 20.00, 22.00, '2026-09-17', '2026-09-20', '2026-09-17 17:49:17', '2026-09-17 18:01:39'),
(4, 2, 10, 20, 20, 16.00, 17.50, '2026-09-17', '2026-09-20', '2026-09-17 18:05:15', '2026-09-17 18:05:15'),
(5, 2, 5, 20, 20, 20.00, 26.00, '2026-09-17', '2026-09-20', '2026-09-17 18:05:15', '2026-09-17 18:05:15'),
(6, 2, 6, 20, 20, 20.00, 22.00, '2026-09-17', '2026-09-20', '2026-09-17 18:05:15', '2026-09-17 19:52:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lecturas`
--

CREATE TABLE `lecturas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `produccion_id` bigint(20) UNSIGNED NOT NULL,
  `sensor_id` bigint(20) UNSIGNED NOT NULL,
  `temperatura` decimal(5,2) NOT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_07_08_150320_create_productos_table', 1),
(5, '2026_07_08_150429_create_dispositivos_table', 1),
(6, '2026_07_08_150531_create_sensores_table', 1),
(7, '2026_07_08_150720_create_actuadores_table', 1),
(8, '2026_07_08_150744_create_producciones_table', 1),
(9, '2026_07_08_150851_create_lecturas_table', 1),
(10, '2026_07_08_150909_create_alertas_table', 1),
(11, '2026_07_08_150925_create_reportes_table', 1),
(12, '2026_07_08_150948_create_configuraciones_table', 1),
(13, '2026_07_09_095126_add_control_fields_to_configuraciones_table', 1),
(14, '2026_07_14_190918_create_eventos_table', 1),
(15, '2026_07_16_141301_add_user_id_to_eventos_table', 1),
(16, '2026_07_16_164440_add_temperaturas_to_producciones_table', 1),
(17, '2026_07_16_165633_add_estadisticas_temperatura_to_producciones_table', 1),
(18, '2026_07_16_171351_make_lectura_id_nullable_in_alertas_table', 1),
(19, '2026_07_16_215818_add_temperatura_pasteurizacion_to_productos_table', 1),
(20, '2026_07_16_222029_add_temperatura_pasteurizacion_to_configuraciones_table', 1),
(21, '2026_07_16_223419_add_temperatura_pasteurizacion_to_configuraciones_table', 1),
(22, '2026_07_29_175807_add_temperatura_actual_to_sensores_table', 2),
(23, '2026_08_25_211718_add_datos_tecnicos_to_productos_table', 3),
(24, '2026_08_25_211829_create_presentaciones_table', 4),
(25, '2026_08_26_004012_add_tipo_cuajo_to_productos_table', 5),
(26, '2026_08_26_104749_add_stock_y_unidad_to_productos_table', 6),
(27, '2026_08_26_121058_add_cuajo_to_producciones_table', 7),
(28, '2026_09_05_000000_create_consignacion_y_ventas_tables', 8),
(29, '2026_09_08_000001_add_productor_role_to_users_table', 9),
(30, '2026_09_08_000002_add_fechas_to_consignacion_items_table', 10),
(31, '2026_09_08_000003_add_venta_id_to_devoluciones_table', 11),
(32, '2026_09_08_000004_update_devoluciones_for_cliente_productor_flow', 12),
(33, '2026_08_27_100000_add_activo_for_logical_deletes', 13),
(34, '2026_08_28_091959_add_catalog_fields_to_presentaciones_table', 13),
(35, '2026_09_10_000001_refactor_acopio', 14),
(36, '2026_09_11_000001_add_inventory_search_indexes', 15),
(37, '2026_09_17_000002_drop_stock_cuajo_from_productos', 16),
(38, '2026_09_17_000001_remove_returns_and_integer_quantities', 17),
(39, '2026_09_17_000003_remove_liquidaciones_completely', 18),
(40, '2026_09_17_000004_create_ajustes_inventario_table', 19),
(41, '2026_09_17_000005_create_descartes_productos_table', 20);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lote_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `tipo` varchar(40) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `motivo` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `movimientos_inventario`
--

INSERT INTO `movimientos_inventario` (`id`, `lote_id`, `user_id`, `tipo`, `cantidad`, `motivo`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 'entrada', 1, 'error', '2026-09-17 18:01:25', '2026-09-17 18:01:25'),
(2, 3, 1, 'salida', -1, 'error', '2026-09-17 18:01:39', '2026-09-17 18:01:39'),
(3, 6, 1, 'ajuste_inventario', -19, 'Ajuste #1: Dañado', '2026-09-17 19:51:42', '2026-09-17 19:51:42'),
(4, 6, 1, 'ajuste_inventario', 19, 'Anulación ajuste #1: Error', '2026-09-17 19:52:55', '2026-09-17 19:52:55'),
(5, 1, 1, 'descarte', -12, 'Descarte #1: caducado', '2026-09-22 19:22:24', '2026-09-22 19:22:24'),
(6, 2, 1, 'descarte', -19, 'Descarte #3: caducado', '2026-09-22 19:24:36', '2026-09-22 19:24:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presentaciones`
--

CREATE TABLE `presentaciones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `envase` varchar(100) DEFAULT NULL,
  `sabor` varchar(100) DEFAULT NULL,
  `contenido` decimal(10,2) DEFAULT NULL,
  `unidad` varchar(10) DEFAULT NULL,
  `precio` decimal(8,2) NOT NULL,
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `imagen_comercial` varchar(255) DEFAULT NULL,
  `con_fruta` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `stock_minimo_alerta` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `presentaciones`
--

INSERT INTO `presentaciones` (`id`, `producto_id`, `nombre`, `envase`, `sabor`, `contenido`, `unidad`, `precio`, `stock`, `imagen_comercial`, `con_fruta`, `activo`, `created_at`, `updated_at`, `stock_minimo_alerta`) VALUES
(4, 1, '2 litros', NULL, NULL, NULL, NULL, 22.00, 0, NULL, 0, 0, '2026-08-27 15:10:41', '2026-08-28 11:55:59', 0),
(5, 1, 'Yogurt de Frutilla 2 L', 'Botella', 'Frutilla', 2.00, 'L', 26.00, 20, 'presentaciones/izw3t0oHzaGuLYircJ9nh7Eb4kq7l1y4t1KvE96H.png', 1, 1, '2026-08-28 13:36:17', '2026-09-22 19:24:36', 0),
(6, 1, 'Yogurt Griego', 'Botella', 'Natural', 1.00, 'L', 22.00, 40, NULL, 1, 1, '2026-08-28 13:47:44', '2026-09-17 19:52:55', 0),
(7, 2, 'Queso Molde Familiar', 'Bolsa', 'Natural', 3.00, 'kg', 30.00, 0, NULL, 0, 0, '2026-08-28 13:48:32', '2026-08-28 13:57:00', 0),
(8, 2, 'Queso molde Italiano de 1 Kl', 'Bolsa', 'Natural', 1.00, 'kg', 40.00, 0, NULL, 0, 1, '2026-09-04 00:03:17', '2026-09-10 15:55:16', 0),
(9, 8, '3 kg', 'Bolsa', 'Neutral', 3.00, 'kg', 105.00, 0, NULL, 0, 1, '2026-09-10 20:04:49', '2026-09-17 16:33:04', 0),
(10, 8, '500 g', 'Bolsa', 'Neutral', 500.00, 'g', 17.50, 20, NULL, 0, 1, '2026-09-10 20:07:15', '2026-09-22 19:22:24', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producciones`
--

CREATE TABLE `producciones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `producto_id` bigint(20) UNSIGNED NOT NULL,
  `dispositivo_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cantidad_leche` decimal(8,2) NOT NULL,
  `tipo_cuajo` varchar(255) DEFAULT NULL,
  `cantidad_cuajo` decimal(8,2) DEFAULT NULL,
  `temperatura_objetivo` decimal(5,2) NOT NULL,
  `temperatura_inicial` decimal(5,2) DEFAULT NULL,
  `temperatura_final` decimal(5,2) DEFAULT NULL,
  `temperatura_minima` decimal(5,2) DEFAULT NULL,
  `temperatura_maxima` decimal(5,2) DEFAULT NULL,
  `temperatura_promedio` decimal(5,2) DEFAULT NULL,
  `estado` varchar(255) NOT NULL DEFAULT 'En proceso',
  `etapa` varchar(255) NOT NULL DEFAULT 'Produccion',
  `observaciones` text DEFAULT NULL,
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producciones`
--

INSERT INTO `producciones` (`id`, `user_id`, `producto_id`, `dispositivo_id`, `cantidad_leche`, `tipo_cuajo`, `cantidad_cuajo`, `temperatura_objetivo`, `temperatura_inicial`, `temperatura_final`, `temperatura_minima`, `temperatura_maxima`, `temperatura_promedio`, `estado`, `etapa`, `observaciones`, `fecha_inicio`, `fecha_fin`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1.00, NULL, NULL, 43.00, 70.00, 45.00, 45.00, 70.00, 67.50, 'Finalizada', 'Produccion', 'Prueba en laptop 1', '2026-07-23 14:17:00', '2026-07-23 15:12:46', '2026-07-23 18:17:00', '2026-07-23 19:12:46'),
(2, 1, 1, 1, 1.00, NULL, NULL, 43.00, 60.00, 45.00, 45.00, 85.00, 66.00, 'Finalizada', 'Produccion', 'Prueba 2', '2026-07-23 15:57:11', '2026-07-23 15:58:46', '2026-07-23 19:57:11', '2026-07-23 19:58:46'),
(3, 1, 1, 1, 1.00, NULL, NULL, 43.00, 56.00, 45.00, 45.00, 80.00, 60.33, 'Finalizada', 'Produccion', 'Prueba 3', '2026-07-23 16:02:11', '2026-07-23 16:03:12', '2026-07-23 20:02:11', '2026-07-23 20:03:12'),
(4, 1, 1, 1, 1.00, NULL, NULL, 45.00, 50.00, 45.00, 45.00, 70.00, 55.00, 'Finalizada', 'Produccion', 'Prueba 4', '2026-07-23 16:08:51', '2026-07-23 16:10:21', '2026-07-23 20:08:51', '2026-07-23 20:10:21'),
(5, 1, 1, 1, 1.00, NULL, NULL, 45.00, 60.00, 45.00, 45.00, 80.00, 59.29, 'Finalizada', 'Produccion', 'Prueba 5', '2026-07-23 16:16:57', '2026-07-23 16:28:55', '2026-07-23 20:16:57', '2026-07-23 20:28:55'),
(6, 1, 1, 1, 1.00, NULL, NULL, 45.00, NULL, NULL, NULL, NULL, NULL, 'Finalizada', 'Produccion', 'Prueba 6', '2026-07-23 16:36:17', '2026-07-23 16:36:24', '2026-07-23 20:36:17', '2026-07-23 20:36:24'),
(7, 1, 1, 1, 1.00, NULL, NULL, 45.00, 50.00, 45.00, 45.00, 85.00, 65.00, 'Finalizada', 'Produccion', 'Prueba 7', '2026-07-23 16:37:24', '2026-07-23 17:01:34', '2026-07-23 20:37:24', '2026-07-23 21:01:34'),
(8, 1, 2, 1, 1.00, NULL, NULL, 35.00, 50.00, 35.00, 35.00, 65.00, 56.00, 'Finalizada', 'Produccion', 'Prueba 1 Queso', '2026-07-23 17:08:14', '2026-07-23 17:33:31', '2026-07-23 21:08:14', '2026-07-23 21:33:31'),
(9, 1, 1, 1, 1.00, NULL, NULL, 45.00, 60.00, 45.00, 45.00, 85.00, 68.00, 'Finalizada', 'Produccion', 'Prueba yogurt', '2026-07-23 17:34:24', '2026-07-23 17:35:24', '2026-07-23 21:34:24', '2026-07-23 21:35:24'),
(10, 1, 1, 1, 1.00, NULL, NULL, 45.00, 60.00, 45.00, 45.00, 60.00, 52.50, 'Finalizada', 'Produccion', 'Prueba', '2026-07-23 17:42:14', '2026-07-23 17:42:33', '2026-07-23 21:42:14', '2026-07-23 21:42:33'),
(11, 1, 1, 1, 1.00, NULL, NULL, 45.00, 85.00, 45.00, 45.00, 85.00, 65.00, 'Finalizada', 'Produccion', 'Prueba', '2026-07-23 18:08:38', '2026-07-23 18:09:07', '2026-07-23 22:08:38', '2026-07-23 22:09:07'),
(12, 1, 1, 1, 1.00, NULL, NULL, 45.00, 85.00, 45.00, 45.00, 85.00, 65.00, 'Finalizada', 'Produccion', 'Prueba', '2026-07-23 18:15:38', '2026-07-23 18:16:05', '2026-07-23 22:15:38', '2026-07-23 22:16:05'),
(13, 1, 1, 1, 2.00, NULL, NULL, 45.00, 24.06, 24.06, 24.06, 24.06, 24.06, 'Finalizada', 'Produccion', 'Prueba En Laptop', '2026-07-23 20:23:56', '2026-07-23 20:23:59', '2026-07-24 00:23:56', '2026-07-24 00:23:59'),
(14, 1, 1, 1, 2.00, NULL, NULL, 42.00, 24.13, 24.13, 24.13, 24.13, 24.13, 'Finalizada', 'Produccion', 'Probando Laptop 2', '2026-07-23 20:25:12', '2026-07-23 20:25:14', '2026-07-24 00:25:12', '2026-07-24 00:25:14'),
(15, 1, 2, 1, 2.00, NULL, NULL, 35.00, 23.88, 23.88, 23.88, 23.88, 23.88, 'Finalizada', 'Produccion', 'asdasd', '2026-07-23 20:29:41', '2026-07-23 20:29:43', '2026-07-24 00:29:41', '2026-07-24 00:29:43'),
(16, 1, 1, 1, 3.00, NULL, NULL, 42.00, 23.88, 23.88, 23.88, 23.88, 23.88, 'Finalizada', 'Produccion', 'Prueba', '2026-07-23 20:30:14', '2026-07-23 20:30:15', '2026-07-24 00:30:14', '2026-07-24 00:30:15'),
(17, 1, 1, 1, 30.00, NULL, NULL, 50.00, 31.56, 31.56, 31.56, 31.56, 31.56, 'Finalizada', 'Produccion', 'Prueba', '2026-07-23 20:31:00', '2026-07-23 20:31:04', '2026-07-24 00:31:00', '2026-07-24 00:31:04'),
(18, 1, 1, 1, 2.00, NULL, NULL, 42.00, 32.19, 32.19, 32.19, 32.19, 32.19, 'Finalizada', 'Produccion', 'Probando', '2026-07-23 20:34:38', '2026-07-23 20:34:41', '2026-07-24 00:34:38', '2026-07-24 00:34:41'),
(19, 1, 1, 1, 4.00, NULL, NULL, 42.00, 28.69, 28.69, 28.69, 28.69, 28.69, 'Finalizada', 'Produccion', NULL, '2026-07-23 20:36:22', '2026-07-23 20:36:24', '2026-07-24 00:36:22', '2026-07-24 00:36:24'),
(20, 1, 1, 1, 3.00, NULL, NULL, 42.00, 26.94, 26.94, 26.94, 26.94, 26.94, 'Finalizada', 'Produccion', NULL, '2026-07-23 20:38:00', '2026-07-23 20:38:05', '2026-07-24 00:38:00', '2026-07-24 00:38:05'),
(21, 1, 1, 1, 4.00, NULL, NULL, 45.00, 28.63, 28.63, 28.63, 28.63, 28.63, 'Finalizada', 'Produccion', NULL, '2026-07-23 20:38:46', '2026-07-23 20:38:47', '2026-07-24 00:38:46', '2026-07-24 00:38:47'),
(22, 1, 1, 1, 1.00, NULL, NULL, 7.00, 25.75, 25.75, 25.75, 25.75, 25.75, 'Finalizada', 'Produccion', 'Prueba', '2026-07-23 20:53:28', '2026-07-23 20:53:29', '2026-07-24 00:53:28', '2026-07-24 00:53:29'),
(23, 1, 1, 1, 3.00, NULL, NULL, 45.00, 40.19, 40.19, 40.19, 40.19, 40.19, 'Finalizada', 'Produccion', 'Prueba', '2026-07-23 21:14:15', '2026-07-23 21:18:00', '2026-07-24 01:14:15', '2026-07-24 01:18:00'),
(24, 1, 5, 1, 2.00, NULL, NULL, 10.00, 36.31, 35.75, 35.75, 36.31, 36.08, 'Finalizada', 'Produccion', 'Prueba', '2026-07-23 21:29:52', '2026-07-23 21:31:43', '2026-07-24 01:29:52', '2026-07-24 01:31:43'),
(25, 1, 5, 1, 10.00, NULL, NULL, 10.00, 35.44, 28.56, 25.00, 35.44, 29.19, 'Finalizada', 'Produccion', NULL, '2026-07-23 21:32:31', '2026-07-23 21:36:31', '2026-07-24 01:32:31', '2026-07-24 01:36:31'),
(26, 2, 5, 1, 2.00, NULL, NULL, 10.00, 22.63, 22.63, 22.63, 22.63, 22.63, 'Finalizada', 'Produccion', 'Prueba Trabajador', '2026-07-27 21:13:51', '2026-07-27 21:14:40', '2026-07-28 01:13:51', '2026-07-28 01:14:40'),
(27, 2, 5, 1, 2.00, NULL, NULL, 20.00, 24.00, 20.00, 20.00, 34.75, 29.99, 'Finalizada', 'Produccion', NULL, '2026-07-27 21:27:43', '2026-07-27 21:32:10', '2026-07-28 01:27:43', '2026-07-28 01:32:10'),
(28, 1, 5, 1, 1.00, NULL, NULL, 10.00, 25.94, 27.25, 24.63, 28.50, 26.02, 'Finalizada', 'Produccion', 'Prueba', '2026-07-29 18:50:32', '2026-07-29 18:59:00', '2026-07-29 22:50:32', '2026-07-29 22:59:00'),
(29, 1, 5, 1, 1.00, NULL, NULL, 10.00, 25.56, 25.38, 25.38, 25.56, 25.45, 'Finalizada', 'Produccion', 'prueba', '2026-07-29 19:09:06', '2026-07-29 19:10:07', '2026-07-29 23:09:06', '2026-07-29 23:10:07'),
(30, 1, 5, 1, 1.00, NULL, NULL, 10.00, NULL, NULL, NULL, NULL, NULL, 'Finalizada', 'Produccion', 'ultima prueba', '2026-07-29 23:44:47', '2026-07-29 23:49:52', '2026-07-30 03:44:47', '2026-07-30 03:49:52'),
(31, 1, 1, 1, 10.00, NULL, NULL, 40.00, NULL, NULL, NULL, NULL, NULL, 'Finalizada', 'Produccion', 'Leche agarrada a las 7:00 am', '2026-08-02 00:03:14', '2026-08-02 00:04:25', '2026-08-02 04:03:14', '2026-08-02 04:04:25'),
(32, 1, 1, 1, 20.00, NULL, NULL, 42.00, 18.94, 18.94, 18.94, 18.94, 18.94, 'Finalizada', 'Produccion', NULL, '2026-08-12 15:56:43', '2026-08-12 15:57:02', '2026-08-12 19:56:43', '2026-08-12 19:57:02'),
(33, 1, 5, 1, 1.00, NULL, NULL, 20.00, 54.63, 19.94, -127.00, 57.06, 37.16, 'Finalizada', 'Produccion', 'Prueba', '2026-08-13 14:39:00', '2026-08-13 15:20:13', '2026-08-13 18:39:00', '2026-08-13 19:20:13'),
(34, 2, 6, 1, 1.00, NULL, NULL, 10.00, 19.88, 22.13, 18.69, 23.13, 20.94, 'Finalizada', 'Produccion', 'prueba ale', '2026-08-15 22:28:19', '2026-08-15 22:36:32', '2026-08-16 02:28:19', '2026-08-16 02:36:32'),
(35, 2, 5, 1, 2.00, NULL, NULL, 20.00, 37.00, 27.94, 27.94, 55.88, 40.44, 'Finalizada', 'Produccion', NULL, '2026-08-17 14:47:37', '2026-08-17 15:25:50', '2026-08-17 18:47:37', '2026-08-17 19:25:50'),
(36, 1, 5, 1, 2.88, NULL, NULL, 20.00, 44.69, 28.00, 28.00, 52.56, 38.90, 'Finalizada', 'Produccion', 'Prueba', '2026-08-18 21:22:09', '2026-08-18 22:00:35', '2026-08-19 01:22:09', '2026-08-19 02:00:35'),
(37, 1, 1, 1, 2.00, NULL, NULL, 41.00, 23.88, 23.88, 23.88, 23.88, 23.88, 'Finalizada', 'Produccion', 'Prueba', '2026-08-20 10:12:48', '2026-08-20 10:12:53', '2026-08-20 14:12:48', '2026-08-20 14:12:53'),
(38, 1, 1, 1, 2.00, NULL, NULL, 42.00, 51.75, 41.31, 41.31, 91.69, 69.05, 'Finalizada', 'Produccion', 'Prueba2', '2026-08-20 10:19:37', '2026-08-20 11:18:23', '2026-08-20 14:19:37', '2026-08-20 15:18:23'),
(39, 2, 1, 1, 2.00, NULL, NULL, 42.00, 34.13, 34.13, 34.13, 34.13, 34.13, 'Finalizada', 'Produccion', NULL, '2026-08-21 19:31:45', '2026-08-21 19:31:46', '2026-08-21 23:31:45', '2026-08-21 23:31:46'),
(40, 2, 1, 1, 2.00, NULL, NULL, 42.00, 36.44, 36.44, 36.44, 36.44, 36.44, 'Finalizada', 'Produccion', NULL, '2026-08-21 19:35:38', '2026-08-21 19:35:42', '2026-08-21 23:35:38', '2026-08-21 23:35:42'),
(41, 2, 1, 1, 2.00, NULL, NULL, 45.00, 36.69, 36.69, 36.69, 36.69, 36.69, 'Finalizada', 'Produccion', NULL, '2026-08-21 19:36:54', '2026-08-21 19:36:57', '2026-08-21 23:36:54', '2026-08-21 23:36:57'),
(42, 2, 2, 1, 2.00, NULL, NULL, 40.00, 38.75, 70.44, 38.75, 72.81, 56.83, 'Finalizada', 'Produccion', NULL, '2026-08-21 19:42:41', '2026-08-21 21:52:32', '2026-08-21 23:42:41', '2026-08-22 01:52:32'),
(43, 1, 1, 1, 40.00, NULL, NULL, 42.00, NULL, NULL, NULL, NULL, NULL, 'Finalizada', 'Produccion', 'Prueba', '2026-08-26 00:58:34', '2026-08-26 00:58:41', '2026-08-26 04:58:34', '2026-08-26 04:58:41'),
(44, 2, 1, 1, 40.00, NULL, NULL, 42.00, NULL, NULL, NULL, NULL, NULL, 'Finalizada', 'Produccion', NULL, '2026-08-26 01:06:14', '2026-08-26 09:05:04', '2026-08-26 05:06:14', '2026-08-26 13:05:04'),
(45, 1, 1, 1, 19.99, NULL, NULL, 85.00, NULL, NULL, NULL, NULL, NULL, 'Finalizada', 'Produccion', NULL, '2026-08-26 11:32:39', '2026-08-26 11:33:23', '2026-08-26 15:32:39', '2026-08-26 15:33:23'),
(46, 1, 1, 1, 20.00, NULL, NULL, 85.00, NULL, NULL, NULL, NULL, NULL, 'Finalizada', 'Produccion', NULL, '2026-08-26 11:39:40', '2026-08-26 11:40:01', '2026-08-26 15:39:40', '2026-08-26 15:40:01'),
(47, 1, 2, 1, 10.00, NULL, NULL, 63.00, NULL, NULL, NULL, NULL, NULL, 'Finalizada', 'Produccion', NULL, '2026-08-26 11:43:15', '2026-08-26 11:43:42', '2026-08-26 15:43:15', '2026-08-26 15:43:42'),
(48, 1, 7, 1, 1.00, NULL, NULL, 85.00, NULL, NULL, NULL, NULL, NULL, 'Finalizada', 'Produccion', NULL, '2026-08-26 11:48:41', '2026-08-26 12:23:11', '2026-08-26 15:48:41', '2026-08-26 16:23:11'),
(49, 2, 7, 1, 1.00, 'Pastillas prueba', 2.00, 85.00, NULL, NULL, NULL, NULL, NULL, 'Finalizada', 'Produccion', NULL, '2026-08-26 12:23:56', '2026-08-26 12:24:48', '2026-08-26 16:23:56', '2026-08-26 16:24:48'),
(50, 1, 7, 1, 1.00, 'Pastillas prueba', 2.00, 85.00, NULL, NULL, NULL, NULL, NULL, 'Finalizada', 'Produccion', NULL, '2026-08-26 12:48:24', '2026-08-26 12:49:06', '2026-08-26 16:48:24', '2026-08-26 16:49:06'),
(51, 2, 1, 1, 100.00, 'Lactobasilo y Estreptococos', 4500.00, 85.00, NULL, NULL, NULL, NULL, NULL, 'Finalizada', 'Produccion', NULL, '2026-09-03 21:33:35', '2026-09-03 21:33:42', '2026-09-04 01:33:35', '2026-09-04 01:33:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productores`
--

CREATE TABLE `productores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `legacy_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nombres` varchar(255) NOT NULL,
  `primer_apellido` varchar(255) NOT NULL,
  `segundo_apellido` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `nombre_unidad_productiva` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productores`
--

INSERT INTO `productores` (`id`, `legacy_user_id`, `nombres`, `primer_apellido`, `segundo_apellido`, `telefono`, `direccion`, `nombre_unidad_productiva`, `activo`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Adolfo', 'Olmos', 'Beltran', '78337071', 'Pucara', 'AdolfoOlmosBeltran', 1, '2026-07-23 18:04:05', '2026-09-10 22:33:13', '2026-09-10 22:33:13'),
(2, 2, 'Alejandro Manuel', 'Olmos', 'Beltran', '000000', 'Fortaleza', 'AlejandroManuel', 1, '2026-07-24 20:49:55', '2026-09-11 14:27:20', NULL),
(3, 3, 'Ana', 'Laura', 'Rosales', '73754913', 'Pucara', 'Rosalmos', 1, '2026-09-08 15:39:42', '2026-09-10 15:27:43', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `temperatura_pasteurizacion` decimal(5,2) NOT NULL DEFAULT 70.00,
  `descripcion` text DEFAULT NULL,
  `temperatura_minima` decimal(5,2) NOT NULL,
  `temperatura_maxima` decimal(5,2) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `imagen_referencial` varchar(255) DEFAULT NULL,
  `cuajo_por_litro` decimal(8,2) DEFAULT NULL,
  `unidad_cuajo` varchar(10) NOT NULL DEFAULT 'ml',
  `tipo_cuajo` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `temperatura_pasteurizacion`, `descripcion`, `temperatura_minima`, `temperatura_maxima`, `activo`, `created_at`, `updated_at`, `imagen_referencial`, `cuajo_por_litro`, `unidad_cuajo`, `tipo_cuajo`) VALUES
(1, 'Yogurt', 85.00, 'Yogurt espeso', 39.00, 42.00, 1, '2026-07-23 18:16:26', '2026-09-04 01:33:35', NULL, 45.00, 'ml', 'Lactobasilo y Estreptococos'),
(2, 'Queso Molde', 63.00, 'Queso Molde', 32.00, 35.00, 1, '2026-07-23 21:07:51', '2026-08-26 15:43:15', NULL, 2.00, 'pastilla', NULL),
(5, 'agua', 50.00, 'Prueba', 10.00, 28.00, 0, '2026-07-24 00:52:52', '2026-09-10 20:01:07', 'productos/jVCo9ExgXY1fxFWabYNzKfPHevnrqfDx9vVvJ6YH.png', 30.00, 'ml', NULL),
(6, 'agua2', 20.00, 'srstssrtrstre', 6.00, 10.00, 0, '2026-08-16 02:27:03', '2026-08-27 14:33:08', NULL, 10.00, 'ml', 'prueba cuajo'),
(7, 'Yogurt con Pastillas', 85.00, 'Yogurt Realizado con pastillas', 40.00, 45.00, 1, '2026-08-26 15:47:05', '2026-08-26 16:48:24', NULL, 2.00, 'pastilla', 'Pastillas prueba'),
(8, 'Queso Crema', 65.00, NULL, 15.00, 20.00, 1, '2026-09-10 20:02:23', '2026-09-10 20:02:23', NULL, 1.00, 'ml', 'Cuajo queso crema');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `produccion_id` bigint(20) UNSIGNED NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `fecha_generacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sensores`
--

CREATE TABLE `sensores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dispositivo_id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `tipo` enum('DS18B20','DHT22','Otro') NOT NULL DEFAULT 'DS18B20',
  `unidad` varchar(10) NOT NULL DEFAULT '°C',
  `estado` enum('Activo','Inactivo','Mantenimiento') NOT NULL DEFAULT 'Activo',
  `temperatura_actual` decimal(5,2) DEFAULT NULL,
  `numero_serie` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sensores`
--

INSERT INTO `sensores` (`id`, `dispositivo_id`, `nombre`, `tipo`, `unidad`, `estado`, `temperatura_actual`, `numero_serie`, `created_at`, `updated_at`) VALUES
(1, 1, 'Sensor Temperatura Leche', 'DS18B20', '°C', 'Inactivo', 27.25, NULL, '2026-07-09 10:58:23', '2026-09-08 21:03:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('86ZPOAYVXeEqbtZi3mXlOWfdjYJy5GqXivkFEwS5', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibWdIM3BNYUdOVTV2czRCbVBTdWtUM1VpbDdiSFBzWGZaQnA2NzBuaCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQvdGVtcGVyYXR1cmFzIjtzOjU6InJvdXRlIjtzOjIyOiJkYXNoYm9hcmQudGVtcGVyYXR1cmFzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1790105748);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tickets_venta`
--

CREATE TABLE `tickets_venta` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `clave` char(36) NOT NULL,
  `payload_hash` varchar(64) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `metodo_pago` enum('efectivo','qr','credito') NOT NULL,
  `cliente` varchar(255) DEFAULT NULL,
  `total` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tickets_venta`
--

INSERT INTO `tickets_venta` (`id`, `clave`, `payload_hash`, `user_id`, `metodo_pago`, `cliente`, `total`, `created_at`, `updated_at`) VALUES
(1, 'b4f13c43-b695-4527-a6a8-871c661fc865', '87af857de25ad2cb4b7a8d38209fd2a0068293a2064e4ebe56beb8a3c2b77bfc', 1, 'qr', 'Daniel', 166.00, '2026-09-17 19:54:02', '2026-09-17 19:54:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `nombre_unidad_productiva` varchar(255) DEFAULT NULL,
  `rol` enum('Administrador','Trabajador','Productor') NOT NULL DEFAULT 'Trabajador',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `telefono`, `direccion`, `nombre_unidad_productiva`, `rol`, `activo`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Pablo Adolfo Olmos Bentran', 'pabloadolfoolmosbeltran@gmail.com', NULL, '$2y$12$WE1lA4jkJMsN1rSnIFohku.zx.74Dsd5wCy.jiE7SgIy/C3RT0da2', '78337071', 'Pucara', 'PabloAdolfoOlmosBeltran', 'Administrador', 1, NULL, '2026-07-23 18:04:05', '2026-07-23 18:04:05'),
(2, 'Alejandro Manuel Olmos Beltran', 'alejandromanuelolmosbeltran@gmail.com', NULL, '$2y$12$EDL2GATQoky77mXBsmY3K.wd//hbepc6qTXTgkCaisUF6WmiWe/.C', '0000000', 'Fortaleza', 'AlejandroManuel', 'Trabajador', 1, NULL, '2026-07-24 20:49:55', '2026-08-28 11:57:21'),
(3, 'Ana Laura Rosales Padilla', 'analaurarosalespadilla@gmail.com', NULL, '$2y$12$V42Sr2OgcMD5IUkP.2qVY.9/JiFdZWPwZBvJA422YzjuFY8geS1AO', '73754913', 'Pucara', 'Rosalmos', 'Trabajador', 1, NULL, '2026-09-08 15:39:42', '2026-09-10 23:17:46'),
(7, 'admin', 'admin@gmail.com', NULL, '$2y$12$vlmSXGnL8AxmZKJVD5V60.jas.FWmfwKFLpVvQEWm21QhaDJtn1wC', '1234567890', 'admin', NULL, 'Administrador', 1, NULL, '2026-09-17 20:25:50', '2026-09-17 20:25:50'),
(8, 'trabajador', 'trabajador@gmail.com', NULL, '$2y$12$qQffK1NEc3IcnGaVc.rY2OH4opoOrD2O29Zb97qJn4t8M0W0VIXCy', '1234567890', 'trabajador', NULL, 'Trabajador', 1, NULL, '2026-09-17 20:26:25', '2026-09-17 20:26:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ingreso_productor_item_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad_vendida` int(10) UNSIGNED NOT NULL,
  `precio_unitario_venta` decimal(10,2) NOT NULL,
  `fecha_venta` timestamp NOT NULL DEFAULT current_timestamp(),
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ticket_venta_id` bigint(20) UNSIGNED DEFAULT NULL
) ;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `ingreso_productor_item_id`, `user_id`, `cantidad_vendida`, `precio_unitario_venta`, `fecha_venta`, `observaciones`, `created_at`, `updated_at`, `ticket_venta_id`) VALUES
(2, 2, 1, 1, 26.00, '2026-09-17 19:54:02', NULL, '2026-09-17 19:54:02', '2026-09-17 19:54:02', 1),
(3, 1, 1, 8, 17.50, '2026-09-17 19:54:02', NULL, '2026-09-17 19:54:02', '2026-09-17 19:54:02', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actuadores`
--
ALTER TABLE `actuadores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `actuadores_dispositivo_id_foreign` (`dispositivo_id`);

--
-- Indices de la tabla `ajustes_inventario`
--
ALTER TABLE `ajustes_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ajustes_inventario_creado_por_foreign` (`creado_por`),
  ADD KEY `ajustes_inventario_actualizado_por_foreign` (`actualizado_por`),
  ADD KEY `ajustes_inventario_anulado_por_foreign` (`anulado_por`),
  ADD KEY `ajustes_lote_estado_idx` (`lote_id`,`estado`),
  ADD KEY `ajustes_motivo_fecha_idx` (`tipo_motivo`,`created_at`);

--
-- Indices de la tabla `alertas`
--
ALTER TABLE `alertas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alertas_produccion_id_foreign` (`produccion_id`),
  ADD KEY `alertas_lectura_id_foreign` (`lectura_id`);

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indices de la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `descartes_productos`
--
ALTER TABLE `descartes_productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `descartes_productos_lote_id_foreign` (`lote_id`),
  ADD KEY `descartes_productos_creado_por_foreign` (`creado_por`),
  ADD KEY `descartes_productos_actualizado_por_foreign` (`actualizado_por`),
  ADD KEY `descartes_productos_procesado_por_foreign` (`procesado_por`),
  ADD KEY `descartes_estado_fecha_idx` (`estado`,`created_at`),
  ADD KEY `descartes_motivo_caducidad_idx` (`tipo_motivo`,`fecha_caducidad`);

--
-- Indices de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dispositivos_mac_address_unique` (`mac_address`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eventos_produccion_id_foreign` (`produccion_id`),
  ADD KEY `eventos_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `ingresos_productores`
--
ALTER TABLE `ingresos_productores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `consignaciones_user_id_estado_index` (`user_id`,`estado`),
  ADD KEY `ingresos_productor_estado_fecha_idx` (`productor_id`,`estado`,`fecha_ingreso`);

--
-- Indices de la tabla `ingresos_productores_items`
--
ALTER TABLE `ingresos_productores_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `consignacion_items_presentacion_id_foreign` (`presentacion_id`),
  ADD KEY `consignacion_items_consignacion_id_presentacion_id_index` (`ingreso_productor_id`,`presentacion_id`),
  ADD KEY `lotes_presentacion_vencimiento_saldo_idx` (`presentacion_id`,`fecha_caducidad`,`cantidad_disponible`),
  ADD KEY `lotes_recepcion_idx` (`fecha_recepcion`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `lecturas`
--
ALTER TABLE `lecturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lecturas_produccion_id_foreign` (`produccion_id`),
  ADD KEY `lecturas_sensor_id_foreign` (`sensor_id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movimientos_inventario_lote_id_foreign` (`lote_id`),
  ADD KEY `movimientos_inventario_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `presentaciones`
--
ALTER TABLE `presentaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `presentaciones_producto_id_foreign` (`producto_id`),
  ADD KEY `presentaciones_activo_index` (`activo`);

--
-- Indices de la tabla `producciones`
--
ALTER TABLE `producciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producciones_user_id_foreign` (`user_id`),
  ADD KEY `producciones_producto_id_foreign` (`producto_id`);

--
-- Indices de la tabla `productores`
--
ALTER TABLE `productores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `productores_legacy_user_id_unique` (`legacy_user_id`),
  ADD KEY `productores_activo_idx` (`activo`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `productos_activo_index` (`activo`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reportes_produccion_id_foreign` (`produccion_id`);

--
-- Indices de la tabla `sensores`
--
ALTER TABLE `sensores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sensores_dispositivo_id_foreign` (`dispositivo_id`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `tickets_venta`
--
ALTER TABLE `tickets_venta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tickets_venta_clave_unique` (`clave`),
  ADD KEY `tickets_venta_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_activo_index` (`activo`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ventas_user_id_foreign` (`user_id`),
  ADD KEY `ventas_consignacion_item_id_fecha_venta_index` (`ingreso_productor_item_id`,`fecha_venta`),
  ADD KEY `ventas_ticket_venta_id_foreign` (`ticket_venta_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actuadores`
--
ALTER TABLE `actuadores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ajustes_inventario`
--
ALTER TABLE `ajustes_inventario`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `alertas`
--
ALTER TABLE `alertas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `descartes_productos`
--
ALTER TABLE `descartes_productos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=633;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ingresos_productores`
--
ALTER TABLE `ingresos_productores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ingresos_productores_items`
--
ALTER TABLE `ingresos_productores_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lecturas`
--
ALTER TABLE `lecturas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `presentaciones`
--
ALTER TABLE `presentaciones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `producciones`
--
ALTER TABLE `producciones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT de la tabla `productores`
--
ALTER TABLE `productores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sensores`
--
ALTER TABLE `sensores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tickets_venta`
--
ALTER TABLE `tickets_venta`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actuadores`
--
ALTER TABLE `actuadores`
  ADD CONSTRAINT `actuadores_dispositivo_id_foreign` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ajustes_inventario`
--
ALTER TABLE `ajustes_inventario`
  ADD CONSTRAINT `ajustes_inventario_actualizado_por_foreign` FOREIGN KEY (`actualizado_por`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ajustes_inventario_anulado_por_foreign` FOREIGN KEY (`anulado_por`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ajustes_inventario_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ajustes_inventario_lote_id_foreign` FOREIGN KEY (`lote_id`) REFERENCES `ingresos_productores_items` (`id`);

--
-- Filtros para la tabla `alertas`
--
ALTER TABLE `alertas`
  ADD CONSTRAINT `alertas_lectura_id_foreign` FOREIGN KEY (`lectura_id`) REFERENCES `lecturas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `alertas_produccion_id_foreign` FOREIGN KEY (`produccion_id`) REFERENCES `producciones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `descartes_productos`
--
ALTER TABLE `descartes_productos`
  ADD CONSTRAINT `descartes_productos_actualizado_por_foreign` FOREIGN KEY (`actualizado_por`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `descartes_productos_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `descartes_productos_lote_id_foreign` FOREIGN KEY (`lote_id`) REFERENCES `ingresos_productores_items` (`id`),
  ADD CONSTRAINT `descartes_productos_procesado_por_foreign` FOREIGN KEY (`procesado_por`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `eventos_produccion_id_foreign` FOREIGN KEY (`produccion_id`) REFERENCES `producciones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `eventos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `ingresos_productores`
--
ALTER TABLE `ingresos_productores`
  ADD CONSTRAINT `consignaciones_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ingresos_productores_productor_id_foreign` FOREIGN KEY (`productor_id`) REFERENCES `productores` (`id`);

--
-- Filtros para la tabla `ingresos_productores_items`
--
ALTER TABLE `ingresos_productores_items`
  ADD CONSTRAINT `consignacion_items_consignacion_id_foreign` FOREIGN KEY (`ingreso_productor_id`) REFERENCES `ingresos_productores` (`id`),
  ADD CONSTRAINT `consignacion_items_presentacion_id_foreign` FOREIGN KEY (`presentacion_id`) REFERENCES `presentaciones` (`id`);

--
-- Filtros para la tabla `lecturas`
--
ALTER TABLE `lecturas`
  ADD CONSTRAINT `lecturas_produccion_id_foreign` FOREIGN KEY (`produccion_id`) REFERENCES `producciones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lecturas_sensor_id_foreign` FOREIGN KEY (`sensor_id`) REFERENCES `sensores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `movimientos_inventario_lote_id_foreign` FOREIGN KEY (`lote_id`) REFERENCES `ingresos_productores_items` (`id`),
  ADD CONSTRAINT `movimientos_inventario_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `presentaciones`
--
ALTER TABLE `presentaciones`
  ADD CONSTRAINT `presentaciones_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `producciones`
--
ALTER TABLE `producciones`
  ADD CONSTRAINT `producciones_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `producciones_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `productores`
--
ALTER TABLE `productores`
  ADD CONSTRAINT `productores_legacy_user_id_foreign` FOREIGN KEY (`legacy_user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `reportes_produccion_id_foreign` FOREIGN KEY (`produccion_id`) REFERENCES `producciones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `sensores`
--
ALTER TABLE `sensores`
  ADD CONSTRAINT `sensores_dispositivo_id_foreign` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tickets_venta`
--
ALTER TABLE `tickets_venta`
  ADD CONSTRAINT `tickets_venta_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_consignacion_item_id_foreign` FOREIGN KEY (`ingreso_productor_item_id`) REFERENCES `ingresos_productores_items` (`id`),
  ADD CONSTRAINT `ventas_ticket_venta_id_foreign` FOREIGN KEY (`ticket_venta_id`) REFERENCES `tickets_venta` (`id`),
  ADD CONSTRAINT `ventas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
