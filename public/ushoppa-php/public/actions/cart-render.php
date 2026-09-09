<?php
require_once __DIR__ . '/../includes/functions.php';
// BASE_URL relativo a la página que hizo la llamada AJAX (no a este script)
define('BASE_URL', $_GET['base'] ?? '');
require_once __DIR__ . '/../includes/cart-partial.php';
header('Content-Type: text/plain; charset=utf-8');
echo $cartBodyHtml . '<!--SPLIT-->' . $cartFooterHtml . '<!--SPLIT-->' . cart_count();
