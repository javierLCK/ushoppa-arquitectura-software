<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('vendedor');
$user = current_user();
$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare('UPDATE productos SET activo = NOT activo WHERE id = ? AND vendedor_id = ?');
$stmt->execute([$id, $user['id']]);
header('Location: dashboard.php');
exit;
