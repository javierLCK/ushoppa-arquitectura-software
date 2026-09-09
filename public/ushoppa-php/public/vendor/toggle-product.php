<?php
/**
 * vendor/toggle-product.php — Activar/desactivar producto (vendedor)
 * -----------------------------------------------------------------------
 * Invierte el flag 'activo' de un producto propio (NOT activo). No borra
 * nada de la BD: un producto inactivo simplemente deja de listarse en el
 * catálogo público, pero el vendedor lo sigue viendo en su dashboard.
 * Requiere token CSRF válido (ver csrf_field() en dashboard.php).
 */
require_once __DIR__ . '/../includes/functions.php';
require_role('vendedor');
csrf_check();
$user = current_user();
$id = (int)($_POST['id'] ?? 0);
// AND vendedor_id = ? evita que un vendedor desactive productos ajenos.
$stmt = $pdo->prepare('UPDATE productos SET activo = NOT activo WHERE id = ? AND vendedor_id = ?');
$stmt->execute([$id, $user['id']]);
header('Location: dashboard.php');
exit;
