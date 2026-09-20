<?php
/**
 * product.php — Detalle de producto (?id=N)
 * -----------------------------------------------------------------------
 * Muestra imagen grande, precio, descripción, datos del vendedor y
 * selector de cantidad + botón para agregar al carrito. Si el id no
 * existe, redirige silenciosamente al catálogo.
 */
$BASE_URL = '';
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT p.*, c.nombre AS categoria, u.nombre AS vendedor_nombre
                        FROM productos p JOIN categorias c ON c.id=p.categoria_id JOIN usuarios u ON u.id=p.vendedor_id
                        WHERE p.id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { header('Location: catalog.php'); exit; }

$pageTitle = $p['nombre'];
require __DIR__ . '/includes/header.php';
$sinStock = (int)$p['stock'] === 0;
?>
<div class="page-wrap">
  <a href="catalog.php" class="btn-ghost" style="display:inline-flex;align-items:center;gap:6px;margin-bottom:28px;font-size:13px;">
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg> Volver al catálogo
  </a>

  <div class="detail-layout">
    <div class="card detail-img">
      <img src="<?= e(img_url($p['imagen'], 700, 700)) ?>" alt="<?= e($p['nombre']) ?>">
      <div style="position:absolute;top:14px;left:14px;display:flex;gap:6px;">
        <?php if (!empty($p['badge'])): ?><span class="tag badge-y" style="font-size:11px;padding:4px 10px;"><?= e($p['badge']) ?></span><?php endif; ?>
        <?php if (!empty($p['es_nuevo'])): ?><span class="tag badge-b" style="font-size:11px;padding:4px 10px;">NUEVO</span><?php endif; ?>
        <?php if (!empty($p['descuento'])): ?><span class="tag badge-r" style="font-size:11px;padding:4px 10px;">-<?= (int)$p['descuento'] ?>%</span><?php endif; ?>
      </div>
    </div>

    <div>
      <div style="font-size:12px;color:#666;font-weight:700;letter-spacing:.08em;margin-bottom:8px;text-transform:uppercase;"><?= e($p['marca']) ?> · <?= e($p['categoria']) ?></div>
      <h1 class="serif" style="font-size:36px;font-weight:700;line-height:1.1;margin-bottom:16px;"><?= e($p['nombre']) ?></h1>
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
        <?= render_stars($p['rating']) ?> <span style="font-size:13px;color:#888;"><?= $p['rating'] ?> · <?= number_format((int)$p['reviews']) ?> reseñas</span>
      </div>
      <div style="display:flex;align-items:baseline;gap:12px;margin-bottom:20px;">
        <span class="serif" style="font-size:40px;font-weight:700;"><?= money0($p['precio']) ?></span>
        <?php if (!empty($p['precio_original'])): ?><span style="font-size:18px;color:#444;text-decoration:line-through;"><?= money0($p['precio_original']) ?></span><?php endif; ?>
        <?php if (!empty($p['descuento'])): ?><span style="background:rgba(239,68,68,.15);color:#f87171;font-size:14px;font-weight:700;padding:3px 10px;border-radius:99px;">-<?= (int)$p['descuento'] ?>%</span><?php endif; ?>
      </div>
      <p style="font-size:15px;color:#999;line-height:1.7;margin-bottom:24px;border-bottom:1px solid #1a1a1a;padding-bottom:24px;"><?= e($p['descripcion']) ?></p>

      <div class="card" style="padding:16px 20px;margin-bottom:24px;display:flex;align-items:center;gap:14px;">
        <div class="avatar lg"><?= e(mb_substr($p['vendedor_nombre'],0,1)) ?></div>
        <div style="flex:1;">
          <div style="font-size:12px;color:#666;margin-bottom:2px;">Vendido por</div>
          <div style="font-size:15px;font-weight:600;"><?= e($p['vendedor_nombre']) ?></div>
        </div>
        <div style="font-size:12px;font-weight:600;color:<?= $sinStock?'#ef4444':'#22c55e' ?>;"><?= $sinStock ? 'Sin stock' : $p['stock'].' disponibles' ?></div>
      </div>

      <?php if (!$sinStock): ?>
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
        <div style="font-size:13px;color:#888;font-weight:600;">Cantidad:</div>
        <div class="qty-box">
          <button type="button" onclick="stepQty(-1)">−</button>
          <span id="qtyVal">1</span>
          <button type="button" onclick="stepQty(1)">+</button>
        </div>
      </div>
      <?php endif; ?>

      <button id="addBtn" class="btn-gold" style="width:100%;padding:14px;font-size:15px;" <?= $sinStock?'disabled':'' ?>
        onclick="addToCart(<?= (int)$p['id'] ?>, document.getElementById('qtyVal')?document.getElementById('qtyVal').textContent:1, this)">
        <?= $sinStock ? 'Sin stock disponible' : 'Agregar al carrito · ' . money0($p['precio']) ?>
      </button>

      <div style="display:flex;align-items:center;gap:6px;margin-top:14px;">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#555;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        <span style="font-size:12px;color:#555;">Compra protegida por NexoMarket · Envío express disponible</span>
      </div>
    </div>
  </div>
</div>

<?php if (!$sinStock): ?>
<script>
// Selector de cantidad: limitado entre 1 y el stock disponible ($max).
// El valor se lee al hacer clic en "Agregar al carrito" (ver el onclick
// del botón #addBtn más arriba, que toma el texto de #qtyVal).
let qty = 1;
const max = <?= (int)$p['stock'] ?>;
function stepQty(d) {
  qty = Math.min(max, Math.max(1, qty + d));
  document.getElementById('qtyVal').textContent = qty;
}
</script>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
