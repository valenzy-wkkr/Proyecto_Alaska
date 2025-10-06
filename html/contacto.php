<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$loggedIn = isset($_SESSION['usuario_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contacto - Alaska Cuidado de Mascotas</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <link rel="stylesheet" href="../assets/css/usuario.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="shortcut icon" href="../img/alaska-ico.ico" type="image/x-icon">
</head>
<body>
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
        <button class="boton-menu-movil" aria-label="Abrir menú"><i class="fas fa-bars"></i></button>
        <ul class="lista-navegacion">
          <li><a href="../index.php">Inicio</a></li>
          <?php if (!$loggedIn): ?>
          <li><a href="../index.php#nosotros">Nosotros</a></li>
          <?php endif; ?>
          <li><a href="contacto.php" class="activo">Contacto</a></li>
          <?php if ($loggedIn): ?>
          <li><a href="../html/citas.html">Citas</a></li>
          <?php endif; ?>
          <li><a href="blog.php">Blog</a></li>
          <?php if (!$loggedIn): ?>
            <li><a href="../index.php#registro" class="boton-nav">Registrarse</a></li>
          <?php endif; ?>
          <?php if ($loggedIn): ?>
            <li><a href="/Proyecto_Alaska4/html/perfil.html" class="inicial-circulo" title="Perfil" aria-label="Perfil"><i class="fas fa-user" aria-hidden="true"></i></a></li>
            <!-- <li><a href="perfil.html" class="inicial-circulo" title="Perfil">U</a></li> -->
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </header>

  <main>
    <section class="banner-pagina">
      <div class="contenedor">
        <h1>Contáctanos</h1>
        <p>Estamos aquí para ayudarte con cualquier duda sobre el cuidado de tu mascota</p>
      </div>
    </section>

    <section class="seccion-contacto">
      <div class="contenedor">
        <div class="contenedor-contacto">
          <div class="info-contacto-pagina">
            <h2>Información de Contacto</h2>
            <p>Estamos disponibles para ayudarte con cualquier consulta sobre nuestros servicios o para brindarte asesoramiento sobre el cuidado de tu mascota.</p>
            <div class="detalles-contacto">
              <div class="detalle"><div class="icono-contacto"><i class="fas fa-map-marker-alt"></i></div><div class="texto-contacto"><h3>Dirección</h3><p>Calle Principal 123, Ciudad</p></div></div>
              <div class="detalle"><div class="icono-contacto"><i class="fas fa-phone"></i></div><div class="texto-contacto"><h3>Teléfono</h3><p>+123 456 7890</p></div></div>
              <div class="detalle"><div class="icono-contacto"><i class="fas fa-envelope"></i></div><div class="texto-contacto"><h3>Email</h3><p>info@alaska-mascotas.com</p></div></div>
              <div class="detalle"><div class="icono-contacto"><i class="fas fa-clock"></i></div><div class="texto-contacto"><h3>Horario de Atención</h3><p>Lunes a Viernes: 9:00 - 18:00</p><p>Sábados: 10:00 - 14:00</p></div></div>
            </div>
            <div class="redes-sociales-contacto">
              <h3>Síguenos</h3>
              <div class="iconos-sociales">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
              </div>
            </div>
          </div>
          <div class="formulario-contacto">
            <h2>Envíanos un Mensaje</h2>
            <div id="mensaje-estado" class="alerta" style="display:none; margin-bottom: 1rem;"></div>
            <form action="https://formsubmit.co/0d1802db591534bb6b7efe59c95df143" method="POST" id="formulario-contacto" enctype="multipart/form-data">
              <input type="hidden" name="_next" value="http://localhost/Proyecto_Alaska4/html/contacto.php?enviado=1">
              <input type="hidden" name="_captcha" value="false">
              <input type="hidden" name="_template" value="table">
              <input type="hidden" name="_subject" value="Nuevo mensaje de contacto - Alaska">
              <div class="grupo-formulario"><label for="nombre-contacto">Nombre Completo</label><div class="entrada-con-icono"><i class="fas fa-user"></i><input type="text" id="nombre-contacto" name="name" placeholder="Tu nombre completo" required></div></div>
              <div class="grupo-formulario"><label for="email-contacto">Correo Electrónico</label><div class="entrada-con-icono"><i class="fas fa-envelope"></i><input type="email" id="email-contacto" name="email" placeholder="ejemplo@correo.com" required></div></div>
              <div class="grupo-formulario"><label for="telefono-contacto">Teléfono</label><div class="entrada-con-icono"><i class="fas fa-phone"></i><input type="tel" id="telefono-contacto" name="phone" placeholder="Tu número de teléfono"></div></div>
              <div class="grupo-formulario"><label for="asunto-contacto">Asunto</label><div class="entrada-con-icono"><i class="fas fa-tag"></i><input type="text" id="asunto-contacto" name="subject" placeholder="Asunto de tu mensaje" required></div></div>
              <div class="grupo-formulario"><label for="mensaje-contacto">Mensaje</label><textarea id="mensaje-contacto" name="message" rows="5" placeholder="Escribe tu mensaje aquí..." required></textarea></div>
              <button type="submit" class="boton-primario boton-completo">Enviar Mensaje</button>
            </form>
          </div>
        </div>
      </div>
    </section>

    <section class="seccion-mapa">
      <div class="contenedor-mapa">
        <!-- Contenedor del mapa de Leaflet -->
        <div id="mapa-contacto" style="width: 100%; height: 450px;"></div>
        
        <!-- Incluir CSS de Leaflet -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
              crossorigin=""/>
        
        <!-- Incluir JavaScript de Leaflet (después del CSS) -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
                crossorigin=""></script>
        
        <!-- Script para inicializar el mapa -->
        <script>
          document.addEventListener('DOMContentLoaded', function() {
            // Coordenadas para la Clínica Veterinaria Alaska (La Ceja, Antioquia, Colombia)
            var lat = 6.0308;
            var lng = -75.4294;
            
            // Inicializar el mapa
            var map = L.map('mapa-contacto').setView([lat, lng], 15);
            
            // Añadir capa de mapa base (OpenStreetMap)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
              attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            // Añadir marcador personalizado
            var clinicaIcon = L.icon({
              iconUrl: '../img/alaska-ico.ico',
              iconSize: [32, 32],
              iconAnchor: [16, 32],
              popupAnchor: [0, -32]
            });
            
            // Añadir el marcador al mapa
            var marker = L.marker([lat, lng], {icon: clinicaIcon}).addTo(map);
            
            // Añadir popup con información
            marker.bindPopup("<b>Clínica Veterinaria Alaska</b><br>Calle 20 #20-40<br>La Ceja, Antioquia, Colombia<br>Tel: +57 604 553 1234").openPopup();
          });
        </script>
      </div>
    </section>

    <section class="seccion-faq">
      <div class="contenedor">
        <div class="encabezado-seccion">
          <h2>Preguntas Frecuentes</h2>
          <p class="subtitulo-seccion">Respuestas a las dudas más comunes sobre nuestros servicios</p>
        </div>
        <div class="contenedor-faq">
          <div class="item-faq"><div class="pregunta-faq"><h3>¿Cómo puedo registrarme en la aplicación?</h3><i class="fas fa-chevron-down"></i></div><div class="respuesta-faq"><p>Puedes registrarte fácilmente haciendo clic en el botón "Registrarse" en la parte superior de nuestra página. Completa el formulario con tus datos y ¡listo! Ya podrás acceder a todas nuestras funcionalidades.</p></div></div>
          <div class="item-faq"><div class="pregunta-faq"><h3>¿La aplicación es gratuita?</h3><i class="fas fa-chevron-down"></i></div><div class="respuesta-faq"><p>Ofrecemos una versión básica gratuita con funcionalidades limitadas. También contamos con planes premium que incluyen todas las características por una suscripción mensual o anual.</p></div></div>
          <div class="item-faq"><div class="pregunta-faq"><h3>¿Cómo funciona el sistema de recordatorios?</h3><i class="fas fa-chevron-down"></i></div><div class="respuesta-faq"><p>Nuestro sistema de recordatorios te permite configurar alertas para vacunas, medicamentos, citas veterinarias y paseos. Recibirás notificaciones en tu dispositivo en los horarios que hayas programado.</p></div></div>
          <div class="item-faq"><div class="pregunta-faq"><h3>¿Puedo registrar más de una mascota?</h3><i class="fas fa-chevron-down"></i></div><div class="respuesta-faq"><p>¡Por supuesto! Puedes registrar todas las mascotas que desees en tu cuenta y gestionar la información de cada una de manera independiente.</p></div></div>
        </div>
      </div>
    </section>
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
            <li><a href="../index.php#inicio">Inicio</a></li>
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

  <script>
    document.querySelectorAll('.pregunta-faq').forEach(p => {
      p.addEventListener('click', () => {
        const r = p.nextElementSibling; const i = p.querySelector('i');
        if (r.style.maxHeight) { r.style.maxHeight = null; i.classList.replace('fa-chevron-up','fa-chevron-down'); }
        else { r.style.maxHeight = r.scrollHeight + 'px'; i.classList.replace('fa-chevron-down','fa-chevron-up'); }
      });
    });
    (function(){
      const params = new URLSearchParams(window.location.search);
      if (!params.has('enviado')) return; const cont = document.getElementById('mensaje-estado'); if (!cont) return;
      const ok = params.get('enviado') === '1'; cont.textContent = ok ? '¡Tu mensaje fue enviado correctamente!' : 'Hubo un problema al enviar tu mensaje. Inténtalo de nuevo.';
      cont.classList.add(ok ? 'exito' : 'error'); cont.style.display = 'block';
      setTimeout(()=>{ cont.style.display = 'none'; }, 5000);
      if (window.history && window.history.replaceState) { const url = new URL(window.location.href); url.searchParams.delete('enviado'); window.history.replaceState({}, document.title, url.toString()); }
    })();
    (function(){
      const form = document.getElementById('formulario-contacto'); const cont = document.getElementById('mensaje-estado'); if (!form || !cont) return;
      // Si el action apunta a FormSubmit, no interceptar el submit: dejar envío nativo
      try { if (/formsubmit\.co/i.test(form.action)) { return; } } catch(_) {}
      function msg(ok, t){ cont.textContent=t; cont.classList.remove('exito','error'); cont.classList.add(ok?'exito':'error'); cont.style.display='block'; setTimeout(()=>{cont.style.display='none';},5000); cont.scrollIntoView({behavior:'smooth',block:'center'}); }
      form.addEventListener('submit', async (e)=>{
        try{ e.preventDefault(); const btn=form.querySelector('[type="submit"]'); const txt=btn?btn.textContent:''; if(btn){btn.disabled=true; btn.textContent='Enviando...';}
          const resp = await fetch(form.action,{ method:'POST', headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, body:new FormData(form)});
          let ok=false, mensaje=''; const ct=resp.headers.get('content-type')||''; if(resp.ok && ct.includes('application/json')){ const data=await resp.json(); ok=!!data.success; mensaje=data.message || (ok?'¡Tu mensaje fue enviado correctamente!':'Hubo un problema al enviar tu mensaje. Inténtalo de nuevo.'); }
          else { ok=resp.ok; mensaje= ok?'¡Tu mensaje fue enviado correctamente!':'Hubo un problema al enviar tu mensaje. Inténtalo de nuevo.'; }
          msg(ok,mensaje); if(ok) form.reset();
        } catch(_) { msg(false, 'No se pudo enviar el mensaje. Verifica tu conexión e inténtalo nuevamente.'); }
        finally { const btn=form.querySelector('[type="submit"]'); if(btn){ btn.disabled=false; btn.textContent='Enviar Mensaje'; } }
      });
    })();
  </script>
  <script src="../views/MenuView.js"></script>
  <script src="../views/ButtonView.js"></script>
  <script src="../views/FormView.js"></script>
  <script src="../assets/js/app.js"></script>
</body>
</html>
