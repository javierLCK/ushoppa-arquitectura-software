<?php
/**
 * functions.php
 * -----------------------------------------------------------------------
 * Librería de funciones comunes de Ushoppa: sesión, autenticación, carrito
 * de compras (basado en $_SESSION), protección CSRF y helpers de
 * presentación (precios, estrellas, badges de estado).
 *
 * Se incluye desde casi todas las páginas de /public a través de:
 *   require_once __DIR__ . '/includes/functions.php';
 * y a su vez incluye db.php, por lo que basta con requerir este archivo
 * para tener disponibles $pdo y todas las funciones de abajo.
 */

if (session_status() === PHP_SESSION_NONE) {
    // HttpOnly evita que JS lea la cookie de sesión (mitiga robo por XSS).
    // SameSite=Lax evita que la cookie se envíe en peticiones cross-site.
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
require_once __DIR__ . '/db.php';

/* ─── CSRF ──────────────────────────────────────────────────────────────
 * Protección contra Cross-Site Request Forgery para toda acción que
 * modifica datos (login, registro, alta de producto, activar/suspender).
 * Flujo: csrf_token() genera/recupera un token único por sesión,
 * csrf_field() lo imprime como <input hidden> dentro del <form>,
 * csrf_check() lo valida al recibir el POST y corta la ejecución con 403
 * si no coincide (o no viene).
 * ─────────────────────────────────────────────────────────────────────── */

/** Devuelve el token CSRF de la sesión actual, generándolo si no existe. */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

/** Imprime el <input type="hidden"> con el token CSRF. Usar dentro de cada <form method="post">. */
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** Corta la ejecución con 403 si el token recibido en $_POST['csrf'] no coincide con el de sesión. */
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403);
        die('Solicitud inválida (CSRF). Vuelve atrás e inténtalo de nuevo.');
    }
}

/** Categorías fijas del catálogo. 'Todas' se usa como filtro comodín en catalog.php. */
const CATEGORIAS_LISTA = ['Todas', 'Electrónica', 'Moda', 'Calzado', 'Accesorios', 'Hogar'];

/* ─── IMÁGENES ──────────────────────────────────────────────────────────
 * Los productos no guardan una URL completa en la BD, solo el ID de foto
 * de Unsplash (columna productos.imagen). img_url() arma la URL final
 * con el tamaño pedido, evitando repetir el dominio/parametros en cada
 * vista. */
function img_url(string $id, int $w = 400, int $h = 300): string {
    return "https://images.unsplash.com/photo-{$id}?w={$w}&h={$h}&fit=crop&auto=format";
}

/* ─── AUTENTICACIÓN ─────────────────────────────────────────────────────
 * No hay tabla de sesiones propia: el usuario autenticado se guarda tal
 * cual en $_SESSION['user'] (id, nombre, correo, rol) tras validar el
 * login en login.php. */

/** Usuario logueado actual (array asociativo) o null si es visitante. */
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

/** Exige sesión iniciada; si no hay, redirige a login.php y detiene la página. */
function require_login(): void {
    if (!current_user()) { header('Location: login.php'); exit; }
}

/** Exige sesión iniciada Y un rol específico ('cliente' | 'vendedor' | 'admin'). */
function require_role(string $rol): void {
    require_login();
    if (current_user()['rol'] !== $rol) { header('Location: index.php'); exit; }
}

/* ─── CARRITO (basado en sesión) ────────────────────────────────────────
 * El carrito se guarda en $_SESSION['cart'] como [producto_id => cantidad].
 * No existe tabla 'carrito' en la BD: es intencional, ya que el carrito
 * es temporal y se descarta (cart_clear) apenas se confirma el pedido en
 * actions/checkout.php, momento en el que sí se persiste en 'pedidos' y
 * 'pedido_detalle'. */

/**
 * Referencia directa a $_SESSION['cart'], inicializándolo si no existe.
 * Se devuelve por referencia (&) para que las funciones de abajo puedan
 * modificar el array de sesión sin tener que reasignarlo manualmente.
 */
function &cart_ref(): array {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    return $_SESSION['cart'];
}

/** Agrega $qty unidades del producto al carrito (suma si ya existía). */
function cart_add(int $productId, int $qty = 1): void {
    $cart = &cart_ref();
    $cart[$productId] = ($cart[$productId] ?? 0) + $qty;
}

