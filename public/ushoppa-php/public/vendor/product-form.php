<?php
/**
 * vendor/product-form.php — Alta / edición de producto (vendedor)
 * -----------------------------------------------------------------------
 * Un mismo formulario sirve para crear (?id ausente) y editar
 * (?id=N, si pertenece al vendedor logueado). Si el id no existe o
 * pertenece a otro vendedor, redirige al dashboard sin mostrar nada
 * (evita IDOR: no se puede editar productos ajenos).
 *
 * El precio original (para mostrar el tachado "antes $X") se recalcula
 * automáticamente a partir del precio final y el % de descuento; el
 * vendedor no lo ingresa a mano para evitar inconsistencias.
 */
$BASE_URL = '../';
require_once __DIR__ . '/../includes/functions.php';
require_role('vendedor');
$user = current_user();

$editId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$product = null;
if ($editId) {
    // AND vendedor_id = ? evita que un vendedor edite productos de otro.
    $stmt = $pdo->prepare('SELECT * FROM productos WHERE id = ? AND vendedor_id = ?');
    $stmt->execute([$editId, $user['id']]);
    $product = $stmt->fetch();
    if (!$product) { header('Location: dashboard.php'); exit; }
}
$isEdit = (bool)$product;
$pageTitle = $isEdit ? 'Editar Producto' : 'Nuevo Producto';

$categorias = $pdo->query('SELECT * FROM categorias ORDER BY nombre')->fetchAll();
$errors = [];
$saved = false;

