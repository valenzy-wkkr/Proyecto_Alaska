<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] === '') {
    header('Location: /Proyecto_Alaska/public/auth/login.php');
    exit();
}

require_once __DIR__ . '/../app/core/Autoloader.php';

use App\Core\Database;

$nombreUsuario = isset($_SESSION['nombre']) && $_SESSION['nombre'] !== ''
    ? $_SESSION['nombre']
    : 'Usuario';

// Obtener foto de perfil del usuario
$fotoPerfil = '';
$usuarioId = $_SESSION['usuario_id'] ?? 0;

if ($usuarioId > 0) {
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
        // En caso de error, continuar sin foto
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
    <title>Panel de Control - Alaska</title>
    <link rel="stylesheet" href="/Proyecto_Alaska/assets/css/style.css" />
    <link rel="stylesheet" href="/Proyecto_Alaska/assets/css/dashboard.css?v=20250922-1" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="shortcut icon" href="/Proyecto_Alaska/img/alaska-ico.png" type="image/x-icon">
</head>

<body>
    <!-- Header -->
    <header class="cabecera-principal">
        <div class="contenedor contenedor-cabecera">
            <div class="logo">
                <div class="contenedor-logo">
                    <div class="contenedor-imagen-logo">
                        <img src="/Proyecto_Alaska/img/alaska.png" alt="Logo Alaska" class="img-logo" />
                    </div>
                    <!-- <h1>ALASKA</h1> -->
                </div>
            </div>
            <nav class="navegacion-principal">
                <button class="boton-menu-movil" aria-label="Abrir menú">
                    <i class="fas fa-bars"></i>
                </button>
                <ul class="lista-navegacion">
                    <li><a href="/Proyecto_Alaska/index.php">Inicio</a></li>
                    <li><a href="/Proyecto_Alaska/html/contacto.php">Contacto</a></li>
                    <li><a href="/Proyecto_Alaska/html/citas.php">Citas</a></li>
                    <li><a href="/Proyecto_Alaska/html/blog.php">Blog</a></li>
                    <li>
                        <a href="/Proyecto_Alaska/html/perfil.php" class="inicial-circulo" title="Perfil" aria-label="Perfil">
                            <?php if (!empty($fotoPerfil)): ?>
                                <img src="/Proyecto_Alaska/public/api/usuario.php?action=profile_picture" alt="Perfil" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            <?php else: ?>
                                <?php echo obtenerIniciales($nombreUsuario); ?>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="dashboard-main">
        <div class="contenedor">
            <!-- Header del Dashboard -->
            <div class="dashboard-header">
                <div class="dashboard-welcome">
                    <h1>¡Bienvenido!</h1>
                    <p id="userName"><?php echo htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <i class="fas fa-paw"></i>
                        <div class="stat-info">
                            <span class="stat-number" id="totalPets">0</span>
                            <span class="stat-label">Mascotas</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-calendar-check"></i>
                        <div class="stat-info">
                            <span class="stat-number" id="upcomingAppointments">0</span>
                            <span class="stat-label">Próximas Citas</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-bell"></i>
                        <div class="stat-info">
                            <span class="stat-number" id="totalReminders">0</span>
                            <span class="stat-label">Recordatorios</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido Principal del Dashboard -->
            <div class="dashboard-content">
                <!-- Columna Izquierda -->
                <div class="dashboard-left">

                    <!-- Estado de Salud de Mascotas -->
                    <section class="dashboard-section">
                        <div class="section-header">
                            <h2><i class="fas fa-heartbeat"></i> Estado de Salud</h2>
                            <button class="btn-add" id="btnAddPet">
                                <i class="fas fa-plus"></i> Agregar Mascota
                            </button>
                        </div>
                        <div class="pets-health-container" id="petsHealthContainer">
                            <div class="empty-state">
                                <p>No tienes mascotas registradas</p>
                            </div>
                        </div>
                    </section>

                    <!-- Recordatorios -->
                    <section class="dashboard-section">
                        <div class="section-header">
                            <h2><i class="fas fa-bell"></i> Próximos Recordatorios</h2>
                            <button class="btn-add" id="btnAddReminder">
                                <i class="fas fa-plus"></i> Agregar
                            </button>
                        </div>
                        <div class="reminders-container" id="remindersContainer">
                            <div class="empty-state">
                                <p>No tienes recordatorios próximos</p>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Columna Derecha -->
                <div class="dashboard-right">
                    <!-- Últimos Artículos del Blog -->
                    <section class="dashboard-section">
                        <div class="section-header">
                            <h2><i class="fas fa-newspaper"></i> Últimos Artículos</h2>
                            <a href="/Proyecto_Alaska/html/blog.php" class="btn-view-all">Ver Todos</a>
                        </div>
                        <div class="blog-articles-container" id="blogArticlesContainer">
                            <div class="empty-state">
                                <p>No hay artículos disponibles</p>
                            </div>
                        </div>
                    </section>

                    <!-- Actividad Reciente -->
                    <!-- <section class="dashboard-section">
                        <div class="section-header">
                            <h2><i class="fas fa-clock"></i> Actividad Reciente</h2>
                        </div>
                        <div class="recent-activity-container" id="recentActivityContainer">
                            <div class="empty-state">
                                <p>No hay actividad reciente</p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main> -->

                    <!-- Modales -->
                    <div class="modal" id="reminderModal">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h3>Agregar Recordatorio</h3>
                                <button class="modal-close" id="closeReminderModal">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <form id="reminderForm">
                                <input type="hidden" id="reminderId" name="id">
                                <div class="form-group">
                                    <label for="reminderTitle">Título</label>
                                    <input type="text" id="reminderTitle" name="title" required>
                                </div>
                                <div class="form-group">
                                    <label for="reminderDate">Fecha</label>
                                    <input type="datetime-local" id="reminderDate" name="date" required>
                                </div>
                                <div class="form-group">
                                    <label for="reminderType">Tipo</label>
                                    <select id="reminderType" name="type" required>
                                        <option value="">Seleccionar tipo</option>
                                        <option value="cita">Cita Veterinaria</option>
                                        <option value="vacuna">Vacuna</option>
                                        <option value="medicamento">Medicamento</option>
                                        <option value="alimentacion">Alimentación</option>
                                        <option value="paseo">Paseo</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="reminderPet">Mascota</label>
                                    <select id="reminderPet" name="petId" required>
                                        <option value="">Seleccionar mascota</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="reminderNotes">Notas</label>
                                    <textarea id="reminderNotes" name="notes" rows="3"></textarea>
                                </div>
                                <div class="form-actions">
                                    <button type="button" class="btn-secondary" id="cancelReminder">Cancelar</button>
                                    <button type="submit" class="btn-primary" id="saveReminder">Guardar</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="modal" id="petModal">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h3 id="tituloModalMascota">Agregar Mascota</h3>
                                <button class="modal-close" id="closePetModal">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <!-- Formulario copiado del perfil (ajustado para el dashboard) -->
                            <form id="formNuevaMascota">
                                <input type="hidden" id="inpIdMascota" value="">

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="inpNombreMascota">Nombre</label>
                                        <input type="text" id="inpNombreMascota" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="selEspecie">Especie</label>
                                        <select id="selEspecie" required>
                                            <option value="">Selecciona especie</option>
                                            <option value="Perro">Perro</option>
                                            <option value="Gato">Gato</option>
                                            <option value="Ave">Ave</option>
                                            <option value="Pez">Pez</option>
                                            <option value="Reptil">Reptil</option>
                                            <option value="Roedor">Roedor</option>
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="selRaza">Raza</label>
                                        <select id="selRaza" disabled>
                                            <option value="">Selecciona primero la especie</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="inpRazaOtro">Otra raza (si aplica)</label>
                                        <input type="text" id="inpRazaOtro" placeholder="Especifica la raza" style="display:none;">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="inpEdad">Edad (años)</label>
                                        <input type="number" id="inpEdad" min="0" step="0.1">
                                    </div>
                                    <div class="form-group">
                                        <label for="inpPeso">Peso (kg)</label>
                                        <input type="number" id="inpPeso" min="0" step="0.1">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="selEstadoSalud">Estado de salud</label>
                                        <select id="selEstadoSalud">
                                            <option value="healthy">Saludable</option>
                                            <option value="attention">Necesita atención</option>
                                            <option value="warning">Requiere revisión</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="inpUltimaRevision">Última revisión</label>
                                        <input type="date" id="inpUltimaRevision">
                                    </div>
                                </div>

                                <!-- Nuevo campo Observaciones (opcional) -->
                                <div>
                                  <label for="inpObservacionesSalud" style="display: block; margin-bottom: 5px; font-weight: 600;">Observaciones (obligatorio)</label>
                                  <textarea
                                    id="inpObservacionesSalud"
                                    rows="3"
                                    maxlength="300"
                                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; resize: vertical;"
                                    placeholder="Ej.: Tos ocasional, seguimiento con veterinario, alergias, etc."
                                  required></textarea>
                                  <small style="color: #666; font-size: 12px;">Máx. 300 caracteres</small>
                                </div>

                                <div class="form-actions">
                                    <button type="button" class="btn-secondary" id="cancelPet">Cancelar</button>
                                    <button type="submit" class="btn-primary" id="guardarMascotaDashboard">Guardar</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Scripts -->
                    <script src="/Proyecto_Alaska/assets/js/dashboard.js?v=20251101-1"></script>
                    <script src="/Proyecto_Alaska/views/MenuView.js"></script>
                    <script>
                        (function() {
                            try {
                                var nameEl = document.getElementById('userName');
                                if (!nameEl) return;
                                var currentText = (nameEl.textContent || '').trim();
                                // Si ya hay un nombre de sesión distinto a 'Usuario', no hacer nada
                                if (currentText && currentText !== 'Usuario') return;

                                var stored = localStorage.getItem('currentUser');
                                if (!stored) return;
                                var user = JSON.parse(stored);
                                if (user && user.name) {
                                    nameEl.textContent = user.name;
                                }
                            } catch (e) {
                                console.error('No se pudo cargar el nombre desde localStorage:', e);
                            }
                        })();
                    </script>
                    <script src="../assets/js/profile-sync.js"></script>
</body>

</html>