<?php
$BASE_URL = '';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Crear Cuenta';
$errors = [];
$success = false;
$form = ['name' => '', 'email' => '', 'role' => 'cliente'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['name']  = trim($_POST['name'] ?? '');
    $form['email'] = trim($_POST['email'] ?? '');
    $form['role']  = $_POST['role'] === 'vendedor' ? 'vendedor' : 'cliente';
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm'] ?? '';

    if ($form['name'] === '') $errors['name'] = 'Requerido';
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Correo inválido';
    if (strlen($password) < 4) $errors['password'] = 'Mínimo 4 caracteres';
    if ($password !== $confirm) $errors['confirm'] = 'Las contraseñas no coinciden';

    if (empty($errors)) {
        $chk = $pdo->prepare('SELECT id FROM usuarios WHERE correo = ?');
        $chk->execute([$form['email']]);
        if ($chk->fetch()) {
            $errors['email'] = 'Ese correo ya está registrado';
        } else {
            // Nota: password en texto plano solo para fines académicos/demo.
            // En producción usar password_hash()/password_verify().
            $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, correo, password, rol, estado) VALUES (?,?,?,?,"activo")');
            $stmt->execute([$form['name'], $form['email'], $password, $form['role']]);
            $success = true;
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<div class="centered-form-wrap">
<?php if ($success): ?>
  <div class="card centered-form" style="max-width:420px;text-align:center;">
    <div style="width:64px;height:64px;border-radius:50%;background:rgba(34,197,94,.12);border:2px solid #22c55e;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
      <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="#22c55e" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    </div>
    <h2 class="serif" style="font-size:26px;font-weight:700;margin-bottom:8px;">Cuenta creada</h2>
    <p style="color:#777;margin-bottom:24px;">Bienvenido a Ushoppa. Ya puedes iniciar sesión.</p>
    <a href="login.php" class="btn-gold" style="padding:12px 32px;">Iniciar Sesión →</a>
  </div>
<?php else: ?>
  <div class="card centered-form" style="max-width:460px;">
    <div class="form-header">
      <div class="serif gold" style="font-size:14px;font-weight:700;letter-spacing:.1em;margin-bottom:10px;">USHOPPA</div>
      <h1 class="serif" style="font-size:28px;font-weight:700;margin-bottom:6px;">Crear Cuenta</h1>
      <p style="color:#666;font-size:13px;">Únete al marketplace de NexoMarket</p>
    </div>
    <form method="post">
      <div style="display:grid;gap:14px;">
        <div>
          <label class="label">Nombre completo</label>
          <input class="input <?= isset($errors['name'])?'error':'' ?>" name="name" placeholder="Tu nombre completo" value="<?= e($form['name']) ?>">
          <?php if (isset($errors['name'])): ?><div class="field-error"><?= $errors['name'] ?></div><?php endif; ?>
        </div>
        <div>
          <label class="label">Correo electrónico</label>
          <input class="input <?= isset($errors['email'])?'error':'' ?>" type="email" name="email" placeholder="tu@correo.com" value="<?= e($form['email']) ?>">
          <?php if (isset($errors['email'])): ?><div class="field-error"><?= $errors['email'] ?></div><?php endif; ?>
        </div>
        <div>
          <label class="label">Contraseña</label>
          <input class="input <?= isset($errors['password'])?'error':'' ?>" type="password" name="password" placeholder="Mínimo 4 caracteres">
          <?php if (isset($errors['password'])): ?><div class="field-error"><?= $errors['password'] ?></div><?php endif; ?>
        </div>
        <div>
          <label class="label">Confirmar contraseña</label>
          <input class="input <?= isset($errors['confirm'])?'error':'' ?>" type="password" name="confirm" placeholder="Repite tu contraseña">
          <?php if (isset($errors['confirm'])): ?><div class="field-error"><?= $errors['confirm'] ?></div><?php endif; ?>
        </div>
        <div>
          <label class="label">Tipo de cuenta</label>
          <div style="display:flex;gap:10px;">
            <?php foreach (['cliente','vendedor'] as $r): ?>
              <label class="role-choice <?= $form['role']===$r?'active':'' ?>" style="flex:1;padding:11px;border-radius:9px;font-size:13px;font-weight:600;text-align:center;cursor:pointer;text-transform:capitalize;">
                <input type="radio" name="role" value="<?= $r ?>" <?= $form['role']===$r?'checked':'' ?> style="display:none;" onclick="document.querySelectorAll('.role-choice').forEach(l=>l.classList.remove('active'));this.parentElement.classList.add('active');"><?= $r ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <button type="submit" class="btn-gold" style="width:100%;padding:13px;font-size:15px;margin-top:24px;margin-bottom:16px;">Crear Cuenta</button>
    </form>
    <div style="text-align:center;font-size:13px;color:#555;">
      Ya tienes cuenta? <a href="login.php" class="gold" style="font-weight:600;">Inicia sesión</a>
    </div>
  </div>
<?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
