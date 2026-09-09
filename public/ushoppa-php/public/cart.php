<?php
/**
 * cart.php — Resumen de compra
 * -----------------------------------------------------------------------
 * Paso intermedio entre el carrito lateral y el pago: lista los productos
 * con su subtotal, muestra los datos del comprador (si hay sesión) y el
 * total con envío. Si no hay sesión, el botón lleva a login.php en vez
 * de a payment.php (el pago exige estar autenticado, ver payment.php).
 */
$BASE_URL = '';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Resumen de Compra';
$user = current_user();
$items = cart_items($pdo);
$total = 0;
foreach ($items as $it) $total += $it['precio'] * $it['cantidad'];
$shipping = $total > 50 ? 0 : 9.99;

require __DIR__ . '/includes/header.php';
?>
<?php if (empty($items)): ?>
  <div class="page-wrap" style="text-align:center;padding-top:80px;">
    <div style="font-size:48px;margin-bottom:16px;">🛒</div>
    <h2 class="serif" style="font-size:28px;margin-bottom:12px;">Carrito vacío</h2>
    <a href="catalog.php" class="btn-gold" style="padding:12px 32px;">Ir al catálogo →</a>
  </div>
<?php else: ?>
  <div class="page-wrap">
    <h1 class="section-title serif" style="font-size:32px;margin-bottom:32px;">Resumen de Compra</h1>
    <div style="display:grid;grid-template-columns:1fr 360px;gap:28px;align-items:start;" class="detail-layout">
      <div>
        <div class="card" style="padding:24px;margin-bottom:20px;">
          <h3 style="font-size:16px;font-weight:700;margin-bottom:20px;">Productos (<?= count($items) ?>)</h3>
          <?php foreach ($items as $it): ?>
            <div style="display:flex;gap:16px;padding:16px 0;border-bottom:1px solid #141414;">
              <img src="<?= e(img_url($it['imagen'],80,80)) ?>" style="width:64px;height:64px;border-radius:8px;object-fit:cover;">
              <div style="flex:1;">
                <div style="font-size:14px;font-weight:600;margin-bottom:2px;"><?= e($it['nombre']) ?></div>
                <div style="font-size:12px;color:#666;margin-bottom:8px;"><?= e($it['marca']) ?> · Vendedor: <?= e($it['vendedor_nombre']) ?></div>
                <div style="font-size:13px;color:#888;">Cantidad: <?= $it['cantidad'] ?></div>
              </div>
              <div style="text-align:right;">
                <div class="serif" style="font-size:18px;font-weight:700;"><?= money0($it['precio']*$it['cantidad']) ?></div>
                <div style="font-size:12px;color:#555;"><?= money0($it['precio']) ?> c/u</div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="card" style="padding:24px;">
          <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;">Datos del comprador</h3>
          <?php if ($user): ?>
            <div style="display:flex;align-items:center;gap:14px;">
              <div class="avatar lg"><?= e(mb_substr($user['nombre'],0,1)) ?></div>
              <div>
                <div style="font-size:15px;font-weight:600;"><?= e($user['nombre']) ?></div>
                <div style="font-size:13px;color:#666;"><?= e($user['correo']) ?></div>
              </div>
            </div>
          <?php else: ?>
            <div class="alert-gold">Inicia sesión para continuar con tu compra. <a href="login.php" class="gold" style="font-weight:700;text-decoration:underline;">Iniciar sesión</a></div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card" style="padding:28px;position:sticky;top:100px;">
        <h3 class="serif" style="font-size:20px;font-weight:700;margin-bottom:24px;">Total del Pedido</h3>
        <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:20px;">
          <div class="row-between" style="font-size:14px;color:#888;"><span>Subtotal</span><span><?= money($total) ?></span></div>
          <div class="row-between" style="font-size:14px;"><span style="color:#888;">Envío</span><span style="<?= $shipping===0?'color:#22c55e;font-weight:700;':'' ?>"><?= $shipping===0?'GRATIS':money($shipping) ?></span></div>
          <div class="row-between" style="padding-top:16px;border-top:1px solid #1a1a1a;">
            <span class="serif" style="font-size:18px;font-weight:700;">Total</span>
            <span class="serif gold" style="font-size:24px;font-weight:700;"><?= money($total+$shipping) ?></span>
          </div>
        </div>
        <?php if ($user): ?>
          <a href="payment.php" class="btn-gold" style="display:block;text-align:center;width:100%;padding:14px;font-size:15px;">Ir al Pago →</a>
        <?php else: ?>
          <a href="login.php" class="btn-gold" style="display:block;text-align:center;width:100%;padding:14px;font-size:15px;">Inicia sesión para pagar →</a>
        <?php endif; ?>
        <a href="catalog.php" class="btn-ghost" style="display:block;text-align:center;width:100%;margin-top:10px;font-size:13px;">Seguir comprando</a>
      </div>
    </div>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
