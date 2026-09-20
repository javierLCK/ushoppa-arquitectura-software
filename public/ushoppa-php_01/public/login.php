<?php
/**
 * login.php — Inicio de sesión
 * -----------------------------------------------------------------------
 * Valida correo + contraseña contra la tabla usuarios usando
 * password_verify() (las contraseñas se guardan con password_hash(),
 * nunca en texto plano). Si el usuario está 'suspendido' o 'inactivo',
 * se bloquea el acceso aunque la contraseña sea correcta.
 *
 * Tras un login exitoso se regenera el ID de sesión (session_regenerate_id)
 * para evitar session fixation, y se redirige según el rol:
 * vendedor -> vendor/dashboard.php, admin -> admin/dashboard.php,
 * cliente -> index.php.
 */

// '' porque login.php vive en la raíz de /public (ver includes/header.php,
// que usa BASE_URL para armar rutas de assets y navegación).
$BASE_URL = '';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Iniciar Sesión'; // se muestra en <title> dentro de header.php
$error = ''; // mensaje de error a mostrar en el formulario, si aplica

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Corta la ejecución con 403 si falta o no coincide el token del
    // <input hidden name="csrf"> (ver csrf_field() más abajo en el form).
    csrf_check();

    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    // Se busca por correo (columna UNIQUE); consulta preparada, sin
    // concatenar el input del usuario al SQL.
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE correo = ?');
    $stmt->execute([$email]);
    $found = $stmt->fetch();

    // password_verify() compara el texto plano ingresado contra el hash
    // bcrypt guardado en BD (nunca se compara contraseña en texto plano).
    if (!$found || !password_verify($pass, $found['password'])) {
        $error = 'Correo o contraseña incorrectos.';
    } elseif ($found['estado'] !== 'activo') {
        // Bloquea también a cuentas 'inactivo' o 'suspendido', aunque la
        // contraseña sea correcta (ver admin/toggle-user.php).
        $error = 'Tu cuenta está suspendida. Contacta soporte.';
    } else {
        // Nuevo ID de sesión al autenticar: evita que un atacante que
        // haya fijado un session ID antes del login lo reutilice después
        // (session fixation).
        session_regenerate_id(true);

        // Solo se guardan en sesión los campos que se necesitan mostrar
        // (nombre, rol) o comparar (id, correo) en el resto del sitio —
        // nunca el hash de la contraseña.
        $_SESSION['user'] = [
            'id'     => $found['id'],
            'nombre' => $found['nombre'],
            'correo' => $found['correo'],
            'rol'    => $found['rol'],
        ];

        // Redirección post-login según rol: cada tipo de cuenta cae en
        // su panel correspondiente (ver require_role() en functions.php).
        if ($found['rol'] === 'vendedor') header('Location: vendor/dashboard.php');
        elseif ($found['rol'] === 'admin') header('Location: admin/dashboard.php');
        else header('Location: index.php');
        exit; // exit obligatorio tras header('Location: ...') para no seguir renderizando el resto del archivo
    }
}

require __DIR__ . '/includes/header.php'; // navbar + <main> abierto
?>
<div class="centered-form-wrap">
  <div class="card centered-form" style="max-width:420px;">
    <div class="form-header">
      <div class="serif gold" style="font-size:14px;font-weight:700;letter-spacing:.1em;margin-bottom:10px;">USHOPPA</div>
      <h1 class="serif" style="font-size:28px;font-weight:700;margin-bottom:6px;">Iniciar Sesión</h1>
      <p style="color:#666;font-size:13px;">Accede a tu cuenta NexoMarket</p>
    </div>

    <!-- Solo se imprime si hubo un intento de login fallido (ver bloque POST arriba) -->
    <?php if ($error): ?><div class="alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="post">
      <!-- Token CSRF oculto; sin esto, csrf_check() de arriba rechaza el POST con 403 -->
      <?= csrf_field() ?>
      <div style="margin-bottom:14px;">
        <label class="label">Correo electrónico</label>
        <!-- value="<?= e($_POST['email'] ?? '') ?>": si el login falla, no se pierde
             lo que el usuario ya había escrito en el campo de correo. -->
        <input class="input" type="email" name="email" id="emailField" placeholder="tu@correo.com" value="<?= e($_POST['email'] ?? '') ?>" required>
      </div>
      <div style="margin-bottom:24px;">
        <label class="label">Contraseña</label>
        <!-- La contraseña nunca se re-imprime en value, ni siquiera si el login falla (por seguridad) -->
        <input class="input" type="password" name="password" id="passField" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn-gold" style="width:100%;padding:13px;font-size:15px;margin-bottom:16px;">Iniciar Sesión</button>
    </form>

    <div style="text-align:center;font-size:13px;color:#555;">
      No tienes cuenta? <a href="register.php" class="gold" style="font-weight:600;">Regístrate gratis</a>
    </div>

    <!-- Bloque solo para demo/pruebas académicas: autocompleta el formulario
         con las 3 cuentas semilla de database/ushoppa.sql al hacer clic.
         No es una vulnerabilidad porque las credenciales son públicas y
         conocidas de antemano (datos de prueba, no cuentas reales). -->
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
