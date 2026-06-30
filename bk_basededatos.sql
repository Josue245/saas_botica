-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.3 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para saas_botica
CREATE DATABASE IF NOT EXISTS `saas_botica` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `saas_botica`;

-- Volcando estructura para tabla saas_botica.ajuste_inventarios
CREATE TABLE IF NOT EXISTS `ajuste_inventarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `tipo` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad` int NOT NULL,
  `stock_anterior` int NOT NULL,
  `stock_nuevo` int NOT NULL,
  `motivo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ajuste_inventarios_producto_id_foreign` (`producto_id`),
  KEY `ajuste_inventarios_user_id_foreign` (`user_id`),
  CONSTRAINT `ajuste_inventarios_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ajuste_inventarios_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.ajuste_inventarios: ~0 rows (aproximadamente)
DELETE FROM `ajuste_inventarios`;

-- Volcando estructura para tabla saas_botica.auditorias
CREATE TABLE IF NOT EXISTS `auditorias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `accion` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modelo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modelo_id` bigint unsigned DEFAULT NULL,
  `descripcion` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `auditorias_user_id_foreign` (`user_id`),
  KEY `auditorias_modelo_modelo_id_index` (`modelo`,`modelo_id`),
  CONSTRAINT `auditorias_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.auditorias: ~0 rows (aproximadamente)
DELETE FROM `auditorias`;
INSERT INTO `auditorias` (`id`, `user_id`, `accion`, `modelo`, `modelo_id`, `descripcion`, `ip`, `created_at`, `updated_at`) VALUES
	(1, 1, 'inició sesión', 'Sesión', 1, 'Acceso al sistema', '127.0.0.1', '2026-06-16 04:48:21', '2026-06-16 04:48:21');

-- Volcando estructura para tabla saas_botica.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.cache: ~0 rows (aproximadamente)
DELETE FROM `cache`;

-- Volcando estructura para tabla saas_botica.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.cache_locks: ~0 rows (aproximadamente)
DELETE FROM `cache_locks`;

-- Volcando estructura para tabla saas_botica.caja_movimientos
CREATE TABLE IF NOT EXISTS `caja_movimientos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `caja_sesion_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `tipo` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `concepto` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `caja_movimientos_caja_sesion_id_foreign` (`caja_sesion_id`),
  KEY `caja_movimientos_user_id_foreign` (`user_id`),
  CONSTRAINT `caja_movimientos_caja_sesion_id_foreign` FOREIGN KEY (`caja_sesion_id`) REFERENCES `caja_sesiones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `caja_movimientos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.caja_movimientos: ~0 rows (aproximadamente)
DELETE FROM `caja_movimientos`;

