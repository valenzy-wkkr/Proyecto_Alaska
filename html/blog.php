<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/core/Autoloader.php';

use App\Core\Database;

$loggedIn = isset($_SESSION['usuario_id']);

// Obtener datos del usuario si está logueado
$fotoPerfil = '';
$nombreUsuario = 'Usuario';

if ($loggedIn) {
    $usuarioId = $_SESSION['usuario_id'];
    $nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';

    try {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
        $stmt->bind_param('i', $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $fotoPerfil = $row['foto_perfil'] ?? '';
        }
    } catch (Exception $e) {
        $fotoPerfil = '';
    }
}

// Función para obtener iniciales
function obtenerIniciales($nombre)
{
    $palabras = explode(' ', trim($nombre));
    if (count($palabras) >= 2) {
        return strtoupper(substr($palabras[0], 0, 1) . substr($palabras[1], 0, 1));
    }
    return strtoupper(substr($nombre, 0, 1));
}
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
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    <link rel="shortcut icon" href="../img/alaska-ico.png" type="image/x-icon">
    <style>
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

        /* Categorías activas */
        .widget-categorias a.active {
            background: var(--color-primario);
            color: var(--color-texto-blanco);
            transform: translateX(5px);
        }

        /* Estado activo de etiquetas (igual al hover) */
        .nube-etiquetas a.active {
            background: var(--color-primario);
            color: var(--color-texto-blanco);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(91, 140, 90, 0.3);
        }
    </style>
</head>


