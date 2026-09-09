// ─── CARRITO LATERAL (AJAX contra /actions/*.php) ────────────
const cartOverlay = document.getElementById('cartOverlay');
const cartSidebar = document.getElementById('cartSidebar');

function openCart() { cartOverlay?.classList.add('open'); cartSidebar?.classList.add('open'); }
function closeCart() { cartOverlay?.classList.remove('open'); cartSidebar?.classList.remove('open'); }

document.querySelectorAll('[data-open-cart]').forEach(el => el.addEventListener('click', openCart));
document.querySelectorAll('[data-close-cart]').forEach(el => el.addEventListener('click', closeCart));
cartOverlay?.addEventListener('click', closeCart);

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

document.querySelectorAll('[data-add-to-cart]').forEach(btn => {
  btn.addEventListener('click', (e) => {
    e.preventDefault(); e.stopPropagation();
    if (btn.disabled) return;
    addToCart(btn.dataset.addToCart, btn.dataset.qty || 1, btn);
  });
});

attachCartLineEvents();

// ─── BÚSQUEDA (redirige al catálogo) ──────────────────────────
const searchInput = document.getElementById('searchInput');
searchInput?.addEventListener('keydown', e => {
  if (e.key === 'Enter') {
    e.preventDefault();
    window.location.href = 'catalog.php?q=' + encodeURIComponent(searchInput.value);
  }
});

// ─── HERO SLIDER ───────────────────────────────────────────────
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
  function restart() { clearInterval(timer); timer = setInterval(next, 5200); }

  dots.forEach((d, i) => d.addEventListener('click', () => { show(i); restart(); }));
  document.querySelector('.hero-arrow.left')?.addEventListener('click', () => { show((current - 1 + slides.length) % slides.length); restart(); });
  document.querySelector('.hero-arrow.right')?.addEventListener('click', () => { next(); restart(); });

  restart();
})();
