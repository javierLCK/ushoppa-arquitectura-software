<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';

const CATEGORIAS_LISTA = ['Todas', 'Electrónica', 'Moda', 'Calzado', 'Accesorios', 'Hogar'];

/* ─── IMÁGENES ─────────────────────────────────────────────── */
function img_url(string $id, int $w = 400, int $h = 300): string {
    return "https://images.unsplash.com/photo-{$id}?w={$w}&h={$h}&fit=crop&auto=format";
}

/* ─── AUTENTICACIÓN ────────────────────────────────────────── */
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}
function require_login(): void {
    if (!current_user()) { header('Location: login.php'); exit; }
}
function require_role(string $rol): void {
    require_login();
    if (current_user()['rol'] !== $rol) { header('Location: index.php'); exit; }
}

/* ─── CARRITO (basado en sesión) ───────────────────────────── */
function &cart_ref(): array {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    return $_SESSION['cart'];
}
function cart_add(int $productId, int $qty = 1): void {
    $cart = &cart_ref();
    $cart[$productId] = ($cart[$productId] ?? 0) + $qty;
}
function cart_update(int $productId, int $qty): void {
    $cart = &cart_ref();
    if ($qty <= 0) { unset($cart[$productId]); return; }
    $cart[$productId] = $qty;
}
function cart_remove(int $productId): void {
    $cart = &cart_ref();
    unset($cart[$productId]);
}
function cart_clear(): void { $_SESSION['cart'] = []; }
function cart_count(): int { return array_sum(cart_ref()); }

function cart_items(PDO $pdo): array {
    $cart = cart_ref();
    if (empty($cart)) return [];
    $ids = array_keys($cart);
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
function cart_total(PDO $pdo): float {
    $total = 0;
    foreach (cart_items($pdo) as $i) $total += $i['precio'] * $i['cantidad'];
    return $total;
}

/* ─── FORMATO / PRESENTACIÓN ───────────────────────────────── */
function money(float $n): string { return '$' . number_format($n, 2); }
function money0(float $n): string { return '$' . number_format($n, 0); }

function render_stars(float $rating): string {
    $html = '<span class="stars">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= round($rating) ? '★' : '☆';
    }
    return $html . '</span>';
}

function status_color(string $s): string {
    $map = [
        'Entregado' => '#22c55e', 'En camino' => '#e8b84b', 'Pendiente' => '#4db8ff',
        'Cancelado' => '#ef4444', 'activo' => '#22c55e', 'inactivo' => '#888',
        'suspendido' => '#ef4444', 'sin_stock' => '#ef4444', 'admin' => '#e8b84b',
        'vendedor' => '#4db8ff', 'cliente' => '#7ed87e',
    ];
    return $map[$s] ?? '#888';
}
function render_badge(string $label): string {
    $color = status_color($label);
    return "<span class=\"badge\" style=\"background:{$color}18;border-color:{$color}30;color:{$color}\">{$label}</span>";
}

function gen_order_id(): string {
    return '#USH-' . random_int(1000, 9999);
}

function e(?string $s): string { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
