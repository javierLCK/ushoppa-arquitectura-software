<?php
$BASE_URL = '';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Iniciar Sesión';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE correo = ?');
    $stmt->execute([$email]);
    $found = $stmt->fetch();

    if (!$found || $found['password'] !== $pass) {
        $error = 'Correo o contraseña incorrectos.';
    } elseif ($found['estado'] !== 'activo') {
        $error = 'Tu cuenta está suspendida. Contacta soporte.';
    } else {
        $_SESSION['user'] = ['id' => $found['id'], 'nombre' => $found['nombre'], 'correo' => $found['correo'], 'rol' => $found['rol']];
        if ($found['rol'] === 'vendedor') header('Location: vendor/dashboard.php');
        elseif ($found['rol'] === 'admin') header('Location: admin/dashboard.php');
        else header('Location: index.php');
        exit;
    }
}

require __DIR__ . '/includes/header.php';
?>
<div class="centered-form-wrap">
  <div class="card centered-form" style="max-width:420px;">
    <div class="form-header">
      <div class="serif gold" style="font-size:14px;font-weight:700;letter-spacing:.1em;margin-bottom:10px;">USHOPPA</div>
      <h1 class="serif" style="font-size:28px;font-weight:700;margin-bottom:6px;">Iniciar Sesión</h1>
      <p style="color:#666;font-size:13px;">Accede a tu cuenta NexoMarket</p>
    </div>

    <?php if ($error): ?><div class="alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="post">
      <div style="margin-bottom:14px;">
        <label class="label">Correo electrónico</label>
        <input class="input" type="email" name="email" id="emailField" placeholder="tu@correo.com" value="<?= e($_POST['email'] ?? '') ?>" required>
      </div>
      <div style="margin-bottom:24px;">
        <label class="label">Contraseña</label>
        <input class="input" type="password" name="password" id="passField" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn-gold" style="width:100%;padding:13px;font-size:15px;margin-bottom:16px;">Iniciar Sesión</button>
    </form>

    <div style="text-align:center;font-size:13px;color:#555;">
      No tienes cuenta? <a href="register.php" class="gold" style="font-weight:600;">Regístrate gratis</a>
    </div>

    <div style="margin-top:24px;padding:14px;background:#111;border-radius:8px;font-size:12px;color:#555;">
      <div style="font-weight:700;color:#666;margin-bottom:6px;">Credenciales de prueba:</div>
      <?php foreach ([['cliente@test.com','Cliente'],['vendedor@test.com','Vendedor'],['admin@test.com','Admin']] as [$em,$rol]): ?>
        <div style="cursor:pointer;margin-bottom:3px;" onclick="document.getElementById('emailField').value='<?= $em ?>';document.getElementById('passField').value='1234';">
          <span class="gold"><?= $em ?></span> — <?= $rol ?> <span style="color:#444;">(password: 1234)</span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
