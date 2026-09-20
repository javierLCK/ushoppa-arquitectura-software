<?php
/**
 * includes/footer.php
 * -----------------------------------------------------------------------
 * Cierra <main>, imprime el footer del sitio y el carrito lateral
 * (overlay + panel deslizante). Se incluye al final de cada página:
 *   require __DIR__ . '/includes/footer.php';
 *
 * Requiere cart-partial.php para tener $cartBodyHtml y $cartFooterHtml
 * ya renderizados con el contenido actual del carrito (mismo partial que
 * usa actions/cart-render.php para las actualizaciones AJAX).
 */
require_once __DIR__ . '/cart-partial.php';
?>
</main>

<footer>
  <div class="footer-inner">
    <div class="footer-grid">
      <div>
        <div class="serif" style="font-size:24px;font-weight:700;margin-bottom:12px;">U<span class="gold">shoppa</span></div>
        <p style="max-width:280px;line-height:1.7;">La plataforma marketplace de NexoMarket que conecta compradores y vendedores con la mejor experiencia de compra en línea.</p>
      </div>
      <div>
        <h4>Empresa</h4>
        <a class="link" href="<?= BASE_URL ?>static.php?page=sobre" style="display:block;">Sobre NexoMarket</a>
        <a class="link" href="<?= BASE_URL ?>static.php?page=terminos" style="display:block;">Términos y condiciones</a>
        <a class="link" href="<?= BASE_URL ?>static.php?page=privacidad" style="display:block;">Privacidad</a>
        <a class="link" href="<?= BASE_URL ?>contact.php" style="display:block;">Contacto</a>
      </div>
      <div>
        <h4>Compradores</h4>
        <a class="link" href="<?= BASE_URL ?>static.php?page=como-comprar" style="display:block;">Cómo comprar</a>
        <a class="link" href="<?= BASE_URL ?>static.php?page=metodos-pago" style="display:block;">Métodos de pago</a>
        <a class="link" href="<?= BASE_URL ?>static.php?page=envios" style="display:block;">Envíos y devoluciones</a>
        <a class="link" href="<?= BASE_URL ?>static.php?page=ayuda" style="display:block;">Centro de ayuda</a>
      </div>
      <div>
        <h4>Vendedores</h4>
        <a class="link" href="<?= BASE_URL ?>register.php" style="display:block;">Vende en Ushoppa</a>
        <a class="link" href="<?= BASE_URL ?>vendor/dashboard.php" style="display:block;">Panel de vendedor</a>
        <a class="link" href="<?= BASE_URL ?>static.php?page=comisiones" style="display:block;">Comisiones</a>
        <a class="link" href="<?= BASE_URL ?>static.php?page=soporte-vendedor" style="display:block;">Soporte vendedor</a>
      </div>
    </div>
    <div class="footer-bottom">
      <span style="font-size:12px;color:#444;">© 2026 NexoMarket · Ushoppa. Todos los derechos reservados.</span>
      <div class="pay-badges"><span>Visa</span><span>MC</span><span>PayPal</span><span>AMEX</span></div>
    </div>
  </div>
</footer>

<div class="cart-overlay" id="cartOverlay"></div>
<aside class="cart-sidebar" id="cartSidebar">
  <div class="cart-header">
    <h2 class="serif" style="font-size:22px;">Carrito <span style="font-size:15px;color:#555;font-weight:400;">(<?= cart_count() ?>)</span></h2>
    <button class="cart-close" data-close-cart>
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
  </div>
  <!-- Contenido inicial renderizado en servidor; main.js lo reemplaza vía AJAX
       (actions/cart-render.php) cada vez que se agrega/quita/actualiza un ítem,
       sin recargar la página. -->
  <div class="cart-body" id="cartBody"><?= $cartBodyHtml ?></div>
  <div id="cartFooterSlot"><?= $cartFooterHtml ?></div>
</aside>

<!-- BASE_URL se expone a main.js para que sus fetch() apunten a la ruta
     correcta según la profundidad de la página actual (ver actions/cart-render.php). -->
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
