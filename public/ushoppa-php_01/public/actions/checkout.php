<?php
/**
 * actions/checkout.php
 * -----------------------------------------------------------------------
 * Endpoint AJAX (POST) que confirma el pago simulado (llamado desde
 * payment.php tras la animación de "Procesando pago..."). Es el único
 * lugar donde el carrito de sesión se convierte en un pedido real:
 *
 *   1) Verifica sesión activa y token CSRF.
 *   2) Simula la respuesta del "banco": la tarjeta terminada en 0002
 *      siempre se rechaza (para poder probar el flujo de error);
 *      cualquier otra se aprueba. No se procesa dinero real.
 *   3) Recalcula el total EN SERVIDOR a partir de los precios actuales
 *      de la BD (nunca se confía en un total que mande el navegador).
 *   4) Inserta el pedido y su detalle dentro de una transacción, para
 *      que no queden pedidos a medio guardar si algo falla.
 *   5) Vacía el carrito y guarda el pedido en sesión para que
 *      payment-success.php pueda mostrarlo.
 *
 * Responde JSON { ok: true, orderId } o { ok: false, error }.
 * payment.php redirige a payment-success.php o payment-rejected.php
 * según el valor de "ok".
 */
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json; charset=utf-8');

$user = current_user();
if (!$user) { echo json_encode(['ok' => false, 'error' => 'not_logged_in']); exit; }
if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    echo json_encode(['ok' => false, 'error' => 'csrf']); exit;
}

$items = cart_items($pdo);
if (empty($items)) { echo json_encode(['ok' => false, 'error' => 'empty_cart']); exit; }

$lastFour = preg_replace('/\D/', '', $_POST['lastFour'] ?? '');
$region = $_POST['region'] ?? '';
$comuna = trim($_POST['comuna'] ?? '');

// Lista blanca estricta: solo se acepta una región de la lista oficial,
// nunca lo que venga crudo del POST.
if (!in_array($region, REGIONES_CHILE, true)) {
    $region = 'Metropolitana de Santiago';
}
if ($comuna === '') $comuna = 'Sin especificar';
$ciudad = $comuna . ', ' . $region;

// Tarjeta de prueba 4000 0000 0000 0002 → simula rechazo del banco
if ($lastFour === '0002') {
    echo json_encode(['ok' => false]);
    exit;
}

// El total SIEMPRE se calcula acá con los precios vigentes de la BD;
// el front-end nunca envía montos que deban ser confiados.
$total = 0;
foreach ($items as $it) $total += $it['precio'] * $it['cantidad'];
$shipping = $total > 50 ? 0 : 9.99;
$grandTotal = $total + $shipping;
$orderId = gen_order_id();

// Transacción: si falla el detalle, se revierte también el pedido cabecera.
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('INSERT INTO pedidos (id, usuario_id, total, estado, ciudad) VALUES (?,?,?,"Pendiente",?)');
    $stmt->execute([$orderId, $user['id'], $grandTotal, $ciudad]);

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
// Se guarda temporalmente en sesión para que payment-success.php pueda
// mostrar el número de pedido y el total sin volver a consultarlo.
$_SESSION['last_order'] = ['id' => $orderId, 'total' => $grandTotal, 'ciudad' => $ciudad];
echo json_encode(['ok' => true, 'orderId' => $orderId]);
