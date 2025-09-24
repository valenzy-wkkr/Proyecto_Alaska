<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$loggedIn = isset($_SESSION['usuario_id']);
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Alaska - Cuidado de Mascotas</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/css/usuario.css" />
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
    <link rel="shortcut icon" href="img/alaska-ico.ico" type="image/x-icon" />
  </head>
  <style>
    /* Contenedor principal */
    .resaltado-registro {
      position: relative;
      display: inline-block;
      border-radius: var(--radio-borde-grande);
      overflow: hidden;
      box-shadow: var(--sombra-caja);
      outline: 2px solid rgba(255, 255, 255, 0.22);
      outline-offset: 6px;
      transition: box-shadow 0.3s ease, outline-color 0.3s ease;
    }

    /* Overlay con degradado dinámico */
    .resaltado-registro::before {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(
        135deg,
        rgba(91, 140, 90, 0.18) 0%,
        rgba(255, 112, 67, 0.18) 100%
      );
      opacity: 0;
      transition: opacity 0.4s ease, backdrop-filter 0.4s ease;
      pointer-events: none;
      backdrop-filter: blur(0px);
    }

    .resaltado-registro:hover::before {
      opacity: 0.4;
      backdrop-filter: blur(3px); /* efecto cristalino */
    }

    /* Efecto glow en el contorno */
    .resaltado-registro:hover,
    .resaltado-registro:focus-within {
      box-shadow: 0 8px 20px rgba(255, 112, 67, 0.35),
        0 0 12px rgba(91, 140, 90, 0.3);
      outline-color: rgba(255, 255, 255, 0.45);
    }

    /* Imagen con zoom suave */
    .resaltado-registro img {
      display: block;
      transition: transform 0.4s ease;
    }

    .resaltado-registro:hover img {
      transform: scale(1.05); /* zoom sutil */
    }
  </style>
  <body>
    <!-- Página de inicio -->
    <header class="cabecera-principal">
      <div class="contenedor contenedor-cabecera">
        <div class="logo">
          <div class="contenedor-logo">
            <div class="contenedor-imagen-logo">
              <img src="img/logo.jpg" alt="Logo Alaska" class="img-logo" />
            </div>
            <h1>ALASKA</h1>
          </div>
        </div>
        <nav class="navegacion-principal">
          <button class="boton-menu-movil" aria-label="Abrir menú">
            <i class="fas fa-bars"></i>
          </button>
          <ul class="lista-navegacion">
            <li><a href="#inicio">Inicio</a></li>
            <li><a href="#nosotros">Nosotros</a></li>
            <li><a href="html/contacto.php">Contacto</a></li>
            <?php if ($loggedIn): ?>
              <li><a href="html/citas.html">Citas</a></li>
            <?php endif; ?>
            <li><a href="html/blog.php">Blog</a></li>
            <?php if (!$loggedIn): ?>
            <li><a href="#registro" class="boton-nav">Registrarse</a></li>
            <?php endif; ?>
            <?php if ($loggedIn): ?>
            <li>
              <a href="public/dashboard.php" class="inicial-circulo">U</a>
            </li>
            <?php endif; ?>
          </ul>
        </nav>
      </div>
    </header>

    <main>
      <!-- Sección Hero -->
      <section id="inicio" class="hero">
        <div class="contenedor">
          <div class="contenido-hero">
            <h2>¡Cuida bien de tus animales!</h2>
            <p>
              Esta aplicación está diseñada para ayudar a los dueños de mascotas
              a ser responsables y atender las necesidades de sus compañeros
              peludos.
            </p>
            <div class="botones-hero">
              <a href="#registro" class="boton-primario">Regístrate Ahora</a>
              <a href="#nosotros" class="boton-secundario">Conoce Más</a>
            </div>
          </div>
        </div>
        <div class="superposicion-hero"></div>
      </section>

      <!-- Quiénes Somos -->
      <section id="nosotros" class="seccion-nosotros">
        <div class="contenedor">
          <div class="encabezado-seccion">
            <h2>¿Quiénes somos?</h2>
            <p class="subtitulo-seccion">
              Esta aplicación está diseñada para ayudar a los dueños de mascotas
              a ser responsables y atender las necesidades de sus compañeros
              peludos.
            </p>
          </div>

          <h3 class="titulo-caracteristicas">¡Te ayudamos con!</h3>
          <div class="caracteristicas">
            <div class="caracteristica">
              <div class="icono-caracteristica">
                <i class="fas fa-notes-medical"></i>
              </div>
              <h4>1. Registro de Datos Veterinarios</h4>
              <p>
                Los dueños pueden ingresar información sobre las visitas al
                veterinario, vacunas, desparasitaciones y tratamientos médicos.
                Esto ayuda a mantener un historial completo de la salud de la
                mascota.
              </p>
            </div>
            <div class="caracteristica">
              <div class="icono-caracteristica">
                <i class="fas fa-bell"></i>
              </div>
              <h4>2. Recordatorios Personalizados</h4>
              <p>
                La aplicación envía notificaciones para recordar a los dueños
                sobre las citas médicas, administración de medicamentos y otras
                tareas importantes.
              </p>
            </div>
            <div class="caracteristica">
              <div class="icono-caracteristica">
                <i class="fas fa-bone"></i>
              </div>
              <h4>3. Plan de Alimentación y Ejercicio</h4>
              <p>
                Los dueños pueden establecer un plan de alimentación adecuado
                según la especie, raza, tamaño y edad de la mascota. También se
                pueden registrar las rutinas de ejercicio y paseos.
              </p>
            </div>
            <div class="caracteristica">
              <div class="icono-caracteristica">
                <i class="fas fa-dog"></i>
              </div>
              <h4>4. Entrenamiento y Socialización</h4>
              <p>
                La aplicación proporciona consejos de entrenamiento y técnicas
                de socialización para ayudar a los dueños a criar mascotas bien
                educadas y felices.
              </p>
            </div>
            <div class="caracteristica">
              <div class="icono-caracteristica">
                <i class="fas fa-map-marker-alt"></i>
              </div>
              <h4>5. Localización y Seguridad</h4>
              <p>
                Incluye un rastreador GPS para ubicar a la mascota en caso de
                pérdida. Además, ofrece consejos sobre seguridad en el hogar y
                durante los paseos.
              </p>
            </div>
            <div class="caracteristica">
              <div class="icono-caracteristica">
                <i class="fas fa-users"></i>
              </div>
              <h4>6. Comunidad de Dueños de Mascotas</h4>
              <p>
                Los usuarios pueden conectarse con otros dueños, compartir
                experiencias y obtener consejos de cuidado.
              </p>
            </div>
            <!-- <div class="caracteristica">
                        <div class="icono-caracteristica">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <h4>7. Información Legal y Responsabilidades</h4>
                        <p>La aplicación educa a los dueños sobre sus obligaciones legales, como mantener a sus mascotas identificadas con microchips, evitar la reproducción incontrolada y recoger los desechos de sus mascotas en lugares públicos.</p>
                    </div> -->
          </div>
        </div>
      </section>

      <!-- Sección de Registro -->
      <section id="registro" class="seccion-registro">
        <div class="contenedor">
          <div class="encabezado-seccion claro">
            <h2>Regístrate</h2>
            <p class="subtitulo-seccion">
              Únete a nuestra comunidad y comienza a cuidar mejor a tu mascota
            </p>
          </div>
          <div class="contenedor-registro">
            <div class="resaltado-registro">
              <img src="img/perro-feliz.jpg" alt="Perro feliz" />
            </div>

            <form
              id="registrationForm"
              class="formulario-registro"
              action="public/api/auth/register.php"
              method="POST"
            >
              <div class="grupo-formulario">
                <label for="nombre">Nombre Completo</label>
                <div class="entrada-con-icono">
                  <i class="fas fa-user"></i>
                  <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    placeholder="Tu nombre completo"
                    required
                  />
                </div>
              </div>
              <div class="grupo-formulario">
                <label for="correo">Correo electrónico</label>
                <div class="entrada-con-icono">
                  <i class="fas fa-envelope"></i>
                  <input
                    type="email"
                    id="correo"
                    name="correo"
                    placeholder="ejemplo@correo.com"
                    required
                  />
                </div>
              </div>
              <div class="grupo-formulario">
                <label for="clave">Contraseña</label>
                <div class="entrada-con-icono">
                  <i class="fas fa-lock"></i>
                  <input
                    type="password"
                    id="clave"
                    name="clave"
                    placeholder="Mínimo 8 caracteres"
                    required
                    minlength="8"
                    pattern=".{8,}"
                    title="La contraseña debe tener al menos 8 caracteres"
                  />
                </div>
              </div>
              <div class="grupo-formulario">
                <label for="apodo">Nombre de usuario</label>
                <div class="entrada-con-icono">
                  <i class="fas fa-at"></i>
                  <input
                    type="text"
                    id="apodo"
                    name="apodo"
                    placeholder="Tu nombre de usuario"
                    required
                  />
                </div>
              </div>
              <div class="grupo-formulario">
                <label for="direccion">Dirección</label>
                <div class="entrada-con-icono">
                  <i class="fas fa-home"></i>
                  <input
                    type="text"
                    id="direccion"
                    name="direccion"
                    placeholder="Tu dirección"
                    required
                  />
                </div>
              </div>
              <div class="grupo-formulario grupo-checkbox">
                <input type="checkbox" id="terminos" name="terminos" required />
                <label for="terminos">Acepto los términos y condiciones</label>
              </div>
              <input
                type="submit"
                value="Registrarse"
                class="boton-primario boton-completo"
              />
              <p class="enlace-login">
                ¿Ya tienes una cuenta?
                <a href="public/auth/login.php">Inicia sesión</a>
              </p>
            </form>
          </div>
        </div>
      </section>

      <!-- Sección de Servicios -->
      <section class="seccion-servicios">
        <div class="contenedor">
          <div class="encabezado-seccion">
            <h2>Cuidados de tus mascotas</h2>
            <p class="subtitulo-seccion">
              Descubre todas las herramientas que tenemos para ti
            </p>
          </div>
          <div class="cuadricula-servicios">
            <div class="item-servicio">
              <i class="fas fa-heartbeat"></i>
              <h4>Síntomas</h4>
            </div>
            <div class="item-servicio">
              <i class="fas fa-utensils"></i>
              <h4>Alimentación sana</h4>
            </div>
            <div class="item-servicio">
              <i class="fas fa-clinic-medical"></i>
              <h4>Veterinarias cercanas</h4>
            </div>
            <div class="item-servicio">
              <i class="fas fa-walking"></i>
              <h4>Configura tu alarma de paseos</h4>
            </div>
            <div class="item-servicio">
              <i class="fas fa-syringe"></i>
              <h4>Notificaciones de vacunas</h4>
            </div>
            <div class="item-servicio">
              <i class="fas fa-drumstick-bite"></i>
              <h4>Notificación de alimentación</h4>
            </div>
            <div class="item-servicio">
              <i class="fas fa-paw"></i>
              <h4>Información acerca del animal</h4>
            </div>
            <div class="item-servicio">
              <i class="fas fa-home"></i>
              <h4>Guarderías cerca</h4>
            </div>
            <div class="item-servicio">
              <i class="fas fa-brain"></i>
              <h4>Salud mental de tu mascota</h4>
            </div>
            <div class="item-servicio">
              <i class="fas fa-graduation-cap"></i>
              <h4>Educación de tu mascota</h4>
            </div>
            <div class="item-servicio">
              <i class="fas fa-couch"></i>
              <h4>Entorno seguro y cómodo</h4>
            </div>
            <div class="item-servicio">
              <i class="fas fa-cut"></i>
              <h4>Beneficios de la esterilización</h4>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- Pie de página -->
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
              <li><a href="#inicio">Inicio</a></li>
              <li><a href="#nosotros">Nosotros</a></li>
              <li><a href="html/contacto.php">Contacto</a></li>
              <?php if ($loggedIn): ?>
              <li><a href="html/citas.html">Citas</a></li>
              <?php endif; ?>
              <!-- <li><a href="#registro">Registro</a></li> -->
              <li><a href="html/blog.php">Blog</a></li>
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
          <p>
            &copy; 2024 Alaska - Cuidado de Mascotas. Todos los derechos
            reservados.
          </p>
        </div>
      </div>
    </footer>

    <!-- Scripts -->
    <script src="views/MenuView.js"></script>
    <script src="views/ButtonView.js"></script>
    <script src="views/FormView.js"></script>
    <script src="assets/js/app.js"></script>
    <script>
      function handleRegistration(event) {
        event.preventDefault();
        try {
          const form = event.target;
          if (!form.checkValidity()) {
            event.stopPropagation();
            form.classList.add("was-validated");
            return false;
          }

          const formData = new FormData(form);
          const payload = {
            nombre: formData.get("nombre"),
            correo: formData.get("correo"),
            clave: formData.get("clave"),
            apodo: formData.get("apodo"),
            direccion: formData.get("direccion"),
          };

          fetch("public/api/auth/register.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify(payload),
          })
            .then(async (res) => {
              const isJson = res.headers
                .get("content-type")
                ?.includes("application/json");
              const data = isJson ? await res.json() : null;
              if (res.ok && data && data.success) {
                window.location.href = "public/dashboard.php";
                return;
              }
              const message =
                data && data.error
                  ? data.error
                  : "No se pudo completar el registro";
              alert(message);
            })
            .catch((err) => {
              console.error("Error al registrar:", err);
              alert("Error de red al intentar registrar.");
            });
          return false;
        } catch (error) {
          console.error("Error en handleRegistration:", error);
          alert("Ocurrió un error inesperado.");
          return false;
        }
      }
    </script>
  </body>
</html>
