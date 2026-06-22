-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: mysql-8001.dinaserver.com:3306
-- Tiempo de generación: 22-06-2026 a las 16:49:56
-- Versión del servidor: 8.0.37
-- Versión de PHP: 8.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `brianbarber`
--
CREATE DATABASE IF NOT EXISTS `brianbarber` DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci;
USE `brianbarber`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id_citas` int NOT NULL,
  `hora` time NOT NULL,
  `fecha` date NOT NULL,
  `clientes_id_cliente` int NOT NULL,
  `empleados_id_empleado` int NOT NULL,
  `tall` varchar(50) NOT NULL,
  `tissores` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id_citas`, `hora`, `fecha`, `clientes_id_cliente`, `empleados_id_empleado`, `tall`, `tissores`) VALUES
(7, '21:00:00', '2026-04-18', 4, 10, 'Tall + Barba', 1),
(8, '00:00:00', '2026-04-19', 4, 10, 'Tall + Barba', 1),
(9, '05:00:00', '2026-04-18', 4, 7, 'Tall + Celles', 0),
(10, '06:00:00', '2026-04-18', 4, 10, 'Tall + Barba', 1),
(14, '13:00:00', '2026-04-22', 4, 10, 'Tall + Barba', 1),
(15, '15:00:00', '2026-04-23', 4, 10, 'Servei complet', 1),
(16, '20:50:00', '2026-05-18', 25, 7, 'Tall', 1),
(17, '22:22:00', '2026-04-23', 18, 10, 'Tall + Barba', 1),
(18, '20:00:00', '2026-04-24', 4, 6, 'Tall + Celles', 1),
(19, '15:00:00', '2026-04-23', 4, 10, 'Tall + Celles', 1),
(20, '12:06:00', '2026-04-30', 29, 10, 'Servei complet', 1),
(21, '11:44:00', '2026-04-24', 30, 7, 'Tall + Barba', 1),
(22, '11:44:00', '2026-04-24', 30, 7, 'Tall + Barba', 1),
(23, '11:44:00', '2026-04-24', 30, 7, 'Tall + Barba', 1),
(24, '20:00:00', '2026-04-24', 4, 6, 'Tall + Barba', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int NOT NULL,
  `tarjeta_credito` varchar(20) NOT NULL,
  `usuarios_id_usuario` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `tarjeta_credito`, `usuarios_id_usuario`) VALUES
(4, '', 4),
(18, '1234123412341234', 14),
(20, '', 16),
(21, '', 17),
(22, '', 18),
(23, '', 19),
(25, '', 21),
(26, '', 22),
(27, '', 23),
(29, '', 25),
(30, '', 26),
(31, '', 27);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id_empleado` int NOT NULL,
  `num_segsocial` varchar(12) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `rol` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `usuarios_id_usuario` int NOT NULL,
  `especialitat_desc` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id_empleado`, `num_segsocial`, `rol`, `usuarios_id_usuario`, `especialitat_desc`) VALUES
