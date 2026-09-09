<?php
/**
 * logout.php — Cierre de sesión
 * -----------------------------------------------------------------------
 * Destruye únicamente los datos de usuario en sesión (el carrito, si
 * existiera, se conserva) y redirige a la home.
 */
require_once __DIR__ . '/includes/functions.php';
unset($_SESSION['user']);
header('Location: index.php');
exit;
