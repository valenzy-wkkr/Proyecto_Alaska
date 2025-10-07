<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$success = isset($_GET['success']) ? (int)$_GET['success'] : 0;
$token = isset($_GET['token']) ? $_GET['token'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Recuperar Contraseña - Alaska</title>
  <link rel="stylesheet" href="/Proyecto_Alaska/assets/css/style.css" />
  <link rel="stylesheet" href="/Proyecto_Alaska/assets/css/login.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="shortcut icon" href="/Proyecto_Alaska/img/alaska-ico.ico" type="image/x-icon">
</head>
<body>
  <main>
    <div class="login-container">
      <h2>Recuperar Contraseña</h2>
      <?php if ($success === 1): ?>
        <div class="success-message">
          Hemos generado un enlace para restablecer tu contraseña.
        </div>
        <?php if ($token): ?>
          <p><strong>Enlace de restablecimiento (solo para desarrollo):</strong></p>
          <p><a href="/Proyecto_Alaska/public/auth/reset.php?token=<?= htmlspecialchars($token) ?>">Abrir formulario de restablecimiento</a></p>
        <?php endif; ?>
      <?php endif; ?>
      <form action="/Proyecto_Alaska/public/api/auth/forgot.php" method="POST">
        <div class="form-group">
          <label for="correo">Correo electrónico:</label>
          <input type="email" id="correo" name="correo" required>
        </div>
        <button type="submit" class="btn-login">Enviar enlace</button>
      </form>
      <p><a href="/Proyecto_Alaska/public/auth/login.php">Volver al inicio de sesión</a></p>
    </div>
  </main>
</body>
</html>
