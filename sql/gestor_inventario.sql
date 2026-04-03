-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-04-2026 a las 00:05:09
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
-- Base de datos: `gestor_inventario`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `ID_CAT` int(11) NOT NULL,
  `GLOSA_CATEGORIA` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`ID_CAT`, `GLOSA_CATEGORIA`) VALUES
(1, 'Equipos Computacionales'),
(2, 'Periféricos y Accesorios'),
(3, 'Equipamiento de Redes');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispositivos`
--

CREATE TABLE `dispositivos` (
  `ID_DISP` int(11) NOT NULL,
  `ID_SUBCAT` int(11) DEFAULT NULL,
  `ID_UBICACION` int(11) DEFAULT NULL,
  `FECHA_REGISTRO` varchar(10) DEFAULT NULL,
  `ID_ESTADO_CGU` int(11) DEFAULT NULL,
  `ID_MARCA` int(11) DEFAULT NULL,
  `MODELO` varchar(150) DEFAULT NULL,
  `SERIE` varchar(150) DEFAULT NULL,
  `IMAGEN` varchar(300) DEFAULT NULL,
  `IP` varchar(45) DEFAULT NULL,
  `MAC` varchar(20) DEFAULT NULL,
  `NOMBRE_MAQUINA` varchar(50) DEFAULT NULL,
  `CODIGO_INVENTARIO` int(11) DEFAULT NULL,
  `CLAVE_ACCESO` varchar(100) DEFAULT NULL,
  `ID_PROVEEDOR` int(11) DEFAULT NULL,
  `OBSERVACION` varchar(100) DEFAULT NULL,
  `ID_USUARIO_REGISTRO` int(11) DEFAULT NULL,
  `ELIMINADO` tinyint(1) DEFAULT 0,
  `FECHA_ELIMINACION` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dispositivos`
--

