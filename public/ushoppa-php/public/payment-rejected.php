<?php
/**
 * payment-rejected.php — Pago rechazado
 * -----------------------------------------------------------------------
 * Pantalla estática a la que redirige payment.php cuando
 * actions/checkout.php responde { ok: false } (tarjeta de prueba
 * terminada en 0002). Ofrece volver al carrito o reintentar el pago.
 */
$BASE_URL = '';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Pago Rechazado';
require __DIR__ . '/includes/header.php';
?>
<div class="centered-form-wrap">
  <div class="card" style="max-width:480px;width:100%;padding:48px 40px;text-align:center;">
    <div style="width:72px;height:72px;border-radius:50%;background:rgba(239,68,68,.12);border:2px solid #ef4444;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
      <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </div>
    <h1 class="serif" style="font-size:30px;font-weight:700;margin-bottom:8px;">Pago Rechazado</h1>
    <p style="color:#777;font-size:14px;margin-bottom:20px;line-height:1.6;">No fue posible procesar tu pago. Fondos insuficientes o tarjeta no autorizada. Por favor verifica tus datos e intenta nuevamente.</p>
    <div style="background:rgba(239,68,68,.06);border:1px solid rgba(239,68,68,.15);border-radius:10px;padding:14px;margin-bottom:28px;font-size:13px;color:#f87171;">
      Código de error: ERR_PAYMENT_DECLINED · Contacta a tu banco si el problema persiste.
    </div>
    <div style="display:flex;gap:12px;">
      <a href="cart.php" class="btn-ghost" style="flex:1;padding:12px;text-align:center;">Volver al carrito</a>
      <a href="payment.php" class="btn-gold" style="flex:1;padding:12px;text-align:center;">Reintentar pago →</a>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
