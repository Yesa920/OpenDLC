-- ===========================================================================
-- OpenDLC - base de datos de DEMOSTRACION (proyecto de DAM)
-- Cuentas de ejemplo (contrasenas guardadas con hash bcrypt + sal):
--   Administrador:  demo_admin   /  Demo-Admin-2026
--   Cliente:        demo@opendlc.test  /  Demo-Cliente-2026
-- Datos de prueba ficticios. Si publicas la web en internet, cambia la clave del admin:
--   docker compose exec web php db/cambiar_clave_admin.php demo_admin TuClaveNueva
-- ===========================================================================

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-05-2026 a las 12:28:54
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
-- Base de datos: `pccomponentes`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id_administrador` int(11) NOT NULL,
  `usuario` varchar(20) NOT NULL,
  `contrasegna` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`id_administrador`, `usuario`, `contrasegna`) VALUES
(1, 'demo_admin', '$2y$12$M/SGcZP4xHs/.VN1IJwVp.4/7IrDV5LvF.36YCKCEEuEWvxNQYwuu');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `tipo_hardware` varchar(50) DEFAULT NULL,
  `Precio` decimal(10,2) DEFAULT NULL,
  `cantidad` int(11) DEFAULT 1,
  `fecha_agregado` datetime DEFAULT current_timestamp(),
  `id_cliente` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `cp` int(5) NOT NULL,
  `email` varchar(70) NOT NULL,
  `telefono` int(9) NOT NULL,
  `contrasegna` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`id_cliente`, `nombre`, `apellido`, `cp`, `email`, `telefono`, `contrasegna`) VALUES
(1, 'Demo', 'Cliente', 28001, 'demo@opendlc.test', 600000000, '$2y$12$.UPh5lXcm2KpeeKCti6AQOV8G8lZV/OcldW.loYrPdZ2QW7BneJPq');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `componentes`
--

CREATE TABLE `componentes` (
  `codigo_producto` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `tipo_hardware` varchar(30) NOT NULL,
  `Antiguo` varchar(2) NOT NULL,
  `Precio` float NOT NULL,
  `Comentario` varchar(300) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `stock` int(11) DEFAULT 10,
  `id_administrador` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `componentes`
--

INSERT INTO `componentes` (`codigo_producto`, `nombre`, `tipo_hardware`, `Antiguo`, `Precio`, `Comentario`, `imagen`, `stock`, `id_administrador`) VALUES
(2, '16 GB de RAM', 'ram', 'no', 20, 'un comentario', '692df16d662bf_ram.png', 6, 1),
(3, 'RAM DDR5 6000MHz 32GB 2x16GB', 'ram', 'no', 50, 'Diseño de enfriamiento de PMIC fortalecido\r\nECC on-die para darle estabilidad al sistema\r\nCircuitos integrados de alta calidad seleccionados para dar estabilidad y confiabilidad', '6a1949b31b7a1_450_1000.jpg', 9, 1),
(5, 'Cable sata', 'cables', 'no', 5, 'AK-CA-51 es un cable SATA III de 50 cm de largo y alta calidad que le permite conectar componentes como discos duros o SSD.\r\n\r\nEl ángulo de 90 ° del conector facilita la conexión a la toma, especialmente para gabinetes con interiores menos espaciosos.', '6a194e2eac4e0_images (1).jpg', 20, 1),
(11, 'Cable de alimientacion', 'cables', 'no', 10, 'Cable de alimentación doble de alta calidad AK-PC-04A, diseñado para alimentar simultáneamente dos adaptadores ATX, ITX y todos los demás compatibles con el enchufe IEC C13.\r\n\r\nEste cable permite alimentar dos dispositivos a la vez. La longitud del cable es de 1.15 m para el cable combinado y', '6a194a5ae5bdf_cable para fuente de alimentacion.jpg', 50, 1),
(12, 'Disco 480GB', 'discos', 'no', 70, 'Los discos de estado sólido ofrecen enormes mejoras en la velocidad de respuesta, sin actualizaciones adicionales del hardware.\r\nBrinda lapsos de arranque, carga y de transferencia de archivos increíblemente más breves en comparación con las unidades de disco duro mecánico.\r\nOfrecen velocidades de l', '6a194abc3f195_disco duro sata.jpg', 10, 1),
(15, 'Tarjeta Gráfica ASUS DUAL GeForce', 'TarjGraf', 'no', 270, 'Equipada con GeForce RTX 5050\r\n8 GB de VRAM GDDR6 e interfaz de memoria de 128 bits\r\n2 ventiladores duales con rodamientos de bolas\r\nPaquete de software GPU Tweak III para gestionar y supervisar el rendimiento de tu hardware', '6a194b3aac432_Tarjeta Gráfica ASUS DUAL GeForce.jpg', 10, 1),
(17, 'Disco 500GB', 'discos', 'no', 50, 'que buen disco duro', '692de815627b8_disco_duro.png', 8, 1),
(20, 'rj-45', 'cables', 'no', 10, 'Este un cable de alta calidad de rj-45 de 1Gb de velocidad', '69d62734c2cbf_rj-45.png', 8, 1),
(23, 'Pc Gaming', 'OrdenadorPremontados', 'no', 500, 'CoolPC Gamer III, un modelo pensado para jugar en Full HD\r\nCoolPC Gamer VI, la opción intermedia para los que busquen un alto rendimiento en 1080p o jugar en 1440p de manera solvente\r\nCoolPC Gamer XIII, un ordenador con hardware de AMD de gran potencia\r\nCoolPC Gamer XIV, el dispositivo perfecto para', '69d62aaa7adbe_pc.png', 10, 1),
(24, 'fuentes de alimentación 287 W', 'FuenteAlimentacion', 'no', 40, 'Fuente de alimentacion calidad precio', '69d777959bd69_fuente_de_alimentacion33.png', 35, 1),
(26, 'fuentes de alimentación', 'FuenteAlimentacion', 'no', 40, '', '69d77afcf20df_fuente_de_alimentacion33.png', 29, 1),
(28, 'Portatil', 'OrdenadorPremontados', 'no', 600, 'Un portatil muy bueno', '6a12f1844b77a_ordenador-portatil-asus-zenbook-14-8gb-512gb.jpg', 3, 1),
(29, 'IntelCore i7-8700', 'Procesador', 'no', 100, 'El Intel Core i7-8700 es un potente procesador perfecto para un uso intensivo, ya sea juegos, edición de vídeo u otras tareas exigentes. Con 6 núcleos y 12 subprocesos, esta CPU ofrece un rendimiento impresionante y garantiza una experiencia fluida y con capacidad de respuesta. Lo que hace que este ', '6a195a707b4e6_intel_core_i7-8700.png', 5, 1),
(30, 'Intel i3/i5/i7 LGA115x disipador de Calor y Ventilador para CPU E97379-003.', 'Refrigeracion', 'no', 9.99, 'Descatalogado por el fabricante ‏ : ‎ No\r\nDimensiones del producto ‏ : ‎ 11,94 x 10,41 x 7,11 cm; 181,44 g\r\nFabricante ‏ : ‎ Intel\r\nASIN ‏ : ‎ B01MSD39CN\r\nNúmero de modelo del producto ‏ : ‎ 8541601779', '6a195aeaa49a7_fun_cpu.jpg', 60, 1),
(31, 'Ventilador para PC RGB de 12v, 120x120x25 mm Ventilador para Ordenador Silencioso de alto flujo de A', 'Refrigeracion', 'no', 7.99, 'DISEÑO DEL VENTILADOR DE AIRE: Ventilador con aspas para generar y mejorar el flujo de aire, facilita una ventilación eficiente, Ventilador diseñado para minimizar el nivel de ruido y reducir la presión estática\r\nRENDIMIENTO SILENCIOSO: Cojinete hidráulico con una vida útil más larga para reducir el', '6a195b6ec6e54_61nuwxyslYL._AC_UF894,1000_QL80_.jpg', 49, 1),
(32, 'ASUS ROG Strix B550-F Gaming', 'Placa_base', 'no', 139.99, 'Zócalo (Socket): AM4, compatible con procesadores AMD Ryzen de las series 3000, 4000G y 5000.Chipset: AMD B550.Ranuras de Expansión: Cuenta con PCIe 4.0 listo para las tarjetas gráficas y los componentes más rápidos de esa generación.Almacenamiento: Dispone de 2 ranuras M.2 (con disipadores térmicos', '6a195ca0b764d_images (2).jpg', 7, 1),
(33, 'Caja ATX Ordenador Pc de Sobremesa Torre Exeom® Office Amarilla USB Frontal S/Fuente', 'Torre', 'no', 29.99, '\r\nDetalles técnicos	 \r\nTipo de Caja	ATX\r\nTipo de Placa	ATX\r\nBahias Internas	2x3,5\"\r\nBahias Externas	2x5.25\"\r\nRefrigreración	Rejilla de Ventilación\r\nMaterial	SPCC\r\nPuertos Frontal	2 x USB\r\nPuertos Audio Frontal	(AC97 + HD) / Auricular\r\nGrosor	0,40 mm\r\nAlimentación	Sin Fuente de Alimentacion\r\nAdaptado', '6a195d44093d9_Caja_ATX_Ordenador_Pc_de_Sobremesa_Torre_Exeom_174__Office_Amarilla_USB_Frontal.jpg', 10, 1),
(35, 'FORE Systems PCI Fast Ethernet Adapter', 'Tarjeta_de_red', 'no', 24.99, 'Tarjeta de red de interfaz PCI clásica con puerto RJ-45. Componente de legado utilizado principalmente en servidores antiguos o entornos industriales.', '6a1962cd7ee24_ForeRunnerLE_25_ATM_Network_Interface_(1).jpg', 7, 1),
(36, 'Creative Sound Blaster Audigy 2 ZS', 'Tarjeta_de_sonido', 'si', 40.99, 'Tarjeta de audio interna legendaria con interfaz PCI clásica. Ofrece soporte para sonido envolvente 7.1, certificación THX y procesamiento de audio de 24 bits a 192 kHz. Muy cotizada hoy en día por entusiastas del software y videojuegos retro.', '6a196361f1042_Soundblaster_Audigy2_ZS.jpg', 4, 1),
(37, 'Monitor PC 68,6 cm (27\") HP S5 527sa, 100 Hz FHD IPS antirreflectante y certificación Eyesafe, con a', 'Monitor', 'no', 149.99, 'Los clientes elogian ampliamente la calidad de imagen excelente, la pantalla clara y nítida, y la relación calidad-precio. Destacan la reproducción de color vívida, el diseño moderno y elegante, y los altavoces integrados como fortalezas principales. Muchos valoran especialmente la ausencia de fatig', '6a1963f94c51e_00115216122406____10__1200x1200.jpg', 7, 1),
(38, 'Ratón Óptico de Oficina USB (6 Botones)', 'Raton', 'no', 9.99, 'Ratón cableado ergonómico con conexión USB estándar. Cuenta con clics silenciosos, rueda de desplazamiento central, botón selector de DPI para ajustar la velocidad del cursor y dos botones laterales para navegación rápida.', '6a1964631987f_Raton-Mouse-usb-Cable-Iluminacion-Para-PC-Gaming-Ordenador-Optico-2400-dpi-Negro.jpg', 18, 1);

--
-- Disparadores `componentes`
--
DELIMITER $$
CREATE TRIGGER `tr_stock_agotado` AFTER UPDATE ON `componentes` FOR EACH ROW BEGIN
    IF NEW.stock = 0 AND OLD.stock > 0 THEN
        INSERT INTO reposicion_pendiente (codigo_producto, fecha_reposicion)
        VALUES (NEW.codigo_producto, DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 09:00:00'));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `devoluciones`
--

CREATE TABLE `devoluciones` (
  `id` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(20) NOT NULL DEFAULT 'PENDIENTE',
  `comentarios` varchar(255) DEFAULT NULL,
  `punto_recogida` varchar(120) DEFAULT NULL,
  `fecha_horario` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quejas`
