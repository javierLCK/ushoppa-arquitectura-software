<?php
// Genera $cartBodyHtml y $cartFooterHtml a partir del carrito en sesión.
// Se usa tanto en el render inicial (footer.php) como en actions/cart-render.php (AJAX).
$items = cart_items($pdo);
$total = 0;
foreach ($items as $it) $total += $it['precio'] * $it['cantidad'];
$shipping = $total > 50 ? 0 : 9.99;

ob_start();
if (empty($items)) {
    echo '<div class="empty-state"><div class="emoji">🛒</div><div style="font-size:15px;color:#666;margin-bottom:6px;">Tu carrito está vacío</div><div style="font-size:13px;">Agrega productos para comenzar</div></div>';
} else {
    foreach ($items as $it) {
        $sub = $it['precio'] * $it['cantidad'];
        echo '<div class="cart-line">';
        echo '<img src="' . e(img_url($it['imagen'], 80, 80)) . '" alt="' . e($it['nombre']) . '">';
        echo '<div style="flex:1;min-width:0;">';
        echo '<div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' . e($it['nombre']) . '</div>';
        echo '<div style="font-size:11px;color:#555;margin-bottom:8px;">' . e($it['marca']) . '</div>';
        echo '<div class="qty-mini">';
        echo '<button data-qty-change="-1" data-id="' . $it['id'] . '" data-current-qty="' . $it['cantidad'] . '">−</button>';
        echo '<span style="min-width:18px;text-align:center;font-weight:600;">' . $it['cantidad'] . '</span>';
        echo '<button data-qty-change="1" data-id="' . $it['id'] . '" data-current-qty="' . $it['cantidad'] . '">+</button>';
        echo '</div></div>';
        echo '<div style="display:flex;flex-direction:column;align-items:flex-end;justify-content:space-between;">';
        echo '<button data-remove-item="' . $it['id'] . '" style="background:none;border:none;cursor:pointer;color:#444;">';
        echo '<svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>';
        echo '<span class="serif" style="font-size:16px;font-weight:700;">' . money0($sub) . '</span>';
        echo '</div></div>';
    }
}
$cartBodyHtml = ob_get_clean();

ob_start();
if (!empty($items)) {
    echo '<div class="cart-footer">';
    echo '<div class="row-between" style="margin-bottom:8px;font-size:13px;"><span style="color:#666;">Subtotal</span><span>' . money($total) . '</span></div>';
    echo '<div class="row-between" style="margin-bottom:16px;font-size:13px;"><span style="color:#666;">Envío</span><span style="' . ($shipping===0?'color:#22c55e;font-weight:700;':'') . '">' . ($shipping===0?'GRATIS':money($shipping)) . '</span></div>';
    if ($total <= 50) {
        echo '<div class="alert-gold" style="margin-bottom:14px;text-align:center;">Agrega ' . money(50-$total) . ' más para envío gratis</div>';
    }
    echo '<div class="row-between" style="padding-top:14px;border-top:1px solid #1a1a1a;margin-bottom:16px;">';
    echo '<span class="serif" style="font-size:17px;font-weight:700;">Total</span>';
    echo '<span class="serif gold" style="font-size:22px;font-weight:700;">' . money($total+$shipping) . '</span></div>';
    echo '<a href="' . BASE_URL . 'cart.php" class="btn-gold" style="display:block;text-align:center;width:100%;padding:13px;font-size:15px;">Finalizar Compra →</a>';
    echo '</div>';
}
$cartFooterHtml = ob_get_clean();
