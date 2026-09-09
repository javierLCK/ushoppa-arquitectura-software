<?php
/**
 * payment-success.php — Confirmación de pedido
 * -----------------------------------------------------------------------
 * Se llega acá solo tras un checkout exitoso (actions/checkout.php dejó
 * $_SESSION['last_order']). Si alguien entra directo a esta URL sin haber
 * pagado, no hay datos de pedido en sesión y se redirige a index.php.
 * $_SESSION['last_order'] se limpia al final para que un refresh (F5) no
 * vuelva a mostrar la misma confirmación indefinidamente.
 */
$BASE_URL = '';
require_once __DIR__ . '/includes/functions.php';
require_login();
$pageTitle = 'Pedido Confirmado';

$order = $_SESSION['last_order'] ?? null;
if (!$order) { header('Location: index.php'); exit; }
$orderId = $order['id'];
$total = $order['total'];

require __DIR__ . '/includes/header.php';
?>
<div class="centered-form-wrap">
  <div class="card" style="max-width:540px;width:100%;padding:48px 40px;text-align:center;">
    <div style="width:72px;height:72px;border-radius:50%;background:rgba(34,197,94,.12);border:2px solid #22c55e;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
      <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#22c55e" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    </div>
    <h1 class="serif" style="font-size:32px;font-weight:700;margin-bottom:8px;">Pedido Confirmado</h1>
    <p style="color:#777;font-size:15px;margin-bottom:28px;">Tu pago fue procesado exitosamente por NexoMarket.</p>
    <div style="background:#111;border:1px solid #1e1e1e;border-radius:12px;padding:20px 28px;margin-bottom:24px;">
      <div style="font-size:11px;color:#666;letter-spacing:.1em;margin-bottom:6px;">NÚMERO DE PEDIDO</div>
      <div class="serif gold" style="font-size:32px;font-weight:700;"><?= e($orderId) ?></div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:32px;">
      <?php foreach ([['Total pagado', money($total)], ['Envío', $total>59.99?'Express Gratis':'$9.99'], ['Entrega estimada','24–48 horas'], ['Ciudad','Ciudad de México']] as [$k,$v]): ?>
        <div style="background:#0a0a0a;border:1px solid #1a1a1a;border-radius:10px;padding:14px;">
          <div style="font-size:11px;color:#555;margin-bottom:4px;"><?= $k ?></div>
          <div style="font-size:13px;font-weight:600;"><?= $v ?></div>
        </div>
      <?php endforeach; ?>
    </div>
    <div style="display:flex;gap:12px;">
      <a href="order-history.php" class="btn-ghost" style="flex:1;padding:12px;text-align:center;">Ver mis pedidos</a>
      <a href="index.php" class="btn-gold" style="flex:1;padding:12px;text-align:center;">Volver al inicio</a>
    </div>
  </div>
</div>
<?php unset($_SESSION['last_order']); require __DIR__ . '/includes/footer.php'; ?>
