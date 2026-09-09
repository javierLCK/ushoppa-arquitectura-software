-- =========================================================================
-- Ushoppa — Esquema de base de datos
-- NexoMarket / ISI602 Arquitectura de Software (A+S)
-- =========================================================================
-- 5 tablas: usuarios, categorias, productos, pedidos, pedido_detalle.
--
-- Diseño intencional:
--   - NO existe tabla "carrito": el carrito de compras es temporal y vive
--     en $_SESSION mientras el usuario navega (ver includes/functions.php,
--     sección CARRITO). Recién se persiste en 'pedidos' + 'pedido_detalle'
--     al confirmar el pago (actions/checkout.php).
--   - pedido_detalle copia nombre_producto/marca/imagen/precio en vez de
--     solo apuntar a productos.id: así el historial de un pedido se ve
--     igual aunque el vendedor después edite o borre el producto original
--     (foto histórica del pedido en el momento de la compra).
--   - Las contraseñas se guardan con password_hash() de PHP (bcrypt),
--     nunca en texto plano (ver public/login.php y public/register.php).
-- =========================================================================

CREATE DATABASE IF NOT EXISTS ushoppa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ushoppa;

-- ─── USUARIOS ───────────────────────────────────────────────
-- rol determina qué panel ve cada quien (require_role() en functions.php):
--   cliente  -> compra en el catálogo
--   vendedor -> gestiona sus propios productos (public/vendor/*.php)
--   admin    -> modera usuarios y productos de toda la plataforma (public/admin/*.php)
-- estado = 'suspendido'/'inactivo' bloquea el login aunque la contraseña sea correcta.
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,       -- UNIQUE: no se permiten dos cuentas con el mismo correo
    password VARCHAR(255) NOT NULL,            -- hash bcrypt (60 caracteres), 255 deja margen a futuro
    rol ENUM('cliente','vendedor','admin') NOT NULL DEFAULT 'cliente',
    estado ENUM('activo','inactivo','suspendido') NOT NULL DEFAULT 'activo',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─── CATEGORÍAS ─────────────────────────────────────────────
-- Tabla simple, separada de CATEGORIAS_LISTA (constante en functions.php)
-- que se usa solo para pintar el filtro "Todas" + categorías en catalog.php.
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ─── PRODUCTOS ──────────────────────────────────────────────
-- precio_original + descuento son redundantes a propósito: precio_original
-- se recalcula en vendor/product-form.php a partir de precio y descuento,
-- para poder mostrar el tachado "antes $X" sin que el vendedor tenga que
-- mantener ambos valores sincronizados a mano.
-- activo=0 oculta el producto del catálogo público sin borrarlo (moderación
-- por el propio vendedor o por un admin, ver toggle-product.php en ambos paneles).
-- stock=0 no desactiva el producto, pero sí bloquea el botón de compra
-- (ver includes/product-card.php y product.php).
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    marca VARCHAR(80) NOT NULL,
    descripcion TEXT NOT NULL,
    categoria_id INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,             -- precio final de venta
    precio_original DECIMAL(10,2) DEFAULT NULL, -- precio "tachado" antes del descuento (NULL si no hay descuento)
    descuento TINYINT DEFAULT 0,               -- porcentaje entero, ej: 25 = 25% off
    stock INT NOT NULL DEFAULT 0,
    imagen VARCHAR(255) NOT NULL,              -- solo el ID de foto de Unsplash (ver img_url() en functions.php)
    vendedor_id INT NOT NULL,
    badge VARCHAR(40) DEFAULT NULL,            -- etiqueta editorial opcional, ej: "Más vendido"
    es_nuevo TINYINT(1) DEFAULT 0,             -- muestra la cinta "NUEVO" en la tarjeta
    rating DECIMAL(2,1) DEFAULT 4.5,           -- promedio de 1.0 a 5.0
    reviews INT DEFAULT 0,                     -- cantidad de reseñas (solo informativo, sin tabla de reseñas)
    activo TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    -- ON DELETE CASCADE: si se elimina un vendedor, se eliminan sus productos.
    -- (en la práctica el proyecto nunca borra usuarios, solo los suspende,
    -- pero se deja la integridad referencial correcta igualmente).
    FOREIGN KEY (vendedor_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── PEDIDOS ────────────────────────────────────────────────
-- id es VARCHAR (no autoincremental) porque se genera en PHP con el
-- formato "#USH-XXXX" (ver gen_order_id() en functions.php), para que
-- el número de pedido mostrado al cliente sea el mismo que la clave real.
CREATE TABLE pedidos (
    id VARCHAR(20) PRIMARY KEY,
    usuario_id INT NOT NULL,                   -- comprador (siempre rol='cliente')
    total DECIMAL(10,2) NOT NULL,              -- subtotal + envío, calculado en servidor al pagar
    estado ENUM('Pendiente','En camino','Entregado','Cancelado') DEFAULT 'Pendiente',
    ciudad VARCHAR(100) DEFAULT 'Ciudad de México',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── DETALLE DE PEDIDO ──────────────────────────────────────
-- "Fotografía" de cada línea del pedido en el momento de la compra
-- (nombre/marca/imagen/precio quedan fijos aunque el producto cambie
-- o se borre después). Ver nota de diseño al inicio del archivo.
CREATE TABLE pedido_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id VARCHAR(20) NOT NULL,
    producto_id INT NOT NULL,                  -- referencia al producto original (solo para trazabilidad)
    nombre_producto VARCHAR(150) NOT NULL,
    marca VARCHAR(80) NOT NULL,
    imagen VARCHAR(255) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,             -- precio unitario al momento de comprar
    cantidad INT NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB;

-- =========================================================================
-- DATOS DE PRUEBA
-- =========================================================================
-- Todas las contraseñas de prueba son "1234", almacenadas con password_hash()
-- (bcrypt) tal como las generaría PHP en un registro real. El correo indica
-- el rol para facilitar las pruebas (cliente@, vendedor@, admin@test.com).
INSERT INTO usuarios (nombre, correo, password, rol, estado) VALUES
('Alex García',    'cliente@test.com',  '$2y$10$DOgM1PWUfjPAXDaUf6eThOe3r9EDh.xCVzeVrMLKFXIWonE.ixsdG', 'cliente',  'activo'),
('Laura Martínez', 'vendedor@test.com', '$2y$10$DOgM1PWUfjPAXDaUf6eThOe3r9EDh.xCVzeVrMLKFXIWonE.ixsdG', 'vendedor', 'activo'),
('Carlos Admin',   'admin@test.com',    '$2y$10$DOgM1PWUfjPAXDaUf6eThOe3r9EDh.xCVzeVrMLKFXIWonE.ixsdG', 'admin',    'activo'),
('Pedro Sánchez',  'pedro@test.com',    '$2y$10$DOgM1PWUfjPAXDaUf6eThOe3r9EDh.xCVzeVrMLKFXIWonE.ixsdG', 'cliente',  'activo'),
('María López',    'maria@test.com',    '$2y$10$DOgM1PWUfjPAXDaUf6eThOe3r9EDh.xCVzeVrMLKFXIWonE.ixsdG', 'vendedor', 'inactivo'),
('Jorge Ruiz',     'jorge@test.com',    '$2y$10$DOgM1PWUfjPAXDaUf6eThOe3r9EDh.xCVzeVrMLKFXIWonE.ixsdG', 'cliente',  'activo');

INSERT INTO categorias (nombre) VALUES
('Electrónica'), ('Moda'), ('Calzado'), ('Accesorios'), ('Hogar');

INSERT INTO productos (nombre, marca, descripcion, categoria_id, precio, precio_original, descuento, stock, imagen, vendedor_id, badge, es_nuevo, rating, reviews, activo) VALUES
('AirPods Pro 3', 'Apple', 'Auriculares inalámbricos con cancelación activa de ruido, chip H2 de última generación y hasta 30h de batería total.', 1, 199, 279, 29, 45, '1606220945770-b5b6c2c55bf1', 2, 'Más vendido', 0, 4.8, 2341, 1),
('Ultrabook Pro 14"', 'Sony', 'Laptop ultradelgada con procesador Intel Core i7, 16GB RAM, SSD 512GB y pantalla OLED 4K. Ideal para profesionales.', 1, 1299, 1599, 19, 12, '1517336714731-489689fd1ca8', 2, NULL, 0, 4.7, 891, 1),
('Smartwatch Carbon', 'Huawei', 'Reloj inteligente con monitor cardíaco, GPS integrado, resistencia al agua IP68 y batería de 7 días.', 1, 249, 349, 29, 28, '1523275335684-37898b6baf30', 5, NULL, 0, 4.5, 560, 1),
('Studio Headphones XM6', 'Sony', 'Auriculares over-ear con la mejor cancelación de ruido del mercado, sonido Hi-Res y 35h de autonomía.', 1, 349, 449, 22, 8, '1505740420928-5e560c06d30e', 2, 'Top Rated', 0, 4.9, 3412, 1),
('Leather Biker Jacket', 'AllSaints', 'Chaqueta de cuero genuino con cortes asimétricos, forro interior suave y herrajes de zinc de alta calidad.', 2, 289, 420, 31, 15, '1551028719-00167b16eac5', 5, NULL, 1, 4.6, 234, 1),
('Silk Evening Dress', 'ZARA', 'Vestido de noche en seda natural, corte midi, manga corta y escote en V. Elegante y versátil para cualquier ocasión.', 2, 119, 189, 37, 22, '1572804013309-59a88b7e92f1', 5, NULL, 0, 4.4, 178, 1),
('Premium Linen Shirt', 'COS', 'Camisa de lino 100% natural, corte relaxed, disponible en múltiples colores. Perfecta para el día a día.', 2, 79, 110, 28, 40, '1596755094514-f87e34085b2c', 2, NULL, 0, 4.3, 445, 1),
('Air Max Pulse', 'Nike', 'Zapatillas con tecnología Air Max visible, suela de goma duradera y upper de malla transpirable. Comodidad todo el día.', 3, 149, 189, 21, 0, '1542291026-7eec264c27ff', 2, 'Nuevo', 1, 4.7, 1023, 1),
('Classic Leather Derby', 'Clarks', 'Zapato derby de piel genuina con suela de cuero, plantilla acolchada y construcción Blake para mayor durabilidad.', 3, 129, 179, 28, 18, '1560769629-975ec94e6a86', 5, NULL, 0, 4.5, 312, 1),
('Minimalist Tote Bag', 'Cuyana', 'Bolso tote de cuero italiano curtido al vegetal, interior forrado y asa de hombro ajustable. Minimalismo y funcionalidad.', 4, 195, 265, 26, 9, '1548036328-c9fa89d128fa', 2, NULL, 0, 4.8, 567, 1),
('Arc Floor Lamp', 'Flos', 'Lámpara de pie arco con brazo articulado, pantalla de aluminio anodizado y base de mármol natural. Diseño italiano atemporal.', 5, 89, 129, 31, 6, '1507473885765-e6ed057f782c', 5, NULL, 0, 4.4, 223, 1),
('Ceramic Candle Set', 'Aesop', 'Set de 3 velas aromáticas en recipientes de cerámica artesanal. Aromas: bergamota, cedro y sándalo. Hasta 60h cada una.', 5, 64, 89, 28, 30, '1602523961358-f9f03dd557db', 2, NULL, 1, 4.6, 189, 1);
