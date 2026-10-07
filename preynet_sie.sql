-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-10-2026 a las 21:25:37
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
-- Base de datos: `preynet_sie`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `direccion` varchar(255) NOT NULL,
  `codigo_postal` varchar(10) NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `provincia` varchar(100) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `apellidos`, `email`, `telefono`, `direccion`, `codigo_postal`, `ciudad`, `provincia`, `fecha_creacion`) VALUES
(1, 'ciente', 'prueba preynet', 'cliente@prueba.test', '600000000', 'Calle Prueba 1', '30001', 'Murcia', 'Murcia', '2026-10-07 17:05:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tipo` varchar(80) NOT NULL,
  `session_id` varchar(128) DEFAULT NULL,
  `cliente_id` int(10) UNSIGNED DEFAULT NULL,
  `producto_id` int(10) UNSIGNED DEFAULT NULL,
  `pedido_id` int(10) UNSIGNED DEFAULT NULL,
  `incidencia_id` int(10) UNSIGNED DEFAULT NULL,
  `metadata` longtext DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id`, `tipo`, `session_id`, `cliente_id`, `producto_id`, `pedido_id`, `incidencia_id`, `metadata`, `fecha_creacion`) VALUES
(1, 'product.viewed', '3u5pu2bpfr5sblmpqhsddk2o41', NULL, 1, NULL, NULL, '{\"sku\":\"MON-001\",\"nombre\":\"PreyView M24 Full HD\",\"categoria\":\"Monitores\"}', '2026-10-07 16:42:34'),
(2, 'product.viewed', '3u5pu2bpfr5sblmpqhsddk2o41', NULL, 1, NULL, NULL, '{\"sku\":\"MON-001\",\"nombre\":\"PreyView M24 Full HD\",\"categoria\":\"Monitores\"}', '2026-10-07 16:47:04'),
(3, 'product.viewed', '3u5pu2bpfr5sblmpqhsddk2o41', NULL, 1, NULL, NULL, '{\"sku\":\"MON-001\",\"nombre\":\"PreyView M24 Full HD\",\"categoria\":\"Monitores\"}', '2026-10-07 16:47:12'),
(4, 'cart.item_added', '3u5pu2bpfr5sblmpqhsddk2o41', NULL, 1, NULL, NULL, '{\"sku\":\"MON-001\",\"nombre\":\"PreyView M24 Full HD\",\"cantidad_anadida\":1,\"cantidad_carrito\":1,\"precio_unitario\":119.9}', '2026-10-07 16:47:14'),
(5, 'product.viewed', '3u5pu2bpfr5sblmpqhsddk2o41', NULL, 1, NULL, NULL, '{\"sku\":\"MON-001\",\"nombre\":\"PreyView M24 Full HD\",\"categoria\":\"Monitores\"}', '2026-10-07 16:47:15'),
(6, 'product.viewed', '3u5pu2bpfr5sblmpqhsddk2o41', NULL, 1, NULL, NULL, '{\"sku\":\"MON-001\",\"nombre\":\"PreyView M24 Full HD\",\"categoria\":\"Monitores\"}', '2026-10-07 16:49:10'),
(7, 'product.viewed', '3u5pu2bpfr5sblmpqhsddk2o41', NULL, 1, NULL, NULL, '{\"sku\":\"MON-001\",\"nombre\":\"PreyView M24 Full HD\",\"categoria\":\"Monitores\"}', '2026-10-07 16:49:16'),
(8, 'checkout.started', '3u5pu2bpfr5sblmpqhsddk2o41', NULL, NULL, NULL, NULL, '{\"unidades\":2,\"subtotal\":239.8,\"iva\":50.36,\"envio\":0,\"total\":290.16}', '2026-10-07 16:57:13'),
(9, 'checkout.started', '3u5pu2bpfr5sblmpqhsddk2o41', NULL, NULL, NULL, NULL, '{\"unidades\":2,\"subtotal\":239.8,\"iva\":50.36,\"envio\":0,\"total\":290.16}', '2026-10-07 17:02:09'),
(10, 'checkout.started', '3u5pu2bpfr5sblmpqhsddk2o41', NULL, NULL, NULL, NULL, '{\"unidades\":2,\"subtotal\":239.8,\"iva\":50.36,\"envio\":0,\"total\":290.16}', '2026-10-07 17:03:50'),
(11, 'checkout.started', '3u5pu2bpfr5sblmpqhsddk2o41', NULL, NULL, NULL, NULL, '{\"unidades\":2,\"subtotal\":239.8,\"iva\":50.36,\"envio\":0,\"total\":290.16}', '2026-10-07 17:04:50'),
(12, 'order.created', '3u5pu2bpfr5sblmpqhsddk2o41', 1, NULL, 1, NULL, '{\"codigo_pedido\":\"PN-20261007-190551-1A779E\",\"subtotal\":239.8,\"iva\":50.36,\"envio\":0,\"total\":290.16,\"estado\":\"pendiente_pago\"}', '2026-10-07 17:05:51'),
(13, 'payment.simulated', '3u5pu2bpfr5sblmpqhsddk2o41', 1, NULL, 1, NULL, '{\"codigo_pedido\":\"PN-20261007-190551-1A779E\",\"metodo\":\"tarjeta_simulada\",\"importe\":290.16,\"referencia\":\"PAY-20261007-191059-7A8316\",\"estado\":\"simulado_aprobado\"}', '2026-10-07 17:10:59'),
(14, 'support.ticket_created', '3u5pu2bpfr5sblmpqhsddk2o41', 1, NULL, 1, NULL, '{\"incidencia_id\":1,\"codigo\":\"INC-20261007-CC2780\",\"asunto\":\"Producto recibido con una incidencia\",\"prioridad\":\"alta\",\"estado\":\"abierta\"}', '2026-10-07 17:35:39'),
(15, 'product.viewed', '3u5pu2bpfr5sblmpqhsddk2o41', NULL, 1, NULL, NULL, '{\"sku\":\"MON-001\",\"nombre\":\"PreyView M24 Full HD\",\"categoria\":\"Monitores\"}', '2026-10-07 19:07:08'),
(16, 'product.viewed', '3u5pu2bpfr5sblmpqhsddk2o41', NULL, 2, NULL, NULL, '{\"sku\":\"TEC-001\",\"nombre\":\"PreyKey MK2\",\"categoria\":\"Teclados\"}', '2026-10-07 19:07:16'),
(17, 'product.viewed', '3u5pu2bpfr5sblmpqhsddk2o41', NULL, 6, NULL, NULL, '{\"sku\":\"MOV-001\",\"nombre\":\"PreyPhone 5G 128 GB\",\"categoria\":\"Móviles\"}', '2026-10-07 19:07:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidencias`
--

CREATE TABLE `incidencias` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(40) NOT NULL,
  `pedido_id` int(10) UNSIGNED DEFAULT NULL,
  `cliente_id` int(10) UNSIGNED DEFAULT NULL,
  `nombre` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `asunto` varchar(150) NOT NULL,
  `mensaje` text NOT NULL,
  `estado` varchar(40) NOT NULL DEFAULT 'abierta',
  `prioridad` enum('baja','media','alta') NOT NULL DEFAULT 'media',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `incidencias`
--

INSERT INTO `incidencias` (`id`, `codigo`, `pedido_id`, `cliente_id`, `nombre`, `email`, `asunto`, `mensaje`, `estado`, `prioridad`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'INC-20261007-CC2780', 1, 1, 'cliente prueba preynet', 'cliente@prueba.test', 'Producto recibido con una incidencia', 'El monitor recibido presenta un problema de imagen y solicito asistencia para resolver la incidencia.', 'en_proceso', 'alta', '2026-10-07 17:35:39', '2026-10-07 17:39:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lineas_pedido`
--

CREATE TABLE `lineas_pedido` (
  `id` int(10) UNSIGNED NOT NULL,
  `pedido_id` int(10) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED DEFAULT NULL,
  `nombre_producto` varchar(150) NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `iva_porcentaje` decimal(5,2) NOT NULL DEFAULT 21.00,
  `total_linea` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `lineas_pedido`
--

INSERT INTO `lineas_pedido` (`id`, `pedido_id`, `producto_id`, `nombre_producto`, `cantidad`, `precio_unitario`, `iva_porcentaje`, `total_linea`) VALUES
(1, 1, 1, 'PreyView M24 Full HD', 2, 119.90, 21.00, 239.80);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(10) UNSIGNED NOT NULL,
  `pedido_id` int(10) UNSIGNED NOT NULL,
  `metodo` varchar(50) NOT NULL,
  `importe` decimal(10,2) NOT NULL,
  `estado` varchar(40) NOT NULL DEFAULT 'simulado',
  `referencia` varchar(100) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id`, `pedido_id`, `metodo`, `importe`, `estado`, `referencia`, `fecha_creacion`) VALUES
(1, 1, 'tarjeta_simulada', 290.16, 'simulado_aprobado', 'PAY-20261007-191059-7A8316', '2026-10-07 17:10:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `cliente_id` int(10) UNSIGNED NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `envio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `iva` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` varchar(40) NOT NULL DEFAULT 'creado',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `codigo`, `cliente_id`, `subtotal`, `descuento`, `envio`, `iva`, `total`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'PN-20261007-190551-1A779E', 1, 239.80, 0.00, 0.00, 50.36, 290.16, 'enviado', '2026-10-07 17:05:51', '2026-10-07 18:00:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(10) UNSIGNED NOT NULL,
  `sku` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `categoria` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `imagen` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `sku`, `nombre`, `categoria`, `descripcion`, `precio`, `stock`, `imagen`, `activo`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'MON-001', 'PreyView M24 Full HD', 'Monitores', 'Monitor de 24 pulgadas Full HD, panel IPS y conexión HDMI.', 119.90, 13, 'img/monitor-m24.jpg', 1, '2026-10-07 16:12:44', '2026-10-07 19:03:13'),
(2, 'TEC-001', 'PreyKey MK2', 'Teclados', 'Teclado mecánico con retroiluminación y conexión USB.', 59.90, 20, 'img/teclado-mecanico.jpg', 1, '2026-10-07 16:12:44', '2026-10-07 19:04:35'),
(3, 'RAT-001', 'PreyMouse W5', 'Ratones', 'Ratón inalámbrico ergonómico con sensor óptico ajustable.', 29.90, 30, 'img/raton-inalambrico.jpg', 1, '2026-10-07 16:12:44', '2026-10-07 19:04:49'),
(4, 'TOR-001', 'PreyCore Gaming R5', 'Torres PC', 'Ordenador de sobremesa para gaming con procesador de seis núcleos y SSD.', 899.00, 6, 'img/torre-gaming-r5.jpg', 1, '2026-10-07 16:12:44', '2026-10-07 19:05:03'),
(5, 'POR-001', 'PreyBook 15 Pro', 'Portátiles', 'Portátil de 15,6 pulgadas con SSD de 512 GB y 16 GB de memoria RAM.', 749.00, 8, 'img/portatil-pro14.jpg', 1, '2026-10-07 16:12:44', '2026-10-07 19:05:18'),
(6, 'MOV-001', 'PreyPhone 5G 128 GB', 'Móviles', 'Smartphone 5G con pantalla de 6,5 pulgadas y 128 GB de almacenamiento.', 399.00, 12, 'img/movil-5g-128gb.jpg', 1, '2026-10-07 16:12:44', '2026-10-07 19:05:36'),
(7, 'MIC-001', 'PreyMic USB Studio', 'Micrófonos', 'Micrófono USB para videollamadas, streaming y creación de contenido.', 69.90, 18, 'img/microfono-usb-studio.jpg', 1, '2026-10-07 16:12:44', '2026-10-07 19:05:54'),
(8, 'WEB-001', 'PreyCam Full HD', 'Webcams', 'Webcam Full HD 1080p con micrófono integrado y conexión USB.', 49.90, 22, 'img/webcam-fullhd.jpg', 1, '2026-10-07 16:12:44', '2026-10-07 19:06:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(30) NOT NULL DEFAULT 'admin',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `activo`, `fecha_creacion`) VALUES
(1, 'Administrador PreyNet', 'admin@preynet.onl', '$2y$10$My8XwaiPdVm0VBqr346aGe2siur7B9jrpmGWV2dW6hZjOjlbX9Xoi', 'admin', 1, '2026-10-07 16:17:07');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_clientes_email` (`email`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_evento_cliente` (`cliente_id`),
  ADD KEY `fk_evento_producto` (`producto_id`),
  ADD KEY `fk_evento_pedido` (`pedido_id`),
  ADD KEY `fk_evento_incidencia` (`incidencia_id`),
  ADD KEY `idx_evento_tipo` (`tipo`),
  ADD KEY `idx_evento_fecha` (`fecha_creacion`);

--
-- Indices de la tabla `incidencias`
--
ALTER TABLE `incidencias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `fk_incidencia_pedido` (`pedido_id`),
  ADD KEY `fk_incidencia_cliente` (`cliente_id`);

--
-- Indices de la tabla `lineas_pedido`
--
ALTER TABLE `lineas_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_linea_pedido` (`pedido_id`),
  ADD KEY `idx_linea_producto` (`producto_id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pago_pedido` (`pedido_id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `idx_pedidos_estado` (`estado`),
  ADD KEY `idx_pedidos_cliente` (`cliente_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `incidencias`
--
ALTER TABLE `incidencias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `lineas_pedido`
--
ALTER TABLE `lineas_pedido`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `fk_evento_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_evento_incidencia` FOREIGN KEY (`incidencia_id`) REFERENCES `incidencias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_evento_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_evento_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `incidencias`
--
ALTER TABLE `incidencias`
  ADD CONSTRAINT `fk_incidencia_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_incidencia_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `lineas_pedido`
--
ALTER TABLE `lineas_pedido`
  ADD CONSTRAINT `fk_linea_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_linea_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `fk_pago_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedido_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
