<?php
/**
 * vendor/dashboard.php — Panel del vendedor
 * -----------------------------------------------------------------------
 * Muestra estadísticas rápidas (total, activos, sin stock) y la tabla de
 * publicaciones propias del vendedor logueado, con acciones para editar
 * o activar/desactivar cada producto.
 *
 * require_role('vendedor') corta el acceso a cualquiera que no tenga
 * sesión con rol=vendedor (clientes/admins/visitantes son redirigidos).
 * Todas las consultas filtran por vendedor_id = $user['id'], por lo que
 * un vendedor jamás ve ni puede tocar productos de otro vendedor.
 */
$BASE_URL = '../';
require_once __DIR__ . '/../includes/functions.php';
require_role('vendedor');
$pageTitle = 'Mi Tienda';
$user = current_user();

$stmt = $pdo->prepare("SELECT p.*, c.nombre AS categoria FROM productos p JOIN categorias c ON c.id=p.categoria_id WHERE p.vendedor_id = ? ORDER BY p.creado_en DESC");
$stmt->execute([$user['id']]);
$mine = $stmt->fetchAll();
$active = array_filter($mine, fn($p) => $p['activo'] && $p['stock'] > 0);
$noStock = array_filter($mine, fn($p) => (int)$p['stock'] === 0);

require __DIR__ . '/../includes/header.php';
?>
<div class="page-wrap">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;flex-wrap:wrap;gap:16px;">
    <div>
      <div style="font-size:12px;color:#e8b84b;font-weight:700;letter-spacing:.1em;margin-bottom:8px;">PANEL VENDEDOR</div>
      <h1 class="serif" style="font-size:32px;font-weight:700;">Mi Tienda</h1>
      <p style="color:#666;font-size:14px;margin-top:4px;">Bienvenido, <?= e($user['nombre']) ?></p>
    </div>
    <a href="product-form.php" class="btn-gold" style="padding:12px 24px;font-size:14px;display:inline-flex;align-items:center;gap:8px;"><span style="font-size:18px;line-height:1;">+</span> Agregar Producto</a>
  </div>

  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:36px;">
    <div class="card stat-card"><div style="font-size:28px;">📦</div><div><div class="stat-val gold"><?= count($mine) ?></div><div style="font-size:13px;color:#666;">Total publicaciones</div></div></div>
    <div class="card stat-card"><div style="font-size:28px;">✅</div><div><div class="stat-val" style="color:#22c55e;"><?= count($active) ?></div><div style="font-size:13px;color:#666;">Disponibles</div></div></div>
    <div class="card stat-card"><div style="font-size:28px;">⚠</div><div><div class="stat-val" style="color:#ef4444;"><?= count($noStock) ?></div><div style="font-size:13px;color:#666;">Sin stock</div></div></div>
  </div>

  <div class="card" style="overflow:hidden;">
    <div style="padding:20px 24px;border-bottom:1px solid #141414;"><h3 style="font-size:18px;font-weight:700;">Mis Publicaciones</h3></div>
    <div style="overflow-x:auto;">
      <table class="data-table">
        <thead><tr><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
          <?php if (empty($mine)): ?>
            <tr><td colspan="6" style="text-align:center;color:#555;padding:48px 24px;">Sin publicaciones. Agrega tu primer producto.</td></tr>
          <?php else: foreach ($mine as $p): ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:12px;">
                  <img class="table-thumb" src="<?= e(img_url($p['imagen'],48,48)) ?>">
                  <div><div style="font-weight:600;"><?= e($p['nombre']) ?></div><div style="font-size:11px;color:#555;"><?= e($p['marca']) ?></div></div>
                </div>
              </td>
              <td style="color:#888;"><?= e($p['categoria']) ?></td>
              <td class="serif" style="font-weight:700;"><?= money0($p['precio']) ?></td>
              <td style="color:<?= $p['stock']==0?'#ef4444':($p['stock']<10?'#e8b84b':'#22c55e') ?>;font-weight:600;"><?= $p['stock']==0?'Sin stock':$p['stock'].' uds.' ?></td>
              <td><?= render_badge($p['stock']==0 ? 'sin_stock' : ($p['activo']?'activo':'inactivo')) ?></td>
              <td>
                <div style="display:flex;gap:8px;">
                  <a href="product-form.php?id=<?= $p['id'] ?>" class="action-btn">Editar</a>
                  <form method="post" action="toggle-product.php" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="action-btn danger"><?= $p['activo']?'Desactivar':'Activar' ?></button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
