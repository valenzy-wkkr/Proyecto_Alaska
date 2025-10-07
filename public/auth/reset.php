<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$token = isset($_GET['token']) ? (string)$_GET['token'] : '';
$error = isset($_GET['error']) ? (string)$_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Restablecer Contraseña - Alaska</title>
  <link rel="stylesheet" href="/Proyecto_Alaska/assets/css/style.css" />
  <link rel="stylesheet" href="/Proyecto_Alaska/assets/css/login.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="shortcut icon" href="/Proyecto_Alaska/img/alaska-ico.ico" type="image/x-icon">
</head>
<body>
  <main>
    <div class="login-container">
      <h2>Restablecer Contraseña</h2>
      <?php if ($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form action="/Proyecto_Alaska/public/api/auth/reset.php" method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <div class="form-group">
          <label for="clave">Nueva contraseña (mínimo 8 caracteres):</label>
          <input type="password" id="clave" name="clave" minlength="8" required>
        </div>
        <div class="form-group">
          <label for="clave2">Confirmar contraseña:</label>
          <input type="password" id="clave2" name="clave2" minlength="8" required>
        </div>
        <button type="submit" class="btn-login">Guardar</button>
      </form>
      <p><a href="/Proyecto_Alaska/public/auth/login.php">Volver al inicio de sesión</a></p>
    </div>
  </main>
</body>
</html>
