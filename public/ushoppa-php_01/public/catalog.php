<?php
/**
 * catalog.php — Catálogo con filtros
 * -----------------------------------------------------------------------
 * Lista de productos filtrable por categoría, precio máximo, búsqueda de
 * texto (?q=, viene del buscador del navbar) y orden. Todos los filtros
 * viajan por GET para que la URL sea compartible/marcable (bookmarkable).
 * El formulario de filtros (más abajo) no hace submit tradicional: usa
 * botones con onclick que fijan un campo oculto y llaman a form.submit(),
 * para poder tener varios "grupos" de filtros en un solo <form>.
 */
$BASE_URL = '';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Catálogo';

$cat      = $_GET['cat'] ?? 'Todas';
$maxPrice = isset($_GET['max']) ? (int)$_GET['max'] : 1500;
$sort     = $_GET['sort'] ?? 'featured';
$q        = trim($_GET['q'] ?? '');

// Consulta armada dinámicamente pero siempre con parámetros preparados
// (:maxp, :cat, :q) -- nunca se concatena el valor del usuario al SQL.
$sql = "SELECT p.*, c.nombre AS categoria, u.nombre AS vendedor_nombre
        FROM productos p JOIN categorias c ON c.id=p.categoria_id JOIN usuarios u ON u.id=p.vendedor_id
        WHERE p.activo=1 AND p.precio <= :maxp";
$params = ['maxp' => $maxPrice];
if ($cat !== 'Todas') { $sql .= " AND c.nombre = :cat"; $params['cat'] = $cat; }
if ($q !== '') { $sql .= " AND (p.nombre LIKE :q OR p.marca LIKE :q)"; $params['q'] = "%$q%"; }
$sql .= match ($sort) {
    'price-asc'  => " ORDER BY p.precio ASC",
    'price-desc' => " ORDER BY p.precio DESC",
    'rating'     => " ORDER BY p.rating DESC",
    default      => "", // 'featured': sin ORDER BY explícito, orden natural de la tabla
};
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

// Conteo de productos por categoría, para mostrar el número junto a cada
// filtro en el aside (ej: "Moda 4"). Se calcula aparte de la consulta
// principal porque necesita ignorar el filtro de categoría actualmente
// aplicado (para poder cambiar de categoría desde cualquier filtro activo).
$counts = [];
foreach (CATEGORIAS_LISTA as $c) {
    if ($c === 'Todas') {
        $counts[$c] = (int)$pdo->query("SELECT COUNT(*) FROM productos WHERE activo=1")->fetchColumn();
    } else {
        $st = $pdo->prepare("SELECT COUNT(*) FROM productos p JOIN categorias cat ON cat.id=p.categoria_id WHERE p.activo=1 AND cat.nombre=?");
        $st->execute([$c]);
        $counts[$c] = (int)$st->fetchColumn();
    }
}

require __DIR__ . '/includes/header.php';
?>
<div class="catalog-layout">
  <aside>
    <div class="card filters">
      <h3 class="serif" style="font-size:20px;font-weight:700;margin-bottom:24px;">Filtros</h3>

      <form method="get" id="filterForm">
        <!-- Todos los filtros viven en el mismo <form> con method="get", para
             que el estado completo quede en la URL (compartible/bookmarkable).
             Los botones de categoría/orden NO son type="submit": son
             type="button" que primero fijan el <input hidden> correspondiente
             (fCat / fSort) y luego llaman a filterForm.submit() a mano.
             Esto evita el problema de tener varios <button name="cat" value="X">
             con el mismo name, que competirían entre sí al enviar el form. -->
        <input type="hidden" name="q" value="<?= e($q) ?>">
        <input type="hidden" name="cat" id="fCat" value="<?= e($cat) ?>">
        <input type="hidden" name="sort" id="fSort" value="<?= e($sort) ?>">
        <div style="margin-bottom:24px;">
          <div class="label" style="margin-bottom:10px;">Categoría</div>
          <?php foreach (CATEGORIAS_LISTA as $c): ?>
            <button type="button" onclick="document.getElementById('fCat').value='<?= e($c) ?>';filterForm.submit();" class="cat-filter-btn <?= $cat===$c?'active':'' ?>">
              <?= e($c) ?> <span style="font-size:11px;color:<?= $cat===$c?'#8b7135':'#444' ?>;"><?= $counts[$c] ?></span>
            </button>
          <?php endforeach; ?>
        </div>
        <div style="margin-bottom:24px;">
          <div class="label" style="margin-bottom:10px;">Precio máximo</div>
          <div style="font-size:14px;color:#e8b84b;font-weight:600;margin-bottom:10px;">$<?= $maxPrice>=1500?'1500+':$maxPrice ?></div>
          <!-- El slider sí es un input real del form (name="max"): al soltarlo
               (onchange) envía directo, sin necesitar botón intermedio. -->
          <input type="range" name="max" min="50" max="1500" step="50" value="<?= $maxPrice ?>" style="width:100%;" onchange="filterForm.submit()">
        </div>
        <div>
          <div class="label" style="margin-bottom:10px;">Ordenar por</div>
          <?php foreach ([['featured','Destacados'],['price-asc','Menor precio'],['price-desc','Mayor precio'],['rating','Mejor valorados']] as [$v,$l]): ?>
            <button type="button" onclick="document.getElementById('fSort').value='<?= $v ?>';filterForm.submit();" class="cat-filter-btn <?= $sort===$v?'active':'' ?>" style="justify-content:flex-start;"><?= $l ?></button>
          <?php endforeach; ?>
        </div>
      </form>
      <!-- Enlace simple sin querystring: vuelve a catalog.php con todos los filtros por defecto -->
      <a href="catalog.php" class="btn-ghost" style="width:100%;display:block;text-align:center;margin-top:20px;font-size:12px;">Limpiar filtros</a>
    </div>
  </aside>

  <div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
      <div>
        <?php if ($q !== ''): ?><div style="font-size:12px;color:#666;margin-bottom:4px;">Resultados: <span class="gold">"<?= e($q) ?>"</span></div><?php endif; ?>
        <h2 class="section-title serif"><?= $cat==='Todas' ? 'Catálogo' : e($cat) ?></h2>
      </div>
      <span style="font-size:13px;color:#555;"><?= count($productos) ?> resultado<?= count($productos)!==1?'s':'' ?></span>
    </div>

    <?php if (empty($productos)): ?>
      <div class="empty-state">
        <div class="emoji">🔍</div>
        <div style="font-size:18px;color:#888;margin-bottom:8px;">Sin resultados</div>
        <div style="font-size:14px;">Prueba con otros filtros</div>
      </div>
    <?php else: ?>
      <div class="product-grid">
        <?php foreach ($productos as $p): include __DIR__ . '/includes/product-card.php'; endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
