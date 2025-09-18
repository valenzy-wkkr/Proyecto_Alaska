<?php
// Configurar encabezados solo para respuestas JSON
if (!isset($_POST['title'])) {
    header('Content-Type: application/json');
}

// Conexión directa a la base de datos
$servidor = "localhost";
$usuario = "root";
$clave = "";
$basedatos = "alaska";
$port = '3306';

// Crear la base de datos si no existe
$conexion_inicial = mysqli_connect($servidor, $usuario, $clave, "", $port);
if (!$conexion_inicial) {
    die("Error al conectar con el servidor: " . mysqli_connect_error());
}

// Crear la base de datos si no existe
$sql_crear_bd = "CREATE DATABASE IF NOT EXISTS $basedatos";
if (!mysqli_query($conexion_inicial, $sql_crear_bd)) {
    die("Error al crear la base de datos: " . mysqli_error($conexion_inicial));
}
mysqli_close($conexion_inicial);

// Conectar a la base de datos
$conexion = mysqli_connect($servidor, $usuario, $clave, $basedatos, $port);
if (!$conexion) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}

// Crear la tabla recordatorios si no existe
$sql_crear_tabla = "CREATE TABLE IF NOT EXISTS recordatorios (
    id INT(11) NOT NULL AUTO_INCREMENT,
    usuario_id INT(11) NOT NULL,
    mascota_id INT(11) NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    fecha_recordatorio DATETIME NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    notas TEXT,
    urgente TINYINT(1) DEFAULT 0,
    completado TINYINT(1) DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (!mysqli_query($conexion, $sql_crear_tabla)) {
    die("Error al crear la tabla recordatorios: " . mysqli_error($conexion));
}

// Obtener el método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Obtener datos del usuario desde la sesión (simulado por ahora)
$usuario_id = 1; // En producción, esto vendría de la sesión

switch ($method) {
    case 'GET':
        // Obtener todos los recordatorios del usuario
        $sql = "SELECT r.*, m.nombre as mascota_nombre 
                FROM recordatorios r 
                JOIN mascotas m ON r.mascota_id = m.id 
                WHERE r.usuario_id = ? 
                ORDER BY r.fecha_recordatorio ASC";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "i", $usuario_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $recordatorios = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $recordatorios[] = [
                'id' => $row['id'],
                'title' => $row['titulo'],
                'date' => $row['fecha_recordatorio'],
                'type' => $row['tipo'],
                'petId' => $row['mascota_id'],
                'petName' => $row['mascota_nombre'],
                'notes' => $row['notas'],
                'urgent' => (bool)$row['urgente'],
                'completed' => (bool)$row['completado']
            ];
        }
        
        echo json_encode($recordatorios);
        break;
        
    case 'POST':
        // Crear nuevo recordatorio
        // Verificar si los datos vienen como JSON o como formulario POST
        if (isset($_POST['title'])) {
            // Datos del formulario POST
            $titulo = $_POST['title'] ?? '';
            $fecha_recordatorio = $_POST['date'] ?? '';
            $tipo = $_POST['type'] ?? '';
            $mascota_id = 1; // Valor por defecto
            $mascota_nombre = $_POST['petName'] ?? '';
            $notas = $_POST['notes'] ?? '';
            $urgente = false;
        } else {
            // Datos como JSON
            $data = json_decode(file_get_contents('php://input'), true);
            
            $titulo = $data['title'] ?? '';
            $fecha_recordatorio = $data['date'] ?? '';
            $tipo = $data['type'] ?? '';
            $mascota_id = $data['petId'] ?? 0;
            $mascota_nombre = $data['petName'] ?? '';
            $notas = $data['notes'] ?? '';
            $urgente = $data['urgent'] ?? false;
        }
        
        // La tabla ya se creó al inicio del script
        
        // Insertar el recordatorio
        $sql = "INSERT INTO recordatorios (usuario_id, mascota_id, titulo, fecha_recordatorio, tipo, notas, urgente) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conexion, $sql);
        
        // Convertir valores para evitar errores
        $usuario_id = (int)$usuario_id;
        $mascota_id = (int)$mascota_id;
        $urgente_int = $urgente ? 1 : 0;
        
        mysqli_stmt_bind_param($stmt, "iissssi", $usuario_id, $mascota_id, $titulo, $fecha_recordatorio, $tipo, $notas, $urgente_int);
        
        if (mysqli_stmt_execute($stmt)) {
            $nuevo_recordatorio = [
                'id' => mysqli_insert_id($conexion),
                'title' => $titulo,
                'date' => $fecha_recordatorio,
                'type' => $tipo,
                'petId' => $mascota_id,
                'petName' => $mascota_nombre ?? '',
                'notes' => $notas,
                'urgent' => $urgente,
                'completed' => false
            ];
            
            // Si la solicitud viene del formulario, redirigir
            if (isset($_POST['title'])) {
                // Redirigir al dashboard con mensaje de éxito
                echo "<script>alert('Recordatorio guardado correctamente'); window.location.href='../dashboard.php';</script>";
                exit;
            } else {
                // Respuesta JSON para solicitudes AJAX
                echo json_encode(['success' => true, 'recordatorio' => $nuevo_recordatorio]);
            }
        } else {
            if (isset($_POST['title'])) {
                // Redirigir con error
                header('Location: ../dashboard.php?mensaje=error_recordatorio');
                exit;
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al crear el recordatorio']);
            }
        }
        break;
        
    case 'PUT':
        // Actualizar recordatorio
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? 0;
        
        $titulo = $data['title'] ?? '';
        $fecha_recordatorio = $data['date'] ?? '';
        $tipo = $data['type'] ?? '';
        $mascota_id = $data['petId'] ?? 0;
        $notas = $data['notes'] ?? '';
        $urgente = $data['urgent'] ?? false;
        $completado = $data['completed'] ?? false;
        
        $sql = "UPDATE recordatorios SET titulo = ?, fecha_recordatorio = ?, tipo = ?, mascota_id = ?, notas = ?, urgente = ?, completado = ? 
                WHERE id = ? AND usuario_id = ?";
        
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ssssisii", $titulo, $fecha_recordatorio, $tipo, $mascota_id, $notas, $urgente, $completado, $id, $usuario_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Recordatorio actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al actualizar el recordatorio']);
        }
        break;
        
    case 'DELETE':
        // Eliminar recordatorio
        $id = $_GET['id'] ?? 0;
        
        $sql = "DELETE FROM recordatorios WHERE id = ? AND usuario_id = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $id, $usuario_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Recordatorio eliminado correctamente']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al eliminar el recordatorio']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}

mysqli_close($conexion);
?>
