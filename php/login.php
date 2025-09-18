<?php
session_start();
if (isset($_SESSION['usuario'])) {
  header("Location: dashboard.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Iniciar Sesión - Alaska</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <link rel="stylesheet" href="../assets/css/login.css">
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap"
    rel="stylesheet" />
  <link rel="shortcut icon" href="../img/alaska-ico.ico" type="image/x-icon">

</head>

<body>
  <!-- Header -->
  <header class="cabecera-principal">
    <div class="contenedor contenedor-cabecera">
      <div class="logo">
        <div class="contenedor-logo">
          <div class="contenedor-imagen-logo">
            <img src="../img/logo.jpg" alt="Logo Alaska" class="img-logo" />
          </div>
          <h1>ALASKA</h1>
        </div>
      </div>
      <nav class="navegacion-principal">
        <button class="boton-menu-movil" aria-label="Abrir menú">
          <i class="fas fa-bars"></i>
        </button>
        <ul class="lista-navegacion">
          <li><a href="../index.html#inicio">Inicio</a></li>
          <li><a href="../index.html#nosotros">Nosotros</a></li>
          <li><a href="../html/contacto.html">Contacto</a></li>
          <li><a href="../html/citas.html">Citas</a></li>
          <li><a href="../html/blog.html">Blog</a></li>
          <li><a href="../index.html#registro" class="boton-nav">Registrarse</a></li>
          <li><a href="dashboard.php" class="inicial-circulo">U</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <!-- Main -->
  <main>
    <div class="login-container">
      <h2>Iniciar Sesión</h2>
      <?php if (isset($_GET['error'])): ?>
        <div class="error-message">
          <?php
          if ($_GET['error'] == 'credenciales') {
            echo 'Usuario o contraseña incorrectos';
          } elseif ($_GET['error'] == 'vacio') {
            echo 'Por favor complete todos los campos';
          }
          ?>
        </div>
      <?php endif; ?>
      <form action="validar_login.php" method="POST">
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

  <!-- Footer -->
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
            <li><a href="../index.html#inicio">Inicio</a></li>
            <li><a href="../index.html#nosotros">Nosotros</a></li>
            <li><a href="../index.html#registro">Registro</a></li>
            <li><a href="../html/blog.html">Blog</a></li>
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

  <!-- Scripts -->
  <script src="../views/MenuView.js"></script>
  <script src="../views/ButtonView.js"></script>
  <script src="../views/FormView.js"></script>
  <script src="../assets/js/app.js"></script>
</body>

</html>