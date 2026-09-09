<?php
/**
 * actions/cart-update.php
 * -----------------------------------------------------------------------
 * Endpoint AJAX (POST) llamado desde main.js al presionar +/- en el
 * carrito lateral. Recibe id y la nueva cantidad absoluta (qty); si
 * qty <= 0, cart_update() elimina el ítem. Responde JSON { ok, count }.
 */
require_once __DIR__ . '/../includes/functions.php';
$id  = (int)($_POST['id'] ?? 0);
$qty = (int)($_POST['qty'] ?? 0);
if ($id > 0) cart_update($id, $qty);
echo json_encode(['ok' => true, 'count' => cart_count()]);
