<?php
/**
 * admin/toggle-product.php — Activar/desactivar producto (admin)
 * -----------------------------------------------------------------------
 * A diferencia de vendor/toggle-product.php, aquí NO se filtra por
 * vendedor_id: el admin puede activar/desactivar el producto de
 * cualquier vendedor de la plataforma (moderación global).
 */
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
csrf_check();
$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare('UPDATE productos SET activo = NOT activo WHERE id = ?');
$stmt->execute([$id]);
header('Location: products.php');
exit;
