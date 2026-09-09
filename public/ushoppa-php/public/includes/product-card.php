<?php
/**
 * Espera $p (fila de producto con categoria y vendedor_nombre) en el scope.
 * Uso: foreach ($productos as $p) { include __DIR__.'/product-card.php'; }
 */
$sinStock = (int)$p['stock'] === 0;
?>
<a href="<?= BASE_URL ?>product.php?id=<?= (int)$p['id'] ?>" class="card product-card">
  <div class="thumb">
    <img src="<?= e(img_url($p['imagen'], 400, 300)) ?>" alt="<?= e($p['nombre']) ?>" style="<?= $sinStock ? 'filter:grayscale(.5);' : '' ?>">
    <div class="thumb-tags">
      <?php if (!empty($p['badge'])): ?><span class="tag badge-y"><?= e($p['badge']) ?></span><?php endif; ?>
      <?php if (!empty($p['es_nuevo'])): ?><span class="tag badge-b">NUEVO</span><?php endif; ?>
      <?php if (!empty($p['descuento'])): ?><span class="tag badge-r">-<?= (int)$p['descuento'] ?>%</span><?php endif; ?>
      <?php if ($sinStock): ?><span class="tag badge-gray">SIN STOCK</span><?php endif; ?>
    </div>
  </div>
  <div class="info">
    <div class="brandline"><?= e($p['marca']) ?> · <?= e($p['vendedor_nombre']) ?></div>
    <div class="pname"><?= e($p['nombre']) ?></div>
    <div class="ratingline"><?= render_stars($p['rating']) ?> (<?= number_format((int)$p['reviews']) ?>)</div>
    <div class="priceline">
      <div>
        <span class="price"><?= money0($p['precio']) ?></span>
        <?php if (!empty($p['precio_original'])): ?><span class="price-orig"><?= money0($p['precio_original']) ?></span><?php endif; ?>
      </div>
      <button class="add-btn" data-add-to-cart="<?= (int)$p['id'] ?>" data-qty="1" <?= $sinStock ? 'disabled' : '' ?>>
        <?= $sinStock ? 'Sin stock' : '+ Carrito' ?>
      </button>
    </div>
  </div>
</a>
