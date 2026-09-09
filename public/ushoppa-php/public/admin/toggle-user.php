<?php
/**
 * admin/toggle-user.php — Suspender/reactivar usuario (admin)
 * -----------------------------------------------------------------------
 * Invierte estado 'activo' <-> 'suspendido'. La condición SQL
 * "rol <> 'admin'" es una segunda barrera (además de ocultar el botón en
 * users.php) para que ningún admin pueda ser suspendido, ni siquiera
 * armando el POST a mano.
 */
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
csrf_check();
$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("UPDATE usuarios SET estado = IF(estado='activo','suspendido','activo') WHERE id = ? AND rol <> 'admin'");
$stmt->execute([$id]);
header('Location: users.php');
exit;