$form = $product ?: ['nombre' => '', 'marca' => '', 'descripcion' => '', 'categoria_id' => $categorias[0]['id'] ?? 1, 'precio' => '', 'stock' => '', 'descuento' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $form['nombre'] = trim($_POST['nombre'] ?? '');
    $form['marca'] = trim($_POST['marca'] ?? '');
    $form['descripcion'] = trim($_POST['descripcion'] ?? '');
    $form['categoria_id'] = (int)($_POST['categoria_id'] ?? 0);
    $form['precio'] = $_POST['precio'] ?? '';
    $form['stock'] = $_POST['stock'] ?? '';
    $form['descuento'] = (int)($_POST['descuento'] ?? 0);

    if ($form['nombre'] === '') $errors['nombre'] = 'Requerido';
    if ($form['marca'] === '') $errors['marca'] = 'Requerido';
    if ($form['descripcion'] === '') $errors['descripcion'] = 'Requerido';
    if (!is_numeric($form['precio']) || (float)$form['precio'] <= 0) $errors['precio'] = 'Precio inválido';
    if (!is_numeric($form['stock']) || (int)$form['stock'] < 0) $errors['stock'] = 'Stock inválido';

    if (empty($errors)) {
        // precio_original se deriva del precio final + % descuento (regla de 3
        // inversa), así el listado siempre puede mostrar "antes / ahora" sin
        // que el vendedor tenga que mantener dos precios sincronizados a mano.
        $precioOriginal = $form['descuento'] > 0 ? round($form['precio'] / (1 - $form['descuento']/100), 2) : null;
        // Los productos nuevos no permiten subir imagen propia (fuera de
        // alcance de este proyecto): se les asigna una foto de stock por defecto.
        $imagen = $product['imagen'] ?? '1606220945770-b5b6c2c55bf1';

        if ($isEdit) {
            // WHERE ... AND vendedor_id = ? -> aunque alguien falsificara el
            // id en el POST, nunca podría actualizar un producto ajeno.
            $stmt = $pdo->prepare('UPDATE productos SET nombre=?, marca=?, descripcion=?, categoria_id=?, precio=?, precio_original=?, descuento=?, stock=? WHERE id=? AND vendedor_id=?');
            $stmt->execute([$form['nombre'], $form['marca'], $form['descripcion'], $form['categoria_id'], $form['precio'], $precioOriginal, $form['descuento'], $form['stock'], $editId, $user['id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO productos (nombre, marca, descripcion, categoria_id, precio, precio_original, descuento, stock, imagen, vendedor_id, es_nuevo, rating, reviews, activo) VALUES (?,?,?,?,?,?,?,?,?,?,1,4.5,0,1)');
            $stmt->execute([$form['nombre'], $form['marca'], $form['descripcion'], $form['categoria_id'], $form['precio'], $precioOriginal, $form['descuento'], $form['stock'], $imagen, $user['id']]);
        }
        $saved = true;
    }
}

require __DIR__ . '/../includes/header.php';
?>
<div class="page-wrap">
  <a href="dashboard.php" class="btn-ghost" style="display:inline-flex;align-items:center;gap:6px;margin-bottom:24px;font-size:13px;">
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg> Volver al panel
  </a>

  <?php if ($saved): ?>
    <div class="card" style="max-width:440px;margin:40px auto;padding:40px;text-align:center;">
      <div style="width:60px;height:60px;border-radius:50%;background:rgba(34,197,94,.12);border:2px solid #22c55e;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
        <svg width="30" height="30" fill="none" viewBox="0 0 24 24" stroke="#22c55e" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
      </div>
      <h2 class="serif" style="font-size:26px;margin-bottom:8px;">Producto <?= $isEdit ? 'actualizado' : 'publicado' ?></h2>
      <p style="color:#777;margin-bottom:24px;">El producto fue guardado correctamente.</p>
      <a href="dashboard.php" class="btn-gold" style="padding:12px 32px;">Ir a mi tienda →</a>
    </div>
  <?php else: ?>
    <div class="card" style="padding:32px;max-width:720px;">
      <h1 class="serif" style="font-size:28px;font-weight:700;margin-bottom:28px;"><?= $isEdit ? 'Editar Producto' : 'Nuevo Producto' ?></h1>
      <form method="post">
        <?= csrf_field() ?>
        <div style="display:grid;gap:18px;">
          <div>
            <label class="label">Nombre del producto</label>
            <input class="input <?= isset($errors['nombre'])?'error':'' ?>" name="nombre" placeholder="Nombre descriptivo del producto" value="<?= e($form['nombre']) ?>">
            <?php if (isset($errors['nombre'])): ?><div class="field-error"><?= $errors['nombre'] ?></div><?php endif; ?>
          </div>
          <div>
            <label class="label">Marca</label>
            <input class="input <?= isset($errors['marca'])?'error':'' ?>" name="marca" placeholder="Ej: Nike, Apple, ZARA" value="<?= e($form['marca']) ?>">
            <?php if (isset($errors['marca'])): ?><div class="field-error"><?= $errors['marca'] ?></div><?php endif; ?>
          </div>
          <div>
            <label class="label">Precio ($)</label>
            <input class="input <?= isset($errors['precio'])?'error':'' ?>" type="number" step="0.01" name="precio" placeholder="99.99" value="<?= e((string)$form['precio']) ?>">
            <?php if (isset($errors['precio'])): ?><div class="field-error"><?= $errors['precio'] ?></div><?php endif; ?>
          </div>
          <div>
            <label class="label">Stock disponible</label>
            <input class="input <?= isset($errors['stock'])?'error':'' ?>" type="number" name="stock" placeholder="25" value="<?= e((string)$form['stock']) ?>">
            <?php if (isset($errors['stock'])): ?><div class="field-error"><?= $errors['stock'] ?></div><?php endif; ?>
          </div>
          <div>
            <label class="label">Descuento (%)</label>
            <input class="input" type="number" name="descuento" placeholder="0" value="<?= e((string)$form['descuento']) ?>">
          </div>
          <div>
            <label class="label">Descripción</label>
            <textarea class="input" name="descripcion" rows="4" placeholder="Descripción detallada del producto..."><?= e($form['descripcion']) ?></textarea>
            <?php if (isset($errors['descripcion'])): ?><div class="field-error"><?= $errors['descripcion'] ?></div><?php endif; ?>
          </div>
          <div>
            <label class="label">Categoría</label>
            <select class="input" name="categoria_id">
              <?php foreach ($categorias as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $form['categoria_id']==$c['id']?'selected':'' ?>><?= e($c['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div style="display:flex;gap:12px;margin-top:28px;">
          <a href="dashboard.php" class="btn-ghost" style="flex:1;text-align:center;">Cancelar</a>
          <button type="submit" class="btn-gold" style="flex:2;padding:13px;"><?= $isEdit ? 'Guardar cambios' : 'Publicar producto' ?></button>
        </div>
      </form>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
