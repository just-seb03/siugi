-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-04-2026 a las 00:04:54
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
-- Base de datos: `gestor_cuentas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas`
--

CREATE TABLE `cuentas` (
  `ID_CUENTA` int(11) NOT NULL,
  `ID_SOFTWARE` int(11) DEFAULT NULL,
  `ID_SEDE` int(11) DEFAULT NULL,
  `USUARIO` int(11) DEFAULT NULL,
  `GLOSA_CUENTA` varchar(100) DEFAULT NULL,
  `ESTADO_CUENTA` tinyint(4) DEFAULT 1,
  `REQUERIMIENTO_INICIO_CUENTA` varchar(100) DEFAULT NULL,
  `REQUERIMIENTO_TERMINO_CUENTA` varchar(100) DEFAULT NULL,
  `FECHA_CREACION` datetime DEFAULT NULL,
  `ES_GENERICA` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cuentas`
--

INSERT INTO `cuentas` (`ID_CUENTA`, `ID_SOFTWARE`, `ID_SEDE`, `USUARIO`, `GLOSA_CUENTA`, `ESTADO_CUENTA`, `REQUERIMIENTO_INICIO_CUENTA`, `REQUERIMIENTO_TERMINO_CUENTA`, `FECHA_CREACION`, `ES_GENERICA`) VALUES
(1, 1, 1, 1, 'jgunnhildr_siugi', 1, 'REQ-2020-001', NULL, '2020-09-28 09:00:00', 0),
(2, 3, 1, 1, 'jean_favonius_adv', 1, 'REQ-2020-045', NULL, '2020-10-15 14:30:00', 0),
(3, 1, 1, 2, 'kalberich_siugi', 1, 'REQ-2020-002', NULL, '2020-09-28 10:15:00', 0),
(4, 3, 1, NULL, 'recepcion_gremio_mond', 1, 'REQ-GEN-001', NULL, '2020-01-01 08:00:00', 1),
(5, 3, 1, 4, 'bpeg_adventurer', 1, 'REQ-2021-112', NULL, '2021-02-15 11:20:00', 0),
(6, 1, 2, 5, 'ningguang_siugi_admin', 1, 'REQ-2020-005', NULL, '2020-09-28 09:00:00', 0),
(7, 5, 2, 5, 'qixing_finanzas', 1, 'REQ-2020-088', NULL, '2020-11-01 16:45:00', 0),
(8, 5, 2, 8, 'zhongli_wangsheng_fin', 0, 'REQ-2020-150', 'REQ-TERM-2021-003', '2020-12-01 10:00:00', 0),
(9, 4, 3, 9, 'ksara_vision_hunt', 1, 'REQ-2021-200', NULL, '2021-09-01 08:30:00', 0),
(10, 1, 3, 9, 'sara_tenryou_siugi', 1, 'REQ-2021-201', NULL, '2021-09-02 09:15:00', 0),
(11, 2, 4, 12, 'alhaitham_akasha_root', 0, 'REQ-2022-010', 'REQ-TERM-2023-001', '2022-08-24 12:00:00', 0),
(12, 1, 4, 12, 'alhaitham_siugi', 1, 'REQ-2023-050', NULL, '2023-01-18 10:00:00', 0),
(13, 6, 5, 14, 'neuvillette_oratrice', 1, 'REQ-2023-100', NULL, '2023-08-16 08:00:00', 0),
(14, 1, 5, 14, 'iudex_siugi', 1, 'REQ-2023-101', NULL, '2023-08-16 08:30:00', 0),
(15, 6, 5, NULL, 'juzgado_publico_fontaine', 1, 'REQ-GEN-005', NULL, '2023-08-01 09:00:00', 1),
(16, 5, 5, 15, '', 3, NULL, NULL, '2026-04-03 00:00:00', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sedes`
--

CREATE TABLE `sedes` (
  `ID_SEDE` int(11) NOT NULL,
  `GLOSA_FISCALIA` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sedes`
--

INSERT INTO `sedes` (`ID_SEDE`, `GLOSA_FISCALIA`) VALUES
(1, 'Mondstadt'),
(2, 'Liyue'),
(3, 'Inazuma'),
(4, 'Sumeru'),
(5, 'Fontaine');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `software`
--

CREATE TABLE `software` (
  `ID_SOFTWARE` int(11) NOT NULL,
  `GLOSA_SOFTWARE` varchar(50) DEFAULT NULL,
  `ESTADO_SOFTWARE` tinyint(4) DEFAULT 1
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `software`
--

INSERT INTO `software` (`ID_SOFTWARE`, `GLOSA_SOFTWARE`, `ESTADO_SOFTWARE`) VALUES
(1, 'intranet de sacarosa', 1),
(2, 'Akasha Terminal Network', 0),
(3, 'KatheryneOS - Gremio Aventureros', 1),
(4, 'Registro de Visión - Sakoku', 1),
(5, 'ERP Banco del Norte', 1),
(6, 'Oratrice Mecanique d\'Analyse Cardinale', 1);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_cuentas_detalle`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_cuentas_detalle` (
`ID_CUENTA` int(11)
,`SOFTWARE` varchar(50)
,`SEDE` varchar(50)
,`USUARIO` int(11)
,`NOMBRE_USUARIO` varchar(50)
,`GLOSA_CUENTA` varchar(100)
,`ESTADO_CUENTA` tinyint(4)
,`REQUERIMIENTO_INICIO_CUENTA` varchar(100)
,`REQUERIMIENTO_TERMINO_CUENTA` varchar(100)
,`FECHA_CREACION` datetime
,`ES_GENERICA` int(11)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_cuentas_detalle`
--
DROP TABLE IF EXISTS `vista_cuentas_detalle`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ugi4`@`%` SQL SECURITY DEFINER VIEW `vista_cuentas_detalle`  AS SELECT `c`.`ID_CUENTA` AS `ID_CUENTA`, `s`.`GLOSA_SOFTWARE` AS `SOFTWARE`, `sd`.`GLOSA_FISCALIA` AS `SEDE`, `c`.`USUARIO` AS `USUARIO`, `u`.`nombre` AS `NOMBRE_USUARIO`, `c`.`GLOSA_CUENTA` AS `GLOSA_CUENTA`, `c`.`ESTADO_CUENTA` AS `ESTADO_CUENTA`, `c`.`REQUERIMIENTO_INICIO_CUENTA` AS `REQUERIMIENTO_INICIO_CUENTA`, `c`.`REQUERIMIENTO_TERMINO_CUENTA` AS `REQUERIMIENTO_TERMINO_CUENTA`, `c`.`FECHA_CREACION` AS `FECHA_CREACION`, `c`.`ES_GENERICA` AS `ES_GENERICA` FROM (((`cuentas` `c` left join `software` `s` on(`c`.`ID_SOFTWARE` = `s`.`ID_SOFTWARE`)) left join `sedes` `sd` on(`c`.`ID_SEDE` = `sd`.`ID_SEDE`)) left join `informatica`.`usuarios` `u` on(`c`.`USUARIO` = `u`.`id`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cuentas`
--
ALTER TABLE `cuentas`
  ADD PRIMARY KEY (`ID_CUENTA`),
  ADD KEY `fk_cuentas_software` (`ID_SOFTWARE`),
  ADD KEY `fk_cuentas_sedes` (`ID_SEDE`);

--
-- Indices de la tabla `sedes`
--
ALTER TABLE `sedes`
  ADD PRIMARY KEY (`ID_SEDE`);

--
-- Indices de la tabla `software`
--
ALTER TABLE `software`
  ADD PRIMARY KEY (`ID_SOFTWARE`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cuentas`
--
ALTER TABLE `cuentas`
  MODIFY `ID_CUENTA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `sedes`
--
ALTER TABLE `sedes`
  MODIFY `ID_SEDE` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `software`
--
ALTER TABLE `software`
  MODIFY `ID_SOFTWARE` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
