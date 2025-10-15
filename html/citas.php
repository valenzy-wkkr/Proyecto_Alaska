<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/core/Autoloader.php';
use App\Core\Database;
use App\Models\Pet;

$loggedIn = isset($_SESSION['usuario_id']);

// Obtener datos del usuario si está logueado
$fotoPerfil = '';
$nombreUsuario = 'Usuario';
$mascotas = [];

if ($loggedIn) {
  $usuarioId = $_SESSION['usuario_id'];
  $nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';
  
  try {
    $db = Database::getConnection();
    
    // Obtener foto de perfil
    $stmt = $db->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
      $fotoPerfil = $row['foto_perfil'] ?? '';
    }
    
    // Obtener mascotas del usuario
    $petModel = new Pet();
    $mascotas = $petModel->allByUser($usuarioId);
    
  } catch (Exception $e) {
    $fotoPerfil = '';
    error_log("Error cargando datos del usuario: " . $e->getMessage());
  }
}

// Función para obtener iniciales
function obtenerIniciales($nombre) {
  $palabras = explode(' ', trim($nombre));
  if (count($palabras) >= 2) {
    return strtoupper(substr($palabras[0], 0, 1) . substr($palabras[1], 0, 1));
  }
  return strtoupper(substr($nombre, 0, 2));
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Citas Veterinarias - Alaska</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="../assets/css/citas.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="shortcut icon"
      href="../img/alaska-ico.png"
      type="image/x-icon"
    />
  </head>
  <style>
    .cuerpo-tarjeta .detalle {
      display: flex;
      align-items: baseline; /* align text baselines */
      gap: 8px; /* spacing between title and value */
    }

    .cuerpo-tarjeta .detalle h4 {
      margin: 0; /* remove bottom margin to keep inline */
    }

    /************** */
    .contenedor-imagen-logo {
      width: 80px;
      height: 80px;
      display: flex;
      justify-content: center;
      align-items: center;
      border-radius: 50%;
      overflow: hidden;
      background-color: white;
      padding: 2px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
      transition: var(--transicion);
    }
    
    .no-mascotas-mensaje {
      background: #f8f9fa;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      padding: 20px;
      text-align: center;
      margin: 20px 0;
    }
    
    .no-mascotas-mensaje i {
      font-size: 3rem;
      color: #6c757d;
      margin-bottom: 15px;
      display: block;
    }
    
    .no-mascotas-mensaje h4 {
      color: #495057;
      margin-bottom: 10px;
    }
    
    .no-mascotas-mensaje p {
      color: #6c757d;
      margin-bottom: 15px;
    }
    
    .btn-agregar-mascota {
      background: var(--color-primario);
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      text-decoration: none;
      display: inline-block;
      font-weight: 600;
      transition: background-color 0.3s ease;
    }
    
    .btn-agregar-mascota:hover {
      background: #5a6fd8;
    }
  </style>
  <body>
    <header class="cabecera-principal">
      <div class="contenedor contenedor-cabecera">
        <div class="logo">
          <div class="contenedor-logo">
            <div class="contenedor-imagen-logo">
              <img
                src="../img/alaska.png"
                alt="Logo Alaska"
                class="imagen-logo"
                style="width: 100%; height: 100%; object-fit: contain"
              />
            </div>
            <h1 class="nombre-empresa">Alaska</h1>
          </div>
        </div>
        <nav class="navegacion-principal">
          <button class="boton-menu-movil" aria-label="Abrir menú">
            <i class="fas fa-bars"></i>
          </button>
          <ul class="lista-navegacion">
            <li><a href="../index.php">Inicio</a></li>
            <?php if (!$loggedIn): ?>
            <li><a href="../index.php#nosotros">Nosotros</a></li>
            <?php endif; ?>
            <li><a href="contacto.php">Contacto</a></li>
            <?php if ($loggedIn): ?>
            <li><a href="citas.php" class="activo">Citas</a></li>
            <?php endif; ?>
            <li><a href="blog.php">Blog</a></li>
            <?php if (!$loggedIn): ?>
              <li><a href="../index.php#registro" class="boton-nav">Registrarse</a></li>
            <?php endif; ?>
            <?php if ($loggedIn): ?>
              <li>
                <a href="/Proyecto_Alaska/html/perfil.php" class="inicial-circulo" title="Perfil" aria-label="Perfil">
                  <?php if (!empty($fotoPerfil)): ?>
                    <img src="/Proyecto_Alaska/public/api/usuario.php?action=profile_picture" alt="Perfil" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                  <?php else: ?>
                    <?php echo obtenerIniciales($nombreUsuario); ?>
                  <?php endif; ?>
                </a>
              </li>
            <?php else: ?>
              <li><a href="../public/auth/login.php" class="boton-nav">Iniciar Sesión</a></li>
            <?php endif; ?>
          </ul>
        </nav>
      </div>
    </header>

    <main class="contenido-principal">
      <section class="seccion-citas">
        <div class="contenedor">
          <h2>Citas Veterinarias</h2>
          <p>
            Agenda tu cita de manera fácil y rápida. Nuestros veterinarios
            expertos están listos para cuidar de tu mascota.
          </p>
        </div>

        <div class="contenedor-citas">
          <?php if ($loggedIn): ?>
            <?php if (count($mascotas) > 0): ?>
              <!-- Formulario de Citas -->
              <div class="formulario-citas">
                <h3>Reservar Cita</h3>
                <div id="mensaje-exito" class="mensaje-exito"></div>
                <div id="mensaje-error" class="mensaje-error"></div>

                <form id="formulario-citas" class="formulario-citas" method="POST">
                  <div class="grupo-formulario">
                    <label for="mascota">Seleccionar Mascota</label>
                    <div class="entrada-con-icono">
                      <i class="fas fa-paw"></i>
                      <select id="mascota" name="petId" required>
                        <option value="">Selecciona una de tus mascotas</option>
                        <?php foreach ($mascotas as $mascota): ?>
                          <option value="<?php echo $mascota['id']; ?>">
                            <?php echo htmlspecialchars($mascota['name']); ?> 
                            (<?php echo htmlspecialchars($mascota['species']); ?>)
                            <?php if (!empty($mascota['breed']) && $mascota['breed'] !== 'No especificada'): ?>
                              - <?php echo htmlspecialchars($mascota['breed']); ?>
                            <?php endif; ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <div class="grupo-formulario">
                    <label for="fecha">Fecha y Hora</label>
                    <div class="entrada-con-icono">
                      <i class="fas fa-calendar-alt"></i>
                      <input
                        type="datetime-local"
                        id="fecha"
                        name="appointmentDate"
                        required
                      />
                    </div>
                  </div>

                  <div class="grupo-formulario">
                    <label for="razon">Motivo de la Cita</label>
                    <div class="entrada-con-icono">
                      <i class="fas fa-clipboard-list"></i>
                      <select id="razon" name="reason" required>
                        <option value="">Selecciona un motivo</option>
                        <option value="Revisión general">Revisión general</option>
                        <option value="Vacunación">Vacunación</option>
                        <option value="Desparasitación">Desparasitación</option>
                        <option value="Enfermedad">Enfermedad</option>
                        <option value="Cirugía">Cirugía</option>
                        <option value="Control de salud">Control de salud</option>
                        <option value="Urgencia">Urgencia</option>
                        <option value="Otro">Otro</option>
                      </select>
                    </div>
                  </div>

                  <div class="grupo-formulario">
                    <label for="notas">Notas Adicionales</label>
                    <div class="entrada-con-icono">
                      <i class="fas fa-sticky-note"></i>
                      <textarea
                        id="notas"
                        name="notes"
                        placeholder="Describe los síntomas o cualquier información relevante para el veterinario"
                        rows="4"
                      ></textarea>
                    </div>
                  </div>

                  <button type="submit" class="boton-primario">
                    <i class="fas fa-calendar-check"></i> Reservar Cita
                  </button>
                </form>
              </div>
            <?php else: ?>
              <!-- Mensaje cuando no hay mascotas registradas -->
              <div class="no-mascotas-mensaje">
                <i class="fas fa-paw"></i>
                <h4>No tienes mascotas registradas</h4>
                <p>Para agendar una cita, primero necesitas registrar a tu mascota en tu perfil.</p>
                <a href="perfil.php" class="btn-agregar-mascota">
                  <i class="fas fa-plus"></i> Registrar Mi Mascota
                </a>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <!-- Mensaje para usuarios no logueados -->
            <div class="no-mascotas-mensaje">
              <i class="fas fa-user-lock"></i>
              <h4>Inicia sesión para agendar citas</h4>
              <p>Necesitas tener una cuenta para poder agendar citas veterinarias.</p>
              <a href="../public/auth/login.php" class="btn-agregar-mascota">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
              </a>
            </div>
          <?php endif; ?>

          <!-- Historial de Citas -->
          <div class="historial-citas">
            <h3>Historial de Citas</h3>
            <div id="lista-citas" class="lista-citas">
              <!-- Las citas se cargarán dinámicamente -->
            </div>
          </div>
        </div>
      </section>
    </main>

    <footer class="pie-pagina">
      <div class="contenedor">
        <div class="contenido-footer">
          <div class="columna-footer info-contacto">
            <h3>Contacto</h3>
            <p>
              <i class="fas fa-map-marker-alt"></i> Calle Principal 123, Ciudad
            </p>
            <p><i class="fas fa-phone"></i> +123 456 7890</p>
            <p><i class="fas fa-envelope"></i> info@alaska-mascotas.com</p>
          </div>
          <div class="columna-footer enlaces-rapidos">
            <h3>Enlaces Rápidos</h3>
            <ul>
              <li><a href="../index.php">Inicio</a></li>
              <li><a href="../index.php#contacto">Contacto</a></li>
              <li><a href="citas.php">Citas</a></li>
              <li><a href="blog.php">Blog</a></li>
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

    <script src="../assets/js/citas.js"></script>
    <script src="../views/MenuView.js"></script>
    <script src="../views/ButtonView.js"></script>
    <script src="../views/FormView.js"></script>
    <script src="../assets/js/profile-sync.js"></script>
  </body>
</html>