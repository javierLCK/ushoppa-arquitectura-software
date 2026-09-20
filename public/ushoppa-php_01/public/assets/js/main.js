/**
 * main.js — JS del sitio Ushoppa (vanilla, sin frameworks)
 * -----------------------------------------------------------------------
 * Tres módulos independientes en este archivo:
 *   1) Carrito lateral: abrir/cerrar panel + llamadas AJAX a /actions/*.php
 *   2) Buscador del navbar: redirige a catalog.php?q=...
 *   3) Slider del hero (home): rotación automática + flechas/puntos
 *
 * window.BASE_URL se define inline en includes/footer.php (antes de
 * cargar este script) para que las rutas relativas a /actions funcionen
 * igual desde /public, /public/vendor y /public/admin.
 */

/* ─── CARRITO LATERAL (AJAX contra /actions/*.php) ──────────────────── */
const cartOverlay = document.getElementById('cartOverlay');
const cartSidebar = document.getElementById('cartSidebar');

function openCart() { cartOverlay?.classList.add('open'); cartSidebar?.classList.add('open'); }
function closeCart() { cartOverlay?.classList.remove('open'); cartSidebar?.classList.remove('open'); }

// Cualquier elemento con data-open-cart / data-close-cart dispara el panel
// (botón "Carrito" del navbar, botón X del panel, click en el overlay oscuro).
document.querySelectorAll('[data-open-cart]').forEach(el => el.addEventListener('click', openCart));
document.querySelectorAll('[data-close-cart]').forEach(el => el.addEventListener('click', closeCart));
cartOverlay?.addEventListener('click', closeCart);

/**
 * Vuelve a pedir el HTML del carrito a actions/cart-render.php y
 * reemplaza el contenido de #cartBody / #cartFooterSlot, sin recargar
 * la página. La respuesta viene en 3 partes separadas por "<!--SPLIT-->"
 * (ver el PHP de cart-render.php): cuerpo, resumen/total y contador.
 * Al final vuelve a enganchar los eventos de +/- y quitar, porque el
 * HTML se reemplazó por completo (los listeners viejos ya no existen).
 */
async function refreshCartSidebar() {
  const res = await fetch(window.BASE_URL + 'actions/cart-render.php?base=' + encodeURIComponent(window.BASE_URL));
  const html = await res.text();
  const body = document.getElementById('cartBody');
  const footer = document.getElementById('cartFooterSlot');
  const parsed = html.split('<!--SPLIT-->');
  if (body) body.innerHTML = parsed[0] ?? '';
  if (footer) footer.innerHTML = parsed[1] ?? '';
  document.querySelectorAll('.cart-count').forEach(c => c.textContent = parsed[2] ?? '0');
  attachCartLineEvents();
}

/**
 * Engancha los botones +/- (data-qty-change) y quitar (data-remove-item)
 * dentro del carrito lateral. Se llama al cargar la página y de nuevo
 * cada vez que refreshCartSidebar() reemplaza el HTML del carrito.
 */
function attachCartLineEvents() {
  document.querySelectorAll('[data-qty-change]').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.id, delta = parseInt(btn.dataset.qtyChange, 10);
      const qty = parseInt(btn.dataset.currentQty, 10) + delta;
      await fetch(window.BASE_URL + 'actions/cart-update.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: `id=${id}&qty=${qty}` });
      refreshCartSidebar();
    });
  });
  document.querySelectorAll('[data-remove-item]').forEach(btn => {
    btn.addEventListener('click', async () => {
      await fetch(window.BASE_URL + 'actions/cart-remove.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: `id=${btn.dataset.removeItem}` });
      refreshCartSidebar();
    });
  });
}

/**
 * Agrega un producto al carrito (llamado desde los botones "+ Carrito"
 * de las tarjetas de producto y el detalle). Tras confirmar en servidor,
 * refresca el panel y lo abre automáticamente; además da feedback visual
 * momentáneo en el botón clickeado (✓ verde por 1.2s).
 */
async function addToCart(id, qty, btn) {
  await fetch(window.BASE_URL + 'actions/cart-add.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: `id=${id}&qty=${qty}` });
  await refreshCartSidebar();
  openCart();
  if (btn) {
    const original = btn.textContent;
    btn.textContent = '✓'; btn.classList.add('added');
    setTimeout(() => { btn.textContent = original; btn.classList.remove('added'); }, 1200);
  }
}

// Delegación simple: cualquier botón con data-add-to-cart="ID" dispara
// addToCart(). stopPropagation() evita que el clic también active el
// <a> que envuelve toda la tarjeta de producto (llevaría al detalle).
document.querySelectorAll('[data-add-to-cart]').forEach(btn => {
  btn.addEventListener('click', (e) => {
    e.preventDefault(); e.stopPropagation();
    if (btn.disabled) return;
    addToCart(btn.dataset.addToCart, btn.dataset.qty || 1, btn);
  });
});

// Enganche inicial de los botones +/- y quitar que ya vinieron
// renderizados desde el servidor (footer.php) al cargar la página.
attachCartLineEvents();

/* ─── BÚSQUEDA (redirige al catálogo) ───────────────────────────────── */
// No hay autocompletado/AJAX: al presionar Enter simplemente navega a
// catalog.php con el término en ?q=, que hace la búsqueda en servidor.
const searchInput = document.getElementById('searchInput');
searchInput?.addEventListener('keydown', e => {
  if (e.key === 'Enter') {
    e.preventDefault();
    window.location.href = 'catalog.php?q=' + encodeURIComponent(searchInput.value);
  }
});

/* ─── HERO SLIDER (solo en index.php) ───────────────────────────────── */
// IIFE para no filtrar variables (current, timer, etc.) al scope global.
// Si la página no tiene .hero-slide (cualquier página que no sea home),
// termina inmediatamente sin hacer nada.
(function heroSlider() {
  const slides = document.querySelectorAll('.hero-slide');
  const dots = document.querySelectorAll('.hero-dots button');
  if (!slides.length) return;
  let current = 0, timer;

  function show(i) {
    slides.forEach((s, idx) => s.classList.toggle('active', idx === i));
    dots.forEach((d, idx) => d.classList.toggle('active', idx === i));
    current = i;
  }
  function next() { show((current + 1) % slides.length); }
  // Reinicia el temporizador de auto-avance; se llama tanto al arrancar
  // como cada vez que el usuario interactúa manualmente (flecha/punto),
  // para que no cambie de slide justo después de una acción manual.
  function restart() { clearInterval(timer); timer = setInterval(next, 5200); }

  dots.forEach((d, i) => d.addEventListener('click', () => { show(i); restart(); }));
  document.querySelector('.hero-arrow.left')?.addEventListener('click', () => { show((current - 1 + slides.length) % slides.length); restart(); });
  document.querySelector('.hero-arrow.right')?.addEventListener('click', () => { next(); restart(); });

  restart();
})();