-- Volcando estructura para tabla saas_botica.caja_sesiones
CREATE TABLE IF NOT EXISTS `caja_sesiones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `cerrado_por` bigint unsigned DEFAULT NULL,
  `monto_inicial` decimal(12,2) NOT NULL DEFAULT '0.00',
  `monto_esperado` decimal(12,2) DEFAULT NULL,
  `monto_final` decimal(12,2) DEFAULT NULL,
  `diferencia` decimal(12,2) DEFAULT NULL,
  `estado` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'abierta',
  `observacion` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `abierta_at` timestamp NULL DEFAULT NULL,
  `cerrada_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `caja_sesiones_user_id_foreign` (`user_id`),
  KEY `caja_sesiones_cerrado_por_foreign` (`cerrado_por`),
  CONSTRAINT `caja_sesiones_cerrado_por_foreign` FOREIGN KEY (`cerrado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `caja_sesiones_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.caja_sesiones: ~0 rows (aproximadamente)
DELETE FROM `caja_sesiones`;

-- Volcando estructura para tabla saas_botica.categorias
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.categorias: ~8 rows (aproximadamente)
DELETE FROM `categorias`;
INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 'Analgésicos', NULL, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(2, 'Antibióticos', NULL, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(3, 'Antigripales', NULL, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(4, 'Vitaminas y Suplementos', NULL, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(5, 'Dermatológicos', NULL, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(6, 'Gastrointestinales', NULL, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(7, 'Cardiovascular', NULL, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(8, 'Cuidado Personal', NULL, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34');

-- Volcando estructura para tabla saas_botica.clientes
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_documento` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DNI',
  `numero_documento` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `puntos` int NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clientes_numero_documento_index` (`numero_documento`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.clientes: ~6 rows (aproximadamente)
DELETE FROM `clientes`;
INSERT INTO `clientes` (`id`, `nombre`, `tipo_documento`, `numero_documento`, `telefono`, `email`, `direccion`, `puntos`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 'Cliente Varios', 'DNI', '00000000', NULL, NULL, NULL, 103, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(2, 'Rosa Huamán Vega', 'DNI', '45678912', NULL, NULL, NULL, 109, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(3, 'José Mendoza Ríos', 'DNI', '40123456', NULL, NULL, NULL, 69, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(4, 'Comercial San Juan S.A.C.', 'RUC', '20601234567', NULL, NULL, NULL, 28, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(5, 'Ana Torres Salas', 'DNI', '70654321', NULL, NULL, NULL, 83, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(6, 'Luis Paredes Gómez', 'DNI', '41239876', NULL, NULL, NULL, 14, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34');

-- Volcando estructura para tabla saas_botica.compras
CREATE TABLE IF NOT EXISTS `compras` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero_documento` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proveedor_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `igv` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `estado` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'recibida',
  `estado_pago` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `observacion` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `compras_proveedor_id_foreign` (`proveedor_id`),
  KEY `compras_user_id_foreign` (`user_id`),
  KEY `compras_numero_documento_index` (`numero_documento`),
  CONSTRAINT `compras_proveedor_id_foreign` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL,
  CONSTRAINT `compras_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.compras: ~0 rows (aproximadamente)
DELETE FROM `compras`;

-- Volcando estructura para tabla saas_botica.compra_detalles
CREATE TABLE IF NOT EXISTS `compra_detalles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `compra_id` bigint unsigned NOT NULL,
  `producto_id` bigint unsigned DEFAULT NULL,
  `descripcion` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cantidad` int NOT NULL DEFAULT '1',
  `precio_compra` decimal(10,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `lote` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `compra_detalles_compra_id_foreign` (`compra_id`),
  KEY `compra_detalles_producto_id_foreign` (`producto_id`),
  CONSTRAINT `compra_detalles_compra_id_foreign` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  CONSTRAINT `compra_detalles_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.compra_detalles: ~0 rows (aproximadamente)
DELETE FROM `compra_detalles`;

-- Volcando estructura para tabla saas_botica.configuraciones
CREATE TABLE IF NOT EXISTS `configuraciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `configuraciones_clave_unique` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.configuraciones: ~0 rows (aproximadamente)
DELETE FROM `configuraciones`;

-- Volcando estructura para tabla saas_botica.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.failed_jobs: ~0 rows (aproximadamente)
DELETE FROM `failed_jobs`;

-- Volcando estructura para tabla saas_botica.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.jobs: ~0 rows (aproximadamente)
DELETE FROM `jobs`;

-- Volcando estructura para tabla saas_botica.job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.job_batches: ~0 rows (aproximadamente)
DELETE FROM `job_batches`;

-- Volcando estructura para tabla saas_botica.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.migrations: ~15 rows (aproximadamente)
DELETE FROM `migrations`;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2026_01_01_100000_create_categorias_table', 1),
	(5, '2026_01_01_100001_create_proveedores_table', 1),
	(6, '2026_01_01_100002_create_productos_table', 1),
	(7, '2026_01_01_100003_create_clientes_table', 1),
	(8, '2026_01_01_100004_create_ventas_table', 1),
	(9, '2026_01_01_100005_create_venta_detalles_table', 1),
	(10, '2026_01_01_100006_create_compras_table', 2),
	(11, '2026_01_01_100007_create_compra_detalles_table', 2),
	(12, '2026_01_01_100008_create_ajuste_inventarios_table', 3),
	(13, '2026_01_01_100009_create_configuraciones_table', 4),
	(14, '2026_01_01_100010_create_caja_sesiones_table', 5),
	(15, '2026_01_01_100011_create_caja_movimientos_table', 5),
	(16, '2026_01_01_100012_create_auditorias_table', 5);

-- Volcando estructura para tabla saas_botica.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.password_reset_tokens: ~0 rows (aproximadamente)
DELETE FROM `password_reset_tokens`;

-- Volcando estructura para tabla saas_botica.productos
CREATE TABLE IF NOT EXISTS `productos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo_barras` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `principio_activo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `presentacion` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `concentracion` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categoria_id` bigint unsigned DEFAULT NULL,
  `proveedor_id` bigint unsigned DEFAULT NULL,
  `laboratorio` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precio_compra` decimal(10,2) NOT NULL DEFAULT '0.00',
  `precio_venta` decimal(10,2) NOT NULL DEFAULT '0.00',
  `stock` int NOT NULL DEFAULT '0',
  `stock_minimo` int NOT NULL DEFAULT '10',
  `lote` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `requiere_receta` tinyint(1) NOT NULL DEFAULT '0',
  `controlado` tinyint(1) NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `productos_categoria_id_foreign` (`categoria_id`),
  KEY `productos_proveedor_id_foreign` (`proveedor_id`),
  KEY `productos_codigo_barras_index` (`codigo_barras`),
  CONSTRAINT `productos_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `productos_proveedor_id_foreign` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.productos: ~16 rows (aproximadamente)
DELETE FROM `productos`;
INSERT INTO `productos` (`id`, `codigo_barras`, `nombre`, `principio_activo`, `presentacion`, `concentracion`, `categoria_id`, `proveedor_id`, `laboratorio`, `precio_compra`, `precio_venta`, `stock`, `stock_minimo`, `lote`, `fecha_vencimiento`, `requiere_receta`, `controlado`, `activo`, `created_at`, `updated_at`) VALUES
	(1, '7750000000001', 'Paracetamol 500mg', 'Paracetamol', 'Tableta', '500mg', 1, 1, 'Genfar', 0.20, 0.50, 480, 50, 'L-2401', '2027-12-15', 0, 0, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(2, '7750000000002', 'Ibuprofeno 400mg', 'Ibuprofeno', 'Tableta', '400mg', 1, 2, 'Bayer', 0.30, 0.80, 320, 40, 'L-2402', '2027-08-15', 0, 0, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(3, '7750000000003', 'Aspirina 100mg', 'Ácido acetilsalicílico', 'Tableta', '100mg', 7, 3, 'Medifarma', 0.25, 0.60, 210, 30, 'L-2403', '2027-04-15', 0, 0, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(4, '7750000000004', 'Amoxicilina 500mg', 'Amoxicilina', 'Cápsula', '500mg', 2, 1, 'Hersil', 0.40, 1.20, 150, 30, 'L-2404', '2027-02-15', 1, 0, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(5, '7750000000005', 'Azitromicina 500mg', 'Azitromicina', 'Tableta', '500mg', 2, 2, 'GSK', 1.50, 3.50, 60, 20, 'L-2405', '2026-11-15', 1, 0, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(6, '7750000000006', 'Panadol Antigripal', 'Paracetamol + Clorfenamina', 'Tableta', '500mg', 3, 3, 'Genfar', 0.60, 1.50, 90, 25, 'L-2406', '2026-08-15', 0, 0, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(7, '7750000000007', 'Sal de Andrews', 'Bicarbonato + Ác. cítrico', 'Sobre', '5g', 6, 1, 'Bayer', 0.40, 1.00, 200, 30, 'L-2407', '2027-06-15', 0, 0, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(8, '7750000000008', 'Vitamina C 1g', 'Ácido ascórbico', 'Tableta efervescente', '1g', 4, 2, 'Medifarma', 0.50, 1.30, 140, 25, 'L-2408', '2027-10-15', 0, 0, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(9, '7750000000009', 'Complejo B', 'Vitaminas B', 'Tableta', '-', 4, 3, 'Hersil', 0.70, 1.80, 75, 20, 'L-2409', '2028-02-15', 0, 0, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(10, '7750000000010', 'Loratadina 10mg', 'Loratadina', 'Tableta', '10mg', 3, 1, 'GSK', 0.30, 0.90, 18, 25, 'L-2410', '2027-03-15', 0, 0, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(11, '7750000000011', 'Omeprazol 20mg', 'Omeprazol', 'Cápsula', '20mg', 6, 2, 'Genfar', 0.35, 1.00, 130, 30, 'L-2411', '2027-05-15', 0, 0, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(12, '7750000000012', 'Crema Hidratante Cerave', 'Cosmético', 'Crema', '236ml', 5, 3, 'Bayer', 18.00, 39.90, 22, 8, 'L-2412', '2028-06-15', 0, 0, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(13, '7750000000013', 'Protector Solar FPS50', 'Cosmético', 'Loción', '60ml', 8, 1, 'Medifarma', 22.00, 45.00, 14, 6, 'L-2413', '2028-04-15', 0, 0, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(14, '7750000000014', 'Alcohol en gel 250ml', 'Etanol', 'Gel', '250ml', 8, 2, 'Hersil', 3.00, 6.50, 5, 15, 'L-2414', '2026-05-26', 0, 0, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(15, '7750000000015', 'Enalapril 10mg', 'Enalapril', 'Tableta', '10mg', 7, 3, 'GSK', 0.30, 0.85, 95, 25, 'L-2415', '2026-07-15', 0, 0, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(16, '7750000000016', 'Metformina 850mg', 'Metformina', 'Tableta', '850mg', 6, 1, 'Genfar', 0.25, 0.70, 160, 30, 'L-2416', '2027-09-15', 0, 0, 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34');

-- Volcando estructura para tabla saas_botica.proveedores
CREATE TABLE IF NOT EXISTS `proveedores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `razon_social` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruc` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contacto` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `condicion_pago` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `proveedores_ruc_unique` (`ruc`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.proveedores: ~3 rows (aproximadamente)
DELETE FROM `proveedores`;
INSERT INTO `proveedores` (`id`, `razon_social`, `ruc`, `contacto`, `telefono`, `email`, `direccion`, `condicion_pago`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 'Distribuidora Farma Perú S.A.C.', '20512345671', 'Jorge Díaz', '014567890', NULL, NULL, 'Crédito 30 días', 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(2, 'Laboratorios Andinos S.A.', '20498765432', 'María Quispe', '014561122', NULL, NULL, 'Contado', 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(3, 'Drogería Salud Total E.I.R.L.', '20587654329', 'Pedro Soto', '013349988', NULL, NULL, 'Crédito 15 días', 1, '2026-06-15 18:43:34', '2026-06-15 18:43:34');

-- Volcando estructura para tabla saas_botica.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.sessions: ~0 rows (aproximadamente)
DELETE FROM `sessions`;

-- Volcando estructura para tabla saas_botica.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'vendedor',
  `telefono` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.users: ~2 rows (aproximadamente)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `rol`, `telefono`, `activo`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, 'Administrador', 'admin@mibotica.test', NULL, '$2y$12$xTakgU8dCTo.pHTTVuJKuORznttBhramSiXfLMJ8QkINUaWeut8nC', 'admin', '987654321', 1, NULL, '2026-06-15 18:43:33', '2026-06-15 18:43:33'),
	(2, 'Lucía Farfán', 'farmaceutico@mibotica.test', NULL, '$2y$12$ZdtaOrPzy4T9exaZeE8fXu3FN.qpHQav0877OCn38uXwhBvl29uDm', 'farmaceutico', '912345678', 1, NULL, '2026-06-15 18:43:34', '2026-06-15 18:43:34'),
	(3, 'Carlos Ramos', 'cajero@mibotica.test', NULL, '$2y$12$WoeaK8Juc0tQELMRQKsmvO9q0R1Gf36M5QYTeKeiC0xx4csvQct8.', 'cajero', '900112233', 1, NULL, '2026-06-15 18:43:34', '2026-06-15 18:43:34');

-- Volcando estructura para tabla saas_botica.ventas
CREATE TABLE IF NOT EXISTS `ventas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero_comprobante` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_comprobante` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'boleta',
  `cliente_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `igv` decimal(10,2) NOT NULL DEFAULT '0.00',
  `descuento` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `metodo_pago` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Efectivo',
  `estado` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pagada',
  `con_receta` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ventas_cliente_id_foreign` (`cliente_id`),
  KEY `ventas_user_id_foreign` (`user_id`),
  KEY `ventas_numero_comprobante_index` (`numero_comprobante`),
  CONSTRAINT `ventas_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ventas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=156 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.ventas: ~155 rows (aproximadamente)
DELETE FROM `ventas`;
INSERT INTO `ventas` (`id`, `numero_comprobante`, `tipo_comprobante`, `cliente_id`, `user_id`, `subtotal`, `igv`, `descuento`, `total`, `metodo_pago`, `estado`, `con_receta`, `created_at`, `updated_at`) VALUES
	(1, 'B001-000001', 'boleta', 2, 1, 28.77, 5.18, 0.00, 33.95, 'Efectivo', 'pagada', 1, '2026-06-02 18:02:00', '2026-06-15 18:43:34'),
	(2, 'B001-000002', 'boleta', 4, 2, 3.39, 0.61, 0.00, 4.00, 'Tarjeta', 'pagada', 0, '2026-06-02 16:59:00', '2026-06-15 18:43:34'),
	(3, 'B001-000003', 'boleta', 4, 3, 1.78, 0.32, 0.00, 2.10, 'Efectivo', 'pagada', 0, '2026-06-02 15:27:00', '2026-06-15 18:43:34'),
	(4, 'B001-000004', 'boleta', 1, 3, 4.62, 0.83, 0.00, 5.45, 'Plin', 'pagada', 1, '2026-06-02 20:19:00', '2026-06-15 18:43:34'),
	(5, 'B001-000005', 'boleta', 6, 3, 6.10, 1.10, 0.00, 7.20, 'Efectivo', 'pagada', 0, '2026-06-02 20:33:00', '2026-06-15 18:43:34'),
	(6, 'B001-000006', 'boleta', 5, 2, 2.88, 0.52, 0.00, 3.40, 'Yape', 'pagada', 0, '2026-06-02 13:08:00', '2026-06-15 18:43:34'),
	(7, 'B001-000007', 'boleta', 4, 3, 3.60, 0.65, 0.00, 4.25, 'Yape', 'pagada', 0, '2026-06-02 17:29:00', '2026-06-15 18:43:34'),
	(8, 'B001-000008', 'boleta', 1, 3, 1.36, 0.24, 0.00, 1.60, 'Transferencia', 'pagada', 0, '2026-06-02 21:23:00', '2026-06-15 18:43:34'),
	(9, 'B001-000009', 'boleta', 5, 1, 11.44, 2.06, 0.00, 13.50, 'Efectivo', 'pagada', 0, '2026-06-03 16:20:00', '2026-06-15 18:43:34'),
	(10, 'B001-000010', 'boleta', 5, 3, 2.12, 0.38, 0.00, 2.50, 'Transferencia', 'pagada', 0, '2026-06-03 15:23:00', '2026-06-15 18:43:34'),
	(11, 'B001-000011', 'boleta', 2, 2, 5.30, 0.95, 0.00, 6.25, 'Tarjeta', 'pagada', 0, '2026-06-03 14:25:00', '2026-06-15 18:43:34'),
	(12, 'B001-000012', 'boleta', 3, 1, 9.19, 1.66, 0.00, 10.85, 'Efectivo', 'pagada', 0, '2026-06-03 13:27:00', '2026-06-15 18:43:34'),
	(13, 'B001-000013', 'boleta', 1, 3, 139.49, 25.11, 0.00, 164.60, 'Efectivo', 'pagada', 0, '2026-06-03 15:32:00', '2026-06-15 18:43:34'),
	(14, 'B001-000014', 'boleta', 4, 1, 12.80, 2.30, 0.00, 15.10, 'Efectivo', 'pagada', 0, '2026-06-03 16:39:00', '2026-06-15 18:43:34'),
	(15, 'B001-000015', 'boleta', 6, 3, 21.27, 3.83, 0.00, 25.10, 'Efectivo', 'pagada', 0, '2026-06-03 16:10:00', '2026-06-15 18:43:34'),
	(16, 'B001-000016', 'boleta', 1, 2, 44.41, 7.99, 0.00, 52.40, 'Transferencia', 'pagada', 0, '2026-06-03 16:04:00', '2026-06-15 18:43:34'),
	(17, 'B001-000017', 'boleta', 6, 3, 0.85, 0.15, 0.00, 1.00, 'Efectivo', 'pagada', 0, '2026-06-03 23:19:00', '2026-06-15 18:43:34'),
	(18, 'B001-000018', 'boleta', 4, 3, 19.07, 3.43, 0.00, 22.50, 'Efectivo', 'pagada', 0, '2026-06-03 19:59:00', '2026-06-15 18:43:34'),
	(19, 'B001-000019', 'boleta', 5, 1, 13.26, 2.39, 0.00, 15.65, 'Yape', 'pagada', 0, '2026-06-04 00:14:00', '2026-06-15 18:43:34'),
	(20, 'B001-000020', 'boleta', 1, 2, 13.90, 2.50, 0.00, 16.40, 'Plin', 'pagada', 0, '2026-06-03 21:40:00', '2026-06-15 18:43:34'),
	(21, 'B001-000021', 'boleta', 5, 3, 20.76, 3.74, 0.00, 24.50, 'Transferencia', 'pagada', 0, '2026-06-03 23:44:00', '2026-06-15 18:43:34'),
	(22, 'B001-000022', 'boleta', 6, 1, 38.14, 6.86, 0.00, 45.00, 'Yape', 'pagada', 1, '2026-06-03 22:38:00', '2026-06-15 18:43:34'),
	(23, 'B001-000023', 'boleta', 5, 2, 137.88, 24.82, 0.00, 162.70, 'Transferencia', 'pagada', 1, '2026-06-03 17:26:00', '2026-06-15 18:43:34'),
	(24, 'B001-000024', 'boleta', 3, 3, 22.88, 4.12, 0.00, 27.00, 'Efectivo', 'pagada', 0, '2026-06-04 20:42:00', '2026-06-15 18:43:34'),
	(25, 'B001-000025', 'boleta', 2, 3, 46.95, 8.45, 0.00, 55.40, 'Plin', 'pagada', 1, '2026-06-04 17:36:00', '2026-06-15 18:43:34'),
	(26, 'B001-000026', 'boleta', 5, 1, 0.85, 0.15, 0.00, 1.00, 'Transferencia', 'pagada', 0, '2026-06-04 23:07:00', '2026-06-15 18:43:34'),
	(27, 'B001-000027', 'boleta', 1, 2, 10.00, 1.80, 0.00, 11.80, 'Yape', 'pagada', 1, '2026-06-04 21:05:00', '2026-06-15 18:43:34'),
	(28, 'B001-000028', 'boleta', 4, 2, 7.20, 1.30, 0.00, 8.50, 'Tarjeta', 'pagada', 0, '2026-06-04 23:53:00', '2026-06-15 18:43:34'),
	(29, 'B001-000029', 'boleta', 6, 2, 1.44, 0.26, 0.00, 1.70, 'Tarjeta', 'pagada', 0, '2026-06-04 14:56:00', '2026-06-15 18:43:34'),
	(30, 'B001-000030', 'boleta', 3, 2, 35.08, 6.32, 0.00, 41.40, 'Yape', 'pagada', 1, '2026-06-04 13:43:00', '2026-06-15 18:43:34'),
	(31, 'B001-000031', 'boleta', 6, 1, 84.15, 15.15, 0.00, 99.30, 'Efectivo', 'pagada', 1, '2026-06-04 22:43:00', '2026-06-15 18:43:34'),
	(32, 'B001-000032', 'boleta', 4, 1, 123.73, 22.27, 0.00, 146.00, 'Yape', 'pagada', 1, '2026-06-04 18:36:00', '2026-06-15 18:43:34'),
	(33, 'B001-000033', 'boleta', 3, 2, 7.63, 1.37, 0.00, 9.00, 'Plin', 'pagada', 1, '2026-06-04 13:51:00', '2026-06-15 18:43:34'),
	(34, 'B001-000034', 'boleta', 2, 2, 110.42, 19.88, 0.00, 130.30, 'Transferencia', 'pagada', 0, '2026-06-04 16:06:00', '2026-06-15 18:43:35'),
	(35, 'B001-000035', 'boleta', 1, 2, 4.92, 0.88, 0.00, 5.80, 'Efectivo', 'pagada', 1, '2026-06-04 18:32:00', '2026-06-15 18:43:35'),
	(36, 'B001-000036', 'boleta', 3, 2, 5.93, 1.07, 0.00, 7.00, 'Efectivo', 'pagada', 0, '2026-06-04 14:30:00', '2026-06-15 18:43:35'),
	(37, 'B001-000037', 'boleta', 4, 1, 2.80, 0.50, 0.00, 3.30, 'Tarjeta', 'pagada', 0, '2026-06-04 15:10:00', '2026-06-15 18:43:35'),
	(38, 'B001-000038', 'boleta', 4, 2, 4.83, 0.87, 0.00, 5.70, 'Tarjeta', 'pagada', 0, '2026-06-04 18:26:00', '2026-06-15 18:43:35'),
	(39, 'B001-000039', 'boleta', 3, 1, 5.51, 0.99, 0.00, 6.50, 'Transferencia', 'pagada', 1, '2026-06-04 23:55:00', '2026-06-15 18:43:35'),
	(40, 'B001-000040', 'boleta', 3, 3, 5.08, 0.92, 0.00, 6.00, 'Efectivo', 'pagada', 1, '2026-06-06 00:39:00', '2026-06-15 18:43:35'),
	(41, 'B001-000041', 'boleta', 1, 3, 2.12, 0.38, 0.00, 2.50, 'Tarjeta', 'pagada', 0, '2026-06-05 17:29:00', '2026-06-15 18:43:35'),
	(42, 'B001-000042', 'boleta', 5, 3, 1.02, 0.18, 0.00, 1.20, 'Plin', 'pagada', 0, '2026-06-05 14:09:00', '2026-06-15 18:43:35'),
	(43, 'B001-000043', 'boleta', 1, 3, 10.59, 1.91, 0.00, 12.50, 'Yape', 'pagada', 0, '2026-06-05 21:11:00', '2026-06-15 18:43:35'),
	(44, 'B001-000044', 'boleta', 6, 2, 3.31, 0.59, 0.00, 3.90, 'Transferencia', 'pagada', 0, '2026-06-05 19:20:00', '2026-06-15 18:43:35'),
	(45, 'B001-000045', 'boleta', 6, 1, 22.63, 4.07, 0.00, 26.70, 'Transferencia', 'pagada', 1, '2026-06-05 22:21:00', '2026-06-15 18:43:35'),
	(46, 'B001-000046', 'boleta', 6, 1, 13.81, 2.49, 0.00, 16.30, 'Tarjeta', 'pagada', 0, '2026-06-05 18:35:00', '2026-06-15 18:43:35'),
	(47, 'B001-000047', 'boleta', 2, 2, 2.16, 0.39, 0.00, 2.55, 'Efectivo', 'pagada', 0, '2026-06-05 23:14:00', '2026-06-15 18:43:35'),
	(48, 'B001-000048', 'boleta', 2, 2, 3.81, 0.69, 0.00, 4.50, 'Plin', 'pagada', 0, '2026-06-05 21:36:00', '2026-06-15 18:43:35'),
	(49, 'B001-000049', 'boleta', 6, 1, 127.63, 22.97, 0.00, 150.60, 'Efectivo', 'pagada', 0, '2026-06-07 00:18:00', '2026-06-15 18:43:35'),
	(50, 'B001-000050', 'boleta', 5, 3, 17.46, 3.14, 0.00, 20.60, 'Plin', 'pagada', 0, '2026-06-07 00:00:00', '2026-06-15 18:43:35'),
	(51, 'B001-000051', 'boleta', 1, 2, 1.19, 0.21, 0.00, 1.40, 'Tarjeta', 'pagada', 0, '2026-06-06 22:01:00', '2026-06-15 18:43:35'),
	(52, 'B001-000052', 'boleta', 6, 3, 4.24, 0.76, 0.00, 5.00, 'Yape', 'pagada', 0, '2026-06-07 00:17:00', '2026-06-15 18:43:35'),
	(53, 'B001-000053', 'boleta', 6, 2, 2.97, 0.53, 0.00, 3.50, 'Efectivo', 'pagada', 0, '2026-06-06 23:21:00', '2026-06-15 18:43:35'),
	(54, 'B001-000054', 'boleta', 5, 2, 43.47, 7.83, 0.00, 51.30, 'Efectivo', 'pagada', 1, '2026-06-06 19:35:00', '2026-06-15 18:43:35'),
	(55, 'B001-000055', 'boleta', 4, 2, 3.05, 0.55, 0.00, 3.60, 'Plin', 'pagada', 0, '2026-06-06 15:34:00', '2026-06-15 18:43:35'),
	(56, 'B001-000056', 'boleta', 3, 1, 2.97, 0.53, 0.00, 3.50, 'Efectivo', 'pagada', 0, '2026-06-07 20:37:00', '2026-06-15 18:43:35'),
	(57, 'B001-000057', 'boleta', 2, 3, 4.24, 0.76, 0.00, 5.00, 'Efectivo', 'pagada', 0, '2026-06-07 15:25:00', '2026-06-15 18:43:35'),
	(58, 'B001-000058', 'boleta', 3, 2, 114.41, 20.59, 0.00, 135.00, 'Efectivo', 'pagada', 0, '2026-06-07 22:05:00', '2026-06-15 18:43:35'),
	(59, 'B001-000059', 'boleta', 5, 3, 119.83, 21.57, 0.00, 141.40, 'Plin', 'pagada', 1, '2026-06-07 23:36:00', '2026-06-15 18:43:35'),
	(60, 'B001-000060', 'boleta', 6, 2, 8.22, 1.48, 0.00, 9.70, 'Efectivo', 'pagada', 1, '2026-06-07 21:36:00', '2026-06-15 18:43:35'),
	(61, 'B001-000061', 'boleta', 1, 1, 172.88, 31.12, 0.00, 204.00, 'Plin', 'pagada', 0, '2026-06-08 01:06:00', '2026-06-15 18:43:35'),
	(62, 'B001-000062', 'boleta', 2, 3, 8.14, 1.46, 0.00, 9.60, 'Plin', 'pagada', 1, '2026-06-07 23:35:00', '2026-06-15 18:43:35'),
	(63, 'B001-000063', 'boleta', 4, 2, 155.51, 27.99, 0.00, 183.50, 'Yape', 'pagada', 1, '2026-06-07 13:42:00', '2026-06-15 18:43:35'),
	(64, 'B001-000064', 'boleta', 1, 3, 25.08, 4.52, 0.00, 29.60, 'Efectivo', 'pagada', 0, '2026-06-08 01:48:00', '2026-06-15 18:43:35'),
	(65, 'B001-000065', 'boleta', 4, 1, 47.80, 8.60, 0.00, 56.40, 'Transferencia', 'pagada', 0, '2026-06-07 19:16:00', '2026-06-15 18:43:35'),
	(66, 'B001-000066', 'boleta', 1, 1, 6.95, 1.25, 0.00, 8.20, 'Transferencia', 'pagada', 1, '2026-06-08 00:51:00', '2026-06-15 18:43:35'),
	(67, 'B001-000067', 'boleta', 4, 1, 1.27, 0.23, 0.00, 1.50, 'Yape', 'pagada', 0, '2026-06-07 17:56:00', '2026-06-15 18:43:35'),
	(68, 'B001-000068', 'boleta', 1, 3, 3.31, 0.59, 0.00, 3.90, 'Yape', 'pagada', 0, '2026-06-07 15:37:00', '2026-06-15 18:43:35'),
	(69, 'B001-000069', 'boleta', 5, 3, 10.93, 1.97, 0.00, 12.90, 'Tarjeta', 'pagada', 1, '2026-06-08 22:00:00', '2026-06-15 18:43:35'),
	(70, 'B001-000070', 'boleta', 5, 3, 8.81, 1.59, 0.00, 10.40, 'Transferencia', 'pagada', 0, '2026-06-08 23:19:00', '2026-06-15 18:43:35'),
	(71, 'B001-000071', 'boleta', 5, 2, 46.02, 8.28, 0.00, 54.30, 'Efectivo', 'pagada', 1, '2026-06-08 15:38:00', '2026-06-15 18:43:35'),
	(72, 'B001-000072', 'boleta', 1, 3, 0.85, 0.15, 0.00, 1.00, 'Transferencia', 'pagada', 0, '2026-06-09 00:44:00', '2026-06-15 18:43:35'),
	(73, 'B001-000073', 'boleta', 4, 3, 5.93, 1.07, 0.00, 7.00, 'Yape', 'pagada', 0, '2026-06-08 13:50:00', '2026-06-15 18:43:35'),
	(74, 'B001-000074', 'boleta', 6, 3, 0.85, 0.15, 0.00, 1.00, 'Efectivo', 'pagada', 0, '2026-06-08 17:31:00', '2026-06-15 18:43:35'),
	(75, 'B001-000075', 'boleta', 2, 3, 122.54, 22.06, 0.00, 144.60, 'Plin', 'pagada', 1, '2026-06-08 22:51:00', '2026-06-15 18:43:35'),
	(76, 'B001-000076', 'boleta', 5, 2, 11.02, 1.98, 0.00, 13.00, 'Efectivo', 'pagada', 1, '2026-06-08 21:59:00', '2026-06-15 18:43:35'),
	(77, 'B001-000077', 'boleta', 1, 3, 22.03, 3.97, 0.00, 26.00, 'Tarjeta', 'pagada', 1, '2026-06-09 01:25:00', '2026-06-15 18:43:35'),
	(78, 'B001-000078', 'boleta', 3, 3, 1.27, 0.23, 0.00, 1.50, 'Efectivo', 'pagada', 0, '2026-06-09 18:56:00', '2026-06-15 18:43:35'),
	(79, 'B001-000079', 'boleta', 1, 3, 170.59, 30.71, 0.00, 201.30, 'Efectivo', 'pagada', 0, '2026-06-09 19:37:00', '2026-06-15 18:43:35'),
	(80, 'B001-000080', 'boleta', 1, 1, 8.52, 1.53, 0.00, 10.05, 'Efectivo', 'pagada', 0, '2026-06-09 20:33:00', '2026-06-15 18:43:35'),
	(81, 'B001-000081', 'boleta', 1, 2, 138.64, 24.96, 0.00, 163.60, 'Efectivo', 'pagada', 0, '2026-06-09 13:40:00', '2026-06-15 18:43:35'),
	(82, 'B001-000082', 'boleta', 2, 1, 2.54, 0.46, 0.00, 3.00, 'Yape', 'pagada', 0, '2026-06-09 15:43:00', '2026-06-15 18:43:35'),
	(83, 'B001-000083', 'boleta', 6, 2, 18.22, 3.28, 0.00, 21.50, 'Yape', 'pagada', 1, '2026-06-09 19:04:00', '2026-06-15 18:43:35'),
	(84, 'B001-000084', 'boleta', 6, 1, 1.44, 0.26, 0.00, 1.70, 'Efectivo', 'pagada', 0, '2026-06-09 14:57:00', '2026-06-15 18:43:35'),
	(85, 'B001-000085', 'boleta', 5, 1, 0.51, 0.09, 0.00, 0.60, 'Efectivo', 'pagada', 0, '2026-06-10 16:46:00', '2026-06-15 18:43:35'),
	(86, 'B001-000086', 'boleta', 2, 2, 10.17, 1.83, 0.00, 12.00, 'Transferencia', 'pagada', 0, '2026-06-10 15:57:00', '2026-06-15 18:43:35'),
	(87, 'B001-000087', 'boleta', 3, 2, 7.97, 1.43, 0.00, 9.40, 'Efectivo', 'pagada', 0, '2026-06-11 01:55:00', '2026-06-15 18:43:35'),
	(88, 'B001-000088', 'boleta', 4, 1, 6.95, 1.25, 0.00, 8.20, 'Yape', 'pagada', 1, '2026-06-10 17:22:00', '2026-06-15 18:43:35'),
	(89, 'B001-000089', 'boleta', 1, 1, 9.92, 1.78, 0.00, 11.70, 'Plin', 'pagada', 0, '2026-06-10 18:19:00', '2026-06-15 18:43:35'),
	(90, 'B001-000090', 'boleta', 6, 1, 7.63, 1.37, 0.00, 9.00, 'Efectivo', 'pagada', 1, '2026-06-10 16:17:00', '2026-06-15 18:43:35'),
	(91, 'B001-000091', 'boleta', 5, 3, 30.59, 5.51, 0.00, 36.10, 'Transferencia', 'pagada', 0, '2026-06-10 14:50:00', '2026-06-15 18:43:35'),
	(92, 'B001-000092', 'boleta', 3, 3, 9.11, 1.64, 0.00, 10.75, 'Transferencia', 'pagada', 0, '2026-06-10 18:03:00', '2026-06-15 18:43:35'),
	(93, 'B001-000093', 'boleta', 4, 2, 5.42, 0.98, 0.00, 6.40, 'Transferencia', 'pagada', 1, '2026-06-10 18:39:00', '2026-06-15 18:43:35'),
	(94, 'B001-000094', 'boleta', 1, 3, 2.97, 0.53, 0.00, 3.50, 'Transferencia', 'pagada', 0, '2026-06-10 20:05:00', '2026-06-15 18:43:35'),
	(95, 'B001-000095', 'boleta', 2, 2, 6.44, 1.16, 0.00, 7.60, 'Yape', 'pagada', 0, '2026-06-12 01:16:00', '2026-06-15 18:43:35'),
	(96, 'B001-000096', 'boleta', 4, 3, 28.90, 5.20, 0.00, 34.10, 'Plin', 'pagada', 1, '2026-06-11 21:56:00', '2026-06-15 18:43:35'),
	(97, 'B001-000097', 'boleta', 2, 1, 9.15, 1.65, 0.00, 10.80, 'Tarjeta', 'pagada', 1, '2026-06-11 18:32:00', '2026-06-15 18:43:35'),
	(98, 'B001-000098', 'boleta', 1, 3, 1.69, 0.31, 0.00, 2.00, 'Transferencia', 'pagada', 0, '2026-06-12 00:04:00', '2026-06-15 18:43:35'),
	(99, 'B001-000099', 'boleta', 4, 3, 11.44, 2.06, 0.00, 13.50, 'Transferencia', 'pagada', 0, '2026-06-11 15:41:00', '2026-06-15 18:43:35'),
	(100, 'B001-000100', 'boleta', 5, 2, 72.71, 13.09, 0.00, 85.80, 'Tarjeta', 'pagada', 1, '2026-06-12 01:27:00', '2026-06-15 18:43:35'),
	(101, 'B001-000101', 'boleta', 6, 1, 7.12, 1.28, 0.00, 8.40, 'Tarjeta', 'pagada', 1, '2026-06-11 19:48:00', '2026-06-15 18:43:35'),
	(102, 'B001-000102', 'boleta', 5, 2, 14.15, 2.55, 0.00, 16.70, 'Plin', 'pagada', 1, '2026-06-11 22:10:00', '2026-06-15 18:43:35'),
	(103, 'B001-000103', 'boleta', 1, 3, 98.90, 17.80, 0.00, 116.70, 'Tarjeta', 'pagada', 0, '2026-06-11 22:52:00', '2026-06-15 18:43:35'),
	(104, 'B001-000104', 'boleta', 1, 3, 33.81, 6.09, 0.00, 39.90, 'Plin', 'pagada', 0, '2026-06-11 19:50:00', '2026-06-15 18:43:35'),
	(105, 'B001-000105', 'boleta', 6, 2, 2.63, 0.47, 0.00, 3.10, 'Efectivo', 'pagada', 0, '2026-06-11 18:44:00', '2026-06-15 18:43:35'),
	(106, 'B001-000106', 'boleta', 1, 3, 8.81, 1.59, 0.00, 10.40, 'Yape', 'pagada', 0, '2026-06-11 13:17:00', '2026-06-15 18:43:35'),
	(107, 'B001-000107', 'boleta', 2, 1, 13.31, 2.39, 0.00, 15.70, 'Transferencia', 'pagada', 1, '2026-06-11 19:52:00', '2026-06-15 18:43:35'),
	(108, 'B001-000108', 'boleta', 5, 1, 11.40, 2.05, 0.00, 13.45, 'Transferencia', 'pagada', 1, '2026-06-12 00:10:00', '2026-06-15 18:43:35'),
	(109, 'B001-000109', 'boleta', 5, 3, 12.20, 2.20, 0.00, 14.40, 'Plin', 'pagada', 1, '2026-06-12 19:29:00', '2026-06-15 18:43:35'),
	(110, 'B001-000110', 'boleta', 6, 3, 3.31, 0.59, 0.00, 3.90, 'Efectivo', 'pagada', 0, '2026-06-12 20:32:00', '2026-06-15 18:43:35'),
	(111, 'B001-000111', 'boleta', 1, 2, 9.66, 1.74, 0.00, 11.40, 'Efectivo', 'pagada', 0, '2026-06-12 16:53:00', '2026-06-15 18:43:35'),
	(112, 'B001-000112', 'boleta', 4, 1, 4.75, 0.85, 0.00, 5.60, 'Transferencia', 'pagada', 1, '2026-06-12 22:08:00', '2026-06-15 18:43:35'),
	(113, 'B001-000113', 'boleta', 2, 1, 8.22, 1.48, 0.00, 9.70, 'Yape', 'pagada', 0, '2026-06-12 17:31:00', '2026-06-15 18:43:35'),
	(114, 'B001-000114', 'boleta', 3, 3, 6.44, 1.16, 0.00, 7.60, 'Tarjeta', 'pagada', 1, '2026-06-13 01:04:00', '2026-06-15 18:43:35'),
	(115, 'B001-000115', 'boleta', 6, 2, 45.34, 8.16, 0.00, 53.50, 'Plin', 'pagada', 0, '2026-06-12 19:24:00', '2026-06-15 18:43:36'),
	(116, 'B001-000116', 'boleta', 6, 1, 14.83, 2.67, 0.00, 17.50, 'Tarjeta', 'pagada', 1, '2026-06-12 18:05:00', '2026-06-15 18:43:36'),
	(117, 'B001-000117', 'boleta', 5, 1, 4.07, 0.73, 0.00, 4.80, 'Transferencia', 'pagada', 0, '2026-06-13 22:38:00', '2026-06-15 18:43:36'),
	(118, 'B001-000118', 'boleta', 1, 2, 33.81, 6.09, 0.00, 39.90, 'Efectivo', 'pagada', 0, '2026-06-13 20:22:00', '2026-06-15 18:43:36'),
	(119, 'B001-000119', 'boleta', 2, 2, 8.47, 1.53, 0.00, 10.00, 'Efectivo', 'pagada', 0, '2026-06-13 15:45:00', '2026-06-15 18:43:36'),
	(120, 'B001-000120', 'boleta', 3, 3, 14.58, 2.62, 0.00, 17.20, 'Yape', 'pagada', 0, '2026-06-13 14:27:00', '2026-06-15 18:43:36'),
	(121, 'B001-000121', 'boleta', 2, 1, 1.78, 0.32, 0.00, 2.10, 'Transferencia', 'pagada', 0, '2026-06-13 19:05:00', '2026-06-15 18:43:36'),
	(122, 'B001-000122', 'boleta', 1, 3, 12.88, 2.32, 0.00, 15.20, 'Yape', 'pagada', 0, '2026-06-13 13:24:00', '2026-06-15 18:43:36'),
	(123, 'B001-000123', 'boleta', 1, 1, 2.71, 0.49, 0.00, 3.20, 'Tarjeta', 'pagada', 0, '2026-06-14 00:39:00', '2026-06-15 18:43:36'),
	(124, 'B001-000124', 'boleta', 2, 2, 11.36, 2.04, 0.00, 13.40, 'Efectivo', 'pagada', 0, '2026-06-13 21:15:00', '2026-06-15 18:43:36'),
	(125, 'B001-000125', 'boleta', 5, 1, 3.73, 0.67, 0.00, 4.40, 'Efectivo', 'pagada', 0, '2026-06-13 15:23:00', '2026-06-15 18:43:36'),
	(126, 'B001-000126', 'boleta', 1, 2, 5.34, 0.96, 0.00, 6.30, 'Tarjeta', 'pagada', 0, '2026-06-13 17:40:00', '2026-06-15 18:43:36'),
	(127, 'B001-000127', 'boleta', 1, 2, 36.19, 6.51, 0.00, 42.70, 'Efectivo', 'pagada', 0, '2026-06-14 22:55:00', '2026-06-15 18:43:36'),
	(128, 'B001-000128', 'boleta', 3, 2, 1.02, 0.18, 0.00, 1.20, 'Efectivo', 'pagada', 0, '2026-06-14 18:56:00', '2026-06-15 18:43:36'),
	(129, 'B001-000129', 'boleta', 4, 3, 114.41, 20.59, 0.00, 135.00, 'Efectivo', 'pagada', 1, '2026-06-15 01:41:00', '2026-06-15 18:43:36'),
	(130, 'B001-000130', 'boleta', 4, 3, 2.54, 0.46, 0.00, 3.00, 'Yape', 'pagada', 0, '2026-06-14 16:13:00', '2026-06-15 18:43:36'),
	(131, 'B001-000131', 'boleta', 3, 2, 157.63, 28.37, 0.00, 186.00, 'Efectivo', 'pagada', 0, '2026-06-14 19:58:00', '2026-06-15 18:43:36'),
	(132, 'B001-000132', 'boleta', 3, 2, 35.51, 6.39, 0.00, 41.90, 'Transferencia', 'pagada', 0, '2026-06-14 21:14:00', '2026-06-15 18:43:36'),
	(133, 'B001-000133', 'boleta', 5, 1, 2.54, 0.46, 0.00, 3.00, 'Efectivo', 'pagada', 0, '2026-06-14 21:13:00', '2026-06-15 18:43:36'),
	(134, 'B001-000134', 'boleta', 3, 1, 3.14, 0.56, 0.00, 3.70, 'Plin', 'pagada', 0, '2026-06-15 01:59:00', '2026-06-15 18:43:36'),
	(135, 'B001-000135', 'boleta', 5, 2, 120.59, 21.71, 0.00, 142.30, 'Plin', 'pagada', 1, '2026-06-15 00:13:00', '2026-06-15 18:43:36'),
	(136, 'B001-000136', 'boleta', 6, 3, 5.34, 0.96, 0.00, 6.30, 'Plin', 'pagada', 1, '2026-06-14 16:58:00', '2026-06-15 18:43:36'),
	(137, 'B001-000137', 'boleta', 5, 2, 138.31, 24.89, 0.00, 163.20, 'Plin', 'pagada', 0, '2026-06-14 23:03:00', '2026-06-15 18:43:36'),
	(138, 'B001-000138', 'boleta', 5, 1, 11.02, 1.98, 0.00, 13.00, 'Yape', 'pagada', 0, '2026-06-14 23:03:00', '2026-06-15 18:43:36'),
	(139, 'B001-000139', 'boleta', 3, 2, 10.68, 1.92, 0.00, 12.60, 'Efectivo', 'pagada', 1, '2026-06-14 14:37:00', '2026-06-15 18:43:36'),
	(140, 'B001-000140', 'boleta', 1, 3, 114.41, 20.59, 0.00, 135.00, 'Plin', 'pagada', 1, '2026-06-14 15:33:00', '2026-06-15 18:43:36'),
	(141, 'B001-000141', 'boleta', 4, 3, 10.00, 1.80, 0.00, 11.80, 'Transferencia', 'pagada', 1, '2026-06-14 21:25:00', '2026-06-15 18:43:36'),
	(142, 'B001-000142', 'boleta', 6, 1, 2.54, 0.46, 0.00, 3.00, 'Efectivo', 'pagada', 0, '2026-06-14 23:27:00', '2026-06-15 18:43:36'),
	(143, 'B001-000143', 'boleta', 6, 3, 39.66, 7.14, 0.00, 46.80, 'Tarjeta', 'pagada', 0, '2026-06-15 16:12:00', '2026-06-15 18:43:36'),
	(144, 'B001-000144', 'boleta', 6, 2, 11.53, 2.07, 0.00, 13.60, 'Tarjeta', 'pagada', 0, '2026-06-15 18:39:00', '2026-06-15 18:43:36'),
	(145, 'B001-000145', 'boleta', 4, 1, 40.85, 7.35, 0.00, 48.20, 'Efectivo', 'pagada', 1, '2026-06-15 15:01:00', '2026-06-15 18:43:36'),
	(146, 'B001-000146', 'boleta', 1, 3, 10.76, 1.94, 0.00, 12.70, 'Yape', 'pagada', 0, '2026-06-15 15:22:00', '2026-06-15 18:43:36'),
	(147, 'B001-000147', 'boleta', 1, 2, 1.02, 0.18, 0.00, 1.20, 'Efectivo', 'pagada', 0, '2026-06-15 15:50:00', '2026-06-15 18:43:36'),
	(148, 'B001-000148', 'boleta', 1, 1, 22.46, 4.04, 0.00, 26.50, 'Transferencia', 'pagada', 0, '2026-06-15 18:34:00', '2026-06-15 18:43:36'),
	(149, 'B001-000149', 'boleta', 4, 2, 21.95, 3.95, 0.00, 25.90, 'Efectivo', 'pagada', 1, '2026-06-15 14:14:00', '2026-06-15 18:43:36'),
	(150, 'B001-000150', 'boleta', 6, 2, 6.95, 1.25, 0.00, 8.20, 'Efectivo', 'pagada', 0, '2026-06-15 19:57:00', '2026-06-15 18:43:36'),
	(151, 'B001-000151', 'boleta', 4, 1, 1.86, 0.34, 0.00, 2.20, 'Yape', 'pagada', 0, '2026-06-15 20:54:00', '2026-06-15 18:43:36'),
	(152, 'B001-000152', 'boleta', 2, 1, 104.49, 18.81, 0.00, 123.30, 'Plin', 'pagada', 1, '2026-06-15 18:00:00', '2026-06-15 18:43:36'),
	(153, 'B001-000153', 'boleta', 4, 1, 103.14, 18.56, 0.00, 121.70, 'Tarjeta', 'pagada', 0, '2026-06-15 18:03:00', '2026-06-15 18:43:36'),
	(154, 'B001-000154', 'boleta', 1, 1, 2.71, 0.49, 0.00, 3.20, 'Plin', 'pagada', 0, '2026-06-15 16:52:00', '2026-06-15 18:43:36'),
	(155, 'B001-000155', 'boleta', 1, 3, 179.92, 32.38, 0.00, 212.30, 'Efectivo', 'pagada', 0, '2026-06-15 14:07:00', '2026-06-15 18:43:36');

-- Volcando estructura para tabla saas_botica.venta_detalles
CREATE TABLE IF NOT EXISTS `venta_detalles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `venta_id` bigint unsigned NOT NULL,
  `producto_id` bigint unsigned DEFAULT NULL,
  `descripcion` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cantidad` int NOT NULL DEFAULT '1',
  `precio_unitario` decimal(10,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `venta_detalles_venta_id_foreign` (`venta_id`),
  KEY `venta_detalles_producto_id_foreign` (`producto_id`),
  CONSTRAINT `venta_detalles_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `venta_detalles_venta_id_foreign` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=356 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla saas_botica.venta_detalles: ~355 rows (aproximadamente)
DELETE FROM `venta_detalles`;
INSERT INTO `venta_detalles` (`id`, `venta_id`, `producto_id`, `descripcion`, `cantidad`, `precio_unitario`, `subtotal`, `created_at`, `updated_at`) VALUES
	(1, 1, 14, 'Alcohol en gel 250ml', 5, 6.50, 32.50, '2026-06-02 18:02:00', '2026-06-02 18:02:00'),
	(2, 1, 15, 'Enalapril 10mg', 1, 0.85, 0.85, '2026-06-02 18:02:00', '2026-06-02 18:02:00'),
	(3, 1, 3, 'Aspirina 100mg', 1, 0.60, 0.60, '2026-06-02 18:02:00', '2026-06-02 18:02:00'),
	(4, 2, 7, 'Sal de Andrews', 4, 1.00, 4.00, '2026-06-02 16:59:00', '2026-06-02 16:59:00'),
	(5, 3, 3, 'Aspirina 100mg', 1, 0.60, 0.60, '2026-06-02 15:27:00', '2026-06-02 15:27:00'),
	(6, 3, 6, 'Panadol Antigripal', 1, 1.50, 1.50, '2026-06-02 15:27:00', '2026-06-02 15:27:00'),
	(7, 4, 6, 'Panadol Antigripal', 1, 1.50, 1.50, '2026-06-02 20:19:00', '2026-06-02 20:19:00'),
	(8, 4, 15, 'Enalapril 10mg', 3, 0.85, 2.55, '2026-06-02 20:19:00', '2026-06-02 20:19:00'),
	(9, 4, 16, 'Metformina 850mg', 2, 0.70, 1.40, '2026-06-02 20:19:00', '2026-06-02 20:19:00'),
	(10, 5, 6, 'Panadol Antigripal', 3, 1.50, 4.50, '2026-06-02 20:33:00', '2026-06-02 20:33:00'),
	(11, 5, 10, 'Loratadina 10mg', 3, 0.90, 2.70, '2026-06-02 20:33:00', '2026-06-02 20:33:00'),
	(12, 6, 15, 'Enalapril 10mg', 4, 0.85, 3.40, '2026-06-02 13:08:00', '2026-06-02 13:08:00'),
	(13, 7, 15, 'Enalapril 10mg', 5, 0.85, 4.25, '2026-06-02 17:29:00', '2026-06-02 17:29:00'),
	(14, 8, 2, 'Ibuprofeno 400mg', 2, 0.80, 1.60, '2026-06-02 21:23:00', '2026-06-02 21:23:00'),
	(15, 9, 14, 'Alcohol en gel 250ml', 1, 6.50, 6.50, '2026-06-03 16:20:00', '2026-06-03 16:20:00'),
	(16, 9, 10, 'Loratadina 10mg', 5, 0.90, 4.50, '2026-06-03 16:20:00', '2026-06-03 16:20:00'),
	(17, 9, 1, 'Paracetamol 500mg', 5, 0.50, 2.50, '2026-06-03 16:20:00', '2026-06-03 16:20:00'),
	(18, 10, 1, 'Paracetamol 500mg', 5, 0.50, 2.50, '2026-06-03 15:23:00', '2026-06-03 15:23:00'),
	(19, 11, 15, 'Enalapril 10mg', 3, 0.85, 2.55, '2026-06-03 14:25:00', '2026-06-03 14:25:00'),
	(20, 11, 10, 'Loratadina 10mg', 3, 0.90, 2.70, '2026-06-03 14:25:00', '2026-06-03 14:25:00'),
	(21, 11, 11, 'Omeprazol 20mg', 1, 1.00, 1.00, '2026-06-03 14:25:00', '2026-06-03 14:25:00'),
	(22, 12, 2, 'Ibuprofeno 400mg', 1, 0.80, 0.80, '2026-06-03 13:27:00', '2026-06-03 13:27:00'),
	(23, 12, 15, 'Enalapril 10mg', 3, 0.85, 2.55, '2026-06-03 13:27:00', '2026-06-03 13:27:00'),
	(24, 12, 6, 'Panadol Antigripal', 5, 1.50, 7.50, '2026-06-03 13:27:00', '2026-06-03 13:27:00'),
	(25, 13, 7, 'Sal de Andrews', 5, 1.00, 5.00, '2026-06-03 15:32:00', '2026-06-03 15:32:00'),
	(26, 13, 12, 'Crema Hidratante Cerave', 4, 39.90, 159.60, '2026-06-03 15:32:00', '2026-06-03 15:32:00'),
	(27, 14, 6, 'Panadol Antigripal', 5, 1.50, 7.50, '2026-06-03 16:39:00', '2026-06-03 16:39:00'),
	(28, 14, 11, 'Omeprazol 20mg', 4, 1.00, 4.00, '2026-06-03 16:39:00', '2026-06-03 16:39:00'),
	(29, 14, 10, 'Loratadina 10mg', 4, 0.90, 3.60, '2026-06-03 16:39:00', '2026-06-03 16:39:00'),
	(30, 15, 2, 'Ibuprofeno 400mg', 4, 0.80, 3.20, '2026-06-03 16:10:00', '2026-06-03 16:10:00'),
	(31, 15, 1, 'Paracetamol 500mg', 4, 0.50, 2.00, '2026-06-03 16:10:00', '2026-06-03 16:10:00'),
	(32, 15, 4, 'Amoxicilina 500mg', 2, 1.20, 2.40, '2026-06-03 16:10:00', '2026-06-03 16:10:00'),
	(33, 15, 5, 'Azitromicina 500mg', 5, 3.50, 17.50, '2026-06-03 16:10:00', '2026-06-03 16:10:00'),
	(34, 16, 14, 'Alcohol en gel 250ml', 1, 6.50, 6.50, '2026-06-03 16:04:00', '2026-06-03 16:04:00'),
	(35, 16, 13, 'Protector Solar FPS50', 1, 45.00, 45.00, '2026-06-03 16:04:00', '2026-06-03 16:04:00'),
	(36, 16, 10, 'Loratadina 10mg', 1, 0.90, 0.90, '2026-06-03 16:04:00', '2026-06-03 16:04:00'),
	(37, 17, 7, 'Sal de Andrews', 1, 1.00, 1.00, '2026-06-03 23:19:00', '2026-06-03 23:19:00'),
	(38, 18, 5, 'Azitromicina 500mg', 5, 3.50, 17.50, '2026-06-03 19:59:00', '2026-06-03 19:59:00'),
	(39, 18, 11, 'Omeprazol 20mg', 3, 1.00, 3.00, '2026-06-03 19:59:00', '2026-06-03 19:59:00'),
	(40, 18, 7, 'Sal de Andrews', 2, 1.00, 2.00, '2026-06-03 19:59:00', '2026-06-03 19:59:00'),
	(41, 19, 9, 'Complejo B', 4, 1.80, 7.20, '2026-06-04 00:14:00', '2026-06-04 00:14:00'),
	(42, 19, 4, 'Amoxicilina 500mg', 1, 1.20, 1.20, '2026-06-04 00:14:00', '2026-06-04 00:14:00'),
	(43, 19, 15, 'Enalapril 10mg', 5, 0.85, 4.25, '2026-06-04 00:14:00', '2026-06-04 00:14:00'),
	(44, 19, 7, 'Sal de Andrews', 3, 1.00, 3.00, '2026-06-04 00:14:00', '2026-06-04 00:14:00'),
	(45, 20, 8, 'Vitamina C 1g', 5, 1.30, 6.50, '2026-06-03 21:40:00', '2026-06-03 21:40:00'),
	(46, 20, 5, 'Azitromicina 500mg', 1, 3.50, 3.50, '2026-06-03 21:40:00', '2026-06-03 21:40:00'),
	(47, 20, 7, 'Sal de Andrews', 4, 1.00, 4.00, '2026-06-03 21:40:00', '2026-06-03 21:40:00'),
	(48, 20, 2, 'Ibuprofeno 400mg', 3, 0.80, 2.40, '2026-06-03 21:40:00', '2026-06-03 21:40:00'),
	(49, 21, 10, 'Loratadina 10mg', 3, 0.90, 2.70, '2026-06-03 23:44:00', '2026-06-03 23:44:00'),
	(50, 21, 9, 'Complejo B', 4, 1.80, 7.20, '2026-06-03 23:44:00', '2026-06-03 23:44:00'),
	(51, 21, 14, 'Alcohol en gel 250ml', 2, 6.50, 13.00, '2026-06-03 23:44:00', '2026-06-03 23:44:00'),
	(52, 21, 2, 'Ibuprofeno 400mg', 2, 0.80, 1.60, '2026-06-03 23:44:00', '2026-06-03 23:44:00'),
	(53, 22, 13, 'Protector Solar FPS50', 1, 45.00, 45.00, '2026-06-03 22:38:00', '2026-06-03 22:38:00'),
	(54, 23, 12, 'Crema Hidratante Cerave', 4, 39.90, 159.60, '2026-06-03 17:26:00', '2026-06-03 17:26:00'),
	(55, 23, 16, 'Metformina 850mg', 3, 0.70, 2.10, '2026-06-03 17:26:00', '2026-06-03 17:26:00'),
	(56, 23, 1, 'Paracetamol 500mg', 2, 0.50, 1.00, '2026-06-03 17:26:00', '2026-06-03 17:26:00'),
	(57, 24, 7, 'Sal de Andrews', 4, 1.00, 4.00, '2026-06-04 20:42:00', '2026-06-04 20:42:00'),
	(58, 24, 14, 'Alcohol en gel 250ml', 3, 6.50, 19.50, '2026-06-04 20:42:00', '2026-06-04 20:42:00'),
	(59, 24, 16, 'Metformina 850mg', 5, 0.70, 3.50, '2026-06-04 20:42:00', '2026-06-04 20:42:00'),
	(60, 25, 7, 'Sal de Andrews', 5, 1.00, 5.00, '2026-06-04 17:36:00', '2026-06-04 17:36:00'),
	(61, 25, 13, 'Protector Solar FPS50', 1, 45.00, 45.00, '2026-06-04 17:36:00', '2026-06-04 17:36:00'),
	(62, 25, 9, 'Complejo B', 3, 1.80, 5.40, '2026-06-04 17:36:00', '2026-06-04 17:36:00'),
	(63, 26, 11, 'Omeprazol 20mg', 1, 1.00, 1.00, '2026-06-04 23:07:00', '2026-06-04 23:07:00'),
	(64, 27, 5, 'Azitromicina 500mg', 1, 3.50, 3.50, '2026-06-04 21:05:00', '2026-06-04 21:05:00'),
	(65, 27, 11, 'Omeprazol 20mg', 2, 1.00, 2.00, '2026-06-04 21:05:00', '2026-06-04 21:05:00'),
	(66, 27, 10, 'Loratadina 10mg', 2, 0.90, 1.80, '2026-06-04 21:05:00', '2026-06-04 21:05:00'),
	(67, 27, 6, 'Panadol Antigripal', 3, 1.50, 4.50, '2026-06-04 21:05:00', '2026-06-04 21:05:00'),
	(68, 28, 11, 'Omeprazol 20mg', 5, 1.00, 5.00, '2026-06-04 23:53:00', '2026-06-04 23:53:00'),
	(69, 28, 10, 'Loratadina 10mg', 2, 0.90, 1.80, '2026-06-04 23:53:00', '2026-06-04 23:53:00'),
	(70, 28, 15, 'Enalapril 10mg', 2, 0.85, 1.70, '2026-06-04 23:53:00', '2026-06-04 23:53:00'),
	(71, 29, 15, 'Enalapril 10mg', 2, 0.85, 1.70, '2026-06-04 14:56:00', '2026-06-04 14:56:00'),
	(72, 30, 15, 'Enalapril 10mg', 2, 0.85, 1.70, '2026-06-04 13:43:00', '2026-06-04 13:43:00'),
	(73, 30, 14, 'Alcohol en gel 250ml', 4, 6.50, 26.00, '2026-06-04 13:43:00', '2026-06-04 13:43:00'),
	(74, 30, 9, 'Complejo B', 4, 1.80, 7.20, '2026-06-04 13:43:00', '2026-06-04 13:43:00'),
	(75, 30, 8, 'Vitamina C 1g', 5, 1.30, 6.50, '2026-06-04 13:43:00', '2026-06-04 13:43:00'),
	(76, 31, 12, 'Crema Hidratante Cerave', 2, 39.90, 79.80, '2026-06-04 22:43:00', '2026-06-04 22:43:00'),
	(77, 31, 14, 'Alcohol en gel 250ml', 3, 6.50, 19.50, '2026-06-04 22:43:00', '2026-06-04 22:43:00'),
	(78, 32, 7, 'Sal de Andrews', 4, 1.00, 4.00, '2026-06-04 18:36:00', '2026-06-04 18:36:00'),
	(79, 32, 9, 'Complejo B', 3, 1.80, 5.40, '2026-06-04 18:36:00', '2026-06-04 18:36:00'),
	(80, 32, 13, 'Protector Solar FPS50', 3, 45.00, 135.00, '2026-06-04 18:36:00', '2026-06-04 18:36:00'),
	(81, 32, 2, 'Ibuprofeno 400mg', 2, 0.80, 1.60, '2026-06-04 18:36:00', '2026-06-04 18:36:00'),
	(82, 33, 9, 'Complejo B', 5, 1.80, 9.00, '2026-06-04 13:51:00', '2026-06-04 13:51:00'),
	(83, 34, 16, 'Metformina 850mg', 3, 0.70, 2.10, '2026-06-04 16:06:00', '2026-06-04 16:06:00'),
	(84, 34, 12, 'Crema Hidratante Cerave', 3, 39.90, 119.70, '2026-06-04 16:06:00', '2026-06-04 16:06:00'),
	(85, 34, 2, 'Ibuprofeno 400mg', 5, 0.80, 4.00, '2026-06-04 16:06:00', '2026-06-04 16:06:00'),
	(86, 34, 10, 'Loratadina 10mg', 5, 0.90, 4.50, '2026-06-04 16:06:00', '2026-06-04 16:06:00'),
	(87, 35, 15, 'Enalapril 10mg', 4, 0.85, 3.40, '2026-06-04 18:32:00', '2026-06-04 18:32:00'),
	(88, 35, 2, 'Ibuprofeno 400mg', 3, 0.80, 2.40, '2026-06-04 18:32:00', '2026-06-04 18:32:00'),
	(89, 36, 6, 'Panadol Antigripal', 4, 1.50, 6.00, '2026-06-04 14:30:00', '2026-06-04 14:30:00'),
	(90, 36, 11, 'Omeprazol 20mg', 1, 1.00, 1.00, '2026-06-04 14:30:00', '2026-06-04 14:30:00'),
	(91, 37, 10, 'Loratadina 10mg', 1, 0.90, 0.90, '2026-06-04 15:10:00', '2026-06-04 15:10:00'),
	(92, 37, 2, 'Ibuprofeno 400mg', 3, 0.80, 2.40, '2026-06-04 15:10:00', '2026-06-04 15:10:00'),
	(93, 38, 1, 'Paracetamol 500mg', 5, 0.50, 2.50, '2026-06-04 18:26:00', '2026-06-04 18:26:00'),
	(94, 38, 2, 'Ibuprofeno 400mg', 4, 0.80, 3.20, '2026-06-04 18:26:00', '2026-06-04 18:26:00'),
	(95, 39, 14, 'Alcohol en gel 250ml', 1, 6.50, 6.50, '2026-06-04 23:55:00', '2026-06-04 23:55:00'),
	(96, 40, 6, 'Panadol Antigripal', 4, 1.50, 6.00, '2026-06-06 00:39:00', '2026-06-06 00:39:00'),
	(97, 41, 1, 'Paracetamol 500mg', 5, 0.50, 2.50, '2026-06-05 17:29:00', '2026-06-05 17:29:00'),
	(98, 42, 4, 'Amoxicilina 500mg', 1, 1.20, 1.20, '2026-06-05 14:09:00', '2026-06-05 14:09:00'),
	(99, 43, 6, 'Panadol Antigripal', 4, 1.50, 6.00, '2026-06-05 21:11:00', '2026-06-05 21:11:00'),
	(100, 43, 8, 'Vitamina C 1g', 5, 1.30, 6.50, '2026-06-05 21:11:00', '2026-06-05 21:11:00'),
	(101, 44, 3, 'Aspirina 100mg', 4, 0.60, 2.40, '2026-06-05 19:20:00', '2026-06-05 19:20:00'),
	(102, 44, 1, 'Paracetamol 500mg', 3, 0.50, 1.50, '2026-06-05 19:20:00', '2026-06-05 19:20:00'),
	(103, 45, 10, 'Loratadina 10mg', 2, 0.90, 1.80, '2026-06-05 22:21:00', '2026-06-05 22:21:00'),
	(104, 45, 9, 'Complejo B', 3, 1.80, 5.40, '2026-06-05 22:21:00', '2026-06-05 22:21:00'),
	(105, 45, 14, 'Alcohol en gel 250ml', 3, 6.50, 19.50, '2026-06-05 22:21:00', '2026-06-05 22:21:00'),
	(106, 46, 5, 'Azitromicina 500mg', 3, 3.50, 10.50, '2026-06-05 18:35:00', '2026-06-05 18:35:00'),
	(107, 46, 11, 'Omeprazol 20mg', 4, 1.00, 4.00, '2026-06-05 18:35:00', '2026-06-05 18:35:00'),
	(108, 46, 9, 'Complejo B', 1, 1.80, 1.80, '2026-06-05 18:35:00', '2026-06-05 18:35:00'),
	(109, 47, 15, 'Enalapril 10mg', 3, 0.85, 2.55, '2026-06-05 23:14:00', '2026-06-05 23:14:00'),
	(110, 48, 3, 'Aspirina 100mg', 5, 0.60, 3.00, '2026-06-05 21:36:00', '2026-06-05 21:36:00'),
	(111, 48, 1, 'Paracetamol 500mg', 3, 0.50, 1.50, '2026-06-05 21:36:00', '2026-06-05 21:36:00'),
	(112, 49, 9, 'Complejo B', 4, 1.80, 7.20, '2026-06-07 00:18:00', '2026-06-07 00:18:00'),
	(113, 49, 13, 'Protector Solar FPS50', 3, 45.00, 135.00, '2026-06-07 00:18:00', '2026-06-07 00:18:00'),
	(114, 49, 4, 'Amoxicilina 500mg', 5, 1.20, 6.00, '2026-06-07 00:18:00', '2026-06-07 00:18:00'),
	(115, 49, 2, 'Ibuprofeno 400mg', 3, 0.80, 2.40, '2026-06-07 00:18:00', '2026-06-07 00:18:00'),
	(116, 50, 14, 'Alcohol en gel 250ml', 1, 6.50, 6.50, '2026-06-07 00:00:00', '2026-06-07 00:00:00'),
	(117, 50, 9, 'Complejo B', 2, 1.80, 3.60, '2026-06-07 00:00:00', '2026-06-07 00:00:00'),
	(118, 50, 7, 'Sal de Andrews', 4, 1.00, 4.00, '2026-06-07 00:00:00', '2026-06-07 00:00:00'),
	(119, 50, 8, 'Vitamina C 1g', 5, 1.30, 6.50, '2026-06-07 00:00:00', '2026-06-07 00:00:00'),
	(120, 51, 16, 'Metformina 850mg', 2, 0.70, 1.40, '2026-06-06 22:01:00', '2026-06-06 22:01:00'),
	(121, 52, 11, 'Omeprazol 20mg', 5, 1.00, 5.00, '2026-06-07 00:17:00', '2026-06-07 00:17:00'),
	(122, 53, 16, 'Metformina 850mg', 5, 0.70, 3.50, '2026-06-06 23:21:00', '2026-06-06 23:21:00'),
	(123, 54, 12, 'Crema Hidratante Cerave', 1, 39.90, 39.90, '2026-06-06 19:35:00', '2026-06-06 19:35:00'),
	(124, 54, 4, 'Amoxicilina 500mg', 2, 1.20, 2.40, '2026-06-06 19:35:00', '2026-06-06 19:35:00'),
	(125, 54, 9, 'Complejo B', 5, 1.80, 9.00, '2026-06-06 19:35:00', '2026-06-06 19:35:00'),
	(126, 55, 9, 'Complejo B', 2, 1.80, 3.60, '2026-06-06 15:34:00', '2026-06-06 15:34:00'),
	(127, 56, 5, 'Azitromicina 500mg', 1, 3.50, 3.50, '2026-06-07 20:37:00', '2026-06-07 20:37:00'),
	(128, 57, 7, 'Sal de Andrews', 5, 1.00, 5.00, '2026-06-07 15:25:00', '2026-06-07 15:25:00'),
	(129, 58, 13, 'Protector Solar FPS50', 3, 45.00, 135.00, '2026-06-07 22:05:00', '2026-06-07 22:05:00'),
	(130, 59, 7, 'Sal de Andrews', 4, 1.00, 4.00, '2026-06-07 23:36:00', '2026-06-07 23:36:00'),
	(131, 59, 3, 'Aspirina 100mg', 4, 0.60, 2.40, '2026-06-07 23:36:00', '2026-06-07 23:36:00'),
	(132, 59, 13, 'Protector Solar FPS50', 3, 45.00, 135.00, '2026-06-07 23:36:00', '2026-06-07 23:36:00'),
	(133, 60, 16, 'Metformina 850mg', 2, 0.70, 1.40, '2026-06-07 21:36:00', '2026-06-07 21:36:00'),
	(134, 60, 5, 'Azitromicina 500mg', 1, 3.50, 3.50, '2026-06-07 21:36:00', '2026-06-07 21:36:00'),
	(135, 60, 4, 'Amoxicilina 500mg', 4, 1.20, 4.80, '2026-06-07 21:36:00', '2026-06-07 21:36:00'),
	(136, 61, 6, 'Panadol Antigripal', 3, 1.50, 4.50, '2026-06-08 01:06:00', '2026-06-08 01:06:00'),
	(137, 61, 12, 'Crema Hidratante Cerave', 5, 39.90, 199.50, '2026-06-08 01:06:00', '2026-06-08 01:06:00'),
	(138, 62, 3, 'Aspirina 100mg', 5, 0.60, 3.00, '2026-06-07 23:35:00', '2026-06-07 23:35:00'),
	(139, 62, 8, 'Vitamina C 1g', 3, 1.30, 3.90, '2026-06-07 23:35:00', '2026-06-07 23:35:00'),
	(140, 62, 10, 'Loratadina 10mg', 3, 0.90, 2.70, '2026-06-07 23:35:00', '2026-06-07 23:35:00'),
	(141, 63, 13, 'Protector Solar FPS50', 4, 45.00, 180.00, '2026-06-07 13:42:00', '2026-06-07 13:42:00'),
	(142, 63, 16, 'Metformina 850mg', 5, 0.70, 3.50, '2026-06-07 13:42:00', '2026-06-07 13:42:00'),
	(143, 64, 5, 'Azitromicina 500mg', 4, 3.50, 14.00, '2026-06-08 01:48:00', '2026-06-08 01:48:00'),
	(144, 64, 6, 'Panadol Antigripal', 4, 1.50, 6.00, '2026-06-08 01:48:00', '2026-06-08 01:48:00'),
	(145, 64, 4, 'Amoxicilina 500mg', 5, 1.20, 6.00, '2026-06-08 01:48:00', '2026-06-08 01:48:00'),
	(146, 64, 10, 'Loratadina 10mg', 4, 0.90, 3.60, '2026-06-08 01:48:00', '2026-06-08 01:48:00'),
	(147, 65, 13, 'Protector Solar FPS50', 1, 45.00, 45.00, '2026-06-07 19:16:00', '2026-06-07 19:16:00'),
	(148, 65, 4, 'Amoxicilina 500mg', 5, 1.20, 6.00, '2026-06-07 19:16:00', '2026-06-07 19:16:00'),
	(149, 65, 9, 'Complejo B', 3, 1.80, 5.40, '2026-06-07 19:16:00', '2026-06-07 19:16:00'),
	(150, 66, 4, 'Amoxicilina 500mg', 1, 1.20, 1.20, '2026-06-08 00:51:00', '2026-06-08 00:51:00'),
	(151, 66, 3, 'Aspirina 100mg', 3, 0.60, 1.80, '2026-06-08 00:51:00', '2026-06-08 00:51:00'),
	(152, 66, 8, 'Vitamina C 1g', 4, 1.30, 5.20, '2026-06-08 00:51:00', '2026-06-08 00:51:00'),
	(153, 67, 1, 'Paracetamol 500mg', 3, 0.50, 1.50, '2026-06-07 17:56:00', '2026-06-07 17:56:00'),
	(154, 68, 8, 'Vitamina C 1g', 3, 1.30, 3.90, '2026-06-07 15:37:00', '2026-06-07 15:37:00'),
	(155, 69, 5, 'Azitromicina 500mg', 1, 3.50, 3.50, '2026-06-08 22:00:00', '2026-06-08 22:00:00'),
	(156, 69, 9, 'Complejo B', 3, 1.80, 5.40, '2026-06-08 22:00:00', '2026-06-08 22:00:00'),
	(157, 69, 11, 'Omeprazol 20mg', 4, 1.00, 4.00, '2026-06-08 22:00:00', '2026-06-08 22:00:00'),
	(158, 70, 15, 'Enalapril 10mg', 4, 0.85, 3.40, '2026-06-08 23:19:00', '2026-06-08 23:19:00'),
	(159, 70, 5, 'Azitromicina 500mg', 2, 3.50, 7.00, '2026-06-08 23:19:00', '2026-06-08 23:19:00'),
	(160, 71, 13, 'Protector Solar FPS50', 1, 45.00, 45.00, '2026-06-08 15:38:00', '2026-06-08 15:38:00'),
	(161, 71, 15, 'Enalapril 10mg', 2, 0.85, 1.70, '2026-06-08 15:38:00', '2026-06-08 15:38:00'),
	(162, 71, 4, 'Amoxicilina 500mg', 4, 1.20, 4.80, '2026-06-08 15:38:00', '2026-06-08 15:38:00'),
	(163, 71, 16, 'Metformina 850mg', 4, 0.70, 2.80, '2026-06-08 15:38:00', '2026-06-08 15:38:00'),
	(164, 72, 11, 'Omeprazol 20mg', 1, 1.00, 1.00, '2026-06-09 00:44:00', '2026-06-09 00:44:00'),
	(165, 73, 2, 'Ibuprofeno 400mg', 4, 0.80, 3.20, '2026-06-08 13:50:00', '2026-06-08 13:50:00'),
	(166, 73, 3, 'Aspirina 100mg', 3, 0.60, 1.80, '2026-06-08 13:50:00', '2026-06-08 13:50:00'),
	(167, 73, 1, 'Paracetamol 500mg', 4, 0.50, 2.00, '2026-06-08 13:50:00', '2026-06-08 13:50:00'),
	(168, 74, 1, 'Paracetamol 500mg', 2, 0.50, 1.00, '2026-06-08 17:31:00', '2026-06-08 17:31:00'),
	(169, 75, 4, 'Amoxicilina 500mg', 2, 1.20, 2.40, '2026-06-08 22:51:00', '2026-06-08 22:51:00'),
	(170, 75, 11, 'Omeprazol 20mg', 4, 1.00, 4.00, '2026-06-08 22:51:00', '2026-06-08 22:51:00'),
	(171, 75, 13, 'Protector Solar FPS50', 3, 45.00, 135.00, '2026-06-08 22:51:00', '2026-06-08 22:51:00'),
	(172, 75, 2, 'Ibuprofeno 400mg', 4, 0.80, 3.20, '2026-06-08 22:51:00', '2026-06-08 22:51:00'),
	(173, 76, 14, 'Alcohol en gel 250ml', 2, 6.50, 13.00, '2026-06-08 21:59:00', '2026-06-08 21:59:00'),
	(174, 77, 6, 'Panadol Antigripal', 5, 1.50, 7.50, '2026-06-09 01:25:00', '2026-06-09 01:25:00'),
	(175, 77, 1, 'Paracetamol 500mg', 2, 0.50, 1.00, '2026-06-09 01:25:00', '2026-06-09 01:25:00'),
	(176, 77, 5, 'Azitromicina 500mg', 5, 3.50, 17.50, '2026-06-09 01:25:00', '2026-06-09 01:25:00'),
	(177, 78, 1, 'Paracetamol 500mg', 3, 0.50, 1.50, '2026-06-09 18:56:00', '2026-06-09 18:56:00'),
	(178, 79, 12, 'Crema Hidratante Cerave', 5, 39.90, 199.50, '2026-06-09 19:37:00', '2026-06-09 19:37:00'),
	(179, 79, 10, 'Loratadina 10mg', 2, 0.90, 1.80, '2026-06-09 19:37:00', '2026-06-09 19:37:00'),
	(180, 80, 15, 'Enalapril 10mg', 1, 0.85, 0.85, '2026-06-09 20:33:00', '2026-06-09 20:33:00'),
	(181, 80, 14, 'Alcohol en gel 250ml', 1, 6.50, 6.50, '2026-06-09 20:33:00', '2026-06-09 20:33:00'),
	(182, 80, 10, 'Loratadina 10mg', 3, 0.90, 2.70, '2026-06-09 20:33:00', '2026-06-09 20:33:00'),
	(183, 81, 2, 'Ibuprofeno 400mg', 5, 0.80, 4.00, '2026-06-09 13:40:00', '2026-06-09 13:40:00'),
	(184, 81, 12, 'Crema Hidratante Cerave', 4, 39.90, 159.60, '2026-06-09 13:40:00', '2026-06-09 13:40:00'),
	(185, 82, 6, 'Panadol Antigripal', 2, 1.50, 3.00, '2026-06-09 15:43:00', '2026-06-09 15:43:00'),
	(186, 83, 5, 'Azitromicina 500mg', 4, 3.50, 14.00, '2026-06-09 19:04:00', '2026-06-09 19:04:00'),
	(187, 83, 16, 'Metformina 850mg', 5, 0.70, 3.50, '2026-06-09 19:04:00', '2026-06-09 19:04:00'),
	(188, 83, 7, 'Sal de Andrews', 4, 1.00, 4.00, '2026-06-09 19:04:00', '2026-06-09 19:04:00'),
	(189, 84, 1, 'Paracetamol 500mg', 2, 0.50, 1.00, '2026-06-09 14:57:00', '2026-06-09 14:57:00'),
	(190, 84, 16, 'Metformina 850mg', 1, 0.70, 0.70, '2026-06-09 14:57:00', '2026-06-09 14:57:00'),
	(191, 85, 3, 'Aspirina 100mg', 1, 0.60, 0.60, '2026-06-10 16:46:00', '2026-06-10 16:46:00'),
	(192, 86, 6, 'Panadol Antigripal', 5, 1.50, 7.50, '2026-06-10 15:57:00', '2026-06-10 15:57:00'),
	(193, 86, 10, 'Loratadina 10mg', 5, 0.90, 4.50, '2026-06-10 15:57:00', '2026-06-10 15:57:00'),
	(194, 87, 2, 'Ibuprofeno 400mg', 4, 0.80, 3.20, '2026-06-11 01:55:00', '2026-06-11 01:55:00'),
	(195, 87, 3, 'Aspirina 100mg', 2, 0.60, 1.20, '2026-06-11 01:55:00', '2026-06-11 01:55:00'),
	(196, 87, 7, 'Sal de Andrews', 5, 1.00, 5.00, '2026-06-11 01:55:00', '2026-06-11 01:55:00'),
	(197, 88, 6, 'Panadol Antigripal', 3, 1.50, 4.50, '2026-06-10 17:22:00', '2026-06-10 17:22:00'),
	(198, 88, 2, 'Ibuprofeno 400mg', 3, 0.80, 2.40, '2026-06-10 17:22:00', '2026-06-10 17:22:00'),
	(199, 88, 8, 'Vitamina C 1g', 1, 1.30, 1.30, '2026-06-10 17:22:00', '2026-06-10 17:22:00'),
	(200, 89, 8, 'Vitamina C 1g', 4, 1.30, 5.20, '2026-06-10 18:19:00', '2026-06-10 18:19:00'),
	(201, 89, 14, 'Alcohol en gel 250ml', 1, 6.50, 6.50, '2026-06-10 18:19:00', '2026-06-10 18:19:00'),
	(202, 90, 9, 'Complejo B', 5, 1.80, 9.00, '2026-06-10 16:17:00', '2026-06-10 16:17:00'),
	(203, 91, 6, 'Panadol Antigripal', 1, 1.50, 1.50, '2026-06-10 14:50:00', '2026-06-10 14:50:00'),
	(204, 91, 16, 'Metformina 850mg', 3, 0.70, 2.10, '2026-06-10 14:50:00', '2026-06-10 14:50:00'),
	(205, 91, 14, 'Alcohol en gel 250ml', 5, 6.50, 32.50, '2026-06-10 14:50:00', '2026-06-10 14:50:00'),
	(206, 92, 15, 'Enalapril 10mg', 3, 0.85, 2.55, '2026-06-10 18:03:00', '2026-06-10 18:03:00'),
	(207, 92, 11, 'Omeprazol 20mg', 3, 1.00, 3.00, '2026-06-10 18:03:00', '2026-06-10 18:03:00'),
	(208, 92, 8, 'Vitamina C 1g', 4, 1.30, 5.20, '2026-06-10 18:03:00', '2026-06-10 18:03:00'),
	(209, 93, 2, 'Ibuprofeno 400mg', 3, 0.80, 2.40, '2026-06-10 18:39:00', '2026-06-10 18:39:00'),
	(210, 93, 7, 'Sal de Andrews', 4, 1.00, 4.00, '2026-06-10 18:39:00', '2026-06-10 18:39:00'),
	(211, 94, 5, 'Azitromicina 500mg', 1, 3.50, 3.50, '2026-06-10 20:05:00', '2026-06-10 20:05:00'),
	(212, 95, 7, 'Sal de Andrews', 5, 1.00, 5.00, '2026-06-12 01:16:00', '2026-06-12 01:16:00'),
	(213, 95, 8, 'Vitamina C 1g', 2, 1.30, 2.60, '2026-06-12 01:16:00', '2026-06-12 01:16:00'),
	(214, 96, 14, 'Alcohol en gel 250ml', 3, 6.50, 19.50, '2026-06-11 21:56:00', '2026-06-11 21:56:00'),
	(215, 96, 4, 'Amoxicilina 500mg', 3, 1.20, 3.60, '2026-06-11 21:56:00', '2026-06-11 21:56:00'),
	(216, 96, 9, 'Complejo B', 5, 1.80, 9.00, '2026-06-11 21:56:00', '2026-06-11 21:56:00'),
	(217, 96, 11, 'Omeprazol 20mg', 2, 1.00, 2.00, '2026-06-11 21:56:00', '2026-06-11 21:56:00'),
	(218, 97, 9, 'Complejo B', 5, 1.80, 9.00, '2026-06-11 18:32:00', '2026-06-11 18:32:00'),
	(219, 97, 3, 'Aspirina 100mg', 1, 0.60, 0.60, '2026-06-11 18:32:00', '2026-06-11 18:32:00'),
	(220, 97, 1, 'Paracetamol 500mg', 1, 0.50, 0.50, '2026-06-11 18:32:00', '2026-06-11 18:32:00'),
	(221, 97, 16, 'Metformina 850mg', 1, 0.70, 0.70, '2026-06-11 18:32:00', '2026-06-11 18:32:00'),
	(222, 98, 1, 'Paracetamol 500mg', 4, 0.50, 2.00, '2026-06-12 00:04:00', '2026-06-12 00:04:00'),
	(223, 99, 3, 'Aspirina 100mg', 1, 0.60, 0.60, '2026-06-11 15:41:00', '2026-06-11 15:41:00'),
	(224, 99, 5, 'Azitromicina 500mg', 3, 3.50, 10.50, '2026-06-11 15:41:00', '2026-06-11 15:41:00'),
	(225, 99, 4, 'Amoxicilina 500mg', 2, 1.20, 2.40, '2026-06-11 15:41:00', '2026-06-11 15:41:00'),
	(226, 100, 12, 'Crema Hidratante Cerave', 2, 39.90, 79.80, '2026-06-12 01:27:00', '2026-06-12 01:27:00'),
	(227, 100, 9, 'Complejo B', 2, 1.80, 3.60, '2026-06-12 01:27:00', '2026-06-12 01:27:00'),
	(228, 100, 3, 'Aspirina 100mg', 4, 0.60, 2.40, '2026-06-12 01:27:00', '2026-06-12 01:27:00'),
	(229, 101, 7, 'Sal de Andrews', 3, 1.00, 3.00, '2026-06-11 19:48:00', '2026-06-11 19:48:00'),
	(230, 101, 9, 'Complejo B', 3, 1.80, 5.40, '2026-06-11 19:48:00', '2026-06-11 19:48:00'),
	(231, 102, 6, 'Panadol Antigripal', 2, 1.50, 3.00, '2026-06-11 22:10:00', '2026-06-11 22:10:00'),
	(232, 102, 10, 'Loratadina 10mg', 5, 0.90, 4.50, '2026-06-11 22:10:00', '2026-06-11 22:10:00'),
	(233, 102, 9, 'Complejo B', 4, 1.80, 7.20, '2026-06-11 22:10:00', '2026-06-11 22:10:00'),
	(234, 102, 11, 'Omeprazol 20mg', 2, 1.00, 2.00, '2026-06-11 22:10:00', '2026-06-11 22:10:00'),
	(235, 103, 13, 'Protector Solar FPS50', 2, 45.00, 90.00, '2026-06-11 22:52:00', '2026-06-11 22:52:00'),
	(236, 103, 7, 'Sal de Andrews', 4, 1.00, 4.00, '2026-06-11 22:52:00', '2026-06-11 22:52:00'),
	(237, 103, 14, 'Alcohol en gel 250ml', 3, 6.50, 19.50, '2026-06-11 22:52:00', '2026-06-11 22:52:00'),
	(238, 103, 2, 'Ibuprofeno 400mg', 4, 0.80, 3.20, '2026-06-11 22:52:00', '2026-06-11 22:52:00'),
	(239, 104, 12, 'Crema Hidratante Cerave', 1, 39.90, 39.90, '2026-06-11 19:50:00', '2026-06-11 19:50:00'),
	(240, 105, 8, 'Vitamina C 1g', 1, 1.30, 1.30, '2026-06-11 18:44:00', '2026-06-11 18:44:00'),
	(241, 105, 10, 'Loratadina 10mg', 2, 0.90, 1.80, '2026-06-11 18:44:00', '2026-06-11 18:44:00'),
	(242, 106, 1, 'Paracetamol 500mg', 4, 0.50, 2.00, '2026-06-11 13:17:00', '2026-06-11 13:17:00'),
	(243, 106, 10, 'Loratadina 10mg', 4, 0.90, 3.60, '2026-06-11 13:17:00', '2026-06-11 13:17:00'),
	(244, 106, 4, 'Amoxicilina 500mg', 4, 1.20, 4.80, '2026-06-11 13:17:00', '2026-06-11 13:17:00'),
	(245, 107, 10, 'Loratadina 10mg', 3, 0.90, 2.70, '2026-06-11 19:52:00', '2026-06-11 19:52:00'),
	(246, 107, 14, 'Alcohol en gel 250ml', 2, 6.50, 13.00, '2026-06-11 19:52:00', '2026-06-11 19:52:00'),
	(247, 108, 9, 'Complejo B', 5, 1.80, 9.00, '2026-06-12 00:10:00', '2026-06-12 00:10:00'),
	(248, 108, 15, 'Enalapril 10mg', 1, 0.85, 0.85, '2026-06-12 00:10:00', '2026-06-12 00:10:00'),
	(249, 108, 10, 'Loratadina 10mg', 4, 0.90, 3.60, '2026-06-12 00:10:00', '2026-06-12 00:10:00'),
	(250, 109, 16, 'Metformina 850mg', 2, 0.70, 1.40, '2026-06-12 19:29:00', '2026-06-12 19:29:00'),
	(251, 109, 14, 'Alcohol en gel 250ml', 2, 6.50, 13.00, '2026-06-12 19:29:00', '2026-06-12 19:29:00'),
	(252, 110, 16, 'Metformina 850mg', 3, 0.70, 2.10, '2026-06-12 20:32:00', '2026-06-12 20:32:00'),
	(253, 110, 3, 'Aspirina 100mg', 3, 0.60, 1.80, '2026-06-12 20:32:00', '2026-06-12 20:32:00'),
	(254, 111, 4, 'Amoxicilina 500mg', 5, 1.20, 6.00, '2026-06-12 16:53:00', '2026-06-12 16:53:00'),
	(255, 111, 2, 'Ibuprofeno 400mg', 3, 0.80, 2.40, '2026-06-12 16:53:00', '2026-06-12 16:53:00'),
	(256, 111, 3, 'Aspirina 100mg', 5, 0.60, 3.00, '2026-06-12 16:53:00', '2026-06-12 16:53:00'),
	(257, 112, 10, 'Loratadina 10mg', 4, 0.90, 3.60, '2026-06-12 22:08:00', '2026-06-12 22:08:00'),
	(258, 112, 11, 'Omeprazol 20mg', 2, 1.00, 2.00, '2026-06-12 22:08:00', '2026-06-12 22:08:00'),
	(259, 113, 15, 'Enalapril 10mg', 2, 0.85, 1.70, '2026-06-12 17:31:00', '2026-06-12 17:31:00'),
	(260, 113, 2, 'Ibuprofeno 400mg', 4, 0.80, 3.20, '2026-06-12 17:31:00', '2026-06-12 17:31:00'),
	(261, 113, 4, 'Amoxicilina 500mg', 4, 1.20, 4.80, '2026-06-12 17:31:00', '2026-06-12 17:31:00'),
	(262, 114, 4, 'Amoxicilina 500mg', 4, 1.20, 4.80, '2026-06-13 01:04:00', '2026-06-13 01:04:00'),
	(263, 114, 1, 'Paracetamol 500mg', 2, 0.50, 1.00, '2026-06-13 01:04:00', '2026-06-13 01:04:00'),
	(264, 114, 10, 'Loratadina 10mg', 2, 0.90, 1.80, '2026-06-13 01:04:00', '2026-06-13 01:04:00'),
	(265, 115, 1, 'Paracetamol 500mg', 4, 0.50, 2.00, '2026-06-12 19:24:00', '2026-06-12 19:24:00'),
	(266, 115, 13, 'Protector Solar FPS50', 1, 45.00, 45.00, '2026-06-12 19:24:00', '2026-06-12 19:24:00'),
	(267, 115, 14, 'Alcohol en gel 250ml', 1, 6.50, 6.50, '2026-06-12 19:24:00', '2026-06-12 19:24:00'),
	(268, 116, 5, 'Azitromicina 500mg', 5, 3.50, 17.50, '2026-06-12 18:05:00', '2026-06-12 18:05:00'),
	(269, 117, 4, 'Amoxicilina 500mg', 4, 1.20, 4.80, '2026-06-13 22:38:00', '2026-06-13 22:38:00'),
	(270, 118, 12, 'Crema Hidratante Cerave', 1, 39.90, 39.90, '2026-06-13 20:22:00', '2026-06-13 20:22:00'),
	(271, 119, 4, 'Amoxicilina 500mg', 4, 1.20, 4.80, '2026-06-13 15:45:00', '2026-06-13 15:45:00'),
	(272, 119, 8, 'Vitamina C 1g', 4, 1.30, 5.20, '2026-06-13 15:45:00', '2026-06-13 15:45:00'),
	(273, 120, 16, 'Metformina 850mg', 4, 0.70, 2.80, '2026-06-13 14:27:00', '2026-06-13 14:27:00'),
	(274, 120, 8, 'Vitamina C 1g', 3, 1.30, 3.90, '2026-06-13 14:27:00', '2026-06-13 14:27:00'),
	(275, 120, 5, 'Azitromicina 500mg', 3, 3.50, 10.50, '2026-06-13 14:27:00', '2026-06-13 14:27:00'),
	(276, 121, 16, 'Metformina 850mg', 3, 0.70, 2.10, '2026-06-13 19:05:00', '2026-06-13 19:05:00'),
	(277, 122, 3, 'Aspirina 100mg', 2, 0.60, 1.20, '2026-06-13 13:24:00', '2026-06-13 13:24:00'),
	(278, 122, 11, 'Omeprazol 20mg', 5, 1.00, 5.00, '2026-06-13 13:24:00', '2026-06-13 13:24:00'),
	(279, 122, 9, 'Complejo B', 5, 1.80, 9.00, '2026-06-13 13:24:00', '2026-06-13 13:24:00'),
	(280, 123, 10, 'Loratadina 10mg', 3, 0.90, 2.70, '2026-06-14 00:39:00', '2026-06-14 00:39:00'),
	(281, 123, 1, 'Paracetamol 500mg', 1, 0.50, 0.50, '2026-06-14 00:39:00', '2026-06-14 00:39:00'),
	(282, 124, 10, 'Loratadina 10mg', 4, 0.90, 3.60, '2026-06-13 21:15:00', '2026-06-13 21:15:00'),
	(283, 124, 9, 'Complejo B', 4, 1.80, 7.20, '2026-06-13 21:15:00', '2026-06-13 21:15:00'),
	(284, 124, 8, 'Vitamina C 1g', 2, 1.30, 2.60, '2026-06-13 21:15:00', '2026-06-13 21:15:00'),
	(285, 125, 2, 'Ibuprofeno 400mg', 3, 0.80, 2.40, '2026-06-13 15:23:00', '2026-06-13 15:23:00'),
	(286, 125, 11, 'Omeprazol 20mg', 2, 1.00, 2.00, '2026-06-13 15:23:00', '2026-06-13 15:23:00'),
	(287, 126, 10, 'Loratadina 10mg', 5, 0.90, 4.50, '2026-06-13 17:40:00', '2026-06-13 17:40:00'),
	(288, 126, 9, 'Complejo B', 1, 1.80, 1.80, '2026-06-13 17:40:00', '2026-06-13 17:40:00'),
	(289, 127, 2, 'Ibuprofeno 400mg', 1, 0.80, 0.80, '2026-06-14 22:55:00', '2026-06-14 22:55:00'),
	(290, 127, 7, 'Sal de Andrews', 2, 1.00, 2.00, '2026-06-14 22:55:00', '2026-06-14 22:55:00'),
	(291, 127, 12, 'Crema Hidratante Cerave', 1, 39.90, 39.90, '2026-06-14 22:55:00', '2026-06-14 22:55:00'),
	(292, 128, 3, 'Aspirina 100mg', 2, 0.60, 1.20, '2026-06-14 18:56:00', '2026-06-14 18:56:00'),
	(293, 129, 13, 'Protector Solar FPS50', 3, 45.00, 135.00, '2026-06-15 01:41:00', '2026-06-15 01:41:00'),
	(294, 130, 7, 'Sal de Andrews', 3, 1.00, 3.00, '2026-06-14 16:13:00', '2026-06-14 16:13:00'),
	(295, 131, 13, 'Protector Solar FPS50', 4, 45.00, 180.00, '2026-06-14 19:58:00', '2026-06-14 19:58:00'),
	(296, 131, 6, 'Panadol Antigripal', 4, 1.50, 6.00, '2026-06-14 19:58:00', '2026-06-14 19:58:00'),
	(297, 132, 7, 'Sal de Andrews', 2, 1.00, 2.00, '2026-06-14 21:14:00', '2026-06-14 21:14:00'),
	(298, 132, 12, 'Crema Hidratante Cerave', 1, 39.90, 39.90, '2026-06-14 21:14:00', '2026-06-14 21:14:00'),
	(299, 133, 3, 'Aspirina 100mg', 5, 0.60, 3.00, '2026-06-14 21:13:00', '2026-06-14 21:13:00'),
	(300, 134, 1, 'Paracetamol 500mg', 4, 0.50, 2.00, '2026-06-15 01:59:00', '2026-06-15 01:59:00'),
	(301, 134, 15, 'Enalapril 10mg', 2, 0.85, 1.70, '2026-06-15 01:59:00', '2026-06-15 01:59:00'),
	(302, 135, 13, 'Protector Solar FPS50', 3, 45.00, 135.00, '2026-06-15 00:13:00', '2026-06-15 00:13:00'),
	(303, 135, 10, 'Loratadina 10mg', 5, 0.90, 4.50, '2026-06-15 00:13:00', '2026-06-15 00:13:00'),
	(304, 135, 16, 'Metformina 850mg', 4, 0.70, 2.80, '2026-06-15 00:13:00', '2026-06-15 00:13:00'),
	(305, 136, 16, 'Metformina 850mg', 5, 0.70, 3.50, '2026-06-14 16:58:00', '2026-06-14 16:58:00'),
	(306, 136, 1, 'Paracetamol 500mg', 2, 0.50, 1.00, '2026-06-14 16:58:00', '2026-06-14 16:58:00'),
	(307, 136, 10, 'Loratadina 10mg', 2, 0.90, 1.80, '2026-06-14 16:58:00', '2026-06-14 16:58:00'),
	(308, 137, 10, 'Loratadina 10mg', 4, 0.90, 3.60, '2026-06-14 23:03:00', '2026-06-14 23:03:00'),
	(309, 137, 12, 'Crema Hidratante Cerave', 4, 39.90, 159.60, '2026-06-14 23:03:00', '2026-06-14 23:03:00'),
	(310, 138, 14, 'Alcohol en gel 250ml', 2, 6.50, 13.00, '2026-06-14 23:03:00', '2026-06-14 23:03:00'),
	(311, 139, 9, 'Complejo B', 5, 1.80, 9.00, '2026-06-14 14:37:00', '2026-06-14 14:37:00'),
	(312, 139, 4, 'Amoxicilina 500mg', 1, 1.20, 1.20, '2026-06-14 14:37:00', '2026-06-14 14:37:00'),
	(313, 139, 3, 'Aspirina 100mg', 4, 0.60, 2.40, '2026-06-14 14:37:00', '2026-06-14 14:37:00'),
	(314, 140, 13, 'Protector Solar FPS50', 3, 45.00, 135.00, '2026-06-14 15:33:00', '2026-06-14 15:33:00'),
	(315, 141, 7, 'Sal de Andrews', 4, 1.00, 4.00, '2026-06-14 21:25:00', '2026-06-14 21:25:00'),
	(316, 141, 9, 'Complejo B', 2, 1.80, 3.60, '2026-06-14 21:25:00', '2026-06-14 21:25:00'),
	(317, 141, 1, 'Paracetamol 500mg', 3, 0.50, 1.50, '2026-06-14 21:25:00', '2026-06-14 21:25:00'),
	(318, 141, 10, 'Loratadina 10mg', 3, 0.90, 2.70, '2026-06-14 21:25:00', '2026-06-14 21:25:00'),
	(319, 142, 11, 'Omeprazol 20mg', 3, 1.00, 3.00, '2026-06-14 23:27:00', '2026-06-14 23:27:00'),
	(320, 143, 11, 'Omeprazol 20mg', 2, 1.00, 2.00, '2026-06-15 16:12:00', '2026-06-15 16:12:00'),
	(321, 143, 14, 'Alcohol en gel 250ml', 4, 6.50, 26.00, '2026-06-15 16:12:00', '2026-06-15 16:12:00'),
	(322, 143, 8, 'Vitamina C 1g', 1, 1.30, 1.30, '2026-06-15 16:12:00', '2026-06-15 16:12:00'),
	(323, 143, 5, 'Azitromicina 500mg', 5, 3.50, 17.50, '2026-06-15 16:12:00', '2026-06-15 16:12:00'),
	(324, 144, 11, 'Omeprazol 20mg', 5, 1.00, 5.00, '2026-06-15 18:39:00', '2026-06-15 18:39:00'),
	(325, 144, 1, 'Paracetamol 500mg', 3, 0.50, 1.50, '2026-06-15 18:39:00', '2026-06-15 18:39:00'),
	(326, 144, 5, 'Azitromicina 500mg', 1, 3.50, 3.50, '2026-06-15 18:39:00', '2026-06-15 18:39:00'),
	(327, 144, 10, 'Loratadina 10mg', 4, 0.90, 3.60, '2026-06-15 18:39:00', '2026-06-15 18:39:00'),
	(328, 145, 2, 'Ibuprofeno 400mg', 4, 0.80, 3.20, '2026-06-15 15:01:00', '2026-06-15 15:01:00'),
	(329, 145, 13, 'Protector Solar FPS50', 1, 45.00, 45.00, '2026-06-15 15:01:00', '2026-06-15 15:01:00'),
	(330, 146, 4, 'Amoxicilina 500mg', 4, 1.20, 4.80, '2026-06-15 15:22:00', '2026-06-15 15:22:00'),
	(331, 146, 8, 'Vitamina C 1g', 4, 1.30, 5.20, '2026-06-15 15:22:00', '2026-06-15 15:22:00'),
	(332, 146, 10, 'Loratadina 10mg', 3, 0.90, 2.70, '2026-06-15 15:22:00', '2026-06-15 15:22:00'),
	(333, 147, 3, 'Aspirina 100mg', 2, 0.60, 1.20, '2026-06-15 15:50:00', '2026-06-15 15:50:00'),
	(334, 148, 7, 'Sal de Andrews', 3, 1.00, 3.00, '2026-06-15 18:34:00', '2026-06-15 18:34:00'),
	(335, 148, 14, 'Alcohol en gel 250ml', 2, 6.50, 13.00, '2026-06-15 18:34:00', '2026-06-15 18:34:00'),
	(336, 148, 5, 'Azitromicina 500mg', 3, 3.50, 10.50, '2026-06-15 18:34:00', '2026-06-15 18:34:00'),
	(337, 149, 6, 'Panadol Antigripal', 2, 1.50, 3.00, '2026-06-15 14:14:00', '2026-06-15 14:14:00'),
	(338, 149, 4, 'Amoxicilina 500mg', 3, 1.20, 3.60, '2026-06-15 14:14:00', '2026-06-15 14:14:00'),
	(339, 149, 5, 'Azitromicina 500mg', 5, 3.50, 17.50, '2026-06-15 14:14:00', '2026-06-15 14:14:00'),
	(340, 149, 10, 'Loratadina 10mg', 2, 0.90, 1.80, '2026-06-15 14:14:00', '2026-06-15 14:14:00'),
	(341, 150, 6, 'Panadol Antigripal', 2, 1.50, 3.00, '2026-06-15 19:57:00', '2026-06-15 19:57:00'),
	(342, 150, 8, 'Vitamina C 1g', 4, 1.30, 5.20, '2026-06-15 19:57:00', '2026-06-15 19:57:00'),
	(343, 151, 16, 'Metformina 850mg', 1, 0.70, 0.70, '2026-06-15 20:54:00', '2026-06-15 20:54:00'),
	(344, 151, 6, 'Panadol Antigripal', 1, 1.50, 1.50, '2026-06-15 20:54:00', '2026-06-15 20:54:00'),
	(345, 152, 9, 'Complejo B', 2, 1.80, 3.60, '2026-06-15 18:00:00', '2026-06-15 18:00:00'),
	(346, 152, 12, 'Crema Hidratante Cerave', 3, 39.90, 119.70, '2026-06-15 18:00:00', '2026-06-15 18:00:00'),
	(347, 153, 11, 'Omeprazol 20mg', 2, 1.00, 2.00, '2026-06-15 18:03:00', '2026-06-15 18:03:00'),
	(348, 153, 12, 'Crema Hidratante Cerave', 3, 39.90, 119.70, '2026-06-15 18:03:00', '2026-06-15 18:03:00'),
	(349, 154, 1, 'Paracetamol 500mg', 2, 0.50, 1.00, '2026-06-15 16:52:00', '2026-06-15 16:52:00'),
	(350, 154, 2, 'Ibuprofeno 400mg', 2, 0.80, 1.60, '2026-06-15 16:52:00', '2026-06-15 16:52:00'),
	(351, 154, 3, 'Aspirina 100mg', 1, 0.60, 0.60, '2026-06-15 16:52:00', '2026-06-15 16:52:00'),
	(352, 155, 12, 'Crema Hidratante Cerave', 5, 39.90, 199.50, '2026-06-15 14:07:00', '2026-06-15 14:07:00'),
	(353, 155, 2, 'Ibuprofeno 400mg', 1, 0.80, 0.80, '2026-06-15 14:07:00', '2026-06-15 14:07:00'),
	(354, 155, 9, 'Complejo B', 5, 1.80, 9.00, '2026-06-15 14:07:00', '2026-06-15 14:07:00'),
	(355, 155, 3, 'Aspirina 100mg', 5, 0.60, 3.00, '2026-06-15 14:07:00', '2026-06-15 14:07:00');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
