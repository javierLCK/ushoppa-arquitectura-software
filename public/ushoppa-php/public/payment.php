<?php
/**
 * payment.php — Pago simulado
 * -----------------------------------------------------------------------
 * Formulario de tarjeta (sin pasarela real: fines académicos). El JS de
 * abajo valida el formato de los campos, muestra una vista previa tipo
 * tarjeta física y, al enviar, dispara una animación de "Procesando
 * pago..." antes de llamar a actions/checkout.php vía fetch(), que es
 * quien realmente valida/crea el pedido en la BD.
 *
 * Requiere sesión iniciada (require_login) y carrito no vacío.
 */
$BASE_URL = '';
require_once __DIR__ . '/includes/functions.php';
require_login();
$pageTitle = 'Pago';

$items = cart_items($pdo);
if (empty($items)) { header('Location: cart.php'); exit; }
$total = 0;
foreach ($items as $it) $total += $it['precio'] * $it['cantidad'];
$shipping = $total > 50 ? 0 : 9.99;
$grandTotal = $total + $shipping;

require __DIR__ . '/includes/header.php';
?>
<div class="page-wrap">

  <div id="processingView" style="display:none;min-height:60vh;flex-direction:column;align-items:center;justify-content:center;text-align:center;">
    <svg width="80" height="80" viewBox="0 0 80 80" fill="none" style="animation:spin 1s linear infinite;margin-bottom:32px;">
      <circle cx="40" cy="40" r="34" stroke="#1e1e1e" stroke-width="6"/>
      <circle cx="40" cy="40" r="34" stroke="#e8b84b" stroke-width="6" stroke-linecap="round" stroke-dasharray="60 150"/>
    </svg>
    <h2 class="serif" style="font-size:24px;font-weight:700;margin-bottom:10px;">Procesando pago...</h2>
    <p style="color:#666;font-size:14px;">Por favor no cierres esta ventana</p>
  </div>

  <div id="formView" style="display:grid;grid-template-columns:1fr 340px;gap:32px;align-items:start;">
    <div>
      <h1 class="section-title serif" style="font-size:32px;margin-bottom:8px;">Pago Simulado</h1>
      <div class="alert-gold" style="display:inline-block;margin-bottom:28px;">
        ⚠ Pago simulado con fines académicos — no se procesa dinero real. Usa 4242 4242 4242 4242 (aprobado) o 4000 0000 0000 0002 (rechazado)
      </div>

      <div style="background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);border-radius:16px;padding:28px;position:relative;overflow:hidden;height:160px;margin-bottom:28px;">
        <div style="display:flex;justify-content:space-between;margin-bottom:20px;">
          <div style="font-size:12px;color:rgba(255,255,255,.5);letter-spacing:.1em;">USHOPPA CARD</div>
          <div id="cardType" style="font-size:15px;font-weight:700;color:#e8b84b;"></div>
        </div>
        <div id="cardPreview" style="font-family:monospace;font-size:20px;letter-spacing:.15em;margin-bottom:16px;">•••• •••• •••• ••••</div>
        <div style="display:flex;justify-content:space-between;">
          <div><div style="font-size:9px;color:rgba(255,255,255,.4);">TITULAR</div><div id="holderPreview" style="font-size:13px;">NOMBRE APELLIDO</div></div>
          <div><div style="font-size:9px;color:rgba(255,255,255,.4);">VENCE</div><div id="expiryPreview" style="font-size:13px;font-family:monospace;">MM/AA</div></div>
        </div>
      </div>

      <form id="paymentForm">
        <div style="display:grid;gap:16px;">
          <div>
            <label class="label">Número de tarjeta</label>
            <input class="input" id="cardInput" placeholder="4242 4242 4242 4242" maxlength="19" autocomplete="off">
            <div class="field-error" id="err-card"></div>
          </div>
          <div>
            <label class="label">Titular de la tarjeta</label>
            <input class="input" id="holderInput" placeholder="NOMBRE APELLIDO">
            <div class="field-error" id="err-holder"></div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div>
              <label class="label">Vencimiento</label>
              <input class="input" id="expiryInput" placeholder="MM/AA" maxlength="5">
              <div class="field-error" id="err-expiry"></div>
            </div>
            <div>
              <label class="label">CVV</label>
              <input class="input" id="cvvInput" type="password" placeholder="•••" maxlength="4">
              <div class="field-error" id="err-cvv"></div>
            </div>
          </div>
        </div>
        <button type="submit" class="btn-gold" style="width:100%;padding:15px;font-size:16px;margin-top:24px;">Pagar <?= money($grandTotal) ?> →</button>
        <div style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:14px;">
          <span style="font-size:12px;color:#555;">Pago simulado · SSL 256-bit · Protegido por NexoMarket</span>
        </div>
      </form>
    </div>

    <div class="card" style="padding:24px;position:sticky;top:100px;">
      <h3 class="serif" style="font-size:18px;font-weight:700;margin-bottom:18px;">Tu pedido</h3>
      <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:20px;">
        <?php foreach ($items as $it): ?>
          <div style="display:flex;gap:10px;align-items:center;">
            <div style="position:relative;flex-shrink:0;">
              <img src="<?= e(img_url($it['imagen'],48,48)) ?>" style="width:40px;height:40px;border-radius:6px;object-fit:cover;">
              <span style="position:absolute;top:-6px;right:-6px;background:#e8b84b;color:#000;border-radius:50%;width:17px;height:17px;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;"><?= $it['cantidad'] ?></span>
            </div>
            <div style="flex:1;min-width:0;"><div style="font-size:12px;color:#ccc;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($it['nombre']) ?></div></div>
            <span style="font-size:13px;font-weight:600;"><?= money0($it['precio']*$it['cantidad']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
      <div style="border-top:1px solid #161616;padding-top:16px;">
        <div class="row-between serif" style="font-size:22px;font-weight:700;"><span>Total</span><span class="gold"><?= money($grandTotal) ?></span></div>
      </div>
    </div>
  </div>
</div>

<script>
// Formateo en vivo de los campos de tarjeta + vista previa visual (no
// valida contra ningún servidor todavía, solo formato).
const cardInput = document.getElementById('cardInput');
const expiryInput = document.getElementById('expiryInput');
const cvvInput = document.getElementById('cvvInput');
const holderInput = document.getElementById('holderInput');

cardInput.addEventListener('input', () => {
  let v = cardInput.value.replace(/\D/g,'').slice(0,16).replace(/(.{4})/g,'$1 ').trim();
  cardInput.value = v;
  document.getElementById('cardPreview').textContent = (v || '•••• •••• •••• ••••').padEnd(19,'•').slice(0,19);
  document.getElementById('cardType').textContent = v.startsWith('4') ? 'Visa' : v.startsWith('5') ? 'Mastercard' : v.startsWith('3') ? 'Amex' : '';
});
expiryInput.addEventListener('input', () => {
  let d = expiryInput.value.replace(/\D/g,'').slice(0,4);
  expiryInput.value = d.length >= 3 ? d.slice(0,2)+'/'+d.slice(2) : d;
  document.getElementById('expiryPreview').textContent = expiryInput.value || 'MM/AA';
});
cvvInput.addEventListener('input', () => { cvvInput.value = cvvInput.value.replace(/\D/g,'').slice(0,4); });
holderInput.addEventListener('input', () => {
  holderInput.value = holderInput.value.toUpperCase();
  document.getElementById('holderPreview').textContent = holderInput.value || 'NOMBRE APELLIDO';
});

document.getElementById('paymentForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  // Validación de formato en cliente (UX inmediata). La validación real
  // de negocio (rechazo/aprobación, creación del pedido) ocurre en el
  // servidor dentro de actions/checkout.php.
  document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
  let ok = true;
  const cardDigits = cardInput.value.replace(/\s/g,'');
  if (cardDigits.length < 16) { document.getElementById('err-card').textContent = 'Número de tarjeta inválido'; ok = false; }
  if (!/^\d{2}\/\d{2}$/.test(expiryInput.value)) { document.getElementById('err-expiry').textContent = 'Formato MM/AA'; ok = false; }
  if (!/^\d{3,4}$/.test(cvvInput.value)) { document.getElementById('err-cvv').textContent = 'CVV inválido'; ok = false; }
  if (!holderInput.value.trim()) { document.getElementById('err-holder').textContent = 'Requerido'; ok = false; }
  if (!ok) return;

  document.getElementById('formView').style.display = 'none';
  document.getElementById('processingView').style.display = 'flex';

  // Espera artificial (solo estética, simula latencia real de pasarela)
  // antes de confirmar el pago contra el servidor.
  setTimeout(async () => {
    const res = await fetch('actions/checkout.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'lastFour=' + cardDigits.slice(-4) + '&csrf=' + encodeURIComponent(<?= json_encode(csrf_token()) ?>)
    });
    const data = await res.json();
    window.location.href = data.ok ? 'payment-success.php' : 'payment-rejected.php';
  }, 2400);
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
