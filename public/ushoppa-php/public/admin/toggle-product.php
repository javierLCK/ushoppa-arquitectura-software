<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare('UPDATE productos SET activo = NOT activo WHERE id = ?');
$stmt->execute([$id]);
header('Location: products.php');
exit;
