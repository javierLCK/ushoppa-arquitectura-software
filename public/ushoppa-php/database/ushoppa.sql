-- Ushoppa — Esquema de base de datos
-- NexoMarket / ISI602 Arquitectura de Software

CREATE DATABASE IF NOT EXISTS ushoppa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ushoppa;

-- ─── USUARIOS ───────────────────────────────────────────────
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('cliente','vendedor','admin') NOT NULL DEFAULT 'cliente',
    estado ENUM('activo','inactivo','suspendido') NOT NULL DEFAULT 'activo',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─── CATEGORÍAS ─────────────────────────────────────────────
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ─── PRODUCTOS ──────────────────────────────────────────────
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    marca VARCHAR(80) NOT NULL,
    descripcion TEXT NOT NULL,
    categoria_id INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    precio_original DECIMAL(10,2) DEFAULT NULL,
    descuento TINYINT DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    imagen VARCHAR(255) NOT NULL,
    vendedor_id INT NOT NULL,
    badge VARCHAR(40) DEFAULT NULL,
    es_nuevo TINYINT(1) DEFAULT 0,
    rating DECIMAL(2,1) DEFAULT 4.5,
    reviews INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    FOREIGN KEY (vendedor_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── PEDIDOS ────────────────────────────────────────────────
CREATE TABLE pedidos (
    id VARCHAR(20) PRIMARY KEY,
    usuario_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    estado ENUM('Pendiente','En camino','Entregado','Cancelado') DEFAULT 'Pendiente',
    ciudad VARCHAR(100) DEFAULT 'Ciudad de México',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─── DETALLE DE PEDIDO ──────────────────────────────────────
CREATE TABLE pedido_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id VARCHAR(20) NOT NULL,
    producto_id INT NOT NULL,
    nombre_producto VARCHAR(150) NOT NULL,
    marca VARCHAR(80) NOT NULL,
    imagen VARCHAR(255) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    cantidad INT NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB;

-- ─── DATOS DE PRUEBA ────────────────────────────────────────
INSERT INTO usuarios (nombre, correo, password, rol, estado) VALUES
('Alex García',    'cliente@test.com',  '1234', 'cliente',  'activo'),
('Laura Martínez', 'vendedor@test.com', '1234', 'vendedor', 'activo'),
('Carlos Admin',   'admin@test.com',    '1234', 'admin',    'activo'),
('Pedro Sánchez',  'pedro@test.com',    '1234', 'cliente',  'activo'),
('María López',    'maria@test.com',    '1234', 'vendedor', 'inactivo'),
('Jorge Ruiz',     'jorge@test.com',    '1234', 'cliente',  'activo');

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
