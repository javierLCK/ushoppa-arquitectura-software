<?php
$BASE_URL = '';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Inicio';

$slides = [
    ['label' => 'TEMPORADA DE VERANO', 'headline' => "Hasta 50% Off\nen Moda", 'sub' => 'Los mejores vendedores con descuentos exclusivos. Solo por tiempo limitado.', 'cta' => 'Comprar Ahora', 'accent' => '#e8b84b', 'image' => '1483985988355-763728e1935b'],
    ['label' => 'TECH WEEK', 'headline' => "40% Off en\nElectrónica", 'sub' => 'Los mejores gadgets de los mejores vendedores al mejor precio del año.', 'cta' => 'Ver Ofertas', 'accent' => '#4db8ff', 'image' => '1468495244123-6c6c332eeece'],
    ['label' => 'ENVÍO EXPRESS', 'headline' => "Compras sobre\n\$50 sin costo", 'sub' => 'Envío express a todo el país garantizado por NexoMarket. Recíbelo en 24h.', 'cta' => 'Descubrir', 'accent' => '#7ed87e', 'image' => '1556742049-0cfed4f6a45d'],
];

$destacados = $pdo->query("SELECT p.*, c.nombre AS categoria, u.nombre AS vendedor_nombre
                            FROM productos p JOIN categorias c ON c.id=p.categoria_id JOIN usuarios u ON u.id=p.vendedor_id
                            WHERE p.activo=1 AND p.descuento >= 25 LIMIT 8")->fetchAll();
$nuevos = $pdo->query("SELECT p.*, c.nombre AS categoria, u.nombre AS vendedor_nombre
                        FROM productos p JOIN categorias c ON c.id=p.categoria_id JOIN usuarios u ON u.id=p.vendedor_id
                        WHERE p.activo=1 AND p.es_nuevo=1")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <?php foreach ($slides as $i => $s): ?>
    <div class="hero-slide <?= $i===0?'active':'' ?>">
      <img src="<?= e(img_url($s['image'], 1600, 600)) ?>" alt="">
      <div class="overlay"></div>
      <div class="hero-content">
        <span class="hero-tag" style="background:<?= $s['accent'] ?>;"><?= e($s['label']) ?></span>
        <h1 class="hero-title serif"><?= nl2br(e($s['headline'])) ?></h1>
        <p class="hero-sub"><?= e($s['sub']) ?></p>
        <a href="catalog.php" class="btn-gold" style="width:fit-content;padding:13px 32px;background:<?= $s['accent'] ?>;"><?= e($s['cta']) ?> →</a>
      </div>
    </div>
  <?php endforeach; ?>
  <div class="hero-dots">
    <?php foreach ($slides as $i => $s): ?><button class="<?= $i===0?'active':'' ?>"></button><?php endforeach; ?>
  </div>
  <button class="hero-arrow left"><svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></button>
  <button class="hero-arrow right"><svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></button>
</section>

<section class="cats-strip">
  <div class="cats-inner">
    <span class="lbl">CATEGORÍAS:</span>
    <?php foreach (array_slice(CATEGORIAS_LISTA, 1) as $c): ?>
      <a href="catalog.php?cat=<?= urlencode($c) ?>" class="cat-pill"><?= e($c) ?></a>
    <?php endforeach; ?>
  </div>
</section>

<section class="page-wrap" style="padding-top:52px;">
  <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:28px;">
    <div>
      <div style="font-size:11px;font-weight:700;letter-spacing:.12em;color:#e8b84b;margin-bottom:8px;">OFERTAS EXCLUSIVAS</div>
      <h2 class="section-title serif">Productos Destacados</h2>
    </div>
    <a href="catalog.php" class="btn-ghost" style="font-size:13px;">Ver todo →</a>
  </div>
  <div class="product-grid">
    <?php foreach ($destacados as $p): include __DIR__ . '/includes/product-card.php'; endforeach; ?>
  </div>
</section>

<section class="page-wrap" style="padding-top:0;padding-bottom:48px;">
  <div class="vendor-cta">
    <div style="position:relative;">
      <div style="font-size:11px;font-weight:700;letter-spacing:.12em;color:#e8b84b;margin-bottom:10px;">PARA VENDEDORES</div>
      <h3 class="serif" style="font-size:28px;font-weight:700;margin-bottom:8px;">Vende en Ushoppa</h3>
      <p style="font-size:14px;color:#777;max-width:380px;">Únete a miles de vendedores en la plataforma NexoMarket. Sin cuotas mensuales, comisión solo por venta.</p>
    </div>
    <a href="register.php" class="btn-gold" style="position:relative;padding:14px 32px;">Comenzar a vender →</a>
  </div>
</section>

<?php if (!empty($nuevos)): ?>
<section class="page-wrap" style="padding-top:0;">
  <div style="margin-bottom:28px;">
    <div style="font-size:11px;font-weight:700;letter-spacing:.12em;color:#4db8ff;margin-bottom:8px;">RECIÉN LLEGADO</div>
    <h2 class="section-title serif">Nuevas Llegadas</h2>
  </div>
  <div class="product-grid">
    <?php foreach ($nuevos as $p): include __DIR__ . '/includes/product-card.php'; endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