/** Fija la cantidad exacta de un producto; si $qty <= 0, lo elimina del carrito. */
function cart_update(int $productId, int $qty): void {
    $cart = &cart_ref();
    if ($qty <= 0) { unset($cart[$productId]); return; }
    $cart[$productId] = $qty;
}

/** Quita un producto del carrito por completo. */
function cart_remove(int $productId): void {
    $cart = &cart_ref();
    unset($cart[$productId]);
}

/** Vacía el carrito (se llama tras confirmar un pedido). */
function cart_clear(): void { $_SESSION['cart'] = []; }

/** Cantidad total de unidades en el carrito (para el contador del ícono). */
function cart_count(): int { return array_sum(cart_ref()); }

/**
 * Trae de la BD los datos completos (nombre, precio, stock, vendedor, etc.)
 * de los productos que están en el carrito, y les agrega la propiedad
 * 'cantidad' tomada de la sesión. Se usa tanto para pintar el carrito
 * lateral como el resumen de compra (cart.php) y el pago (payment.php).
 */
function cart_items(PDO $pdo): array {
    $cart = cart_ref();
    if (empty($cart)) return [];
    $ids = array_keys($cart);
    // Construye el IN (?,?,?...) dinámicamente según cuántos productos haya.
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT p.*, c.nombre AS categoria, u.nombre AS vendedor_nombre
                            FROM productos p
                            JOIN categorias c ON c.id = p.categoria_id
                            JOIN usuarios u ON u.id = p.vendedor_id
                            WHERE p.id IN ($in)");
    $stmt->execute($ids);
    $items = [];
    foreach ($stmt->fetchAll() as $row) {
        $row['cantidad'] = $cart[$row['id']];
        $items[] = $row;
    }
    return $items;
}

/** Subtotal del carrito (sin envío), recalculado siempre desde precios de BD. */
function cart_total(PDO $pdo): float {
    $total = 0;
    foreach (cart_items($pdo) as $i) $total += $i['precio'] * $i['cantidad'];
    return $total;
}

/* ─── FORMATO / PRESENTACIÓN ────────────────────────────────────────── */

/** Formatea un monto con 2 decimales y símbolo de moneda, ej: $199.00 */
function money(float $n): string { return '$' . number_format($n, 2); }

/** Formatea un monto redondeado sin decimales, ej: $199 (para tarjetas de producto). */
function money0(float $n): string { return '$' . number_format($n, 0); }

/** Devuelve el HTML de las 5 estrellas (★/☆) según el rating redondeado del producto. */
function render_stars(float $rating): string {
    $html = '<span class="stars">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= round($rating) ? '★' : '☆';
    }
    return $html . '</span>';
}

/** Color hexadecimal asociado a cada estado/rol/etiqueta, usado por render_badge(). */
function status_color(string $s): string {
    $map = [
        'Entregado' => '#22c55e', 'En camino' => '#e8b84b', 'Pendiente' => '#4db8ff',
        'Cancelado' => '#ef4444', 'activo' => '#22c55e', 'inactivo' => '#888',
        'suspendido' => '#ef4444', 'sin_stock' => '#ef4444', 'admin' => '#e8b84b',
        'vendedor' => '#4db8ff', 'cliente' => '#7ed87e',
    ];
    return $map[$s] ?? '#888';
}

/** Imprime una "píldora" de color (badge) para estados de pedido, usuario o producto. */
function render_badge(string $label): string {
    $color = status_color($label);
    return "<span class=\"badge\" style=\"background:{$color}18;border-color:{$color}30;color:{$color}\">{$label}</span>";
}

/** Genera un número de pedido legible, ej: #USH-4821. Se usa al confirmar el pago. */
function gen_order_id(): string {
    return '#USH-' . random_int(1000, 9999);
}

/**
 * Escapa una cadena para imprimirla de forma segura en HTML (previene XSS).
 * Envolver SIEMPRE cualquier dato que venga de la BD o del usuario antes
 * de imprimirlo con <?= ... ?>, salvo que ya se sepa que es HTML confiable
 * (como el resultado de render_badge()/render_stars()).
 */
function e(?string $s): string { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
