<?php
$BASE_URL = '../';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
$pageTitle = 'Administración';

$totalUsers = (int)$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
$totalVendors = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='vendedor'")->fetchColumn();
$totalProducts = (int)$pdo->query('SELECT COUNT(*) FROM productos')->fetchColumn();
$noStock = (int)$pdo->query('SELECT COUNT(*) FROM productos WHERE stock=0')->fetchColumn();

$recentUsers = $pdo->query('SELECT * FROM usuarios ORDER BY creado_en DESC LIMIT 5')->fetchAll();
$recentProducts = $pdo->query("SELECT p.*, u.nombre AS vendedor_nombre FROM productos p JOIN usuarios u ON u.id=p.vendedor_id ORDER BY p.creado_en DESC LIMIT 5")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="page-wrap">
  <div style="margin-bottom:36px;">
    <div style="font-size:12px;color:#e8b84b;font-weight:700;letter-spacing:.1em;margin-bottom:8px;">PANEL ADMINISTRATIVO</div>
    <h1 class="serif" style="font-size:32px;font-weight:700;">Administración Ushoppa</h1>
    <p style="color:#666;font-size:14px;margin-top:4px;">Vista general de la plataforma NexoMarket</p>
  </div>

  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:40px;">
    <a href="users.php" class="card" style="padding:24px;"><div style="font-size:28px;margin-bottom:12px;">👥</div><div class="stat-val" style="color:#4db8ff;"><?= $totalUsers ?></div><div style="font-size:13px;color:#666;">Usuarios totales</div></a>
    <a href="users.php" class="card" style="padding:24px;"><div style="font-size:28px;margin-bottom:12px;">🏪</div><div class="stat-val gold"><?= $totalVendors ?></div><div style="font-size:13px;color:#666;">Vendedores</div></a>
    <a href="products.php" class="card" style="padding:24px;"><div style="font-size:28px;margin-bottom:12px;">📦</div><div class="stat-val" style="color:#7ed87e;"><?= $totalProducts ?></div><div style="font-size:13px;color:#666;">Productos</div></a>
    <a href="products.php" class="card" style="padding:24px;"><div style="font-size:28px;margin-bottom:12px;">⚠</div><div class="stat-val" style="color:#ef4444;"><?= $noStock ?></div><div style="font-size:13px;color:#666;">Sin stock</div></a>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
    <div class="card" style="overflow:hidden;">
      <div style="padding:18px 22px;border-bottom:1px solid #141414;display:flex;justify-content:space-between;align-items:center;">
        <h3 style="font-size:16px;font-weight:700;">Usuarios recientes</h3>
        <a href="users.php" style="font-size:12px;color:#e8b84b;">Ver todos →</a>
      </div>
      <?php foreach ($recentUsers as $u): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:12px 22px;border-bottom:1px solid #0f0f0f;">
          <div class="avatar"><?= e(mb_substr($u['nombre'],0,1)) ?></div>
          <div style="flex:1;"><div style="font-size:13px;font-weight:600;"><?= e($u['nombre']) ?></div><div style="font-size:11px;color:#555;"><?= e($u['correo']) ?></div></div>
          <?= render_badge($u['rol']) ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="card" style="overflow:hidden;">
      <div style="padding:18px 22px;border-bottom:1px solid #141414;display:flex;justify-content:space-between;align-items:center;">
        <h3 style="font-size:16px;font-weight:700;">Publicaciones recientes</h3>
        <a href="products.php" style="font-size:12px;color:#e8b84b;">Ver todas →</a>
      </div>
      <?php foreach ($recentProducts as $p): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:12px 22px;border-bottom:1px solid #0f0f0f;">
          <img src="<?= e(img_url($p['imagen'],40,40)) ?>" style="width:36px;height:36px;border-radius:6px;object-fit:cover;flex-shrink:0;">
          <div style="flex:1;min-width:0;"><div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($p['nombre']) ?></div><div style="font-size:11px;color:#555;"><?= e($p['vendedor_nombre']) ?></div></div>
          <?= render_badge($p['stock']==0?'sin_stock':($p['activo']?'activo':'inactivo')) ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
