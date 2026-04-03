-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-04-2026 a las 00:05:21
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
-- Base de datos: `informatica`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `datos_actualiza`
--

CREATE TABLE `datos_actualiza` (
  `RUT` varchar(50) DEFAULT NULL,
  `FEC_NACIMIENTO` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Volcado de datos para la tabla `datos_actualiza`
--

INSERT INTO `datos_actualiza` (`RUT`, `FEC_NACIMIENTO`) VALUES
('11111111-1', '1995-03-14'),
('11111112-K', '1996-11-30'),
('11111113-8', '1997-10-25'),
('11111114-6', '2004-02-29'),
('22222221-5', '1993-08-26'),
('22222222-3', '1998-11-20'),
('22222223-1', '2001-07-15'),
('22222224-K', '1000-12-31'),
('33333331-9', '1997-07-14'),
('33333332-7', '1995-03-26'),
('33333333-5', '2000-09-28'),
('44444441-2', '1994-02-11'),
('44444442-0', '1996-06-23'),
('55555551-6', '1500-12-18'),
('55555552-4', '1995-09-20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fiscalias`
--

CREATE TABLE `fiscalias` (
  `cod_fiscalia` int(11) DEFAULT NULL,
  `gls_fiscalia` varchar(100) DEFAULT NULL,
  `gls_corta_fiscalia` varchar(100) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `comuna` varchar(100) DEFAULT NULL,
  `coordenadas_x` varchar(200) DEFAULT NULL,
  `coordenadas_y` varchar(100) DEFAULT NULL,
  `fiscal_jefe` varchar(100) DEFAULT NULL,
  `administrador` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(100) DEFAULT NULL,
  `iniciales` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Volcado de datos para la tabla `fiscalias`
--

INSERT INTO `fiscalias` (`cod_fiscalia`, `gls_fiscalia`, `gls_corta_fiscalia`, `direccion`, `comuna`, `coordenadas_x`, `coordenadas_y`, `fiscal_jefe`, `administrador`, `email`, `telefono`, `iniciales`) VALUES
(1, 'Fiscalía Regional de Mondstadt', 'Mondstadt', 'Sede de los Caballeros de Favonius', 'Ciudad de Mondstadt', '2500.5', '-1200.3', 'Jean Gunnhildr', 'Venti', 'contacto@mondstadt.teyvat', '+56911111111', 'FRM'),
(2, 'Fiscalía Regional de Liyue', 'Liyue', 'Terraza Yujing 123', 'Puerto de Liyue', '1000.0', '-3000.5', 'Ningguang', 'Zhongli', 'contacto@liyue.teyvat', '+56922222222', 'FRL'),
(3, 'Fiscalía Regional de Inazuma', 'Inazuma', 'Castillo Tenshukaku', 'Ciudad de Inazuma', '-3500.2', '-4000.8', 'Raiden Ei', 'Yae Miko', 'contacto@inazuma.teyvat', '+56933333333', 'FRI'),
(4, 'Fiscalía Regional de Sumeru', 'Sumeru', 'Santuario Surasthana', 'Ciudad de Sumeru', '-1500.0', '1500.0', 'Nahida', 'Alhaitham', 'contacto@sumeru.teyvat', '+56944444444', 'FRS'),
(5, 'Fiscalía Regional de Fontaine', 'Fontaine', 'Corte de Fontaine, Palacio de Mermonia', 'Corte de Fontaine', '3500.0', '2500.0', 'Neuvillette', 'Furina', 'contacto@fontaine.teyvat', '+56955555555', 'FRF');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unidad`
--

CREATE TABLE `unidad` (
  `cod_unidad` int(11) NOT NULL,
  `gls_unidad` varchar(100) DEFAULT NULL,
  `gls_corta_unidad` varchar(100) DEFAULT NULL,
  `descripcion_unidad` text DEFAULT NULL,
  `img_unidad` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Volcado de datos para la tabla `unidad`
--

INSERT INTO `unidad` (`cod_unidad`, `gls_unidad`, `gls_corta_unidad`, `descripcion_unidad`, `img_unidad`) VALUES
(101, 'Caballeros de Favonius', 'Favonius', 'Orden de caballería que protege a la nación de Mondstadt.', 'favonius.png'),
(102, 'Gremio de Aventureros', 'Aventureros', 'Organización internacional encargada de misiones y comisiones.', 'adventurers.png'),
(201, 'Las Siete Estrellas de Liyue', 'Qixing', 'Comité de mercaderes y líderes que gobiernan Liyue.', 'qixing.png'),
(202, 'Funeraria El Camino', 'Wangsheng', 'Encargados de los ritos funerarios tradicionales en Liyue.', 'wangsheng.png'),
(301, 'Comisión Tenryou', 'Tenryou', 'Comisión militar y ejecutiva de Inazuma.', 'tenryou.png'),
(302, 'Comisión Yashiro', 'Yashiro', 'Encargados de eventos culturales y rituales en Inazuma.', 'yashiro.png'),
(401, 'La Akademiya', 'Akademiya', 'Principal institución académica y gubernamental de Sumeru.', 'akademiya.png'),
(501, 'Marechaussee Phantom', 'Marechaussee', 'Fuerza policial de élite de Fontaine.', 'marechaussee.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `cod_fiscalia` int(11) NOT NULL,
  `cod_unidad` int(11) DEFAULT 0,
  `rut` varchar(20) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `telefono` varchar(100) DEFAULT NULL,
  `estado` int(11) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `equipo` int(11) DEFAULT 0,
  `perfil` int(11) DEFAULT 0,
  `correo_electronico` varchar(100) DEFAULT NULL,
  `fec_nacimiento` date DEFAULT NULL,
  `mostrar_intranet` int(11) DEFAULT 0,
  `fec_inicio_funciones` date DEFAULT NULL,
  `fec_termino_funciones` date DEFAULT NULL,
  `adm_intranet` int(11) NOT NULL DEFAULT 0,
  `fiscal_func` int(11) DEFAULT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `tipo_usuario` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `cod_fiscalia`, `cod_unidad`, `rut`, `usuario`, `nombre`, `telefono`, `estado`, `cargo`, `equipo`, `perfil`, `correo_electronico`, `fec_nacimiento`, `mostrar_intranet`, `fec_inicio_funciones`, `fec_termino_funciones`, `adm_intranet`, `fiscal_func`, `ip`, `tipo_usuario`) VALUES
(1, 1, 101, '11111111-1', 'jgunnhildr', 'Jean Gunnhildr', '+56910000001', 0, 'Gran Maestra Intendente', 1, 1, 'jean@mondstadt.teyvat', '1995-03-14', 1, '2020-09-28', NULL, 1, 1, '192.168.1.10', 1),
(2, 1, 101, '11111112-K', 'krobertson', 'Kaeya Alberich', '+56910000002', 0, 'Capitán de Caballería', 1, 2, 'kaeya@mondstadt.teyvat', '1996-11-30', 1, '2020-09-28', NULL, 0, 2, '192.168.1.11', 2),
(3, 1, 101, '11111113-8', 'eula', 'Eula Lawrence', '+56910000003', 0, 'Capitana de Reconocimiento', 1, 2, 'eula@mondstadt.teyvat', '1997-10-25', 1, '2021-05-18', NULL, 0, 2, '192.168.1.12', 2),
(4, 1, 102, '11111114-6', 'bpeg', 'Bennett', '+56910000004', 0, 'Aventurero', 2, 3, 'bennett@aventureros.teyvat', '2004-02-29', 1, '2020-09-28', NULL, 0, NULL, '192.168.1.13', 3),
(5, 2, 201, '22222221-5', 'ningguang', 'Ningguang', '+56920000001', 0, 'Equilibrio Terrenal (Tianquan)', 1, 1, 'ningguang@liyue.teyvat', '1993-08-26', 1, '2020-09-28', NULL, 1, 1, '192.168.2.10', 1),
(6, 2, 201, '22222222-3', 'keqing', 'Keqing', '+56920000002', 0, 'Equilibrio Terrenal (Yuheng)', 1, 1, 'keqing@liyue.teyvat', '1998-11-20', 1, '2020-09-28', NULL, 0, 1, '192.168.2.11', 1),
(7, 2, 202, '22222223-1', 'hutao', 'Hu Tao', '+56920000003', 0, 'Directora Funcional', 3, 2, 'hutao@wangsheng.teyvat', '2001-07-15', 1, '2021-03-02', NULL, 0, 2, '192.168.2.12', 2),
(8, 2, 202, '22222224-K', 'zhongli', 'Zhongli', '+56920000004', 0, 'Consultor Experto', 3, 2, 'zhongli@wangsheng.teyvat', '1000-12-31', 1, '2020-12-01', NULL, 0, 2, '192.168.2.13', 2),
(9, 3, 301, '33333331-9', 'ksara', 'Kujou Sara', '+56930000001', 0, 'General de la Comisión', 1, 1, 'sara.kujou@inazuma.teyvat', '1997-07-14', 1, '2021-09-01', NULL, 0, 1, '192.168.3.10', 1),
(10, 3, 302, '33333332-7', 'kayato', 'Kamisato Ayato', '+56930000002', 0, 'Líder del Clan Kamisato', 2, 1, 'ayato@inazuma.teyvat', '1995-03-26', 1, '2022-03-30', NULL, 1, 1, '192.168.3.11', 1),
(11, 3, 302, '33333333-5', 'kayaka', 'Kamisato Ayaka', '+56930000003', 0, 'Garza de la Escarcha', 0, 0, 'ayaka@inazuma.teyvat', '2000-09-28', 1, '2021-07-21', '2034-12-12', 0, 2, '192.168.3.12', 2),
(12, 4, 401, '44444441-2', 'alhaitham', 'Alhaitham', '+56940000001', 0, 'Gran Sabio Interino', 1, 1, 'alhaitham@akademiya.teyvat', '1994-02-11', 1, '2023-01-18', NULL, 1, 1, '192.168.4.10', 1),
(13, 4, 401, '44444442-0', 'cyno', 'Cyno', '+56940000002', 0, 'Gran Juez de la Matra', 1, 1, 'cyno@akademiya.teyvat', '1996-06-23', 1, '2022-09-28', NULL, 0, 1, '192.168.4.11', 1),
(14, 5, 501, '55555551-6', 'neuvillette', 'Neuvillette', '+56950000001', 1, 'Iudex Supremo', 0, 0, 'neuvillette@fontaine.teyvat', '1500-12-18', 1, '2023-09-27', NULL, 0, 1, '192.168.5.10', 1),
(15, 5, 501, '55555552-4', 'clorinde', 'Clorinde', '+56950000002', 0, 'Duelista Representante', 1, 2, 'clorinde@fontaine.teyvat', '1995-09-20', 1, '2024-06-05', NULL, 0, 2, '192.168.5.11', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_unidad`
--

CREATE TABLE `usuario_unidad` (
  `cod_usuario_uni` int(11) NOT NULL,
  `cod_usuario` int(11) NOT NULL,
  `cod_unidad` int(11) NOT NULL,
  `estado_usuario_uni` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Volcado de datos para la tabla `usuario_unidad`
--

INSERT INTO `usuario_unidad` (`cod_usuario_uni`, `cod_usuario`, `cod_unidad`, `estado_usuario_uni`) VALUES
(1, 1, 101, 1),
(2, 2, 101, 1),
(3, 3, 101, 1),
(4, 4, 102, 1),
(5, 5, 201, 1),
(6, 6, 201, 1),
(7, 7, 202, 1),
(8, 8, 202, 1),
(9, 9, 301, 1),
(10, 10, 302, 1),
(11, 11, 302, 1),
(12, 12, 401, 1),
(13, 13, 401, 1),
(14, 14, 501, 1),
(15, 15, 501, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `unidad`
--
ALTER TABLE `unidad`
  ADD PRIMARY KEY (`cod_unidad`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_ID_USUARIO` (`id`,`usuario`);

--
-- Indices de la tabla `usuario_unidad`
--
ALTER TABLE `usuario_unidad`
  ADD PRIMARY KEY (`cod_usuario_uni`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `usuario_unidad`
--
ALTER TABLE `usuario_unidad`
  MODIFY `cod_usuario_uni` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
