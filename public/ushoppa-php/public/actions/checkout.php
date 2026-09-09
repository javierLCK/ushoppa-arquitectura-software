<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json; charset=utf-8');

$user = current_user();
if (!$user) { echo json_encode(['ok' => false, 'error' => 'not_logged_in']); exit; }

$items = cart_items($pdo);
if (empty($items)) { echo json_encode(['ok' => false, 'error' => 'empty_cart']); exit; }

$lastFour = preg_replace('/\D/', '', $_POST['lastFour'] ?? '');

// Tarjeta de prueba 4000 0000 0000 0002 → simula rechazo
if ($lastFour === '0002') {
    echo json_encode(['ok' => false]);
    exit;
}

$total = 0;
foreach ($items as $it) $total += $it['precio'] * $it['cantidad'];
$shipping = $total > 50 ? 0 : 9.99;
$grandTotal = $total + $shipping;
$orderId = gen_order_id();

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('INSERT INTO pedidos (id, usuario_id, total, estado, ciudad) VALUES (?,?,?,"Pendiente","Ciudad de México")');
    $stmt->execute([$orderId, $user['id'], $grandTotal]);

    $stmt = $pdo->prepare('INSERT INTO pedido_detalle (pedido_id, producto_id, nombre_producto, marca, imagen, precio, cantidad) VALUES (?,?,?,?,?,?,?)');
    foreach ($items as $it) {
        $stmt->execute([$orderId, $it['id'], $it['nombre'], $it['marca'], $it['imagen'], $it['precio'], $it['cantidad']]);
    }
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['ok' => false, 'error' => 'db_error']);
    exit;
}

cart_clear();
$_SESSION['last_order'] = ['id' => $orderId, 'total' => $grandTotal];
echo json_encode(['ok' => true, 'orderId' => $orderId]);
