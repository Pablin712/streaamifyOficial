-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 10, 2025 at 09:37 PM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u557565149_streamify`
--

-- --------------------------------------------------------

--
-- Table structure for table `asistencias`
--

CREATE TABLE `asistencias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empleado_id` bigint(20) UNSIGNED NOT NULL,
  `ruta_actual` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bancos`
--

CREATE TABLE `bancos` (
  `idban` bigint(20) UNSIGNED NOT NULL,
  `nombreban` varchar(100) NOT NULL,
  `propietarioban` varchar(150) NOT NULL,
  `cedulaban` varchar(20) NOT NULL,
  `numeroban` varchar(20) NOT NULL,
  `tipoban` varchar(50) NOT NULL,
  `detalleban` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categorias`
--

CREATE TABLE `categorias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clientes`
--

CREATE TABLE `clientes` (
  `idcli` bigint(20) UNSIGNED NOT NULL,
  `nombrecli` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `telefonocli` varchar(50) DEFAULT NULL,
  `saldo` decimal(15,2) NOT NULL DEFAULT 0.00,
  `pais` varchar(30) NOT NULL DEFAULT 'Ecuador',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `codigo_referidor` varchar(50) DEFAULT NULL,
  `referido_por` int(10) UNSIGNED DEFAULT NULL,
  `ya_compro` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `clientes`
--
DELIMITER $$
CREATE TRIGGER `trg_insert_codigo_referidor` BEFORE INSERT ON `clientes` FOR EACH ROW BEGIN
    SET NEW.codigo_referidor = UPPER(CONCAT(SUBSTRING_INDEX(NEW.nombrecli, ' ', 1), '-', LPAD(NEW.idcli, 3, '0')));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_update_codigo_referidor` BEFORE UPDATE ON `clientes` FOR EACH ROW BEGIN
    IF NEW.nombrecli != OLD.nombrecli THEN
        SET NEW.codigo_referidor = UPPER(CONCAT(SUBSTRING_INDEX(NEW.nombrecli, ' ', 1), '-', LPAD(NEW.idcli, 3, '0')));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `contabilidad`
--

CREATE TABLE `contabilidad` (
  `idcon` bigint(20) UNSIGNED NOT NULL,
  `mes` int(11) NOT NULL DEFAULT extract(month from curdate()),
  `año` int(11) NOT NULL DEFAULT extract(year from curdate()),
  `detalle` varchar(20) NOT NULL,
  `num_cuentas` int(11) NOT NULL,
  `num_usuarios` int(11) NOT NULL,
  `ingresos` decimal(15,2) NOT NULL,
  `costos` decimal(15,2) NOT NULL,
  `gastos` decimal(15,2) NOT NULL,
  `ganancias` decimal(15,2) NOT NULL,
  `renta` decimal(5,2) NOT NULL,
  `num_ventas` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `costos`
--

CREATE TABLE `costos` (
  `idcos` bigint(20) UNSIGNED NOT NULL,
  `idcue` varchar(50) DEFAULT NULL,
  `fechacos` date NOT NULL DEFAULT curdate(),
  `montocos` decimal(8,2) NOT NULL,
  `descripcioncos` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cuentas`
--

CREATE TABLE `cuentas` (
  `idcue` varchar(50) NOT NULL,
  `idval` varchar(30) DEFAULT NULL,
  `fechavencue` date NOT NULL,
  `usuariocue` varchar(50) NOT NULL,
  `contrasenacue` varchar(50) NOT NULL,
  `caidacue` tinyint(1) NOT NULL,
  `activocue` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `cuentas`
--
DELIMITER $$
CREATE TRIGGER `insertar_perfiles` AFTER INSERT ON `cuentas` FOR EACH ROW BEGIN
    -- Insertar perfiles para Netflix
    IF NEW.idcue LIKE 'NETFLIX%' THEN
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
            (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, '1000'),
            (CONCAT(NEW.idcue, '.2'), NEW.idcue, 2, '5555'),
            (CONCAT(NEW.idcue, '.3'), NEW.idcue, 3, '8833'),
            (CONCAT(NEW.idcue, '.4'), NEW.idcue, 4, '6622'),
            (CONCAT(NEW.idcue, '.5'), NEW.idcue, 5, '9000');
    
    ELSEIF NEW.idcue LIKE 'DISNEY%' THEN
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
            (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, '1000'),
            (CONCAT(NEW.idcue, '.2'), NEW.idcue, 2, '5555'),
            (CONCAT(NEW.idcue, '.3'), NEW.idcue, 3, '8833'),
            (CONCAT(NEW.idcue, '.4'), NEW.idcue, 4, '6622'),
            (CONCAT(NEW.idcue, '.5'), NEW.idcue, 5, '9000'),
            (CONCAT(NEW.idcue, '.6'), NEW.idcue, 6, '2012'),
            (CONCAT(NEW.idcue, '.7'), NEW.idcue, 7, '2000');
    
    ELSEIF NEW.idcue LIKE 'PRIME%' THEN
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
            (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, '10000'),
            (CONCAT(NEW.idcue, '.2'), NEW.idcue, 2, '55555'),
            (CONCAT(NEW.idcue, '.3'), NEW.idcue, 3, '88333'),
            (CONCAT(NEW.idcue, '.4'), NEW.idcue, 4, '66222'),
            (CONCAT(NEW.idcue, '.5'), NEW.idcue, 5, '90000'),
            (CONCAT(NEW.idcue, '.6'), NEW.idcue, 6, '20122');
    
    ELSEIF NEW.idcue LIKE 'MAX%' THEN
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
            (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, '1000'),
            (CONCAT(NEW.idcue, '.2'), NEW.idcue, 2, '5555'),
            (CONCAT(NEW.idcue, '.3'), NEW.idcue, 3, '8833'),
            (CONCAT(NEW.idcue, '.4'), NEW.idcue, 4, '6622'),
            (CONCAT(NEW.idcue, '.5'), NEW.idcue, 5, '9000');
    
    ELSEIF NEW.idcue LIKE 'PARAMOUNT%' THEN
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
            (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, '1000'),
            (CONCAT(NEW.idcue, '.2'), NEW.idcue, 2, '5555'),
            (CONCAT(NEW.idcue, '.3'), NEW.idcue, 3, '8833'),
            (CONCAT(NEW.idcue, '.4'), NEW.idcue, 4, '6622'),
            (CONCAT(NEW.idcue, '.5'), NEW.idcue, 5, '9000'),
            (CONCAT(NEW.idcue, '.6'), NEW.idcue, 6, '2012');
    
    ELSEIF NEW.idcue LIKE 'SPOTIFY%' THEN
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
            (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, 'owner'),
            (CONCAT(NEW.idcue, '.2'), NEW.idcue, 2, 'invit'),
            (CONCAT(NEW.idcue, '.3'), NEW.idcue, 3, 'invit'),
            (CONCAT(NEW.idcue, '.4'), NEW.idcue, 4, 'invit'),
            (CONCAT(NEW.idcue, '.5'), NEW.idcue, 5, 'invit'),
            (CONCAT(NEW.idcue, '.6'), NEW.idcue, 6, 'invit');
    
    ELSEIF NEW.idcue LIKE 'MAGIS%' THEN
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
            (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, ''),
            (CONCAT(NEW.idcue, '.2'), NEW.idcue, 2, ''),
            (CONCAT(NEW.idcue, '.3'), NEW.idcue, 3, '');
    
    ELSEIF NEW.idcue LIKE 'CRUNCHY%' THEN
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
            (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, ''),
            (CONCAT(NEW.idcue, '.2'), NEW.idcue, 2, ''),
            (CONCAT(NEW.idcue, '.3'), NEW.idcue, 3, ''),
            (CONCAT(NEW.idcue, '.4'), NEW.idcue, 4, ''),
            (CONCAT(NEW.idcue, '.5'), NEW.idcue, 5, '');
    
    ELSEIF NEW.idcue LIKE 'IND%' THEN
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
            (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, 'aparte');
    
    ELSEIF NEW.idcue LIKE 'COM%' THEN
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
            (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, 'Cmplta');
    
    ELSE
        INSERT INTO perfiles (idper, idcue, numeroper, pinper) VALUES
            (CONCAT(NEW.idcue, '.1'), NEW.idcue, 1, '1111'),
            (CONCAT(NEW.idcue, '.2'), NEW.idcue, 2, '2222'),
            (CONCAT(NEW.idcue, '.3'), NEW.idcue, 3, '3333');
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `daily_statistics`
--

CREATE TABLE `daily_statistics` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `active_users` int(11) NOT NULL,
  `affected_customers` int(11) NOT NULL DEFAULT 0,
  `pending_payments` int(11) NOT NULL DEFAULT 0,
  `danger_accounts` int(11) NOT NULL DEFAULT 0,
  `accounts` int(11) NOT NULL DEFAULT 0,
  `daily_revenue` decimal(10,2) NOT NULL DEFAULT 0.00,
  `daily_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `daily_bill` decimal(10,2) NOT NULL DEFAULT 0.00,
  `daily_sales` int(11) NOT NULL DEFAULT 0,
  `new_customers` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detalles_venta`
--

CREATE TABLE `detalles_venta` (
  `iddet` bigint(20) UNSIGNED NOT NULL,
  `idven` varchar(20) NOT NULL,
  `idper` varchar(50) DEFAULT NULL,
  `descripciondet` varchar(60) DEFAULT NULL,
  `fechavendet` date NOT NULL,
  `montodet` decimal(8,2) NOT NULL,
  `activodet` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `detalles_venta`
--
DELIMITER $$
CREATE TRIGGER `trg_actualizar_total_venta_delete` AFTER DELETE ON `detalles_venta` FOR EACH ROW BEGIN
    -- Calcula el total de la venta actual
    UPDATE ventas
    SET totalpagoven = (
        SELECT COALESCE(SUM(montodet), 0)
        FROM detalles_venta
        WHERE idven = OLD.idven
    )
    WHERE idven = OLD.idven;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_actualizar_total_venta_insert` AFTER INSERT ON `detalles_venta` FOR EACH ROW BEGIN
    -- Calcula el total de la venta actual
    UPDATE ventas
    SET totalpagoven = (
        SELECT COALESCE(SUM(montodet), 0)
        FROM detalles_venta
        WHERE idven = NEW.idven
    )
    WHERE idven = NEW.idven;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_actualizar_total_venta_update` AFTER UPDATE ON `detalles_venta` FOR EACH ROW BEGIN
    -- Calcula el total de la venta actual
    UPDATE ventas
    SET totalpagoven = (
        SELECT COALESCE(SUM(montodet), 0)
        FROM detalles_venta
        WHERE idven = NEW.idven
    )
    WHERE idven = NEW.idven;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `detalle_productos`
--

CREATE TABLE `detalle_productos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` bigint(20) UNSIGNED NOT NULL,
  `idser` varchar(10) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `meses` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `empleados`
--

CREATE TABLE `empleados` (
  `idemp` bigint(20) UNSIGNED NOT NULL,
  `nombreemp` varchar(50) NOT NULL,
  `telefonoemp` varchar(15) DEFAULT NULL,
  `usuarioemp` varchar(20) NOT NULL,
  `foto_url` varchar(255) DEFAULT NULL,
  `passwordemp` varchar(60) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estado_recargas`
--

CREATE TABLE `estado_recargas` (
  `idestado` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
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
-- Table structure for table `gastos`
--

CREATE TABLE `gastos` (
  `idgas` bigint(20) UNSIGNED NOT NULL,
  `idtip` bigint(20) UNSIGNED NOT NULL,
  `fechagas` date NOT NULL DEFAULT curdate(),
  `montogas` decimal(8,2) NOT NULL,
  `descripciongas` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `historial`
--

CREATE TABLE `historial` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `accion` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `empleado_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
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
-- Table structure for table `job_batches`
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
-- Table structure for table `mails`
--

CREATE TABLE `mails` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `host` varchar(20) NOT NULL DEFAULT 'Hostinger',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mantenimientos`
--

CREATE TABLE `mantenimientos` (
  `idman` bigint(20) UNSIGNED NOT NULL,
  `idcue` varchar(20) NOT NULL,
  `descripcionman` varchar(255) NOT NULL,
  `fechaman` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL DEFAULT 'App\\Models\\Empleado',
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pedidos`
--

CREATE TABLE `pedidos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `idcli` bigint(20) UNSIGNED NOT NULL,
  `producto_id` bigint(20) UNSIGNED NOT NULL,
  `idestado` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `fechapedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `respuesta` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `perfiles`
--

CREATE TABLE `perfiles` (
  `idper` varchar(50) NOT NULL,
  `idcue` varchar(50) DEFAULT NULL,
  `numeroper` int(11) NOT NULL,
  `pinper` varchar(255) DEFAULT 'ninguno'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `productos`
--

CREATE TABLE `productos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `codigopro` varchar(20) NOT NULL,
  `nombrepro` varchar(100) NOT NULL,
  `preciopro` decimal(10,2) NOT NULL,
  `estrellaspro` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `descripcionpro` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `tipo_producto_id` bigint(20) UNSIGNED NOT NULL,
  `categoria_id` bigint(20) UNSIGNED NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proveedores`
--

CREATE TABLE `proveedores` (
  `idpro` bigint(20) UNSIGNED NOT NULL,
  `nombrepro` varchar(20) NOT NULL,
  `telefonopro` varchar(15) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `activopro` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recargas`
--

CREATE TABLE `recargas` (
  `idrec` bigint(20) UNSIGNED NOT NULL,
  `idcli` bigint(20) UNSIGNED NOT NULL,
  `numcomprobante` varchar(50) NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `idestado` bigint(20) UNSIGNED NOT NULL,
  `idban` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rolesAntes`
--

CREATE TABLE `rolesAntes` (
  `idrol` varchar(20) NOT NULL,
  `detallerol` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `secuencia_factura`
--

CREATE TABLE `secuencia_factura` (
  `id` bigint(20) NOT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `servicios`
--

CREATE TABLE `servicios` (
  `idser` varchar(10) NOT NULL,
  `nombreser` varchar(20) NOT NULL,
  `completoser` decimal(5,2) DEFAULT NULL,
  `precioser` decimal(5,2) DEFAULT NULL,
  `comboser` decimal(5,2) DEFAULT NULL,
  `reventaser` decimal(5,2) DEFAULT NULL,
  `revcompser` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tareas`
--

CREATE TABLE `tareas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombretarea` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `prioridad` enum('alta','media','baja') NOT NULL DEFAULT 'baja',
  `completada` tinyint(1) NOT NULL DEFAULT 0,
  `fechalimit` datetime DEFAULT NULL,
  `completada_por` bigint(20) UNSIGNED DEFAULT NULL,
  `fecha_completada` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tipos_producto`
--

CREATE TABLE `tipos_producto` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tipo_gasto`
--

CREATE TABLE `tipo_gasto` (
  `idtip` bigint(20) UNSIGNED NOT NULL,
  `detalletip` varchar(30) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `usuarios_activos_mensuales`
-- (See below for the actual view)
--
CREATE TABLE `usuarios_activos_mensuales` (
`anio` int(5)
,`mes` int(3)
,`promedio_usuarios_activos` decimal(11,0)
);

-- --------------------------------------------------------

--
-- Table structure for table `valores`
--

CREATE TABLE `valores` (
  `idval` varchar(30) NOT NULL,
  `idser` varchar(10) NOT NULL,
  `idpro` bigint(20) UNSIGNED NOT NULL,
  `costoval` decimal(5,2) DEFAULT NULL,
  `tipoval` enum('completo','individual','hibrido') NOT NULL DEFAULT 'completo' COMMENT 'Tipo de valor: completo, individual o híbrido',
  `pantminval` int(11) DEFAULT NULL,
  `pantmaxval` int(11) DEFAULT NULL,
  `mesesval` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `activoval` tinyint(1) DEFAULT 1,
  `bot` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `valores`
--
DELIMITER $$
CREATE TRIGGER `trigger_generar_idval` BEFORE INSERT ON `valores` FOR EACH ROW BEGIN
    DECLARE tipo_valor VARCHAR(10);
    DECLARE proveedor_nombre VARCHAR(100);

    SET tipo_valor = LEFT(NEW.tipoval, 3);

    SELECT SUBSTRING_INDEX(nombrepro, ' ', 1) INTO proveedor_nombre
    FROM proveedores 
    WHERE idpro = NEW.idpro 
    LIMIT 1;

    SET NEW.idval = CONCAT(NEW.idser, '-', proveedor_nombre, '-', tipo_valor, '-', NEW.mesesval, 'm');
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `ventas`
--

CREATE TABLE `ventas` (
  `idven` varchar(20) NOT NULL,
  `idemp` bigint(20) UNSIGNED NOT NULL,
  `idcli` bigint(20) UNSIGNED NOT NULL,
  `fechaven` date NOT NULL DEFAULT curdate(),
  `totalpagoven` decimal(8,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `ventas`
--
DELIMITER $$
CREATE TRIGGER `trg_generar_idventa` BEFORE INSERT ON `ventas` FOR EACH ROW BEGIN
    DECLARE secuencia BIGINT;
    DECLARE establecimiento VARCHAR(3) DEFAULT '001';  -- Número de establecimiento
    DECLARE facturero VARCHAR(3) DEFAULT '001';        -- Número de facturero

    -- Insertar un nuevo registro en la tabla secuencia_factura y obtener el ID generado
    INSERT INTO secuencia_factura () VALUES ();
    SET secuencia = LAST_INSERT_ID();

    -- Generar el ID de venta con el formato: establecimiento-facturero-secuencia de 9 dígitos
    SET NEW.idven = CONCAT(establecimiento, '-', facturero, '-', LPAD(secuencia, 9, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `ventas_diarias`
--

CREATE TABLE `ventas_diarias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `numero_venta` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `ventas_mensuales`
-- (See below for the actual view)
--
CREATE TABLE `ventas_mensuales` (
`anio` int(5)
,`mes` int(3)
,`total_ventas` bigint(21)
,`total_monto` decimal(30,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_clientes_usuarios`
-- (See below for the actual view)
--
CREATE TABLE `view_clientes_usuarios` (
`idcli` bigint(20) unsigned
,`nombre_cliente` varchar(50)
,`usuarios` bigint(21)
,`facturado` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_usuarios_activos`
-- (See below for the actual view)
--
CREATE TABLE `view_usuarios_activos` (
`idcli` bigint(20) unsigned
,`nombre_cliente` varchar(50)
,`idven` varchar(20)
,`iddet` bigint(20) unsigned
,`idcue` varchar(50)
,`perfil` int(11)
,`fecha_vencimiento` date
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asistencias_empleado_id_foreign` (`empleado_id`);

--
-- Indexes for table `bancos`
--
ALTER TABLE `bancos`
  ADD PRIMARY KEY (`idban`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categorias_nombre_unique` (`nombre`);

--
-- Indexes for table `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`idcli`),
  ADD UNIQUE KEY `clientes_email_unique` (`email`);

--
-- Indexes for table `contabilidad`
--
ALTER TABLE `contabilidad`
  ADD PRIMARY KEY (`idcon`);

--
-- Indexes for table `costos`
--
ALTER TABLE `costos`
  ADD PRIMARY KEY (`idcos`),
  ADD KEY `costos_idcue_foreign` (`idcue`);

--
-- Indexes for table `cuentas`
--
ALTER TABLE `cuentas`
  ADD PRIMARY KEY (`idcue`),
  ADD KEY `fk_valores_idval` (`idval`);

--
-- Indexes for table `daily_statistics`
--
ALTER TABLE `daily_statistics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `daily_statistics_date_unique` (`date`);

--
-- Indexes for table `detalles_venta`
--
ALTER TABLE `detalles_venta`
  ADD PRIMARY KEY (`iddet`),
  ADD KEY `detalles_venta_idven_foreign` (`idven`),
  ADD KEY `detalles_venta_idper_foreign` (`idper`);

--
-- Indexes for table `detalle_productos`
--
ALTER TABLE `detalle_productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detalle_productos_producto_id_foreign` (`producto_id`);

--
-- Indexes for table `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`idemp`);

--
-- Indexes for table `estado_recargas`
--
ALTER TABLE `estado_recargas`
  ADD PRIMARY KEY (`idestado`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`idgas`),
  ADD KEY `gastos_idtip_foreign` (`idtip`);

--
-- Indexes for table `historial`
--
ALTER TABLE `historial`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mails`
--
ALTER TABLE `mails`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mails_email_unique` (`email`);

--
-- Indexes for table `mantenimientos`
--
ALTER TABLE `mantenimientos`
  ADD PRIMARY KEY (`idman`),
  ADD UNIQUE KEY `mantenimientos_idcue_unique` (`idcue`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedidos_idcli_foreign` (`idcli`),
  ADD KEY `pedidos_producto_id_foreign` (`producto_id`),
  ADD KEY `pedidos_idestado_foreign` (`idestado`);

--
-- Indexes for table `perfiles`
--
ALTER TABLE `perfiles`
  ADD PRIMARY KEY (`idper`),
  ADD KEY `perfiles_idcue_foreign` (`idcue`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `productos_codigopro_unique` (`codigopro`),
  ADD KEY `productos_tipo_producto_id_foreign` (`tipo_producto_id`),
  ADD KEY `productos_categoria_id_foreign` (`categoria_id`);

--
-- Indexes for table `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`idpro`);

--
-- Indexes for table `recargas`
--
ALTER TABLE `recargas`
  ADD PRIMARY KEY (`idrec`),
  ADD UNIQUE KEY `recargas_numcomprobante_unique` (`numcomprobante`),
  ADD KEY `recargas_idcli_foreign` (`idcli`),
  ADD KEY `recargas_idestado_foreign` (`idestado`),
  ADD KEY `recargas_idban_foreign` (`idban`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `rolesAntes`
--
ALTER TABLE `rolesAntes`
  ADD PRIMARY KEY (`idrol`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `secuencia_factura`
--
ALTER TABLE `secuencia_factura`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`idser`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tareas_completada_por_foreign` (`completada_por`);

--
-- Indexes for table `tipos_producto`
--
ALTER TABLE `tipos_producto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tipos_producto_nombre_unique` (`nombre`);

--
-- Indexes for table `tipo_gasto`
--
ALTER TABLE `tipo_gasto`
  ADD PRIMARY KEY (`idtip`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `valores`
--
ALTER TABLE `valores`
  ADD PRIMARY KEY (`idval`),
  ADD KEY `valores_idser_foreign` (`idser`),
  ADD KEY `valores_idpro_foreign` (`idpro`);

--
-- Indexes for table `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`idven`),
  ADD KEY `ventas_idemp_foreign` (`idemp`),
  ADD KEY `ventas_idcli_foreign` (`idcli`);

--
-- Indexes for table `ventas_diarias`
--
ALTER TABLE `ventas_diarias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ventas_diarias_fecha_unique` (`fecha`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bancos`
--
ALTER TABLE `bancos`
  MODIFY `idban` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `idcli` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contabilidad`
--
ALTER TABLE `contabilidad`
  MODIFY `idcon` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `costos`
--
ALTER TABLE `costos`
  MODIFY `idcos` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daily_statistics`
--
ALTER TABLE `daily_statistics`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detalles_venta`
--
ALTER TABLE `detalles_venta`
  MODIFY `iddet` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detalle_productos`
--
ALTER TABLE `detalle_productos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `empleados`
--
ALTER TABLE `empleados`
  MODIFY `idemp` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estado_recargas`
--
ALTER TABLE `estado_recargas`
  MODIFY `idestado` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gastos`
--
ALTER TABLE `gastos`
  MODIFY `idgas` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `historial`
--
ALTER TABLE `historial`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mails`
--
ALTER TABLE `mails`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mantenimientos`
--
ALTER TABLE `mantenimientos`
  MODIFY `idman` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productos`
--
ALTER TABLE `productos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `idpro` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recargas`
--
ALTER TABLE `recargas`
  MODIFY `idrec` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `secuencia_factura`
--
ALTER TABLE `secuencia_factura`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tipos_producto`
--
ALTER TABLE `tipos_producto`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tipo_gasto`
--
ALTER TABLE `tipo_gasto`
  MODIFY `idtip` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ventas_diarias`
--
ALTER TABLE `ventas_diarias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Structure for view `usuarios_activos_mensuales`
--
DROP TABLE IF EXISTS `usuarios_activos_mensuales`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u557565149_pablin`@`127.0.0.1` SQL SECURITY DEFINER VIEW `usuarios_activos_mensuales`  AS SELECT year(`daily_statistics`.`date`) AS `anio`, month(`daily_statistics`.`date`) AS `mes`, round(avg(`daily_statistics`.`active_users`),0) AS `promedio_usuarios_activos` FROM `daily_statistics` GROUP BY year(`daily_statistics`.`date`), month(`daily_statistics`.`date`) ORDER BY year(`daily_statistics`.`date`) DESC, month(`daily_statistics`.`date`) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `ventas_mensuales`
--
DROP TABLE IF EXISTS `ventas_mensuales`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u557565149_pablin`@`127.0.0.1` SQL SECURITY DEFINER VIEW `ventas_mensuales`  AS SELECT year(`ventas`.`fechaven`) AS `anio`, month(`ventas`.`fechaven`) AS `mes`, count(0) AS `total_ventas`, sum(`ventas`.`totalpagoven`) AS `total_monto` FROM `ventas` GROUP BY year(`ventas`.`fechaven`), month(`ventas`.`fechaven`) ORDER BY year(`ventas`.`fechaven`) DESC, month(`ventas`.`fechaven`) DESC ;

-- --------------------------------------------------------

--
-- Structure for view `view_clientes_usuarios`
--
DROP TABLE IF EXISTS `view_clientes_usuarios`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u557565149_pablin`@`127.0.0.1` SQL SECURITY DEFINER VIEW `view_clientes_usuarios`  AS SELECT `u`.`idcli` AS `idcli`, `u`.`nombre_cliente` AS `nombre_cliente`, count(`u`.`idcli`) AS `usuarios`, `calcular_total_pagado_mes`(`u`.`idcli`,month(curdate()),year(curdate())) AS `facturado` FROM `view_usuarios_activos` AS `u` GROUP BY `u`.`idcli`, `u`.`nombre_cliente` ;

-- --------------------------------------------------------

--
-- Structure for view `view_usuarios_activos`
--
DROP TABLE IF EXISTS `view_usuarios_activos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u557565149_pablin`@`127.0.0.1` SQL SECURITY DEFINER VIEW `view_usuarios_activos`  AS SELECT `v`.`idcli` AS `idcli`, `cl`.`nombrecli` AS `nombre_cliente`, `dv`.`idven` AS `idven`, `dv`.`iddet` AS `iddet`, `p`.`idcue` AS `idcue`, `p`.`numeroper` AS `perfil`, `dv`.`fechavendet` AS `fecha_vencimiento` FROM ((((`detalles_venta` `dv` join `ventas` `v` on(`dv`.`idven` = `v`.`idven`)) join `clientes` `cl` on(`v`.`idcli` = `cl`.`idcli`)) join `perfiles` `p` on(`dv`.`idper` = `p`.`idper`)) join `cuentas` `c` on(`p`.`idcue` = `c`.`idcue`)) WHERE `dv`.`activodet` = 1 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `asistencias`
--
ALTER TABLE `asistencias`
  ADD CONSTRAINT `asistencias_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`idemp`) ON DELETE CASCADE;

--
-- Constraints for table `costos`
--
ALTER TABLE `costos`
  ADD CONSTRAINT `costos_idcue_foreign` FOREIGN KEY (`idcue`) REFERENCES `cuentas` (`idcue`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cuentas`
--
ALTER TABLE `cuentas`
  ADD CONSTRAINT `fk_valores_idval` FOREIGN KEY (`idval`) REFERENCES `valores` (`idval`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detalles_venta`
--
ALTER TABLE `detalles_venta`
  ADD CONSTRAINT `detalles_venta_idper_foreign` FOREIGN KEY (`idper`) REFERENCES `perfiles` (`idper`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detalles_venta_idven_foreign` FOREIGN KEY (`idven`) REFERENCES `ventas` (`idven`);

--
-- Constraints for table `detalle_productos`
--
ALTER TABLE `detalle_productos`
  ADD CONSTRAINT `detalle_productos_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Constraints for table `gastos`
--
ALTER TABLE `gastos`
  ADD CONSTRAINT `gastos_idtip_foreign` FOREIGN KEY (`idtip`) REFERENCES `tipo_gasto` (`idtip`);

--
-- Constraints for table `mantenimientos`
--
ALTER TABLE `mantenimientos`
  ADD CONSTRAINT `mantenimientos_idcue_foreign` FOREIGN KEY (`idcue`) REFERENCES `cuentas` (`idcue`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_idcli_foreign` FOREIGN KEY (`idcli`) REFERENCES `clientes` (`idcli`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedidos_idestado_foreign` FOREIGN KEY (`idestado`) REFERENCES `estado_recargas` (`idestado`),
  ADD CONSTRAINT `pedidos_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `perfiles`
--
ALTER TABLE `perfiles`
  ADD CONSTRAINT `perfiles_idcue_foreign` FOREIGN KEY (`idcue`) REFERENCES `cuentas` (`idcue`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  ADD CONSTRAINT `productos_tipo_producto_id_foreign` FOREIGN KEY (`tipo_producto_id`) REFERENCES `tipos_producto` (`id`);

--
-- Constraints for table `recargas`
--
ALTER TABLE `recargas`
  ADD CONSTRAINT `recargas_idban_foreign` FOREIGN KEY (`idban`) REFERENCES `bancos` (`idban`) ON DELETE CASCADE,
  ADD CONSTRAINT `recargas_idcli_foreign` FOREIGN KEY (`idcli`) REFERENCES `clientes` (`idcli`) ON DELETE CASCADE,
  ADD CONSTRAINT `recargas_idestado_foreign` FOREIGN KEY (`idestado`) REFERENCES `estado_recargas` (`idestado`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tareas`
--
ALTER TABLE `tareas`
  ADD CONSTRAINT `tareas_completada_por_foreign` FOREIGN KEY (`completada_por`) REFERENCES `empleados` (`idemp`) ON DELETE SET NULL;

--
-- Constraints for table `valores`
--
ALTER TABLE `valores`
  ADD CONSTRAINT `valores_idpro_foreign` FOREIGN KEY (`idpro`) REFERENCES `proveedores` (`idpro`),
  ADD CONSTRAINT `valores_idser_foreign` FOREIGN KEY (`idser`) REFERENCES `servicios` (`idser`);

--
-- Constraints for table `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_idcli_foreign` FOREIGN KEY (`idcli`) REFERENCES `clientes` (`idcli`),
  ADD CONSTRAINT `ventas_idemp_foreign` FOREIGN KEY (`idemp`) REFERENCES `empleados` (`idemp`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
