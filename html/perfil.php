<?php
session_start();

// Debug: Verificar sesión
error_log("Perfil.php - Sesión iniciada. Datos de sesión: " . json_encode($_SESSION));

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    error_log("Usuario no está logueado, redirigiendo a login");
    header('Location: /Proyecto_Alaska/public/auth/login.php');
    exit();
}

// Incluir los archivos necesarios
require_once __DIR__ . '/../app/core/Autoloader.php';

use App\Models\User;
use App\Models\Pet;
use App\Core\Database;

// Obtener información del usuario
$usuarioId = $_SESSION['usuario_id'];
error_log("ID de usuario obtenido de la sesión: " . $usuarioId);

$userModel = new User();
$petModel = new Pet();

// Obtener datos del usuario desde la base de datos
$userData = null;
$pets = [];

try {
    $db = Database::getConnection();
    
    // Debug: Verificar conexión
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    // Debug: Verificar si el usuario existe
    error_log("Buscando usuario con ID: " . $usuarioId);
    
    // Verificar primero si existe la tabla usuarios
    $tableCheck = $db->query("SHOW TABLES LIKE 'usuarios'");
    if ($tableCheck->num_rows == 0) {
        throw new Exception("La tabla 'usuarios' no existe en la base de datos");
    }
    
    // Verificar qué columnas existen en la tabla
    $columnsResult = $db->query("DESCRIBE usuarios");
    if (!$columnsResult) {
        throw new Exception("Error al obtener la estructura de la tabla usuarios: " . $db->error);
    }
    
    $columns = [];
    while ($row = $columnsResult->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    error_log("Columnas disponibles en usuarios: " . implode(', ', $columns));
    
    // Construir la consulta con las columnas que realmente existen
    $selectColumns = ['id', 'nombre', 'correo'];
    
    // Agregar columnas opcionales si existen
    if (in_array('apodo', $columns)) {
        $selectColumns[] = 'apodo';
    }
    if (in_array('direccion', $columns)) {
        $selectColumns[] = 'direccion';
    }
    if (in_array('fecha_creacion', $columns)) {
        $selectColumns[] = 'fecha_creacion';
    }
    if (in_array('foto_perfil', $columns)) {
        $selectColumns[] = 'foto_perfil';
    }
    
    $selectQuery = "SELECT " . implode(', ', $selectColumns) . " FROM usuarios WHERE id = ?";
    error_log("Consulta SQL: " . $selectQuery);
    
    $stmt = $db->prepare($selectQuery);
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $db->error);
    }
    
    $stmt->bind_param('i', $usuarioId);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    
    // Debug: Verificar resultado
    error_log("Datos del usuario obtenidos: " . json_encode($userData));
    
    if (!$userData) {
        // Verificar si realmente hay usuarios en la tabla
        $countResult = $db->query("SELECT COUNT(*) as total FROM usuarios");
        $countData = $countResult->fetch_assoc();
        error_log("Total de usuarios en la base de datos: " . $countData['total']);
        
        // Verificar si el ID existe
        $checkUserResult = $db->query("SELECT id FROM usuarios LIMIT 5");
        $existingIds = [];
        while ($row = $checkUserResult->fetch_assoc()) {
            $existingIds[] = $row['id'];
        }
        error_log("IDs de usuarios existentes: " . implode(', ', $existingIds));
        
        throw new Exception("Usuario no encontrado en la base de datos. ID buscado: " . $usuarioId . ". IDs existentes: " . implode(', ', $existingIds));
    }
    
    // Agregar valores por defecto para columnas que no existen
    if (!isset($userData['apodo'])) {
        $userData['apodo'] = '';
    }
    if (!isset($userData['direccion'])) {
        $userData['direccion'] = '';
    }
    if (!isset($userData['fecha_creacion'])) {
        $userData['fecha_creacion'] = date('Y-m-d');
    }
    if (!isset($userData['foto_perfil'])) {
        $userData['foto_perfil'] = '';
    }
    
    // Obtener mascotas del usuario
    $pets = $petModel->allByUser($usuarioId);
    error_log("Mascotas encontradas: " . count($pets));
    
} catch (Exception $e) {
    error_log("Error en perfil.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Mostrar error en pantalla también para debugging
    echo "<div style='background:red;color:white;padding:10px;margin:10px;'>";
    echo "<h3>Error de debugging:</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Usuario ID:</strong> " . htmlspecialchars($usuarioId) . "</p>";
    echo "<p><strong>Revisa los logs de error para más detalles.</strong></p>";
    echo "</div>";
    
    // Datos de fallback solo en caso de error real
    $userData = [
        'id' => $usuarioId,
        'nombre' => 'Error al cargar datos',
        'correo' => 'error@demo.com',
        'apodo' => 'error_usuario',
        'direccion' => '',
        'fecha_creacion' => date('Y-m-d')
    ];
    $pets = [];
}

// Debug: Mostrar información en pantalla
if (isset($_GET['debug'])) {
    echo "<div style='background:#f0f0f0;padding:10px;margin:10px;'>";
    echo "<h3>Información de Debug:</h3>";
    echo "<p><strong>Usuario ID:</strong> " . $usuarioId . "</p>";
    echo "<p><strong>Datos obtenidos:</strong></p>";
    echo "<pre>" . print_r($userData, true) . "</pre>";
    echo "<p><strong>Mascotas:</strong> " . count($pets) . "</p>";
    echo "</div>";
}

// Función para obtener las iniciales del nombre
function obtenerIniciales($nombre) {
    $palabras = explode(' ', trim($nombre));
    if (count($palabras) >= 2) {
        return strtoupper(substr($palabras[0], 0, 1) . substr($palabras[1], 0, 1));
    }
    return strtoupper(substr($nombre, 0, 1));
}

// Función para formatear fecha
function formatearFecha($fecha) {
    return date('d/m/Y', strtotime($fecha));
}

// Función para traducir estado de salud
function traducirEstadoSalud($estado) {
    switch ($estado) {
        case 'healthy':
            return 'Sana';
        case 'attention':
            return 'Atención';
        case 'warning':
            return 'Cuidado';
        default:
            return 'Desconocido';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Perfil de Usuario - Alaska</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="../assets/css/dashboard.css" />
    <link rel="stylesheet" href="../assets/css/usuario.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="shortcut icon"
      href="../img/alaska-ico.png"
      type="image/x-icon"
    />
    <link rel="stylesheet" href="../assets/css/perfil.css" />
    <style>
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
      
      .avatar {
        position: relative;
        cursor: pointer;
        overflow: hidden;
      }
      
      .avatar:hover .avatar-overlay {
        opacity: 1;
      }
      
      .avatar-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        border-radius: 50%;
        color: white;
        font-size: 1.2rem;
      }
      
      #inputFotoPerfil {
        display: none;
      }
      
      .modal-foto {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        animation: fadeIn 0.3s ease;
      }
      
      .modal-foto-content {
        background-color: white;
        margin: 5% auto;
        padding: 20px;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        position: relative;
      }
      
      .preview-container {
        text-align: center;
        margin: 20px 0;
      }
      
      .preview-image {
        max-width: 200px;
        max-height: 200px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--color-primario);
      }
      
      .upload-area {
        border: 2px dashed var(--color-borde);
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        margin: 15px 0;
        cursor: pointer;
        transition: all 0.3s ease;
      }
      
      .upload-area:hover {
        border-color: var(--color-primario);
        background-color: var(--fondo-claro);
      }
      
      .upload-area.dragover {
        border-color: var(--color-primario);
        background-color: var(--color-primario-claro);
      }
      
      /* Estilos para formulario de mascotas */
      #formNuevaMascota input:focus,
      #formNuevaMascota select:focus {
        outline: none;
        border-color: var(--color-primario);
        box-shadow: 0 0 0 2px rgba(64, 123, 255, 0.1);
      }
      
      #formNuevaMascota select {
        background-image: url("data:image/svg+xml;charset=UTF-8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 8'><path fill='%23666' d='M6 8L0 2h12z'/></svg>");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 12px;
        padding-right: 30px;
        cursor: pointer;
      }
      
      #formNuevaMascota small {
        font-size: 12px;
        color: #666;
        margin-top: 4px;
        display: block;
      }
      
      .btn-guardar {
        background: var(--color-primario);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s ease;
      }
      
      .btn-guardar:hover {
        background: #5a6fd8;
      }
      
      .btn-guardar:disabled {
        background: #ccc;
        cursor: not-allowed;
      }
    </style>
  </head>
  <body>
    <header class="cabecera-principal">
      <div class="contenedor contenedor-cabecera">
        <div class="logo">
          <div class="contenedor-logo">
            <div class="contenedor-imagen-logo">
              <img src="../img/alaska.png" alt="Logo Alaska" class="img-logo" />
            </div>
          </div>
        </div>
        <nav class="navegacion-principal">
          <button class="boton-menu-movil" aria-label="Abrir menú">
            <i class="fas fa-bars"></i>
          </button>
          <ul class="lista-navegacion">
            <li><a href="../index.php">Inicio</a></li>
            <li><a href="contacto.php">Contacto</a></li>
            <li><a href="citas.php">Citas</a></li>
            <li><a href="blog.php">Blog</a></li>
            <li>
              <a
                href="/Proyecto_Alaska/public/api/auth/logout.php"
                class="boton-nav"
                id="btnCerrarSesion"
                >Cerrar Sesión</a
              >
            </li>
          </ul>
        </nav>
      </div>
    </header>

    <main class="perfil-main">
      <div class="contenedor perfil-grid">
        <!-- Columna Izquierda: Info Usuario -->
        <div>
          <div class="card-perfil" id="card-info-usuario">
            <div class="header-perfil">
              <div class="avatar" id="avatarInicial" onclick="abrirSelectorImagen()">
                <?php if (!empty($userData['foto_perfil'])): ?>
                  <img src="/Proyecto_Alaska/public/api/usuario.php?action=profile_picture" alt="Foto de perfil" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                <?php else: ?>
                  <?php echo obtenerIniciales($userData['nombre']); ?>
                <?php endif; ?>
                <div class="avatar-overlay">
                  <i class="fas fa-camera"></i>
                </div>
              </div>
              <div class="info-usuario">
                <h2 id="nombreUsuario"><?php echo htmlspecialchars($userData['nombre']); ?></h2>
                <p id="emailUsuario">
                  <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($userData['correo']); ?>
                </p>
                <p id="fechaRegistro">
                  <i class="fas fa-calendar-alt"></i> Miembro desde: <?php echo formatearFecha($userData['fecha_creacion']); ?>
                </p>
                <div
                  style="
                    margin-top: 0.4rem;
                    display: flex;
                    gap: 0.5rem;
                    flex-wrap: wrap;
                    align-items: center;
                  "
                >
                  <span class="badge-plan" id="badgePlan"
                    ><i class="fas fa-crown"></i> Básico</span
                  >
                </div>
              </div>
            </div>
            <div class="divider"></div>
            <h4 class="seccion-subtitulo">
              <i class="fas fa-id-card"></i> Detalles
            </h4>
            <ul class="lista-datos" id="listaDatosUsuario">
              <li>
                <i class="fas fa-user"></i>
                <span id="datoNombre">Nombre: <?php echo htmlspecialchars($userData['nombre']); ?></span>
              </li>
              <li>
                <i class="fas fa-at"></i>
                <span id="datoUsuario">Usuario: <?php echo htmlspecialchars($userData['apodo'] ?? 'No definido'); ?></span>
              </li>
              <li>
                <i class="fas fa-globe"></i>
                <span id="datoUbicacion">Ubicación: <?php echo htmlspecialchars($userData['direccion'] ?? '-'); ?></span>
              </li>
              <li>
                <i class="fas fa-paw"></i>
                <span id="datoMascotas">Mascotas registradas: <?php echo count($pets); ?></span>
              </li>
              <li>
                <i class="fas fa-clock"></i>
                <span id="datoUltimoAcceso">Último acceso: <?php echo date('d/m/Y H:i'); ?></span>
              </li>
            </ul>
          </div>

          <div class="card-perfil" id="card-upgrade">
            <div class="upgrade-box">
              <h3><i class="fas fa-rocket"></i> Mejora tu experiencia</h3>
              <p>
                Desbloquea recordatorios ilimitados, historial extendido y
                reportes PDF avanzados pasando a Premium.
              </p>
              <button id="btnUpgrade">
                <i class="fas fa-crown"></i> Actualizar Plan
              </button>
            </div>
            <div class="alerta-lite">
              <i class="fas fa-info-circle"></i> En el plan Básico algunas
              funciones se limitan tras 10 registros (recordatorios, citas y
              mascotas).
            </div>
          </div>
        </div>

        <!-- Columna Derecha: Mascotas y Ajustes -->
        <div>
          <div class="card-perfil" id="card-mis-mascotas">
            <div class="header-perfil" style="margin-bottom: 0.4rem">
              <h2
                style="
                  margin: 0;
                  font-size: 1.15rem;
                  display: flex;
                  align-items: center;
                  gap: 0.5rem;
                  color: var(--color-primario);
                "
              >
                <i class="fas fa-paw"></i> Mis Mascotas
              </h2>
              <button
                id="btnNuevaMascota"
                style="
                  background: var(--color-primario);
                  color: #fff;
                  border: none;
                  padding: 0.5rem 0.8rem;
                  border-radius: 8px;
                  font-size: 0.75rem;
                  font-weight: 600;
                  cursor: pointer;
                  display: flex;
                  align-items: center;
                  gap: 0.4rem;
                "
              >
                <i class="fas fa-plus"></i> Añadir
              </button>
            </div>
            <div class="pets-wrapper" id="listaMascotas">
              <?php if (empty($pets)): ?>
              <div
                class="pet-card placeholder"
                style="text-align: center; padding: 2.2rem 1rem"
              >
                <p
                  style="
                    margin: 0;
                    color: var(--color-texto-claro);
                    font-size: 0.9rem;
                  "
                >
                  <i
                    class="fas fa-dog"
                    style="font-size: 1.4rem; color: var(--color-primario)"
                  ></i
                  ><br />No tienes mascotas registradas aún.
                </p>
              </div>
              <?php else: ?>
                <?php foreach ($pets as $pet): ?>
                <div class="pet-card" data-pet-id="<?php echo $pet['id']; ?>">
                  <div class="pet-head">
                    <h4 class="pet-name"><?php echo htmlspecialchars($pet['name']); ?></h4>
                    <span class="pet-species"><?php echo htmlspecialchars($pet['species']); ?></span>
                  </div>
                  <div class="pet-meta">
                    <div class="pet-meta-item">
                      <span>Edad</span>
                      <strong><?php echo number_format($pet['age'], 1); ?> años</strong>
                    </div>
                    <div class="pet-meta-item">
                      <span>Peso</span>
                      <strong><?php echo number_format($pet['weight'], 1); ?>kg</strong>
                    </div>
                    <div class="pet-meta-item">
                      <span>Salud</span>
                      <strong><?php echo traducirEstadoSalud($pet['healthStatus']); ?></strong>
                    </div>
                    <?php if ($pet['breed']): ?>
                    <div class="pet-meta-item">
                      <span>Raza</span>
                      <strong><?php echo htmlspecialchars($pet['breed']); ?></strong>
                    </div>
                    <?php endif; ?>
                  </div>
                  <div class="acciones-pet">
                    <button data-id="<?php echo $pet['id']; ?>" class="btn-edit-pet">
                      <i class="fas fa-pen"></i> Editar
                    </button>
                    <button data-id="<?php echo $pet['id']; ?>" class="danger btn-del-pet">
                      <i class="fas fa-trash"></i> Eliminar
                    </button>
                  </div>
                </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

          <div class="card-perfil" id="card-ajustes">
            <div class="header-perfil" style="margin-bottom: 0.4rem">
              <h2
                style="
                  margin: 0;
                  font-size: 1.15rem;
                  display: flex;
                  align-items: center;
                  gap: 0.5rem;
                  color: var(--color-primario);
                "
              >
                <i class="fas fa-sliders"></i> Ajustes
              </h2>
            </div>
            <div class="tabs" id="tabsAjustes">
              <button data-tab="perfil" class="activo">
                <i class="fas fa-user"></i> Perfil
              </button>
              <button data-tab="seguridad">
                <i class="fas fa-shield"></i> Seguridad
              </button>
              <button data-tab="preferencias">
                <i class="fas fa-gear"></i> Preferencias
              </button>
              <button data-tab="notificaciones">
                <i class="fas fa-bell"></i> Notificaciones
              </button>
            </div>
            <div class="paneles">
              <div class="panel-ajuste activo" id="panel-perfil">
                <form id="formPerfil">
                  <div class="form-inline-dos">
                    <div class="grupo-form-perfil">
                      <label for="inpNombre">Nombre</label>
                      <input
                        id="inpNombre"
                        type="text"
                        placeholder="Tu nombre"
                        value="<?php echo htmlspecialchars($userData['nombre']); ?>"
                      />
                    </div>
                    <div class="grupo-form-perfil">
                      <label for="inpApodo">Usuario</label>
                      <input
                        id="inpApodo"
                        type="text"
                        placeholder="Tu nombre de usuario"
                        value="<?php echo htmlspecialchars($userData['apodo'] ?? ''); ?>"
                      />
                    </div>
                  </div>
                  <div class="grupo-form-perfil">
                    <label for="inpUbicacion">Ubicación</label>
                    <input
                      id="inpUbicacion"
                      type="text"
                      placeholder="Ciudad / País"
                      value="<?php echo htmlspecialchars($userData['direccion'] ?? ''); ?>"
                    />
                  </div>
                  <button type="submit" class="btn-guardar">
                    <i class="fas fa-save"></i> Guardar Cambios
                  </button>
                </form>
              </div>
              <div class="panel-ajuste" id="panel-seguridad">
                <form id="formSeguridad">
                  <div class="grupo-form-perfil">
                    <label for="inpEmail">Email</label>
                    <input
                      id="inpEmail"
                      type="email"
                      placeholder="email@ejemplo.com"
                      value="<?php echo htmlspecialchars($userData['correo']); ?>"
                    />
                  </div>
                  <div class="form-inline-dos">
                    <div class="grupo-form-perfil">
                      <label for="inpClaveActual">Clave Actual</label>
                      <input id="inpClaveActual" type="password" />
                    </div>
                    <div class="grupo-form-perfil">
                      <label for="inpClaveNueva">Nueva Clave</label>
                      <input id="inpClaveNueva" type="password" />
                    </div>
                  </div>
                  <div class="grupo-form-perfil">
                    <label for="inpRepiteClave">Repite Nueva Clave</label>
                    <input id="inpRepiteClave" type="password" />
                  </div>
                  <button type="submit" class="btn-guardar">
                    <i class="fas fa-lock"></i> Actualizar Clave
                  </button>
                </form>
              </div>
              <div class="panel-ajuste" id="panel-preferencias">
                <form id="formPreferencias">
                  <div class="grupo-form-perfil">
                    <label for="selIdioma">Idioma</label>
                    <select id="selIdioma">
                      <option value="es">Español</option>
                      <option value="en">Inglés</option>
                    </select>
                  </div>
                  <div class="grupo-form-perfil">
                    <label for="selTema">Tema</label>
                    <select id="selTema">
                      <option value="claro">Claro</option>
                      <option value="oscuro">Oscuro</option>
                    </select>
                  </div>
                  <button type="submit" class="btn-guardar">
                    <i class="fas fa-palette"></i> Guardar Preferencias
                  </button>
                </form>
              </div>
              <div class="panel-ajuste" id="panel-notificaciones">
                <form id="formNotificaciones">
                  <div class="grupo-form-perfil">
                    <label>Recordatorios</label>
                    <select id="selRecordatorios">
                      <option value="email">Email</option>
                      <option value="app">In-App</option>
                      <option value="ambos">Ambos</option>
                    </select>
                  </div>
                  <div class="grupo-form-perfil">
                    <label>Frecuencia Resumen</label>
                    <select id="selFrecuencia">
                      <option value="diario">Diario</option>
                      <option value="semanal">Semanal</option>
                      <option value="mensual">Mensual</option>
                    </select>
                  </div>
                  <button type="submit" class="btn-guardar">
                    <i class="fas fa-bell"></i> Guardar Notificaciones
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal para cambiar foto de perfil -->
    <div id="modalFotoPerfil" class="modal-foto">
      <div class="modal-foto-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
          <h3 style="margin: 0;">Cambiar Foto de Perfil</h3>
          <button onclick="cerrarModalFoto()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        
        <div class="preview-container">
          <img id="previewImagen" class="preview-image" src="" alt="Vista previa" style="display: none;">
          <div id="previewIniciales" class="avatar" style="width: 150px; height: 150px; font-size: 3rem; margin: 0 auto;">
            <?php echo obtenerIniciales($userData['nombre']); ?>
          </div>
        </div>
        
        <div class="upload-area" onclick="document.getElementById('inputFotoPerfil').click()">
          <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--color-primario); margin-bottom: 10px;"></i>
          <p style="margin: 0; color: var(--color-texto-claro);">
            Haz clic aquí o arrastra una imagen<br>
            <small>Formatos: JPG, PNG (máx. 2MB)</small>
          </p>
        </div>
        
        <input type="file" id="inputFotoPerfil" accept="image/*" onchange="previsualizarImagen(this)">
        
        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
          <button onclick="cerrarModalFoto()" class="btn-guardar" style="background: var(--color-texto-claro);">
            Cancelar
          </button>
          <button onclick="subirFotoPerfil()" class="btn-guardar" id="btnSubirFoto" disabled>
            <i class="fas fa-save"></i> Guardar Foto
          </button>
        </div>
      </div>
    </div>

    <!-- Modal para agregar nueva mascota -->
    <div id="modalNuevaMascota" class="modal-foto">
      <div class="modal-foto-content" style="max-width: 600px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
          <h3 id="tituloModalMascota" style="margin: 0; color: var(--color-primario);"><i class="fas fa-paw"></i> Nueva Mascota</h3>
          <button onclick="cerrarModalMascota()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        
        <form id="formNuevaMascota">
          <input type="hidden" id="inpIdMascota" value="">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
            <div>
              <label for="inpNombreMascota" style="display: block; margin-bottom: 5px; font-weight: 600;">Nombre *</label>
              <input 
                type="text" 
                id="inpNombreMascota" 
                required
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;"
                placeholder="Ej: Max, Luna, Rocky"
              >
            </div>
            
            <div>
              <label for="selEspecie" style="display: block; margin-bottom: 5px; font-weight: 600;">Especie *</label>
              <select 
                id="selEspecie" 
                required
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;"
              >
                <option value="">Seleccionar especie</option>
                <option value="Perro">Perro</option>
                <option value="Gato">Gato</option>
                <option value="Ave">Ave</option>
                <option value="Pez">Pez</option>
                <option value="Reptil">Reptil</option>
                <option value="Roedor">Roedor</option>
                <option value="Otro">Otro</option>
              </select>
            </div>
            
            <div>
              <label for="inpRaza" style="display: block; margin-bottom: 5px; font-weight: 600;">Raza</label>
              <input 
                type="text" 
                id="inpRaza" 
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;"
                placeholder="Ej: Labrador, Siamés, Mestizo"
              >
            </div>
            
            <div>
              <label for="inpEdad" style="display: block; margin-bottom: 5px; font-weight: 600;">Edad (años) *</label>
              <input 
                type="number" 
                id="inpEdad" 
                min="0" 
                max="50" 
                step="0.1"
                required
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;"
                placeholder="Ej: 2.5"
              >
            </div>
            
            <div>
              <label for="inpPeso" style="display: block; margin-bottom: 5px; font-weight: 600;">Peso (kg) *</label>
              <input 
                type="number" 
                id="inpPeso" 
                min="0" 
                max="200" 
                step="0.1"
                required
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;"
                placeholder="Ej: 15.5"
              >
            </div>
            
            <div>
              <label for="selEstadoSalud" style="display: block; margin-bottom: 5px; font-weight: 600;">Estado de Salud *</label>
              <select 
                id="selEstadoSalud" 
                required
                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;"
              >
                <option value="">Seleccionar estado</option>
                <option value="Sano">Sano</option>
                <option value="Enfermo">Enfermo</option>
                <option value="En tratamiento">En tratamiento</option>
                <option value="Recuperándose">Recuperándose</option>
              </select>
            </div>
          </div>
          
          <div style="margin-bottom: 20px;">
            <label for="inpUltimaRevision" style="display: block; margin-bottom: 5px; font-weight: 600;">Última Revisión</label>
            <input 
              type="date" 
              id="inpUltimaRevision" 
              style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;"
            >
            <small style="color: #666; font-size: 12px;">Si no recuerda la fecha exacta, puede dejarla vacía</small>
          </div>
          
          <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" onclick="cerrarModalMascota()" class="btn-guardar" style="background: var(--color-texto-claro);">
              Cancelar
            </button>
            <button type="submit" class="btn-guardar" id="btnGuardarMascota">
              <i class="fas fa-save"></i> Guardar Mascota
            </button>
          </div>
        </form>
      </div>
    </div>

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
              <li><a href="citas.html">Citas</a></li>
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
          <p>
            &copy; 2025 Alaska - Cuidado de Mascotas. Todos los derechos
            reservados.
          </p>
        </div>
      </div>
    </footer>

    <script>
      // Datos del usuario desde PHP
      const userData = {
        id: <?php echo $usuarioId; ?>,
        nombre: <?php echo json_encode($userData['nombre']); ?>,
        correo: <?php echo json_encode($userData['correo']); ?>,
        apodo: <?php echo json_encode($userData['apodo'] ?? ''); ?>,
        direccion: <?php echo json_encode($userData['direccion'] ?? ''); ?>,
        fechaRegistro: <?php echo json_encode($userData['fecha_creacion']); ?>,
        plan: "basico"
      };

      const petsData = <?php echo json_encode($pets); ?>;

      function initTabs() {
        const tabs = document.querySelectorAll("#tabsAjustes button");
        const paneles = document.querySelectorAll(".panel-ajuste");
        tabs.forEach((btn) =>
          btn.addEventListener("click", () => {
            tabs.forEach((b) => b.classList.remove("activo"));
            btn.classList.add("activo");
            paneles.forEach((p) => p.classList.remove("activo"));
            const id = "panel-" + btn.dataset.tab;
            const panel = document.getElementById(id);
            if (panel) panel.classList.add("activo");
          })
        );
      }

      function mostrarNotificacion(mensaje, tipo = 'info') {
        // Crear elemento de notificación
        const notif = document.createElement('div');
        notif.className = `notificacion ${tipo}`;
        notif.style.cssText = `
          position: fixed;
          top: 20px;
          right: 20px;
          background: ${tipo === 'success' ? '#4CAF50' : tipo === 'error' ? '#f44336' : '#2196F3'};
          color: white;
          padding: 15px 20px;
          border-radius: 8px;
          box-shadow: 0 4px 12px rgba(0,0,0,0.15);
          z-index: 10000;
          max-width: 300px;
          font-size: 14px;
          animation: slideIn 0.3s ease;
        `;
        notif.textContent = mensaje;
        
        document.body.appendChild(notif);
        
        // Remover después de 3 segundos
        setTimeout(() => {
          notif.style.animation = 'slideOut 0.3s ease';
          setTimeout(() => document.body.removeChild(notif), 300);
        }, 3000);
      }

      function actualizarPerfil(formData) {
        fetch('/Proyecto_Alaska/public/api/usuario.php', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            mostrarNotificacion('Perfil actualizado correctamente', 'success');
            // Actualizar datos en la página
            if (formData.nombre) {
              document.getElementById('nombreUsuario').textContent = formData.nombre;
              document.getElementById('datoNombre').textContent = 'Nombre: ' + formData.nombre;
            }
            if (formData.direccion) {
              document.getElementById('datoUbicacion').textContent = 'Ubicación: ' + formData.direccion;
            }
          } else {
            mostrarNotificacion(data.message || 'Error al actualizar perfil', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          mostrarNotificacion('Error de conexión', 'error');
        });
      }

      function actualizarSeguridad(formData) {
        fetch('/Proyecto_Alaska/public/api/usuario.php', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            mostrarNotificacion('Seguridad actualizada correctamente', 'success');
            // Limpiar campos de contraseña
            document.getElementById('inpClaveActual').value = '';
            document.getElementById('inpClaveNueva').value = '';
            document.getElementById('inpRepiteClave').value = '';
          } else {
            mostrarNotificacion(data.message || 'Error al actualizar seguridad', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          mostrarNotificacion('Error de conexión', 'error');
        });
      }

      // Funciones para manejo de foto de perfil
      function abrirSelectorImagen() {
        document.getElementById('modalFotoPerfil').style.display = 'block';
      }

      function cerrarModalFoto() {
        document.getElementById('modalFotoPerfil').style.display = 'none';
        document.getElementById('inputFotoPerfil').value = '';
        document.getElementById('previewImagen').style.display = 'none';
        document.getElementById('previewIniciales').style.display = 'flex';
        document.getElementById('btnSubirFoto').disabled = true;
      }

      function previsualizarImagen(input) {
        if (input.files && input.files[0]) {
          const file = input.files[0];
          
          // Validar tamaño (2MB max)
          if (file.size > 2 * 1024 * 1024) {
            mostrarNotificacion('La imagen debe ser menor a 2MB', 'error');
            return;
          }
          
          // Validar tipo
          if (!file.type.match('image.*')) {
            mostrarNotificacion('Por favor selecciona una imagen válida', 'error');
            return;
          }
          
          const reader = new FileReader();
          reader.onload = function(e) {
            document.getElementById('previewImagen').src = e.target.result;
            document.getElementById('previewImagen').style.display = 'block';
            document.getElementById('previewIniciales').style.display = 'none';
            document.getElementById('btnSubirFoto').disabled = false;
          };
          reader.readAsDataURL(file);
        }
      }

      function subirFotoPerfil() {
        const fileInput = document.getElementById('inputFotoPerfil');
        const file = fileInput.files[0];
        
        if (!file) {
          mostrarNotificacion('Por favor selecciona una imagen', 'error');
          return;
        }
        
        const formData = new FormData();
        formData.append('foto_perfil', file);
        formData.append('action', 'upload_profile_picture');
        
        // Mostrar loading
        document.getElementById('btnSubirFoto').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo...';
        document.getElementById('btnSubirFoto').disabled = true;
        
        fetch('/Proyecto_Alaska/public/api/usuario.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            mostrarNotificacion('Foto de perfil actualizada correctamente', 'success');
            
            // Actualizar avatar en la página usando endpoint protegido
            const newImageUrl = '/Proyecto_Alaska/public/api/usuario.php?action=profile_picture&v=' + Date.now();
            document.getElementById('avatarInicial').innerHTML = `
              <img src="${newImageUrl}" alt="Foto de perfil" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
              <div class="avatar-overlay">
                <i class="fas fa-camera"></i>
              </div>
            `;
            
            // Actualizar foto en la barra de navegación si existe
            actualizarFotoNavegacion(newImageUrl);
            
            cerrarModalFoto();
          } else {
            mostrarNotificacion(data.message || 'Error al subir la imagen', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          mostrarNotificacion('Error de conexión', 'error');
        })
        .finally(() => {
          document.getElementById('btnSubirFoto').innerHTML = '<i class="fas fa-save"></i> Guardar Foto';
          document.getElementById('btnSubirFoto').disabled = false;
        });
      }

      function actualizarFotoNavegacion(imageUrl) {
        // Usar el sistema global de sincronización
        if (window.profileSync) {
          window.profileSync.notificarCambio(imageUrl);
        } else {
          // Fallback si el script global no está cargado
          console.warn('Script de sincronización global no encontrado');
          const profileElements = document.querySelectorAll('.inicial-circulo');
          
          profileElements.forEach(element => {
            const existingImg = element.querySelector('img');
            
            if (existingImg) {
              existingImg.src = imageUrl + '?v=' + Date.now();
            } else {
              element.innerHTML = '';
              const img = document.createElement('img');
              img.src = imageUrl + '?v=' + Date.now();
              img.alt = 'Perfil';
              img.style.cssText = 'width: 100%; height: 100%; object-fit: cover; border-radius: 50%;';
              element.appendChild(img);
            }
          });
        }
        
        console.log('Foto actualizada:', imageUrl);
      }

      // Funciones para manejo de mascotas
      function abrirModalMascota() {
        // Limpiar formulario para nueva mascota
        document.getElementById('formNuevaMascota').reset();
        document.getElementById('inpIdMascota').value = '';
        document.getElementById('tituloModalMascota').innerHTML = '<i class="fas fa-paw"></i> Nueva Mascota';
        
        document.getElementById('modalNuevaMascota').style.display = 'block';
        // Establecer fecha de hoy como valor por defecto para última revisión
        document.getElementById('inpUltimaRevision').value = new Date().toISOString().split('T')[0];
      }

      function cerrarModalMascota() {
        document.getElementById('modalNuevaMascota').style.display = 'none';
        document.getElementById('formNuevaMascota').reset();
        document.getElementById('inpIdMascota').value = '';
        document.getElementById('tituloModalMascota').innerHTML = '<i class="fas fa-paw"></i> Nueva Mascota';
      }

      function editarMascota(petId) {
        // Buscar la mascota en los datos cargados
        const mascotas = <?php echo json_encode($pets); ?>;
        const mascota = mascotas.find(m => m.id == petId);
        
        if (!mascota) {
          mostrarNotificacion('No se encontró la mascota', 'error');
          return;
        }

        // Llenar el formulario con los datos de la mascota
        document.getElementById('inpIdMascota').value = mascota.id;
        document.getElementById('inpNombreMascota').value = mascota.name || '';
        document.getElementById('selEspecie').value = mascota.species || '';
        document.getElementById('inpRaza').value = mascota.breed || '';
        document.getElementById('inpEdad').value = mascota.age || '';
        document.getElementById('inpPeso').value = mascota.weight || '';
        document.getElementById('selEstadoSalud').value = mascota.healthStatus || 'healthy';
        document.getElementById('inpUltimaRevision').value = mascota.lastCheckup ? mascota.lastCheckup.split(' ')[0] : '';

        // Cambiar el título del modal
        document.getElementById('tituloModalMascota').innerHTML = '<i class="fas fa-pen"></i> Editar Mascota';
        
        // Abrir el modal
        document.getElementById('modalNuevaMascota').style.display = 'block';
      }

      function guardarMascota(formData) {
        const petId = document.getElementById('inpIdMascota').value;
        const isEditing = petId && petId !== '';
        
        const method = isEditing ? 'PUT' : 'POST';
        const url = '../public/api/mascotas.php';
        
        if (isEditing) {
          formData.id = parseInt(petId);
        }

        fetch(url, {
          method: method,
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const mensaje = isEditing ? 'Mascota actualizada correctamente' : 'Mascota agregada correctamente';
            mostrarNotificacion(mensaje, 'success');
            cerrarModalMascota();
            // Recargar la lista de mascotas
            location.reload();
          } else {
            const mensaje = isEditing ? 'Error al actualizar mascota' : 'Error al agregar mascota';
            mostrarNotificacion(data.message || mensaje, 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          mostrarNotificacion('Error de conexión', 'error');
        });
      }

      function eliminarMascota(petId) {
        if (!confirm('¿Estás seguro de que quieres eliminar esta mascota?\n\nEsta acción también eliminará todas las citas programadas para esta mascota.\n\nEsta acción no se puede deshacer.')) {
          return;
        }

        console.log('Eliminando mascota ID:', petId); // Debug

        fetch(`../public/api/mascotas.php?id=${petId}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json'
          }
        })
        .then(response => {
          console.log('Response status:', response.status); // Debug
          return response.json();
        })
        .then(data => {
          console.log('Response data:', data); // Debug
          if (data.success) {
            mostrarNotificacion('Mascota eliminada correctamente', 'success');
            // Recargar la página para actualizar la lista
            setTimeout(() => {
              location.reload();
            }, 1500);
          } else {
            mostrarNotificacion(data.message || data.error || 'Error al eliminar mascota', 'error');
          }
        })
        .catch(error => {
          console.error('Error completo:', error); // Debug
          mostrarNotificacion('Error de conexión', 'error');
        });
      }

      // Drag and drop para el área de subida
      document.addEventListener('DOMContentLoaded', function() {
        const uploadArea = document.querySelector('.upload-area');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
          uploadArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
          e.preventDefault();
          e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
          uploadArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
          uploadArea.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight(e) {
          uploadArea.classList.add('dragover');
        }
        
        function unhighlight(e) {
          uploadArea.classList.remove('dragover');
        }
        
        uploadArea.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
          const dt = e.dataTransfer;
          const files = dt.files;
          
          if (files.length > 0) {
            document.getElementById('inputFotoPerfil').files = files;
            previsualizarImagen(document.getElementById('inputFotoPerfil'));
          }
        }
      });

      document.addEventListener("DOMContentLoaded", () => {
        initTabs();
        
        // Manejar envío de formulario de perfil
        document.getElementById('formPerfil').addEventListener('submit', (e) => {
          e.preventDefault();
          const formData = {
            action: 'update_profile',
            nombre: document.getElementById('inpNombre').value,
            apodo: document.getElementById('inpApodo').value,
            direccion: document.getElementById('inpUbicacion').value
          };
          actualizarPerfil(formData);
        });

        // Manejar envío de formulario de seguridad
        document.getElementById('formSeguridad').addEventListener('submit', (e) => {
          e.preventDefault();
          const claveActual = document.getElementById('inpClaveActual').value;
          const claveNueva = document.getElementById('inpClaveNueva').value;
          const repiteClave = document.getElementById('inpRepiteClave').value;
          
          if (!claveActual || !claveNueva || !repiteClave) {
            mostrarNotificacion('Todos los campos son obligatorios', 'error');
            return;
          }
          
          if (claveNueva !== repiteClave) {
            mostrarNotificacion('Las contraseñas no coinciden', 'error');
            return;
          }
          
          if (claveNueva.length < 8) {
            mostrarNotificacion('La contraseña debe tener al menos 8 caracteres', 'error');
            return;
          }
          
          const formData = {
            action: 'update_security',
            email: document.getElementById('inpEmail').value,
            current_password: claveActual,
            new_password: claveNueva
          };
          actualizarSeguridad(formData);
        });

        // Botones de acción
        document.getElementById('btnUpgrade').addEventListener('click', () => {
          mostrarNotificacion('Función de upgrade en desarrollo', 'info');
        });

        document.getElementById('btnNuevaMascota').addEventListener('click', () => {
          abrirModalMascota();
        });

        // Event listener para formulario de nueva mascota
        document.getElementById('formNuevaMascota').addEventListener('submit', (e) => {
          e.preventDefault();
          
          const formData = {
            action: 'create',
            name: document.getElementById('inpNombreMascota').value.trim(),
            species: document.getElementById('selEspecie').value,
            breed: document.getElementById('inpRaza').value.trim() || 'No especificada',
            age: parseFloat(document.getElementById('inpEdad').value) || 0,
            weight: parseFloat(document.getElementById('inpPeso').value) || 0,
            healthStatus: document.getElementById('selEstadoSalud').value,
            lastCheckup: document.getElementById('inpUltimaRevision').value || new Date().toISOString().split('T')[0]
          };
          
          // Validaciones básicas
          if (!formData.name || !formData.species || !formData.healthStatus) {
            mostrarNotificacion('Por favor complete todos los campos obligatorios', 'error');
            return;
          }
          
          if (formData.age < 0 || formData.age > 50) {
            mostrarNotificacion('La edad debe estar entre 0 y 50 años', 'error');
            return;
          }
          
          if (formData.weight <= 0 || formData.weight > 200) {
            mostrarNotificacion('El peso debe estar entre 0.1 y 200 kg', 'error');
            return;
          }
          
          guardarMascota(formData);
        });

        // Event listeners para formularios de preferencias y notificaciones
        document.getElementById('formPreferencias').addEventListener('submit', (e) => {
          e.preventDefault();
          mostrarNotificacion('Preferencias guardadas', 'success');
        });

        document.getElementById('formNotificaciones').addEventListener('submit', (e) => {
          e.preventDefault();
          mostrarNotificacion('Notificaciones actualizadas', 'success');
        });

        // Botones de editar y eliminar mascotas
        document.addEventListener('click', (e) => {
          if (e.target.classList.contains('btn-edit-pet') || e.target.closest('.btn-edit-pet')) {
            const button = e.target.classList.contains('btn-edit-pet') ? e.target : e.target.closest('.btn-edit-pet');
            const petId = button.dataset.id;
            editarMascota(petId);
          }
          
          if (e.target.classList.contains('btn-del-pet') || e.target.closest('.btn-del-pet')) {
            console.log('Botón eliminar clickeado'); // Debug
            const button = e.target.classList.contains('btn-del-pet') ? e.target : e.target.closest('.btn-del-pet');
            const petId = button.dataset.id;
            console.log('Pet ID obtenido:', petId); // Debug
            eliminarMascota(petId);
          }
        });
        
        // Cerrar modales al hacer clic fuera de ellos
        document.getElementById('modalFotoPerfil').addEventListener('click', (e) => {
          if (e.target === e.currentTarget) {
            cerrarModalFoto();
          }
        });
        
        document.getElementById('modalNuevaMascota').addEventListener('click', (e) => {
          if (e.target === e.currentTarget) {
            cerrarModalMascota();
          }
        });
        
        // Cerrar modales con tecla Escape
        document.addEventListener('keydown', (e) => {
          if (e.key === 'Escape') {
            const modalFoto = document.getElementById('modalFotoPerfil');
            const modalMascota = document.getElementById('modalNuevaMascota');
            
            if (modalFoto.style.display === 'block') {
              cerrarModalFoto();
            }
            if (modalMascota.style.display === 'block') {
              cerrarModalMascota();
            }
          }
        });
      });

      // Añadir estilos para las animaciones de notificación
      const style = document.createElement('style');
      style.textContent = `
        @keyframes slideIn {
          from { transform: translateX(100%); opacity: 0; }
          to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
          from { transform: translateX(0); opacity: 1; }
          to { transform: translateX(100%); opacity: 0; }
        }
      `;
      document.head.appendChild(style);
    </script>
    <script src="../views/MenuView.js"></script>
    <script src="../assets/js/profile-sync.js"></script>
  </body>
</html>