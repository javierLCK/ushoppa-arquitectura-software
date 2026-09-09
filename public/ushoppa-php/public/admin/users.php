<?php
$BASE_URL = '../';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
$pageTitle = 'Gestión de Usuarios';

$filter = trim($_GET['q'] ?? '');
if ($filter !== '') {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nombre LIKE ? OR correo LIKE ? OR rol LIKE ? ORDER BY creado_en DESC");
    $like = "%$filter%";
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $pdo->query('SELECT * FROM usuarios ORDER BY creado_en DESC');
}
$users = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="page-wrap">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
    <div>
      <div style="font-size:12px;color:#e8b84b;font-weight:700;letter-spacing:.1em;margin-bottom:6px;">ADMINISTRACIÓN</div>
      <h1 class="serif" style="font-size:30px;font-weight:700;">Gestión de Usuarios</h1>
    </div>
    <form method="get"><input class="input" name="q" placeholder="Buscar usuarios..." style="max-width:260px;" value="<?= e($filter) ?>" onchange="this.form.submit()"></form>
  </div>

  <div class="card" style="overflow:hidden;">
    <table class="data-table">
      <thead><tr><th>Usuario</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><div style="display:flex;align-items:center;gap:10px;"><div class="avatar"><?= e(mb_substr($u['nombre'],0,1)) ?></div><span style="font-weight:600;"><?= e($u['nombre']) ?></span></div></td>
            <td style="color:#888;"><?= e($u['correo']) ?></td>
            <td><?= render_badge($u['rol']) ?></td>
            <td><?= render_badge($u['estado']) ?></td>
            <td>
              <?php if ($u['rol'] !== 'admin'): ?>
                <form method="post" action="toggle-user.php">
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <button type="submit" class="action-btn danger"><?= $u['estado']==='activo' ? 'Suspender' : 'Activar' ?></button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