--

CREATE TABLE `quejas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(20) DEFAULT NULL,
  `correo` varchar(50) DEFAULT NULL,
  `telefono` int(11) DEFAULT NULL,
  `comentario` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reposicion_pendiente`
--

CREATE TABLE `reposicion_pendiente` (
  `id` int(11) NOT NULL,
  `codigo_producto` int(11) NOT NULL,
  `fecha_reposicion` datetime NOT NULL,
  `procesado` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `valoraciones`
--

CREATE TABLE `valoraciones` (
  `id` int(11) NOT NULL,
  `codigo_producto` int(11) DEFAULT NULL,
  `puntuacion` int(11) DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `valoraciones`
--

INSERT INTO `valoraciones` (`id`, `codigo_producto`, `puntuacion`, `comentario`, `id_cliente`) VALUES
(1, 2, 1, NULL, NULL),
(2, 3, 4, NULL, NULL),
(4, 5, 5, NULL, NULL),
(9, 26, 5, NULL, NULL),
(11, 11, 5, NULL, NULL),
(13, 23, 3, NULL, NULL),
(15, 17, 2, '', NULL),
(16, 24, 1, 'No ha funcionado', NULL),
(17, 31, 1, 'me ha llegado roto', NULL),
(18, 28, 5, 'ordenadores muy buenos', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `codigo_producto` int(11) DEFAULT NULL,
  `producto` varchar(100) DEFAULT NULL,
  `tipoHardware` varchar(50) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `id_administrador` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_cliente` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `codigo_producto`, `producto`, `tipoHardware`, `cantidad`, `precio`, `total`, `id_administrador`, `fecha`, `id_cliente`) VALUES
(4, 23, 'Pc Gaming', 'OrdenadorPremontados', 1, 500.00, 500.00, 1, '2026-04-12 15:12:15', 1),
(5, 5, '16 GB de RAM', 'ram', 1, 20.00, 20.00, 1, '2026-04-12 15:23:39', 1),
(8, 17, 'Disco 500GB', 'discos', 1, 50.00, 50.00, 1, '2026-04-15 07:13:03', 1),
(17, 5, '16 GB de RAM', 'ram', 1, 20.00, 20.00, 1, '2026-04-29 07:33:50', 1),
(19, 5, '16 GB de RAM', 'ram', 1, 20.00, 20.00, 1, '2026-04-29 07:42:11', 1),
(20, 24, 'fuentes de alimentación 287 W', 'FuenteAlimentacion', 8, 40.00, 320.00, 1, '2026-05-23 12:22:27', 1),
(21, 2, '16 GB de RAM', 'ram', 2, 20.00, 40.00, 1, '2026-05-23 12:35:04', 1),
(23, 5, '16 GB de RAM', 'ram', 2, 20.00, 40.00, 1, '2026-05-23 13:39:03', 1),
(30, 23, 'Pc Gaming', 'OrdenadorPremontados', 2, 500.00, 1000.00, 1, '2026-05-24 09:17:16', 1),
(31, 17, 'Disco 500GB', 'discos', 1, 50.00, 50.00, 1, '2026-05-24 09:18:53', 1),
(32, 26, 'fuentes de alimentación', 'FuenteAlimentacion', 1, 40.00, 40.00, 1, '2026-05-24 09:19:50', 1),
(33, 26, 'fuentes de alimentación', 'FuenteAlimentacion', 1, 40.00, 40.00, 1, '2026-05-24 09:22:23', 1),
(35, 5, '16 GB de RAM', 'ram', 1, 20.00, 20.00, 1, '2026-05-24 12:28:57', 1),
(38, 28, 'Portatil', 'OrdenadorPremontados', 1, 600.00, 600.00, 1, '2026-05-25 08:29:33', 1),
(42, 20, 'rj-45', 'cables', 2, 10.00, 20.00, 1, '2026-05-29 10:10:59', 1),
(43, 28, 'Portatil', 'OrdenadorPremontados', 2, 600.00, 1200.00, 1, '2026-05-29 10:10:59', 1),
(44, 31, 'Ventilador para PC RGB de 12v, 120x120x25 mm Ventilador para Ordenador Silencioso de alto flujo de A', 'Refrigeracion', 1, 7.99, 7.99, 1, '2026-05-29 10:10:59', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id_administrador`);

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_id_cliente` (`id_cliente`),
  ADD KEY `fk_productos_cliente` (`producto_id`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `uk_cliente_email` (`email`);

--
-- Indices de la tabla `componentes`
--
ALTER TABLE `componentes`
  ADD PRIMARY KEY (`codigo_producto`),
  ADD KEY `id_administrador` (`id_administrador`);

--
-- Indices de la tabla `devoluciones`
--
ALTER TABLE `devoluciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `quejas`
--
ALTER TABLE `quejas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `reposicion_pendiente`
--
ALTER TABLE `reposicion_pendiente`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `valoraciones`
--
ALTER TABLE `valoraciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_producto` (`codigo_producto`,`id_cliente`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_administrador` (`id_administrador`),
  ADD KEY `fk_producto_componente` (`codigo_producto`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id_administrador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `componentes`
--
ALTER TABLE `componentes`
  MODIFY `codigo_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `devoluciones`
--
ALTER TABLE `devoluciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `quejas`
--
ALTER TABLE `quejas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `reposicion_pendiente`
--
ALTER TABLE `reposicion_pendiente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `valoraciones`
--
ALTER TABLE `valoraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `fk_id_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`),
  ADD CONSTRAINT `fk_productos_cliente` FOREIGN KEY (`producto_id`) REFERENCES `componentes` (`codigo_producto`);

--
-- Filtros para la tabla `componentes`
--
ALTER TABLE `componentes`
  ADD CONSTRAINT `id_administrador` FOREIGN KEY (`id_administrador`) REFERENCES `administradores` (`id_administrador`);

--
-- Filtros para la tabla `valoraciones`
--
ALTER TABLE `valoraciones`
  ADD CONSTRAINT `codigo_producto` FOREIGN KEY (`codigo_producto`) REFERENCES `componentes` (`codigo_producto`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `fk_producto_componente` FOREIGN KEY (`codigo_producto`) REFERENCES `componentes` (`codigo_producto`),
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`id_administrador`) REFERENCES `administradores` (`id_administrador`);

DELIMITER $$
--
-- Eventos
--
CREATE DEFINER=`root`@`localhost` EVENT `ev_reponer_stock` ON SCHEDULE EVERY 1 DAY STARTS '2026-04-29 09:00:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    UPDATE componentes c
    JOIN reposicion_pendiente r ON c.codigo_producto = r.codigo_producto
    SET c.stock = 5, r.procesado = 1
    WHERE r.fecha_reposicion <= NOW() AND r.procesado = 0;
END$$

CREATE DEFINER=`root`@`localhost` EVENT `ev_procesar_devoluciones` ON SCHEDULE EVERY 1 DAY STARTS '2026-05-24 10:25:11' ON COMPLETION PRESERVE ENABLE DO BEGIN
    -- 1. Devolver stock a cada componente cuya devolución ya llegó
    UPDATE componentes c
    JOIN ventas v ON v.codigo_producto = c.codigo_producto
    JOIN devoluciones d ON d.id_venta = v.id
    SET c.stock = c.stock + v.cantidad,
        d.estado = 'DEVUELTA'
    WHERE d.fecha_horario <= NOW()
      AND d.estado = 'PROGRAMADA';
 
    -- 2. Eliminar las ventas que ya han sido procesadas
    DELETE v FROM ventas v
    JOIN devoluciones d ON d.id_venta = v.id
    WHERE d.estado = 'DEVUELTA'
      AND d.fecha_horario <= NOW();
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
