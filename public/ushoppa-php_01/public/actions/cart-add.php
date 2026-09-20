<?php
/**
 * actions/cart-add.php
 * -----------------------------------------------------------------------
 * Endpoint AJAX (POST) llamado desde main.js -> addToCart() cuando el
 * usuario hace clic en "+ Carrito" (tarjeta de producto o detalle).
 * Recibe: id (producto), qty (cantidad, por defecto 1).
 * Responde: JSON { ok, count } donde count es el total de unidades en
 * el carrito, usado para actualizar el badge del ícono sin recargar.
 */
require_once __DIR__ . '/../includes/functions.php';
$id  = (int)($_POST['id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));
if ($id > 0) cart_add($id, $qty);
echo json_encode(['ok' => true, 'count' => cart_count()]);
