<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("UPDATE usuarios SET estado = IF(estado='activo','suspendido','activo') WHERE id = ? AND rol <> 'admin'");
$stmt->execute([$id]);
header('Location: users.php');
exit;
