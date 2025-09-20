<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (isset($_SESSION['usuario'])) {
  header('Location: /Proyecto_Alaska4/public/dashboard.php');
  exit();
}
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Iniciar Sesión - Alaska</title>
  <link rel="stylesheet" href="/Proyecto_Alaska4/assets/css/style.css" />
  <link rel="stylesheet" href="/Proyecto_Alaska4/assets/css/login.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="shortcut icon" href="/Proyecto_Alaska4/img/alaska-ico.ico" type="image/x-icon">
</head>
<body>
  <header class="cabecera-principal">
    <div class="contenedor contenedor-cabecera">
      <div class="logo">
        <div class="contenedor-logo">
          <div class="contenedor-imagen-logo">
            <img src="/Proyecto_Alaska4/img/logo.jpg" alt="Logo Alaska" class="img-logo" />
          </div>
          <h1>ALASKA</h1>
        </div>
      </div>
      <nav class="navegacion-principal">
        <button class="boton-menu-movil" aria-label="Abrir menú">
          <i class="fas fa-bars"></i>
        </button>
        <ul class="lista-navegacion">
          <li><a href="/Proyecto_Alaska4/index.html#inicio">Inicio</a></li>
          <li><a href="/Proyecto_Alaska4/index.html#nosotros">Nosotros</a></li>
          <li><a href="/Proyecto_Alaska4/html/contacto.html">Contacto</a></li>
          <li><a href="/Proyecto_Alaska4/html/citas.html">Citas</a></li>
          <li><a href="/Proyecto_Alaska4/html/blog.html">Blog</a></li>
          <!-- <li><a href="/index.html#registro" class="boton-nav">Registrarse</a></li> -->
          <!-- <li><a href="/Proyecto_Alaska4/public/dashboard.php" class="inicial-circulo">U</a></li> -->
        </ul>
      </nav>
    </div>
  </header>

  <main>
    <div class="login-container">
      <h2>Iniciar Sesión</h2>
      <?php if ($error): ?>
        <div class="error-message">
          <?php if ($error === 'credenciales'): ?>
            Usuario o contraseña incorrectos
          <?php elseif ($error === 'vacio'): ?>
            Por favor complete todos los campos
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <form action="/Proyecto_Alaska4/public/api/auth/login.php" method="POST">
        <div class="form-group">
          <label for="correo">Correo electrónico:</label>
          <input type="email" id="correo" name="correo" required>
        </div>
        <div class="form-group">
          <label for="clave">Contraseña:</label>
          <input type="password" id="clave" name="clave" required>
        </div>
        <button type="submit" class="btn-login">Iniciar Sesión</button>
      </form>
      <a href="#">¿Olvidaste tu contraseña?</a>
    </div>
  </main>

  <footer class="pie-pagina">
    <div class="contenedor">
      <div class="contenido-footer">
        <div class="columna-footer info-contacto">
          <h3>Contacto</h3>
          <p><i class="fas fa-map-marker-alt"></i> Calle Principal 123, Ciudad</p>
          <p><i class="fas fa-phone"></i> +123 456 7890</p>
          <p><i class="fas fa-envelope"></i> info@alaska-mascotas.com</p>
        </div>
        <div class="columna-footer enlaces-rapidos">
          <h3>Enlaces Rápidos</h3>
          <ul>
            <li><a href="/Proyecto_Alaska4/index.html#inicio">Inicio</a></li>
            <li><a href="/Proyecto_Alaska4/index.html#nosotros">Nosotros</a></li>
            <li><a href="/Proyecto_Alaska4/index.html#registro">Registro</a></li>
            <li><a href="/Proyecto_Alaska4/html/blog.html">Blog</a></li>
          </ul>
        </div>
        <div class="columna-footer redes-sociales">
          <h3>Síguenos</h3>
          <div class="iconos-sociales">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
          </div>
        </div>
      </div>
      <div class="copyright">
        <p>&copy; 2024 Alaska - Cuidado de Mascotas. Todos los derechos reservados.</p>
      </div>
    </div>
  </footer>

  <script src="/Proyecto_Alaska4/views/MenuView.js"></script>
  <script src="/Proyecto_Alaska4/views/ButtonView.js"></script>
  <script src="/Proyecto_Alaska4/views/FormView.js"></script>
  <script src="/Proyecto_Alaska4/assets/js/app.js"></script>
</body>
</html>

