<?php
$BASE_URL = '../';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
$pageTitle = 'Gestión de Productos';

$filter = trim($_GET['q'] ?? '');
$sql = "SELECT p.*, c.nombre AS categoria, u.nombre AS vendedor_nombre
        FROM productos p JOIN categorias c ON c.id=p.categoria_id JOIN usuarios u ON u.id=p.vendedor_id";
$params = [];
if ($filter !== '') {
    $sql .= " WHERE p.nombre LIKE ? OR p.marca LIKE ? OR u.nombre LIKE ?";
    $like = "%$filter%";
    $params = [$like, $like, $like];
}
$sql .= " ORDER BY p.creado_en DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="page-wrap">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
    <div>
      <div style="font-size:12px;color:#e8b84b;font-weight:700;letter-spacing:.1em;margin-bottom:6px;">ADMINISTRACIÓN</div>
      <h1 class="serif" style="font-size:30px;font-weight:700;">Gestión de Productos</h1>
    </div>
    <form method="get"><input class="input" name="q" placeholder="Buscar productos..." style="max-width:260px;" value="<?= e($filter) ?>" onchange="this.form.submit()"></form>
  </div>

  <div class="card" style="overflow:hidden;">
    <div style="overflow-x:auto;">
      <table class="data-table">
        <thead><tr><th>Producto</th><th>Vendedor</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
          <?php if (empty($products)): ?>
            <tr><td colspan="7" style="text-align:center;color:#555;padding:48px 24px;">Sin productos que coincidan.</td></tr>
          <?php else: foreach ($products as $p): ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:12px;">
                  <img class="table-thumb" src="<?= e(img_url($p['imagen'],48,48)) ?>">
                  <div><div style="font-weight:600;"><?= e($p['nombre']) ?></div><div style="font-size:11px;color:#555;"><?= e($p['marca']) ?></div></div>
                </div>
              </td>
              <td style="color:#888;"><?= e($p['vendedor_nombre']) ?></td>
              <td style="color:#888;"><?= e($p['categoria']) ?></td>
              <td class="serif" style="font-weight:700;"><?= money0($p['precio']) ?></td>
              <td style="color:<?= $p['stock']==0?'#ef4444':($p['stock']<10?'#e8b84b':'#22c55e') ?>;font-weight:600;"><?= $p['stock']==0?'Sin stock':$p['stock'] ?></td>
              <td><?= render_badge($p['stock']==0 ? 'sin_stock' : ($p['activo']?'activo':'inactivo')) ?></td>
              <td>
                <form method="post" action="toggle-product.php">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <button type="submit" class="action-btn danger"><?= $p['activo']?'Desactivar':'Activar' ?></button>
                </form>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
