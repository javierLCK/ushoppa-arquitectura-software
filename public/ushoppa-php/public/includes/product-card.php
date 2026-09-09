<?php
/**
 * includes/product-card.php
 * -----------------------------------------------------------------------
 * Partial de una tarjeta de producto (imagen, badges, nombre, rating,
 * precio y botón "+ Carrito"). Se incluye dentro de un foreach, una vez
 * por producto, en index.php y catalog.php:
 *
 *   foreach ($productos as $p) { include __DIR__.'/product-card.php'; }
 *
 * Espera en $p una fila de la tabla productos con los JOIN de categoria
 * y vendedor_nombre ya resueltos (ver las consultas en index.php/catalog.php).
 * El botón "+ Carrito" no hace submit de formulario: dispara addToCart()
 * en main.js vía el atributo data-add-to-cart (event listener delegado).
 */
$sinStock = (int)$p['stock'] === 0;
?>
<a href="<?= BASE_URL ?>product.php?id=<?= (int)$p['id'] ?>" class="card product-card">
  <div class="thumb">
    <img src="<?= e(img_url($p['imagen'], 400, 300)) ?>" alt="<?= e($p['nombre']) ?>" style="<?= $sinStock ? 'filter:grayscale(.5);' : '' ?>">
    <div class="thumb-tags">
      <?php /* Hasta 4 etiquetas simultáneas: destacado, nuevo, % descuento y sin stock */ ?>
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
      <!-- data-add-to-cart activa el listener global de main.js; sin stock, el botón queda deshabilitado -->
      <button class="add-btn" data-add-to-cart="<?= (int)$p['id'] ?>" data-qty="1" <?= $sinStock ? 'disabled' : '' ?>>
        <?= $sinStock ? 'Sin stock' : '+ Carrito' ?>
      </button>
    </div>
  </div>
</a>
