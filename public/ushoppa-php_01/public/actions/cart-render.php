<?php
/**
 * actions/cart-render.php
 * -----------------------------------------------------------------------
 * Endpoint AJAX (GET) que re-renderiza el carrito lateral completo tras
 * cualquier cambio (agregar/quitar/actualizar). main.js lo llama después
 * de cada acción y reemplaza el HTML de #cartBody y #cartFooterSlot con
 * la respuesta, sin recargar la página.
 *
 * Responde texto plano con tres partes separadas por "<!--SPLIT-->":
 *   1) HTML del cuerpo del carrito (cart-partial.php -> $cartBodyHtml)
 *   2) HTML del resumen/total     (cart-partial.php -> $cartFooterHtml)
 *   3) Cantidad total de unidades (para el badge del ícono)
 *
 * Nota de seguridad: BASE_URL depende de la página que hizo la llamada
 * (raíz de /public vs. /vendor o /admin), y llega por querystring
 * (?base=../). Como ese valor se imprime dentro de un href en
 * cart-partial.php, se valida contra una lista blanca estricta antes de
 * usarlo -- cualquier otro valor se descarta y se usa '' por defecto,
 * para evitar XSS reflejado vía el parámetro "base".
 */
require_once __DIR__ . '/../includes/functions.php';
$allowedBase = ['', '../'];
$requestedBase = $_GET['base'] ?? '';
define('BASE_URL', in_array($requestedBase, $allowedBase, true) ? $requestedBase : '');
require_once __DIR__ . '/../includes/cart-partial.php';
header('Content-Type: text/plain; charset=utf-8');
echo $cartBodyHtml . '<!--SPLIT-->' . $cartFooterHtml . '<!--SPLIT-->' . cart_count();