<body>
    <!-- Cabecera -->
    <header class="cabecera-principal">
        <div class="contenedor contenedor-cabecera">
            <div class="logo">
                <div class="contenedor-logo">
                    <div class="contenedor-imagen-logo">
                        <img src="../img/alaska.png" alt="Logo Alaska" class="img-logo" />
                    </div>
                    <!-- <h1>ALASKA</h1> -->
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
                        <li><a href="citas.php">Citas</a></li>
                    <?php endif; ?>

                    <li><a href="blog.php" class="activo">Blog</a></li>
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
                        <article class="articulo-destacado" data-categoria="Salud">
                            <div class="imagen-articulo">
                                <img src="../img/blog-destacado.jpg" alt="Artículo destacado" loading="eager" fetchpriority="high" decoding="async" style="aspect-ratio: 16/9; width: 100%; height: auto; object-fit: cover;">
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
                            <article class="articulo" data-categoria="Alimentación">
                                <div class="imagen-articulo">
                                    <img src="../img/Alimentacion.jpg" alt="Alimentación de mascotas" loading="lazy" decoding="async" style="aspect-ratio: 16/9; width: 100%; height: auto; object-fit: cover;">
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

                            <article class="articulo" data-categoria="Entrenamiento">
                                <div class="imagen-articulo">
                                    <img src="../img/entrenamientoMacotas.jpg" alt="Entrenamiento de mascotas" loading="lazy" decoding="async" style="aspect-ratio: 16/9; width: 100%; height: auto; object-fit: cover;">
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

                            <article class="articulo" data-categoria="Gatos">
                                <div class="imagen-articulo">
                                    <img src="../img/cuidaddoGatos.jpg" alt="Cuidado de gatos" loading="lazy" decoding="async" style="aspect-ratio: 16/9; width: 100%; height: auto; object-fit: cover;">
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

                            <article class="articulo" data-categoria="Mascotas Exóticas">
                                <div class="imagen-articulo">
                                    <img src="../img/mascotasExoticas.jpg" alt="Mascotas exóticas" loading="lazy" decoding="async" style="aspect-ratio: 16/9; width: 100%; height: auto; object-fit: cover;">
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

                            <article class="articulo" data-categoria="Salud">
                                <div class="imagen-articulo">
                                    <img src="../img/salud.jpg" alt="Salud de mascotas" loading="lazy" decoding="async" style="aspect-ratio: 16/9; width: 100%; height: auto; object-fit: cover;">
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

                            <article class="articulo" data-categoria="Adopción">
                                <div class="imagen-articulo">
                                    <img src="../img/Adopcion.jpg" alt="Adopción de mascotas" loading="lazy" decoding="async" style="aspect-ratio: 16/9; width: 100%; height: auto; object-fit: cover;">
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
                                <!-- <button type="submit"><i class="fas fa-search"></i></button> -->
                            </form>
                        </div>

                        <!-- Categorías -->
                        <div class="widget widget-categorias">
                            <h3>Categorías</h3>
                            <ul>
                                <li><a href="?categoria=Salud" class="categoria-enlace" data-categoria="Salud">Salud</a></li>
                                <li><a href="?categoria=Alimentación" class="categoria-enlace" data-categoria="Alimentación">Alimentación</a></li>
                                <li><a href="?categoria=Entrenamiento" class="categoria-enlace" data-categoria="Entrenamiento">Entrenamiento</a></li>
                                <li><a href="?categoria=Gatos" class="categoria-enlace" data-categoria="Gatos">Gatos</a></li>
                                <li><a href="?categoria=Perros" class="categoria-enlace" data-categoria="Perros">Perros</a></li>
                                <li><a href="?categoria=Mascotas%20Exóticas" class="categoria-enlace" data-categoria="Mascotas Exóticas">Mascotas Exóticas</a></li>
                                <li><a href="?categoria=Adopción" class="categoria-enlace" data-categoria="Adopción">Adopción</a></li>
                            </ul>
                        </div>

                        <!-- Artículos populares -->
                        <div class="widget widget-populares">
                            <h3>Artículos Populares</h3>
                            <div class="articulos-populares">
                                <div class="articulo-popular">
                                    <div class="imagen-popular">
                                        <img src="../img/popular1.jpg" alt="Artículo popular" loading="lazy" decoding="async" style="aspect-ratio: 16/9; width: 100%; height: auto; object-fit: cover;">
                                    </div>
                                    <div class="info-popular">
                                        <h4><a href="#">Los mejores juguetes para estimular a tu gato</a></h4>
                                        <span><i class="fas fa-calendar"></i> 5 Marzo, 2024</span>
                                    </div>
                                </div>

                                <div class="articulo-popular">
                                    <div class="imagen-popular">
                                        <img src="../img/popular2.jpg" alt="Artículo popular" loading="lazy" decoding="async" style="aspect-ratio: 16/9; width: 100%; height: auto; object-fit: cover;">
                                    </div>
                                    <div class="info-popular">
                                        <h4><a href="#">Cómo prevenir la ansiedad por separación en perros</a></h4>
                                        <span><i class="fas fa-calendar"></i> 20 Febrero, 2024</span>
                                    </div>
                                </div>

                                <div class="articulo-popular">
                                    <div class="imagen-popular">
                                        <img src="../img/popular3.jpg" alt="Artículo popular" loading="lazy" decoding="async" style="aspect-ratio: 16/9; width: 100%; height: auto; object-fit: cover;">
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
    <script src="../assets/js/profile-sync.js"></script>
    <script src="/Proyecto_Alaska/views/MenuView.js"></script>
    <script>
        (function() {
            const articulos = Array.from(document.querySelectorAll('.articulo, .articulo-destacado'));
            const enlacesCategorias = document.querySelectorAll('.widget-categorias .categoria-enlace');
            const enlacesEtiquetas = document.querySelectorAll('.nube-etiquetas a');
            const inputBusqueda = document.querySelector('.formulario-busqueda input');
            let categoriaActual = '';
            let queryActual = '';
            let etiquetaActual = '';

            function actualizarContadores() {
                const conteos = {};
                articulos.forEach(a => {
                    const cat = (a.getAttribute('data-categoria') || '').trim();
                    if (!cat) return;
                    conteos[cat] = (conteos[cat] || 0) + 1;
                });
                enlacesCategorias.forEach(enlace => {
                    const existente = enlace.querySelector('span');
                    if (existente) existente.remove();
                    const cat = enlace.dataset.categoria || '';
                    const n = conteos[cat] || 0;
                    if (n > 0) {
                        const s = document.createElement('span');
                        s.textContent = ` (${n})`;
                        enlace.appendChild(s);
                    }
                });
            }

            function obtenerTextoArticulo(el) {
                const titulo = (el.querySelector('h2, h3')?.textContent || '').toLowerCase();
                const parrafo = (el.querySelector('p')?.textContent || '').toLowerCase();
                return `${titulo} ${parrafo}`;
            }

            function aplicarFiltros() {
                const q = queryActual.trim().toLowerCase();
                let visibles = 0;
                articulos.forEach(a => {
                    const cat = (a.getAttribute('data-categoria') || '').trim();
                    const coincideCategoria = !categoriaActual || cat === categoriaActual;
                    const coincideBusqueda = !q || obtenerTextoArticulo(a).includes(q);
                    const mostrar = coincideCategoria && coincideBusqueda;
                    a.style.display = mostrar ? '' : 'none';
                    if (mostrar) visibles++;
                });
                enlacesCategorias.forEach(e => e.classList.toggle('active', e.dataset.categoria === categoriaActual));
                enlacesEtiquetas.forEach(e => e.classList.toggle('active', e.textContent.trim().toLowerCase() === (etiquetaActual || '').toLowerCase()));

                // Mensaje de sin resultados
                let noRes = document.querySelector('.no-results');
                if (!noRes) {
                    noRes = document.createElement('div');
                    noRes.className = 'no-results';
                    noRes.innerHTML = '<i class="fas fa-search"></i><p>No se encontraron artículos.</p>';
                    const contenedor = document.querySelector('.articulos-blog');
                    if (contenedor) contenedor.appendChild(noRes);
                }
                noRes.style.display = visibles === 0 ? '' : 'none';
            }

            // Inicializar contadores al cargar
            actualizarContadores();

            // Manejar clics en categorías (toggle)
            enlacesCategorias.forEach(enlace => {
                enlace.addEventListener('click', function(ev) {
                    ev.preventDefault();
                    const yaActiva = this.classList.contains('active');
                    categoriaActual = yaActiva ? '' : (this.dataset.categoria || '');
                    const url = new URL(window.location);
                    if (categoriaActual) {
                        url.searchParams.set('categoria', categoriaActual);
                    } else {
                        url.searchParams.delete('categoria');
                    }
                    window.history.pushState({}, '', url);
                    aplicarFiltros();
                });
            });

            // Buscar en tiempo real
            if (inputBusqueda) {
                inputBusqueda.addEventListener('input', function() {
                    queryActual = this.value || '';
                    // Si hay una etiqueta activa pero el texto ya no coincide, desactivarla
                    if (etiquetaActual && queryActual.trim().toLowerCase() !== etiquetaActual.toLowerCase()) {
                        etiquetaActual = '';
                    }
                    aplicarFiltros();
                });
            }

            // Manejar clics en etiquetas (toggle y sincroniza con búsqueda)
            enlacesEtiquetas.forEach(et => {
                et.addEventListener('click', function(ev) {
                    ev.preventDefault();
                    const texto = (this.textContent || '').trim();
                    const esMisma = etiquetaActual.toLowerCase() === texto.toLowerCase();
                    etiquetaActual = esMisma ? '' : texto;
                    queryActual = etiquetaActual;
                    if (inputBusqueda) inputBusqueda.value = etiquetaActual;
                    aplicarFiltros();
                });
            });

            // Leer parámetro de URL para pre-filtrar
            const params = new URLSearchParams(window.location.search);
            categoriaActual = params.get('categoria') || '';
            aplicarFiltros();
        })();
    </script>
    <script>
        (function () {
            try { if ('scrollRestoration' in history) { history.scrollRestoration = 'manual'; } } catch (e) {}
            const key = 'scroll:' + location.pathname + location.search;
            function save() {
                const y = window.pageYOffset || document.documentElement.scrollTop || 0;
                sessionStorage.setItem(key, String(y));
            }
            window.addEventListener('beforeunload', save);
            document.addEventListener('visibilitychange', function () { if (document.visibilityState === 'hidden') save(); });
            window.addEventListener('DOMContentLoaded', function () {
                const y = sessionStorage.getItem(key);
                if (y !== null) {
                    requestAnimationFrame(function(){ window.scrollTo(0, parseInt(y, 10) || 0); });
                }
            });
        })();
    </script>
</body>

</html>