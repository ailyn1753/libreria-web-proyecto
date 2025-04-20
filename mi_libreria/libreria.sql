--Creacion de la base de datos.
CREATE DATABASE libreria;
USE LIBRERIA;

--
-- Estructura de tabla para la tabla `USUARIOS`
--
CREATE TABLE `USUARIOS` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contraseña` varchar(255) NOT NULL, -- Hashed password
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `rol` enum('cliente','admin') NOT NULL DEFAULT 'cliente',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ID`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

----------------------------------------------------------

--
-- Estructura de tabla para la tabla `LIBROS`
--
CREATE TABLE `LIBROS` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `autor` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `cantidad_inventario` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `descripcion` text DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ID`),
  UNIQUE KEY `isbn` (`isbn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

----------------------------------------------------------

--
-- Estructura de tabla para la tabla `CARRITO`
--
CREATE TABLE `CARRITO` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_usuario` int(11) NOT NULL,
  `ID_libro` int(11) NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ID`),
  KEY `ID_usuario` (`ID_usuario`),
  KEY `ID_libro` (`ID_libro`),
  UNIQUE KEY `idx_usuario_libro` (`ID_usuario`,`ID_libro`),-- verifica que el usuario no tenga el mismo libro dos veces en el carrito
  CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`ID_usuario`) REFERENCES `USUARIOS` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`ID_libro`) REFERENCES `LIBROS` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

----------------------------------------------------------

--
-- Estructura de tabla para la tabla `PEDIDOS`
--
CREATE TABLE `PEDIDOS` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_usuario` int(11) NOT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `monto_total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','procesando','enviado','completado','cancelado') NOT NULL DEFAULT 'pendiente',
  `direccion_envio` text DEFAULT NULL, -- Puede tomarse del perfil o ingresarse
  `metodo_pago` varchar(50) DEFAULT NULL, -- Ej: 'Transferencia', 'Webpay Simulado'
  PRIMARY KEY (`ID`),
  KEY `ID_usuario` (`ID_usuario`),
  CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`ID_usuario`) REFERENCES `USUARIOS` (`ID`) ON DELETE RESTRICT ON UPDATE CASCADE -- RESTRICT para no borrar usuarios con pedidos
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

----------------------------------------------------------

--
-- Estructura de tabla para la tabla `DETALLES_PEDIDO`
--
CREATE TABLE `DETALLES_PEDIDO` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_pedido` int(11) NOT NULL,
  `ID_libro` int(11) NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL, -- Precio al momento de la compra
  PRIMARY KEY (`ID`),
  KEY `ID_pedido` (`ID_pedido`),
  KEY `ID_libro` (`ID_libro`),
  CONSTRAINT `detalles_pedido_ibfk_1` FOREIGN KEY (`ID_pedido`) REFERENCES `PEDIDOS` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detalles_pedido_ibfk_2` FOREIGN KEY (`ID_libro`) REFERENCES `LIBROS` (`ID`) ON DELETE RESTRICT ON UPDATE CASCADE -- RESTRICT para no borrar libros si están en pedidos
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
------------------------------------------------------------

--
-- Insertar libros a la base de datos
--
INSERT INTO LIBROS (titulo, autor, precio, cantidad_inventario) VALUES
('Cien años de soledad', 'Gabriel García Márquez', 14990.00, 15),
('Dune', 'Frank Herbert', 19990.00, 8),
('El nombre del viento', 'Patrick Rothfuss', 22500.00, 12),
('La casa de los espíritus', 'Isabel Allende', 16800.00, 20),
('La chica del tren', 'Paula Hawkins', 12990.00, 5),
('Sapiens: De animales a dioses', 'Yuval Noah Harari', 18500.00, 10),
('Veinte poemas de amor y una canción desesperada', 'Pablo Neruda', 9990.00, 25),
('Papelucho', 'Marcela Paz', 7500.00, 30),
('El código Da Vinci', 'Dan Brown', 11500.00, 0),
('Así habló Zaratustra', 'Friedrich Nietzsche', 13490.00, 7),
('La cancion de Aquiles', 'Madeline Miller', 32500.00, 7),
('La Sombra de los Dioses', 'John Gwynee', 17990.00, 20);

COMMIT;