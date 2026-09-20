<?php
$BASE_URL = '';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Contacto';

$sent = false;
$errors = [];
$form = ['name' => '', 'email' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $form['name'] = trim($_POST['name'] ?? '');
    $form['email'] = trim($_POST['email'] ?? '');
    $form['message'] = trim($_POST['message'] ?? '');

    if ($form['name'] === '') $errors['name'] = 'Requerido';
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Correo inválido';
    if ($form['message'] === '') $errors['message'] = 'Requerido';

    // No hay servidor de correo configurado en este entorno académico: el
    // mensaje no se envía realmente, solo se valida y se confirma en pantalla.
    if (empty($errors)) $sent = true;
}

require __DIR__ . '/includes/header.php';
?>
<div class="page-wrap" style="max-width:640px;">
  <a href="index.php" class="btn-ghost" style="display:inline-flex;align-items:center;gap:6px;margin-bottom:28px;font-size:13px;">
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg> Volver al inicio
  </a>

  <?php if ($sent): ?>
    <div class="card" style="padding:40px;text-align:center;">
      <div style="width:60px;height:60px;border-radius:50%;background:rgba(34,197,94,.12);border:2px solid #22c55e;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
        <svg width="30" height="30" fill="none" viewBox="0 0 24 24" stroke="#22c55e" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
      </div>
      <h2 class="serif" style="font-size:26px;margin-bottom:8px;">Mensaje enviado</h2>
      <p style="color:#777;margin-bottom:24px;">Gracias por escribirnos, <?= e($form['name']) ?>. Te responderemos a <?= e($form['email']) ?> a la brevedad.</p>
      <a href="index.php" class="btn-gold" style="padding:12px 32px;">Volver al inicio →</a>
    </div>
  <?php else: ?>
    <h1 class="section-title serif" style="font-size:34px;margin-bottom:8px;">Contacto</h1>
    <p style="color:#777;font-size:14px;margin-bottom:28px;">¿Tienes dudas, sugerencias o problemas con tu pedido? Escríbenos.</p>
    <div class="card" style="padding:32px;">
      <form method="post">
        <?= csrf_field() ?>
        <div style="display:grid;gap:16px;">
          <div>
            <label class="label">Nombre</label>
            <input class="input <?= isset($errors['name'])?'error':'' ?>" name="name" placeholder="Tu nombre" value="<?= e($form['name']) ?>">
            <?php if (isset($errors['name'])): ?><div class="field-error"><?= $errors['name'] ?></div><?php endif; ?>
          </div>
          <div>
            <label class="label">Correo electrónico</label>
            <input class="input <?= isset($errors['email'])?'error':'' ?>" type="email" name="email" placeholder="tu@correo.com" value="<?= e($form['email']) ?>">
            <?php if (isset($errors['email'])): ?><div class="field-error"><?= $errors['email'] ?></div><?php endif; ?>
          </div>
          <div>
            <label class="label">Mensaje</label>
            <textarea class="input <?= isset($errors['message'])?'error':'' ?>" name="message" rows="5" placeholder="Cuéntanos en qué te podemos ayudar..."><?= e($form['message']) ?></textarea>
            <?php if (isset($errors['message'])): ?><div class="field-error"><?= $errors['message'] ?></div><?php endif; ?>
          </div>
        </div>
        <button type="submit" class="btn-gold" style="width:100%;padding:13px;font-size:15px;margin-top:24px;">Enviar mensaje</button>
      </form>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
