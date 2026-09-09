<?php
/**
 * order-history.php — Historial de pedidos del cliente
 * -----------------------------------------------------------------------
 * Dos vistas en un mismo archivo, según si viene ?order=ID en la URL:
 *   - Sin ?order: lista todos los pedidos del usuario logueado.
 *   - Con ?order: detalle de ese pedido (productos, cantidades, total).
 *
 * Seguridad: la consulta de detalle siempre filtra también por
 * usuario_id = $user['id'], así un cliente no puede ver el pedido de
 * otro cambiando el número en la URL (?order=#USH-1234).
 */
$BASE_URL = '';
require_once __DIR__ . '/includes/functions.php';
require_login();
$pageTitle = 'Mis Pedidos';
$user = current_user();

$stmt = $pdo->prepare('SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY fecha DESC');
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

$detailId = $_GET['order'] ?? null;
$detail = null;
$detailItems = [];
if ($detailId) {
    // AND usuario_id = ? es la protección clave contra IDOR (ver nota arriba).
    $stmt = $pdo->prepare('SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?');
    $stmt->execute([$detailId, $user['id']]);
    $detail = $stmt->fetch();
    if ($detail) {
        $stmt = $pdo->prepare('SELECT * FROM pedido_detalle WHERE pedido_id = ?');
        $stmt->execute([$detailId]);
        $detailItems = $stmt->fetchAll();
    }
}

require __DIR__ . '/includes/header.php';
?>
<div class="page-wrap">
  <h1 class="section-title serif" style="font-size:32px;margin-bottom:32px;">Mis Pedidos</h1>

  <?php if ($detail): ?>
    <a href="order-history.php" class="btn-ghost" style="display:inline-flex;align-items:center;gap:6px;margin-bottom:24px;font-size:13px;">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg> Volver
    </a>
    <div class="card" style="padding:28px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
          <div class="serif" style="font-size:24px;font-weight:700;"><?= e($detail['id']) ?></div>
          <div style="font-size:13px;color:#666;"><?= date('j M Y', strtotime($detail['fecha'])) ?> · <?= e($detail['ciudad']) ?></div>
        </div>
        <?= render_badge($detail['estado']) ?>
      </div>
      <?php foreach ($detailItems as $it): ?>
        <div style="display:flex;gap:16px;padding:16px 0;border-bottom:1px solid #141414;">
          <img src="<?= e(img_url($it['imagen'],80,80)) ?>" style="width:56px;height:56px;border-radius:8px;object-fit:cover;">
          <div style="flex:1;">
            <div style="font-size:14px;font-weight:600;margin-bottom:2px;"><?= e($it['nombre_producto']) ?></div>
            <div style="font-size:12px;color:#666;"><?= e($it['marca']) ?> · x<?= $it['cantidad'] ?></div>
          </div>
          <div class="serif" style="font-size:18px;font-weight:700;"><?= money0($it['precio']*$it['cantidad']) ?></div>
        </div>
      <?php endforeach; ?>
      <div style="display:flex;justify-content:flex-end;padding-top:16px;gap:8px;align-items:center;">
        <span style="color:#666;font-size:15px;">Total:</span>
        <span class="serif gold" style="font-size:26px;font-weight:700;"><?= money($detail['total']) ?></span>
      </div>
    </div>

  <?php elseif (empty($orders)): ?>
    <div class="empty-state">
      <div class="emoji">📦</div>
      <h2 class="serif" style="font-size:24px;margin-bottom:12px;color:#f0f0f0;">Sin pedidos aún</h2>
      <a href="catalog.php" class="btn-gold" style="padding:12px 32px;">Ir al catálogo →</a>
    </div>

  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:12px;">
      <?php foreach ($orders as $order): ?>
        <?php
        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM pedido_detalle WHERE pedido_id = ?');
        $countStmt->execute([$order['id']]);
        $itemCount = (int)$countStmt->fetchColumn();
        ?>
        <a href="order-history.php?order=<?= urlencode($order['id']) ?>" class="card" style="padding:20px 24px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;cursor:pointer;">
          <div style="width:44px;height:44px;border-radius:10px;background:#141414;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#e8b84b" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
          </div>
          <div style="flex:1;">
            <div style="font-size:15px;font-weight:600;margin-bottom:2px;"><?= e($order['id']) ?></div>
            <div style="font-size:12px;color:#666;"><?= date('j M Y', strtotime($order['fecha'])) ?> · <?= $itemCount ?> producto<?= $itemCount!==1?'s':'' ?> · <?= e($order['ciudad']) ?></div>
          </div>
          <div style="text-align:right;">
            <div class="serif" style="font-size:20px;font-weight:700;margin-bottom:6px;"><?= money($order['total']) ?></div>
            <?= render_badge($order['estado']) ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
