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
    <title>Blog - Alaska Cuidado de Mascotas</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="../assets/css/blog.css" />
    <link rel="stylesheet" href="../assets/css/usuario.css">

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
    <link rel="shortcut icon" href="../img/alaska-ico.ico" type="image/x-icon">
  </head>

  
  <body>
    <!-- Cabecera -->
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
                <li><a href="../index.php">Inicio</a></li>
                <?php if (!$loggedIn): ?>
                <li><a href="../index.php#nosotros">Nosotros</a></li>
                <?php endif; ?>

                <li><a href="contacto.php">Contacto</a></li>
                <?php if ($loggedIn): ?>
                <li><a href="citas.html">Citas</a></li>
                <?php endif; ?>

                <li><a href="blog.php" class="activo">Blog</a></li>
                <?php if (!$loggedIn): ?>
                <li><a href="../index.php#registro" class="boton-nav">Registrarse</a></li>
                <?php endif; ?>
                <?php if ($loggedIn): ?>
                    <li><a href="/Proyecto_Alaska4/html/perfil.html" class="inicial-circulo" title="Perfil" aria-label="Perfil"><i class="fas fa-user" aria-hidden="true"></i></a></li>
                <?php endif; ?>
            </ul>
        </nav>
      </div>
    </header>

    <main>
        <!-- Banner de página -->
        <section class="banner-pagina">
            <div class="contenedor">
                <h1>Blog de Mascotas</h1>
                <p>Consejos, noticias y todo lo que necesitas saber sobre el cuidado de tus compañeros peludos</p>
            </div>
        </section>

        <!-- Sección de blog -->
        <section class="seccion-blog">
            <div class="contenedor">
                <div class="contenedor-blog">
                    <div class="articulos-blog">
                        <!-- Artículo destacado -->
                        <article class="articulo-destacado">
                            <div class="imagen-articulo">
                                <img src="../img/blog-destacado.jpg" alt="Artículo destacado">
                                <div class="etiqueta-articulo">Destacado</div>
                            </div>
                            <div class="contenido-articulo">
                                <div class="meta-articulo">
                                    <span><i class="fas fa-calendar"></i> 15 Mayo, 2024</span>
                                    <span><i class="fas fa-user"></i> Dr. Martínez</span>
                                    <span><i class="fas fa-folder"></i> Salud</span>
                                </div>
                                <h2>10 Señales de que tu mascota necesita atención veterinaria urgente</h2>
                                <p>Conocer las señales de alarma puede marcar la diferencia entre la vida y la muerte de tu mascota. En este artículo, te explicamos los síntomas que nunca debes ignorar y cuándo debes acudir inmediatamente al veterinario.</p>
                                <a href="../blog/destacado/destacado.html" class="boton-secundario" style="color: #000">Leer más</a>
                            </div>
                        </article>

                        <!-- Artículos regulares -->
                        <div class="grid-articulos">
                            <article class="articulo">
                                <div class="imagen-articulo">
                                    <img src="../img/Alimentacion.jpg" alt="Alimentación de mascotas">
                                </div>
                                <div class="contenido-articulo">
                                    <div class="meta-articulo">
                                        <span><i class="fas fa-calendar"></i> 10 Mayo, 2024</span>
                                        <span><i class="fas fa-folder"></i> Alimentación</span>
                                    </div>
                                    <h3>Guía completa de alimentación para perros según su edad</h3>
                                    <p>La alimentación adecuada es fundamental para la salud de tu perro. Descubre qué tipo de alimento necesita tu mascota según su etapa de vida.</p>
                                    <a href="../blog/alimentacion/food.html" class="enlace-articulo">Leer más <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </article>

                            <article class="articulo">
                                <div class="imagen-articulo">
                                    <img src="../img/entrenamientoMacotas.jpg" alt="Entrenamiento de mascotas">
                                </div>
                                <div class="contenido-articulo">
                                    <div class="meta-articulo">
                                        <span><i class="fas fa-calendar"></i> 5 Mayo, 2024</span>
                                        <span><i class="fas fa-folder"></i> Entrenamiento</span>
                                    </div>
                                    <h3>5 Técnicas efectivas para entrenar a tu cachorro</h3>
                                    <p>El entrenamiento temprano es clave para tener un perro bien educado. Aprende estas técnicas simples pero efectivas para entrenar a tu cachorro.</p>
                                    <a href="../blog/entrenamiento/training.html" class="enlace-articulo">Leer más <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </article>

                            <article class="articulo">
                                <div class="imagen-articulo">
                                    <img src="../img/cuidaddoGatos.jpg" alt="Cuidado de gatos">
                                </div>
                                <div class="contenido-articulo">
                                    <div class="meta-articulo">
                                        <span><i class="fas fa-calendar"></i> 28 Abril, 2024</span>
                                        <span><i class="fas fa-folder"></i> Gatos</span>
                                    </div>
                                    <h3>Cómo entender el lenguaje corporal de tu gato</h3>
                                    <p>Los gatos se comunican principalmente a través de su lenguaje corporal. Aprende a interpretar las señales que tu felino te está enviando.</p>
                                    <a href="../blog/lenguajeGatos/language.html" class="enlace-articulo">Leer más <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </article>

                            <article class="articulo">
                                <div class="imagen-articulo">
                                    <img src="../img/mascotasExoticas.jpg" alt="Mascotas exóticas">
                                </div>
                                <div class="contenido-articulo">
                                    <div class="meta-articulo">
                                        <span><i class="fas fa-calendar"></i> 20 Abril, 2024</span>
                                        <span><i class="fas fa-folder"></i> Mascotas Exóticas</span>
                                    </div>
                                    <h3>Cuidados básicos para mascotas exóticas</h3>
                                    <p>Las mascotas exóticas requieren cuidados especiales. Descubre lo que necesitas saber si tienes o estás pensando en adoptar una mascota no convencional.</p>
                                    <a href="../blog/exotic_pets/care.html" class="enlace-articulo">Leer más <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </article>

                            <article class="articulo">
                                <div class="imagen-articulo">
                                    <img src="../img/salud.jpg" alt="Salud de mascotas">
                                </div>
                                <div class="contenido-articulo">
                                    <div class="meta-articulo">
                                        <span><i class="fas fa-calendar"></i> 15 Abril, 2024</span>
                                        <span><i class="fas fa-folder"></i> Salud</span>
                                    </div>
                                    <h3>Calendario de vacunación para perros y gatos</h3>
                                    <p>Mantener al día las vacunas de tu mascota es esencial para su salud. Conoce el calendario de vacunación recomendado para perros y gatos.</p>
                                    <a href="../blog/vaccination/vaccination.html" class="enlace-articulo">Leer más <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </article>

                            <article class="articulo">
                                <div class="imagen-articulo">
                                    <img src="../img/Adopcion.jpg" alt="Adopción de mascotas">
                                </div>
                                <div class="contenido-articulo">
                                    <div class="meta-articulo">
                                        <span><i class="fas fa-calendar"></i> 10 Abril, 2024</span>
                                        <span><i class="fas fa-folder"></i> Adopción</span>
                                    </div>
                                    <h3>Guía para adoptar una mascota responsablemente</h3>
                                    <p>Adoptar una mascota es una decisión importante. Te ofrecemos una guía completa para que la adopción sea responsable y exitosa.</p>
                                    <a href="../blog/adopcion/adopcion.html" class="enlace-articulo">Leer más <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </article>
                        </div>

                        <!-- Paginación -->
                        <!-- <div class="paginacion">
                            <a href="#" class="pagina activa">1</a>
                            <a href="#" class="pagina">2</a>
                            <a href="#" class="pagina">3</a>
                            <a href="#" class="pagina siguiente">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div> -->
                    </div>

                    <!-- Barra lateral -->
                    <aside class="barra-lateral">
                        <!-- Búsqueda -->
                        <div class="widget widget-busqueda">
                            <h3>Buscar</h3>
                            <form class="formulario-busqueda">
                                <input type="text" placeholder="Buscar artículos...">
                                <button type="submit"><i class="fas fa-search"></i></button>
                            </form>
                        </div>

                        <!-- Categorías -->
                        <div class="widget widget-categorias">
                            <h3>Categorías</h3>
                            <ul>
                                <li><a href="#">Salud <span>(12)</span></a></li>
                                <li><a href="#">Alimentación <span>(8)</span></a></li>
                                <li><a href="#">Entrenamiento <span>(6)</span></a></li>
                                <li><a href="#">Gatos <span>(5)</span></a></li>
                                <li><a href="#">Perros <span>(10)</span></a></li>
                                <li><a href="#">Mascotas Exóticas <span>(3)</span></a></li>
                                <li><a href="#">Adopción <span>(4)</span></a></li>
                            </ul>
                        </div>

                        <!-- Artículos populares -->
                        <div class="widget widget-populares">
                            <h3>Artículos Populares</h3>
                            <div class="articulos-populares">
                                <div class="articulo-popular">
                                    <div class="imagen-popular">
                                        <img src="../img/popular1.jpg" alt="Artículo popular">
                                    </div>
                                    <div class="info-popular">
                                        <h4><a href="#">Los mejores juguetes para estimular a tu gato</a></h4>
                                        <span><i class="fas fa-calendar"></i> 5 Marzo, 2024</span>
                                    </div>
                                </div>

                                <div class="articulo-popular">
                                    <div class="imagen-popular">
                                        <img src="../img/popular2.jpg" alt="Artículo popular">
                                    </div>
                                    <div class="info-popular">
                                        <h4><a href="#">Cómo prevenir la ansiedad por separación en perros</a></h4>
                                        <span><i class="fas fa-calendar"></i> 20 Febrero, 2024</span>
                                    </div>
                                </div>

                                <div class="articulo-popular">
                                    <div class="imagen-popular">
                                        <img src="../img/popular3.jpg" alt="Artículo popular">
                                    </div>
                                    <div class="info-popular">
                                        <h4><a href="#">Beneficios de la esterilización en mascotas</a></h4>
                                        <span><i class="fas fa-calendar"></i> 10 Febrero, 2024</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Etiquetas -->
                        <div class="widget widget-etiquetas">
                            <h3>Etiquetas</h3>
                            <div class="nube-etiquetas">
                                <a href="#">Perros</a>
                                <a href="#">Gatos</a>
                                <a href="#">Salud</a>
                                <a href="#">Alimentación</a>
                                <a href="#">Entrenamiento</a>
                                <a href="#">Vacunas</a>
                                <a href="#">Adopción</a>
                                <a href="#">Cuidados</a>
                                <a href="#">Veterinario</a>
                                <a href="#">Mascotas</a>
                            </div>
                        </div>

                        <?php if (isset($_SESSION['usuario_id'])) : ?>
                            <!-- Widget del Dashboard -->
                            <div class="widget widget-dashboard">
                                <h3>Mi Mascota</h3>
                                <!-- <p>Agenda citas para tus mascotas.</p> -->
                                 <p>Porque su bienestar es primero: agenda su cita ahora.</p>
                                <a href="../html/citas.html" class="boton-primario boton-completo" role="button">Ir a Citas</a>
                            </div>
                        <?php else : ?>
                            <!-- Widget de Registro -->
                            <div class="widget widget-registro">
                                <h3>Gestiona la Salud de tu Mascota</h3>
                                <p>Crea una cuenta para llevar un registro de las vacunas, citas y recordatorios de tu mascota.</p>
                                <a href="../index.php#registro" class="boton-primario boton-completo" role="button">Regístrate Gratis</a>
                            </div>
                        <?php endif; ?>
                    </aside>
                </div>
            </div>
        </section>

        <!-- Llamado a la acción -->
        <!-- <section class="seccion-cta">
            <div class="contenedor">
                <div class="contenido-cta">
                    <h2>¿Listo para cuidar mejor a tu mascota?</h2>
                    <p>Regístrate ahora y comienza a utilizar todas nuestras herramientas para el cuidado de tu compañero peludo.</p>
                    <a href="../index.php#registro" class="boton-primario">Registrarse Ahora</a>
                </div>
            </div>
        </section> -->
    </main>

    <!-- Pie de página -->
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
                        <li><a href="../index.php">Inicio</a></li>
                        <?php if (!$loggedIn): ?>
                        <li><a href="../index.php#nosotros">Nosotros</a></li>
                        <?php endif; ?>
                        <li><a href="contacto.php">Contacto</a></li>
                        <?php if ($loggedIn): ?>
                        <li><a href="citas.html">Citas</a></li>
                        <?php endif; ?>
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
  </body>
</html>