(2, '234523452345', 'empleado', 3, NULL),
(4, NULL, 'empleado', 17, 'Expert en les últimes tendències urbanes i degradats d\'alta definició. Es focalitza en la geometria del rostre per oferir estils moderns, nítids i amb una estètica molt actual i totalment trencadora.'),
(6, '147258369369', 'empleado', 16, 'Dedicat a l\'excel·lència en la cura personal. El seu punt fort és la combinació harmoniosa de tisora i màquina per aconseguir volums equilibrats i un manteniment superior de la barba.'),
(7, '258147736914', 'supervisor', 14, 'Especialista en talls polits i acabats mil·limètrics. Destaca per la seva precisió tècnica i un tracte proper que assegura un resultat impecable i totalment adaptat a les faccions de cada client.'),
(8, '159784632122', 'empleado', 18, 'Tradició i tècnica clàssica s\'uneixen a les seves mans. És un referent en l\'afaitat tradicional i en el disseny de perfils definits, garantint una experiència de benestar i elegància absoluta.'),
(9, NULL, 'supervisor', 19, 'L\'especialista en canvis d\'imatge dinàmics i frescos. Té un ull clínic per a les tendències contemporànies, assegurant que cada tall sigui pràctic, modern i fàcil de lluir en el dia a dia.'),
(10, NULL, 'empleado', 15, 'Un mestre en el maneig de textures naturals i la cura de barbes amb caràcter. Aporta un enfocament artístic a cada servei, ideal per a qui busca un estil fluid i amb molta personalitat.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura_producto`
--

CREATE TABLE `factura_producto` (
  `id_factura_producto` int NOT NULL,
  `usuarios_id_usuario` int DEFAULT NULL,
  `productos_id_producto` int DEFAULT NULL,
  `cantidad` int NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura_servicio`
--

CREATE TABLE `factura_servicio` (
  `id_factura_servicio` int NOT NULL,
  `coste` decimal(10,2) NOT NULL,
  `servicios_id_servicio` int NOT NULL,
  `citas_id_citas` int DEFAULT NULL,
  `tijeras` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `factura_servicio`
--

INSERT INTO `factura_servicio` (`id_factura_servicio`, `coste`, `servicios_id_servicio`, `citas_id_citas`, `tijeras`) VALUES
(1, 10.00, 1, 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horaris_barberia`
--

CREATE TABLE `horaris_barberia` (
  `id` int NOT NULL,
  `dia_setmana` int NOT NULL,
  `hora_obertura` time DEFAULT NULL,
  `hora_tancament` time DEFAULT NULL,
  `es_tancat` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `horaris_barberia`
--

INSERT INTO `horaris_barberia` (`id`, `dia_setmana`, `hora_obertura`, `hora_tancament`, `es_tancat`) VALUES
(1, 1, '09:00:00', '20:00:00', 0),
(2, 2, '09:00:00', '20:00:00', 0),
(3, 3, '09:00:00', '20:00:00', 0),
(4, 4, '09:00:00', '20:00:00', 0),
(5, 5, '09:00:00', '20:00:00', 0),
(6, 6, '09:00:00', '14:00:00', 0),
(7, 7, '00:00:00', '00:00:00', 1);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `mostrar_trabajadores`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `mostrar_trabajadores` (
`apellidos` varchar(50)
,`id_usuario` int
,`nombre` varchar(45)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `ofrecer_servicios`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `ofrecer_servicios` (
`precio` decimal(10,2)
,`servicio` varchar(45)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int NOT NULL,
  `nombre_producto` varchar(45) NOT NULL,
  `descripcion` varchar(999) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int NOT NULL,
  `imagen` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre_producto`, `descripcion`, `precio`, `stock`, `imagen`) VALUES
(11, 'Crema d\'Afeitar', 'Crema suau i nutritiva que facilita un afaitat precís i còmode. La seva fórmula ajuda a protegir la pell, reduint la irritació i deixant una sensació de frescor i suavitat després de cada ús.', 12.50, 0, 'crema_afaitar.png'),
(12, 'Xampú de Barba', 'Xampú específic per netejar la barba en profunditat sense ressecar-la. Elimina impureses, aporta frescor i deixa el pèl suau, cuidant també la pell del rostre.', 14.00, 0, 'xampu_barba.png'),
(15, 'Pomada Fixadora', 'Pomada d’alta qualitat que proporciona fixació i control duradors sense deixar residus. Ideal per definir el pentinat amb un acabat natural o brillant, adaptable a diferents estils.', 15.00, 0, 'pomada_cabells.png'),
(16, 'Oli per a Barba', 'Oli hidratant dissenyat per nodrir i suavitzar la barba. Ajuda a controlar l’encrespament, aporta brillantor natural i manté la pell sota la barba saludable i hidratada.', 18.00, 0, 'oli_barba.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reseñas`
--

CREATE TABLE `reseñas` (
  `id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `puntuacion` int NOT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `trabajador` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Volcado de datos para la tabla `reseñas`
--

INSERT INTO `reseñas` (`id`, `nombre`, `puntuacion`, `comentario`, `trabajador`, `fecha`) VALUES
(3, 'pepe', 5, 'Vaig demanar cita amb el Rashid per un canvi de look radical i no podria estar més content. Té una mà increïble amb el cabell llarg i la barba. La millor barberia de Badalona!', 'Rashid', '2026-04-17 22:23:13'),
(4, 'Paco', 5, 'Sempre que vinc em talla el cabell l\\\'Ngolo. És un autèntic perfeccionista amb els degradats i les línies. Un 10 en tracte i professionalitat.', 'Ngolo', '2026-04-17 22:24:44'),
(5, 'paula', 4, 'Vaig anar perquè em fessin un undercut amb dibuix a la nuca i el Karim va fer una feina brutal. El local és molt xulo i, tot i que gairebé tots eren homes, em vaig sentir súper còmoda. Li poso 4 estrelles només perquè és difícil aparcar per la zona, però el servei és de 10.', 'Karim', '2026-04-17 22:28:34'),
(6, 'Brian', 3, 'Molt bona experiència amb el Manuel. Em va arreglar la barba i em va aconsellar uns productes que em van de meravella. Només falta que posin una mica més fluixa la música, però per la resta, genial.', 'Manuel', '2026-04-18 11:57:46'),
(7, 'Maria', 3, 'Molt content amb el resultat! El tracte ha estat excel·lent i s\\\'han fixat en cada detall. Sens dubte, s\\\'ha convertit en la meva barberia de confiança a partir d\\\'ara. Molt recomanable!', 'Karim', '2026-04-18 22:28:49'),
(8, 'Brian', 5, 'asdsdsad', 'Hamood', '2026-04-22 11:49:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id_servicio` int NOT NULL,
  `nombre_servicio` varchar(45) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `Duracion` time NOT NULL,
  `categoria` varchar(50) DEFAULT 'Bàsic',
  `descripcion` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id_servicio`, `nombre_servicio`, `precio`, `Duracion`, `categoria`, `descripcion`) VALUES
(1, 'Tall', 10.00, '00:30:00', 'Bàsic', NULL),
(2, 'Tall + Barba', 15.00, '00:45:00', 'Bàsic', NULL),
(3, 'Tall + Celles', 12.00, '00:35:00', 'Bàsic', NULL),
(4, 'Servei complet', 17.00, '01:00:00', 'Bàsic', NULL),
(6, 'Tall complet', 1555.00, '01:55:55', 'Premium', 'asasas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int NOT NULL,
  `correo` varchar(45) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `nombre` varchar(45) NOT NULL,
  `apellidos` varchar(50) NOT NULL,
  `es_trabajador` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `correo`, `contraseña`, `nombre`, `apellidos`, `es_trabajador`) VALUES
(3, 'pedro@pedro.com', '$2y$10$LwaPyEhFU/DR14T0P/jTeeuAlYuRAvOsUOdzBB7uFcnwZ3kPRvFVC', 'Pedro', '', 0),
(4, 'ivan@ivan.com', '$2y$10$LwaPyEhFU/DR14T0P/jTeeuAlYuRAvOsUOdzBB7uFcnwZ3kPRvFVC', 'ivan', 'moreno', 0),
(14, 'brian.almansa@brianbarber.cat', '$2y$10$fUe7Wb0eBACACdwxImPSpeIep2aIyxMrdvZ8Std/aHMhAMTAA.3La', 'Brian', 'Almansa', 1),
(15, 'rashid.al_haddad@brianbarber.cat', '$2y$10$a0iTEbZ7J6nstnQezJkkyOmefdvtQAaQ6pRaRIiYYdglgWdqojTqq', 'Rashid', 'Al-Haddad', 1),
(16, 'hamood.bakri@brianbarber.cat', '$2y$10$IaxFa8sSaiNUPZnuxT4H1uM0W9MjYJqQOx06AkZpZbWBp0nL05dIK', 'Hamood', 'Bakri', 1),
(17, 'ngolo.diop@brianbarber.cat', '$2y$10$SC6P4mMB6s77HVO2lkCjm.4V6wNbnU00KZFDeuQ9d7hACflA7pqDy', 'Ngolo', 'Diop', 1),
(18, 'karim.ziani@brianbarber.cat', '$2y$10$MJQBAoo7nBifPF/.n0vsBuqg74RdAkzFdiPt0Ih39WD5ioZrl8gr6', 'Karim', 'Ziani', 1),
(19, 'manuel.cortes@brianbarber.cat', '$2y$10$C8qvmpbMJSgB0jtKUCuBsuanAbgl08g8CIFo.Oukryerjs6fWcEKm', 'Manuel', 'Cortes', 1),
(21, 'kiny@gmail.com', '$2y$10$FHcCgEAFYz/1mDX3NISjv.5A4tIe4pDT/JbmWEA2sDFcPkQlEIL9u', 'Kiny', 'Coñada', 0),
(22, 'pepe@gmail.com', '$2y$10$9.BKYHcOCv5GegDE1ftdEOuN8w10xIyJXQDUOdD4K./lhN7d.6hqi', 'pepe', 'viyuela', 0),
(23, 'paco123@gmail.com', '$2y$10$ZrEVZJ.vQbllGy0VqXLS7uyMatDgAiNmbH/TKn0IfCBwCDiK6KOtK', 'Paco', 'Gonzalez', 0),
(25, 'maria@gmail.com', '$2y$10$se3sizz/952Oje.KZJ/bTOQ3aj7ywr3CjMkT4xj4cWgkBLqDbC9VG', 'maria', 'gomez', 0),
(26, 'oriol@gmail.com', '$2y$10$ZenCGrQe26AQaCPy.0eB6OJIZUhO6ATR4YLXyWE7jexTI7n.xORya', 'oriol', 'casanova palmer', 0),
(27, 'hola@hola.com', '$2y$10$k9ZOUc7RVQ3mY3cPrwPL5OjPBpInCTEZRLJi6XnDEhx4T6y/KuuLa', 'hola', 'hola', 0);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_agenda_detallada`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_agenda_detallada` (
`fecha` date
,`hora` time
,`id_citas` int
,`nombre_barbero` varchar(45)
,`nombre_cliente` varchar(45)
,`puesto_barbero` varchar(45)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_mis_citas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_mis_citas` (
`apellidos_barbero` varchar(50)
,`extra_tijera` int
,`fecha` date
,`hora` time
,`id_citas` int
,`id_usuario_web` int
,`nombre_barbero` varchar(45)
,`servicio` varchar(50)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_stock_critico`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_stock_critico` (
`id_producto` int
,`nombre_producto` varchar(45)
,`precio` decimal(10,2)
,`stock` int
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_horaris_barberia`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_horaris_barberia` (
`dia_setmana` int
,`es_tancat` tinyint(1)
,`hora_obertura` time
,`hora_tancament` time
);

-- --------------------------------------------------------

--
-- Estructura para la vista `mostrar_trabajadores`
--
DROP TABLE IF EXISTS `mostrar_trabajadores`;

CREATE ALGORITHM=UNDEFINED DEFINER=`kinybdn`@`%` SQL SECURITY DEFINER VIEW `mostrar_trabajadores`  AS SELECT `usuarios`.`id_usuario` AS `id_usuario`, `usuarios`.`nombre` AS `nombre`, `usuarios`.`apellidos` AS `apellidos` FROM `usuarios` WHERE (`usuarios`.`es_trabajador` = 1) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `ofrecer_servicios`
--
DROP TABLE IF EXISTS `ofrecer_servicios`;

CREATE ALGORITHM=UNDEFINED DEFINER=`kinybdn`@`%` SQL SECURITY DEFINER VIEW `ofrecer_servicios`  AS SELECT `servicios`.`nombre_servicio` AS `servicio`, `servicios`.`precio` AS `precio` FROM `servicios` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_agenda_detallada`
--
DROP TABLE IF EXISTS `vista_agenda_detallada`;

CREATE ALGORITHM=UNDEFINED DEFINER=`kinybdn`@`%` SQL SECURITY DEFINER VIEW `vista_agenda_detallada`  AS SELECT `c`.`id_citas` AS `id_citas`, `c`.`fecha` AS `fecha`, `c`.`hora` AS `hora`, `u_cli`.`nombre` AS `nombre_cliente`, `u_emp`.`nombre` AS `nombre_barbero`, `e`.`rol` AS `puesto_barbero` FROM ((((`citas` `c` join `clientes` `cl` on((`c`.`clientes_id_cliente` = `cl`.`id_cliente`))) join `usuarios` `u_cli` on((`cl`.`usuarios_id_usuario` = `u_cli`.`id_usuario`))) join `empleados` `e` on((`c`.`empleados_id_empleado` = `e`.`id_empleado`))) join `usuarios` `u_emp` on((`e`.`usuarios_id_usuario` = `u_emp`.`id_usuario`))) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_mis_citas`
--
DROP TABLE IF EXISTS `vista_mis_citas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`kinybdn`@`%` SQL SECURITY DEFINER VIEW `vista_mis_citas`  AS SELECT `c`.`id_citas` AS `id_citas`, `c`.`fecha` AS `fecha`, `c`.`hora` AS `hora`, `c`.`tall` AS `servicio`, `c`.`tissores` AS `extra_tijera`, `u_barber`.`nombre` AS `nombre_barbero`, `u_barber`.`apellidos` AS `apellidos_barbero`, `cl`.`usuarios_id_usuario` AS `id_usuario_web` FROM (((`citas` `c` join `empleados` `e` on((`c`.`empleados_id_empleado` = `e`.`id_empleado`))) join `usuarios` `u_barber` on((`e`.`usuarios_id_usuario` = `u_barber`.`id_usuario`))) join `clientes` `cl` on((`c`.`clientes_id_cliente` = `cl`.`id_cliente`))) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_stock_critico`
--
DROP TABLE IF EXISTS `vista_stock_critico`;

CREATE ALGORITHM=UNDEFINED DEFINER=`kinybdn`@`%` SQL SECURITY DEFINER VIEW `vista_stock_critico`  AS SELECT `productos`.`id_producto` AS `id_producto`, `productos`.`nombre_producto` AS `nombre_producto`, `productos`.`stock` AS `stock`, `productos`.`precio` AS `precio` FROM `productos` WHERE (`productos`.`stock` < 5) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_horaris_barberia`
--
DROP TABLE IF EXISTS `v_horaris_barberia`;

CREATE ALGORITHM=UNDEFINED DEFINER=`kinybdn`@`%` SQL SECURITY DEFINER VIEW `v_horaris_barberia`  AS SELECT `horaris_barberia`.`dia_setmana` AS `dia_setmana`, `horaris_barberia`.`hora_obertura` AS `hora_obertura`, `horaris_barberia`.`hora_tancament` AS `hora_tancament`, `horaris_barberia`.`es_tancat` AS `es_tancat` FROM `horaris_barberia` ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id_citas`),
  ADD KEY `fk_citas_clientes1_idx` (`clientes_id_cliente`),
  ADD KEY `fk_citas_empleados1_idx` (`empleados_id_empleado`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `usuarios_id_usuario_UNIQUE` (`usuarios_id_usuario`),
  ADD KEY `fk_empleados_copy1_usuarios1_idx` (`usuarios_id_usuario`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id_empleado`),
  ADD UNIQUE KEY `usuarios_id_usuario_UNIQUE` (`usuarios_id_usuario`),
  ADD UNIQUE KEY `num_segsocial_UNIQUE` (`num_segsocial`),
  ADD KEY `fk_empleados_usuarios1_idx` (`usuarios_id_usuario`);

--
-- Indices de la tabla `factura_producto`
--
ALTER TABLE `factura_producto`
  ADD PRIMARY KEY (`id_factura_producto`),
  ADD UNIQUE KEY `id_factura_producto_UNIQUE` (`id_factura_producto`),
  ADD KEY `fk_usuarios_has_productos_productos1_idx` (`productos_id_producto`),
  ADD KEY `fk_usuarios_has_productos_usuarios1_idx` (`usuarios_id_usuario`);

--
-- Indices de la tabla `factura_servicio`
--
ALTER TABLE `factura_servicio`
  ADD PRIMARY KEY (`id_factura_servicio`),
  ADD UNIQUE KEY `id_factura_servicio_UNIQUE` (`id_factura_servicio`),
  ADD KEY `fk_citas_has_servicios_servicios1_idx` (`servicios_id_servicio`),
  ADD KEY `fk_citas_has_servicios_citas1_idx` (`citas_id_citas`);

--
-- Indices de la tabla `horaris_barberia`
--
ALTER TABLE `horaris_barberia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dia_setmana` (`dia_setmana`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`);

--
-- Indices de la tabla `reseñas`
--
ALTER TABLE `reseñas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id_servicio`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo_UNIQUE` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id_citas` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id_empleado` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `factura_producto`
--
ALTER TABLE `factura_producto`
  MODIFY `id_factura_producto` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `factura_servicio`
--
ALTER TABLE `factura_servicio`
  MODIFY `id_factura_servicio` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `horaris_barberia`
--
ALTER TABLE `horaris_barberia`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `reseñas`
--
ALTER TABLE `reseñas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id_servicio` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `fk_citas_clientes1` FOREIGN KEY (`clientes_id_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `fk_citas_empleados1` FOREIGN KEY (`empleados_id_empleado`) REFERENCES `empleados` (`id_empleado`);

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `fk_empleados_copy1_usuarios1` FOREIGN KEY (`usuarios_id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD CONSTRAINT `fk_empleados_usuarios1` FOREIGN KEY (`usuarios_id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `factura_producto`
--
ALTER TABLE `factura_producto`
  ADD CONSTRAINT `fk_usuarios_has_productos_productos1` FOREIGN KEY (`productos_id_producto`) REFERENCES `productos` (`id_producto`),
  ADD CONSTRAINT `fk_usuarios_has_productos_usuarios1` FOREIGN KEY (`usuarios_id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `factura_servicio`
--
ALTER TABLE `factura_servicio`
  ADD CONSTRAINT `fk_citas_has_servicios_servicios1` FOREIGN KEY (`servicios_id_servicio`) REFERENCES `servicios` (`id_servicio`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