INSERT INTO `dispositivos` (`ID_DISP`, `ID_SUBCAT`, `ID_UBICACION`, `FECHA_REGISTRO`, `ID_ESTADO_CGU`, `ID_MARCA`, `MODELO`, `SERIE`, `IMAGEN`, `IP`, `MAC`, `NOMBRE_MAQUINA`, `CODIGO_INVENTARIO`, `CLAVE_ACCESO`, `ID_PROVEEDOR`, `OBSERVACION`, `ID_USUARIO_REGISTRO`, `ELIMINADO`, `FECHA_ELIMINACION`) VALUES
(0, 101, 1000, '2023-01-15', 0, 1, 'Terminal Administrativa Anemo', 'FTE-001-MND', NULL, '10.0.1.15', '00:1A:2B:3C:4D:5E', 'PC-JGUNNHILDR', 10001, 'Dandelion2023!', 1, 'Equipo principal de Jean', 1, 0, NULL),
(1, 201, 1000, '2023-01-15', 0, 3, 'Cristal Visor 24\"', 'ALK-24-009', NULL, NULL, NULL, NULL, 10002, NULL, 1, 'Monitor dual izquierdo', 1, 0, NULL),
(2, 102, 3000, '2023-02-10', 0, 4, 'Daguerrotipo Táctico Inazuma', 'LY-NOTE-992', NULL, '10.0.3.50', 'AA:BB:CC:DD:EE:FF', 'LT-KSARA', 20005, 'GloryToShogun!', 3, 'Asignado a Kujou Sara para terreno', 9, 0, NULL),
(3, 301, 3000, '2023-02-11', 0, 1, 'Enrutador Ley-Line Mesh X', 'ROU-FT-X88', NULL, '10.0.3.1', '11:22:33:44:55:66', 'GW-TENRYOU', 20006, 'admin/admin', 2, 'Router principal del cuartel', 9, 0, NULL),
(4, 101, 4000, '2023-05-20', 0, 2, 'Akasha Terminal Pro', 'AK-PRO-001', NULL, '10.0.4.12', 'FF:EE:DD:CC:BB:AA', 'PC-ALHAITHAM', 30010, 'LogicAndReason01', 1, 'Terminal del Escriba', 12, 0, NULL),
(5, 202, 4000, '2023-05-20', 1, 2, 'Teclado Ergonómico Dendro', 'AK-KEY-005', NULL, NULL, NULL, NULL, 30011, NULL, 1, 'Falla en la tecla espaciadora', 12, 0, NULL),
(6, 101, 5000, '2023-08-01', 0, 1, 'Oratrice Interface Terminal', 'OR-INT-001', NULL, '10.0.5.10', '99:88:77:66:55:44', 'PC-NEUVILLETTE', 40020, 'WaterTastesGood', 2, 'Conexión a la Oratrice', 14, 0, NULL),
(7, 102, 2000, '2022-11-05', 1, 4, 'Portátil Ejecutivo Oro', 'LY-GOLD-001', NULL, '10.0.2.100', '1A:2B:3C:4D:5E:6F', 'LT-NINGGUANG', 50050, 'MoraMaker99', 3, 'Uso exclusivo financiero', 5, 0, NULL),
(8, 101, 2000, '2021-04-10', 2, 4, 'Terminal Arcana Vieja', 'LY-OLD-777', NULL, '10.0.2.200', '00:00:00:00:00:00', 'PC-ZHONGLI', 50051, 'OsmanthusWine', 3, 'Destruido por meteorito', 8, 1, '2023-01-01 12:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `divisiones`
--

CREATE TABLE `divisiones` (
  `ID_EDIFICIO` int(11) NOT NULL,
  `ID_DIVISION` int(11) NOT NULL,
  `GLOSA_DIVISION` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `divisiones`
--

INSERT INTO `divisiones` (`ID_EDIFICIO`, `ID_DIVISION`, `GLOSA_DIVISION`) VALUES
(10, 100, 'Ala Administrativa'),
(20, 200, 'Oficinas del Qixing'),
(30, 300, 'Comandancia Tenryou'),
(40, 400, 'Casa de la Daena (Biblioteca)'),
(50, 500, 'Archivos Judiciales');

--
-- Disparadores `divisiones`
--
DELIMITER $$
CREATE TRIGGER `prevent_delete_divisiones` BEFORE DELETE ON `divisiones` FOR EACH ROW BEGIN
    DECLARE device_count INT;
    SELECT COUNT(*) INTO device_count FROM dispositivos d JOIN ubicaciones u ON d.ID_UBICACION = u.ID_UBICACION WHERE u.DIVISION_UBICACION = OLD.ID_DIVISION AND d.ELIMINADO = 0;
    IF device_count > 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: No se puede eliminar la División. Tiene dispositivos activos asociados.'; END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `prevent_update_divisiones` BEFORE UPDATE ON `divisiones` FOR EACH ROW BEGIN
    DECLARE device_count INT;
    IF OLD.ID_DIVISION <> NEW.ID_DIVISION THEN
        SELECT COUNT(*) INTO device_count FROM dispositivos d JOIN ubicaciones u ON d.ID_UBICACION = u.ID_UBICACION WHERE u.DIVISION_UBICACION = OLD.ID_DIVISION AND d.ELIMINADO = 0;
        IF device_count > 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: No se puede cambiar el ID de la División. Tiene dispositivos asociados.'; END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `edificios`
--

CREATE TABLE `edificios` (
  `ID_SEDE` int(11) NOT NULL,
  `ID_EDIFICIO` int(11) NOT NULL,
  `GLOSA_EDIFICIO` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `edificios`
--

INSERT INTO `edificios` (`ID_SEDE`, `ID_EDIFICIO`, `GLOSA_EDIFICIO`) VALUES
(1, 10, 'Sede de los Caballeros de Favonius'),
(2, 20, 'Terraza Yujing'),
(3, 30, 'Castillo Tenshukaku'),
(4, 40, 'El Árbol Divino - Surasthana'),
(5, 50, 'Palacio de Mermonia');

--
-- Disparadores `edificios`
--
DELIMITER $$
CREATE TRIGGER `prevent_delete_edificios` BEFORE DELETE ON `edificios` FOR EACH ROW BEGIN
    DECLARE device_count INT;
    SELECT COUNT(*) INTO device_count FROM dispositivos d JOIN ubicaciones u ON d.ID_UBICACION = u.ID_UBICACION WHERE u.EDIFICIO_UBICACION = OLD.ID_EDIFICIO AND d.ELIMINADO = 0;
    IF device_count > 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: No se puede eliminar el Edificio. Tiene dispositivos activos asociados.'; END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `prevent_update_edificios` BEFORE UPDATE ON `edificios` FOR EACH ROW BEGIN
    DECLARE device_count INT;
    IF OLD.ID_EDIFICIO <> NEW.ID_EDIFICIO THEN
        SELECT COUNT(*) INTO device_count FROM dispositivos d JOIN ubicaciones u ON d.ID_UBICACION = u.ID_UBICACION WHERE u.EDIFICIO_UBICACION = OLD.ID_EDIFICIO AND d.ELIMINADO = 0;
        IF device_count > 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: No se puede cambiar el ID del Edificio. Tiene dispositivos asociados.'; END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_cgu`
--

CREATE TABLE `estado_cgu` (
  `ID_ESTADO_CGU` int(11) NOT NULL,
  `GLOSA_ESTADO` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado_cgu`
--

INSERT INTO `estado_cgu` (`ID_ESTADO_CGU`, `GLOSA_ESTADO`) VALUES
(0, 'Alta'),
(1, 'Baja'),
(2, 'Pendiente de Baja'),
(3, 'Bodega');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcas`
--

CREATE TABLE `marcas` (
  `ID_MARCA` int(11) NOT NULL,
  `GLOSA_MARCA` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `marcas`
--

INSERT INTO `marcas` (`ID_MARCA`, `GLOSA_MARCA`) VALUES
(1, 'FontaineTech Industries'),
(2, 'Akademiya Labs'),
(3, 'Alquimia Kreideprinz'),
(4, 'Manufactura de Liyue');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `ID_PROV` int(11) NOT NULL,
  `GLOSA_PROVEEDOR` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`ID_PROV`, `GLOSA_PROVEEDOR`) VALUES
(1, 'Gremio de Comerciantes de Teyvat'),
(2, 'Instituto de Investigación de Fontaine'),
(3, 'Cámara de Comercio de Liyue');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registros`
--

CREATE TABLE `registros` (
  `ID_REGISTRO` int(11) NOT NULL,
  `ID_DISPOSITIVO` int(11) DEFAULT NULL,
  `TIPO` varchar(100) DEFAULT NULL,
  `ID_NUEVA_UBICACION` int(11) DEFAULT NULL,
  `ID_ANTIGUA_UBICACION` int(11) DEFAULT NULL,
  `FECHA_MOVIMIENTO` datetime DEFAULT NULL,
  `ID_USUARIO_REGISTRO` int(11) DEFAULT NULL,
  `OBSERVACION` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `registros`
--

INSERT INTO `registros` (`ID_REGISTRO`, `ID_DISPOSITIVO`, `TIPO`, `ID_NUEVA_UBICACION`, `ID_ANTIGUA_UBICACION`, `FECHA_MOVIMIENTO`, `ID_USUARIO_REGISTRO`, `OBSERVACION`) VALUES
(1, 0, 'Asignación Inicial', 1000, NULL, '2023-01-15 09:00:00', 1, 'Instalación del equipo.'),
(2, 4, 'Asignación Inicial', 4000, NULL, '2023-05-20 10:30:00', 12, 'Entrega de terminal.'),
(3, 5, 'Envío a Reparación', 4000, 4000, '2023-09-15 14:00:00', 12, 'Se reporta fallo en el teclado.'),
(4, 8, 'Baja de Equipo', NULL, 2000, '2023-01-01 12:15:00', 8, 'Pérdida total del equipo.');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `respaldo`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `respaldo` (
`ID` int(11)
,`ID_UBICACION` int(11)
,`SUBCATEGORIA` varchar(50)
,`FISCALIA` varchar(100)
,`EDIFICIO` varchar(100)
,`DIVISION` varchar(100)
,`UBICACION_DETALLE` varchar(50)
,`FECHA_REGISTRO` varchar(10)
,`ESTADO` varchar(50)
,`MARCA` varchar(50)
,`MODELO` varchar(150)
,`SERIE` varchar(150)
,`IP` varchar(45)
,`MAC` varchar(20)
,`NOMBRE_MAQUINA` varchar(50)
,`CODIGO_INVENTARIO` int(11)
,`PROVEEDOR` varchar(50)
,`OBSERVACION` varchar(100)
,`QUIEN_REGISTRA` varchar(50)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sedes`
--

CREATE TABLE `sedes` (
  `ID_SEDE` int(11) NOT NULL,
  `GLOSA_FISCALIA` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sedes`
--

INSERT INTO `sedes` (`ID_SEDE`, `GLOSA_FISCALIA`) VALUES
(1, 'Fiscalía Regional de Mondstadt'),
(2, 'Fiscalía Regional de Liyue'),
(3, 'Fiscalía Regional de Inazuma'),
(4, 'Fiscalía Regional de Sumeru'),
(5, 'Fiscalía Regional de Fontaine');

--
-- Disparadores `sedes`
--
DELIMITER $$
CREATE TRIGGER `prevent_delete_sedes` BEFORE DELETE ON `sedes` FOR EACH ROW BEGIN
    DECLARE device_count INT;
    SELECT COUNT(*) INTO device_count FROM dispositivos d JOIN ubicaciones u ON d.ID_UBICACION = u.ID_UBICACION WHERE u.FISCALIA_UBICACION = OLD.ID_SEDE AND d.ELIMINADO = 0;
    IF device_count > 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: No se puede eliminar la Sede. Tiene dispositivos activos asociados.'; END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `prevent_update_sedes` BEFORE UPDATE ON `sedes` FOR EACH ROW BEGIN
    DECLARE device_count INT;
    IF OLD.ID_SEDE <> NEW.ID_SEDE THEN
        SELECT COUNT(*) INTO device_count FROM dispositivos d JOIN ubicaciones u ON d.ID_UBICACION = u.ID_UBICACION WHERE u.FISCALIA_UBICACION = OLD.ID_SEDE AND d.ELIMINADO = 0;
        IF device_count > 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: No se puede cambiar el ID de la Sede. Tiene dispositivos activos asociados.'; END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sub_categorias`
--

CREATE TABLE `sub_categorias` (
  `ID_CAT` int(11) DEFAULT NULL,
  `ID_SUBCAT` int(11) NOT NULL,
  `GLOSA_SUBCATEGORIA` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sub_categorias`
--

INSERT INTO `sub_categorias` (`ID_CAT`, `ID_SUBCAT`, `GLOSA_SUBCATEGORIA`) VALUES
(1, 101, 'Terminal Akasha (Escritorio)'),
(1, 102, 'Daguerrotipo Portátil (Notebook)'),
(2, 201, 'Monitor de Cristal Metálico'),
(2, 202, 'Teclado Mecánico de Fontaine'),
(3, 301, 'Enrutador de Líneas Ley (Router)'),
(3, 302, 'Conmutador Elemental (Switch)');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `sumario_ubicacion_categoria`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `sumario_ubicacion_categoria` (
`UBICACION` varchar(50)
,`CATEGORIA` varchar(50)
,`TOTAL` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ubicaciones`
--

CREATE TABLE `ubicaciones` (
  `ID_UBICACION` int(11) NOT NULL,
  `FISCALIA_UBICACION` int(11) DEFAULT NULL,
  `EDIFICIO_UBICACION` int(11) DEFAULT NULL,
  `DIVISION_UBICACION` int(11) DEFAULT NULL,
  `GLOSA_UBICACION` varchar(50) DEFAULT NULL,
  `TIPO` varchar(100) DEFAULT NULL,
  `ID_USUARIO_ASIGNADO` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ubicaciones`
--

INSERT INTO `ubicaciones` (`ID_UBICACION`, `FISCALIA_UBICACION`, `EDIFICIO_UBICACION`, `DIVISION_UBICACION`, `GLOSA_UBICACION`, `TIPO`, `ID_USUARIO_ASIGNADO`) VALUES
(1000, 1, 10, 100, 'Despacho de la Gran Maestra', 'Oficina Privada', 1),
(2000, 2, 20, 200, 'Despacho del Equilibrio Terrenal', 'Oficina Privada', 5),
(3000, 3, 30, 300, 'Sala de Estrategia Militar', 'Sala de Reuniones', 9),
(4000, 4, 40, 400, 'Cubículo del Escriba', 'Oficina Abierta', 12),
(5000, 5, 50, 500, 'Oficina del Iudex', 'Oficina Privada', 14);

--
-- Disparadores `ubicaciones`
--
DELIMITER $$
CREATE TRIGGER `prevent_delete_ubicaciones` BEFORE DELETE ON `ubicaciones` FOR EACH ROW BEGIN
    DECLARE device_count INT;
    SELECT COUNT(*) INTO device_count FROM dispositivos WHERE ID_UBICACION = OLD.ID_UBICACION AND ELIMINADO = 0;
    IF device_count > 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: No se puede eliminar la Ubicación. Hay dispositivos registrados en ella.'; END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `prevent_update_ubicaciones` BEFORE UPDATE ON `ubicaciones` FOR EACH ROW BEGIN
    DECLARE device_count INT;
    IF OLD.ID_UBICACION <> NEW.ID_UBICACION THEN
        SELECT COUNT(*) INTO device_count FROM dispositivos WHERE ID_UBICACION = OLD.ID_UBICACION AND ELIMINADO = 0;
        IF device_count > 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: No se puede cambiar el ID de la Ubicación. Hay dispositivos en ella.'; END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `ID_USUARIO` int(11) NOT NULL,
  `USUARIO` varchar(50) DEFAULT NULL,
  `NOMBRE` varchar(50) DEFAULT NULL,
  `ADMIN` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`ID_USUARIO`, `USUARIO`, `NOMBRE`, `ADMIN`) VALUES
(1, 'jgunnhildr', 'Jean Gunnhildr', 1),
(2, 'krobertson', 'Kaeya Alberich', 0),
(5, 'ningguang', 'Ningguang', 1),
(8, 'zhongli', 'Zhongli', 0),
(9, 'ksara', 'Kujou Sara', 1),
(12, 'alhaitham', 'Alhaitham', 1),
(14, 'neuvillette', 'Neuvillette', 1);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_dispositivos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_dispositivos` (
`ID` int(11)
,`ID_UBICACION` int(11)
,`SUBCATEGORIA` varchar(50)
,`FISCALIA_UBICACION` int(11)
,`EDIFICIO_UBICACION` int(11)
,`DIVISION_UBICACION` int(11)
,`UBICACION_DETALLE` varchar(50)
,`FECHA_REGISTRO` varchar(10)
,`ESTADO` varchar(50)
,`MARCA` varchar(50)
,`MODELO` varchar(150)
,`SERIE` varchar(150)
,`IP` varchar(45)
,`MAC` varchar(20)
,`NOMBRE_MAQUINA` varchar(50)
,`CODIGO_INVENTARIO` int(11)
,`PROVEEDOR` varchar(50)
,`OBSERVACION` varchar(100)
,`QUIEN_REGISTRA` varchar(50)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_registros_detallada`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_registros_detallada` (
`ID_REGISTRO` int(11)
,`ID_DISPOSITIVO` int(11)
,`TIPO` varchar(100)
,`ANTIGUA_SEDE` varchar(50)
,`NUEVA_SEDE` varchar(50)
,`USUARIO_REGISTRO` int(11)
,`FECHA_MOVIMIENTO` datetime
,`OBSERVACION` text
);

-- --------------------------------------------------------

--
-- Estructura para la vista `respaldo`
--
DROP TABLE IF EXISTS `respaldo`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `respaldo`  AS SELECT `d`.`ID_DISP` AS `ID`, `d`.`ID_UBICACION` AS `ID_UBICACION`, `sc`.`GLOSA_SUBCATEGORIA` AS `SUBCATEGORIA`, `s`.`GLOSA_FISCALIA` AS `FISCALIA`, `e`.`GLOSA_EDIFICIO` AS `EDIFICIO`, `div`.`GLOSA_DIVISION` AS `DIVISION`, `ub`.`GLOSA_UBICACION` AS `UBICACION_DETALLE`, `d`.`FECHA_REGISTRO` AS `FECHA_REGISTRO`, `est`.`GLOSA_ESTADO` AS `ESTADO`, `m`.`GLOSA_MARCA` AS `MARCA`, `d`.`MODELO` AS `MODELO`, `d`.`SERIE` AS `SERIE`, `d`.`IP` AS `IP`, `d`.`MAC` AS `MAC`, `d`.`NOMBRE_MAQUINA` AS `NOMBRE_MAQUINA`, `d`.`CODIGO_INVENTARIO` AS `CODIGO_INVENTARIO`, `p`.`GLOSA_PROVEEDOR` AS `PROVEEDOR`, `d`.`OBSERVACION` AS `OBSERVACION`, `u_reg`.`NOMBRE` AS `QUIEN_REGISTRA` FROM (((((((((`dispositivos` `d` left join `sub_categorias` `sc` on(`sc`.`ID_SUBCAT` = `d`.`ID_SUBCAT`)) left join `ubicaciones` `ub` on(`ub`.`ID_UBICACION` = `d`.`ID_UBICACION`)) left join `sedes` `s` on(`ub`.`FISCALIA_UBICACION` = `s`.`ID_SEDE`)) left join `edificios` `e` on(`ub`.`EDIFICIO_UBICACION` = `e`.`ID_EDIFICIO`)) left join `divisiones` `div` on(`ub`.`DIVISION_UBICACION` = `div`.`ID_DIVISION`)) left join `estado_cgu` `est` on(`est`.`ID_ESTADO_CGU` = `d`.`ID_ESTADO_CGU`)) left join `marcas` `m` on(`m`.`ID_MARCA` = `d`.`ID_MARCA`)) left join `proveedores` `p` on(`p`.`ID_PROV` = `d`.`ID_PROVEEDOR`)) left join `usuarios` `u_reg` on(`u_reg`.`ID_USUARIO` = `d`.`ID_USUARIO_REGISTRO`)) WHERE `d`.`ELIMINADO` = 0 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `sumario_ubicacion_categoria`
--
DROP TABLE IF EXISTS `sumario_ubicacion_categoria`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `sumario_ubicacion_categoria`  AS SELECT `ub`.`GLOSA_UBICACION` AS `UBICACION`, `c`.`GLOSA_CATEGORIA` AS `CATEGORIA`, count(`d`.`ID_DISP`) AS `TOTAL` FROM (((`dispositivos` `d` join `ubicaciones` `ub` on(`d`.`ID_UBICACION` = `ub`.`ID_UBICACION`)) join `sub_categorias` `sc` on(`d`.`ID_SUBCAT` = `sc`.`ID_SUBCAT`)) join `categorias` `c` on(`sc`.`ID_CAT` = `c`.`ID_CAT`)) WHERE `d`.`ELIMINADO` = 0 GROUP BY `ub`.`GLOSA_UBICACION`, `c`.`GLOSA_CATEGORIA` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_dispositivos`
--
DROP TABLE IF EXISTS `vista_dispositivos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_dispositivos`  AS SELECT `d`.`ID_DISP` AS `ID`, `d`.`ID_UBICACION` AS `ID_UBICACION`, `sc`.`GLOSA_SUBCATEGORIA` AS `SUBCATEGORIA`, `ub`.`FISCALIA_UBICACION` AS `FISCALIA_UBICACION`, `ub`.`EDIFICIO_UBICACION` AS `EDIFICIO_UBICACION`, `ub`.`DIVISION_UBICACION` AS `DIVISION_UBICACION`, `ub`.`GLOSA_UBICACION` AS `UBICACION_DETALLE`, `d`.`FECHA_REGISTRO` AS `FECHA_REGISTRO`, `est`.`GLOSA_ESTADO` AS `ESTADO`, `m`.`GLOSA_MARCA` AS `MARCA`, `d`.`MODELO` AS `MODELO`, `d`.`SERIE` AS `SERIE`, `d`.`IP` AS `IP`, `d`.`MAC` AS `MAC`, `d`.`NOMBRE_MAQUINA` AS `NOMBRE_MAQUINA`, `d`.`CODIGO_INVENTARIO` AS `CODIGO_INVENTARIO`, `p`.`GLOSA_PROVEEDOR` AS `PROVEEDOR`, `d`.`OBSERVACION` AS `OBSERVACION`, `u_reg`.`NOMBRE` AS `QUIEN_REGISTRA` FROM ((((((`dispositivos` `d` left join `sub_categorias` `sc` on(`sc`.`ID_SUBCAT` = `d`.`ID_SUBCAT`)) left join `ubicaciones` `ub` on(`ub`.`ID_UBICACION` = `d`.`ID_UBICACION`)) left join `estado_cgu` `est` on(`est`.`ID_ESTADO_CGU` = `d`.`ID_ESTADO_CGU`)) left join `marcas` `m` on(`m`.`ID_MARCA` = `d`.`ID_MARCA`)) left join `proveedores` `p` on(`p`.`ID_PROV` = `d`.`ID_PROVEEDOR`)) left join `usuarios` `u_reg` on(`u_reg`.`ID_USUARIO` = `d`.`ID_USUARIO_REGISTRO`)) WHERE `d`.`ELIMINADO` = 0 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_registros_detallada`
--
DROP TABLE IF EXISTS `vista_registros_detallada`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_registros_detallada`  AS SELECT `r`.`ID_REGISTRO` AS `ID_REGISTRO`, `r`.`ID_DISPOSITIVO` AS `ID_DISPOSITIVO`, `r`.`TIPO` AS `TIPO`, `ub_ant`.`GLOSA_UBICACION` AS `ANTIGUA_SEDE`, `ub_nuev`.`GLOSA_UBICACION` AS `NUEVA_SEDE`, `r`.`ID_USUARIO_REGISTRO` AS `USUARIO_REGISTRO`, `r`.`FECHA_MOVIMIENTO` AS `FECHA_MOVIMIENTO`, `r`.`OBSERVACION` AS `OBSERVACION` FROM ((`registros` `r` left join `ubicaciones` `ub_ant` on(`r`.`ID_ANTIGUA_UBICACION` = `ub_ant`.`ID_UBICACION`)) left join `ubicaciones` `ub_nuev` on(`r`.`ID_NUEVA_UBICACION` = `ub_nuev`.`ID_UBICACION`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`ID_CAT`);

--
-- Indices de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  ADD PRIMARY KEY (`ID_DISP`),
  ADD KEY `fk_sub_categoria_dispositivos` (`ID_SUBCAT`),
  ADD KEY `fk_ubicacion_dispositivos` (`ID_UBICACION`),
  ADD KEY `fk_cgu_dispositivos` (`ID_ESTADO_CGU`),
  ADD KEY `fk_marcas_dispositivos` (`ID_MARCA`),
  ADD KEY `fk_proveedor_dispositivos` (`ID_PROVEEDOR`);

--
-- Indices de la tabla `divisiones`
--
ALTER TABLE `divisiones`
  ADD PRIMARY KEY (`ID_DIVISION`),
  ADD KEY `pk_divisiones_edificios` (`ID_EDIFICIO`);

--
-- Indices de la tabla `edificios`
--
ALTER TABLE `edificios`
  ADD PRIMARY KEY (`ID_EDIFICIO`),
  ADD KEY `pk_edificios_sedes` (`ID_SEDE`);

--
-- Indices de la tabla `estado_cgu`
--
ALTER TABLE `estado_cgu`
  ADD PRIMARY KEY (`ID_ESTADO_CGU`);

--
-- Indices de la tabla `marcas`
--
ALTER TABLE `marcas`
  ADD PRIMARY KEY (`ID_MARCA`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`ID_PROV`);

--
-- Indices de la tabla `registros`
--
ALTER TABLE `registros`
  ADD PRIMARY KEY (`ID_REGISTRO`),
  ADD KEY `fk_antigua_ubi_reg` (`ID_ANTIGUA_UBICACION`),
  ADD KEY `fk_nueva_ubi_reg` (`ID_NUEVA_UBICACION`);

--
-- Indices de la tabla `sedes`
--
ALTER TABLE `sedes`
  ADD PRIMARY KEY (`ID_SEDE`);

--
-- Indices de la tabla `sub_categorias`
--
ALTER TABLE `sub_categorias`
  ADD PRIMARY KEY (`ID_SUBCAT`),
  ADD KEY `fk_categoria_subcategoria` (`ID_CAT`);

--
-- Indices de la tabla `ubicaciones`
--
ALTER TABLE `ubicaciones`
  ADD PRIMARY KEY (`ID_UBICACION`),
  ADD KEY `pk_ubicaciones_divisiones` (`DIVISION_UBICACION`),
  ADD KEY `pk_ubicaciones_edificios` (`EDIFICIO_UBICACION`),
  ADD KEY `pk_ubicaciones_sedes` (`FISCALIA_UBICACION`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`ID_USUARIO`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  MODIFY `ID_DISP` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `registros`
--
ALTER TABLE `registros`
  MODIFY `ID_REGISTRO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  ADD CONSTRAINT `fk_cgu_dispositivos` FOREIGN KEY (`ID_ESTADO_CGU`) REFERENCES `estado_cgu` (`ID_ESTADO_CGU`),
  ADD CONSTRAINT `fk_marcas_dispositivos` FOREIGN KEY (`ID_MARCA`) REFERENCES `marcas` (`ID_MARCA`),
  ADD CONSTRAINT `fk_proveedor_dispositivos` FOREIGN KEY (`ID_PROVEEDOR`) REFERENCES `proveedores` (`ID_PROV`),
  ADD CONSTRAINT `fk_sub_categoria_dispositivos` FOREIGN KEY (`ID_SUBCAT`) REFERENCES `sub_categorias` (`ID_SUBCAT`),
  ADD CONSTRAINT `fk_ubicacion_dispositivos` FOREIGN KEY (`ID_UBICACION`) REFERENCES `ubicaciones` (`ID_UBICACION`);

--
-- Filtros para la tabla `divisiones`
--
ALTER TABLE `divisiones`
  ADD CONSTRAINT `pk_divisiones_edificios` FOREIGN KEY (`ID_EDIFICIO`) REFERENCES `edificios` (`ID_EDIFICIO`);

--
-- Filtros para la tabla `edificios`
--
ALTER TABLE `edificios`
  ADD CONSTRAINT `pk_edificios_sedes` FOREIGN KEY (`ID_SEDE`) REFERENCES `sedes` (`ID_SEDE`);

--
-- Filtros para la tabla `registros`
--
ALTER TABLE `registros`
  ADD CONSTRAINT `fk_antigua_ubi_reg` FOREIGN KEY (`ID_ANTIGUA_UBICACION`) REFERENCES `ubicaciones` (`ID_UBICACION`),
  ADD CONSTRAINT `fk_nueva_ubi_reg` FOREIGN KEY (`ID_NUEVA_UBICACION`) REFERENCES `ubicaciones` (`ID_UBICACION`);

--
-- Filtros para la tabla `sub_categorias`
--
ALTER TABLE `sub_categorias`
  ADD CONSTRAINT `fk_categoria_subcategoria` FOREIGN KEY (`ID_CAT`) REFERENCES `categorias` (`ID_CAT`);

--
-- Filtros para la tabla `ubicaciones`
--
ALTER TABLE `ubicaciones`
  ADD CONSTRAINT `pk_ubicaciones_divisiones` FOREIGN KEY (`DIVISION_UBICACION`) REFERENCES `divisiones` (`ID_DIVISION`),
  ADD CONSTRAINT `pk_ubicaciones_edificios` FOREIGN KEY (`EDIFICIO_UBICACION`) REFERENCES `edificios` (`ID_EDIFICIO`),
  ADD CONSTRAINT `pk_ubicaciones_sedes` FOREIGN KEY (`FISCALIA_UBICACION`) REFERENCES `sedes` (`ID_SEDE`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
