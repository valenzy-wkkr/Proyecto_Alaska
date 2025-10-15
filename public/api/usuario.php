<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    // Caso especial: si se solicita imagen, devolver 401 sin JSON
    if (($_SERVER['REQUEST_METHOD'] === 'GET') && isset($_GET['action']) && $_GET['action'] === 'profile_picture') {
        http_response_code(401);
        exit();
    }
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Incluir archivos necesarios
require_once '../../app/core/Autoloader.php';

use App\Core\Database;

$method = $_SERVER['REQUEST_METHOD'];
$usuarioId = $_SESSION['usuario_id'];

try {
    $db = Database::getConnection();

    // Acción especial: servir la imagen del perfil del usuario autenticado
    if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'profile_picture') {
        // Buscar el nombre de archivo de la foto del usuario autenticado
        $stmt = $db->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
        $stmt->bind_param('i', $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if (!$row || empty($row['foto_perfil'])) {
            http_response_code(404);
            exit();
        }
        $filename = $row['foto_perfil'];
        $path = realpath(__DIR__ . '/../../uploads/perfiles/' . $filename);
        // Validar que el archivo exista y esté dentro del directorio esperado
        $baseDir = realpath(__DIR__ . '/../../uploads/perfiles');
        if (!$path || strpos($path, $baseDir) !== 0 || !is_file($path)) {
            http_response_code(404);
            exit();
        }
        // Determinar tipo MIME y enviar imagen
        $mime = mime_content_type($path);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif'])) {
            $mime = 'application/octet-stream';
        }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        readfile($path);
        exit();
    }

    switch ($method) {
        case 'GET':
            // Obtener información del usuario
            header('Content-Type: application/json');
            $stmt = $db->prepare("SELECT id, nombre, correo, apodo, direccion, fecha_creacion FROM usuarios WHERE id = ?");
            $stmt->bind_param('i', $usuarioId);
            $stmt->execute();
            $result = $stmt->get_result();
            $userData = $result->fetch_assoc();

            if ($userData) {
                echo json_encode(['success' => true, 'data' => $userData]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            }
            break;

        case 'POST':
            // Manejar subida de archivos (foto de perfil)
            header('Content-Type: application/json');
            if (isset($_POST['action']) && $_POST['action'] === 'upload_profile_picture') {
                if (!isset($_FILES['foto_perfil']) || $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_OK) {
                    echo json_encode(['success' => false, 'message' => 'Error al subir la imagen']);
                    break;
                }

                $file = $_FILES['foto_perfil'];

                // Validaciones
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $maxSize = 2 * 1024 * 1024; // 2MB

                if (!in_array($file['type'], $allowedTypes)) {
                    echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido. Use JPG, PNG o GIF']);
                    break;
                }

                if ($file['size'] > $maxSize) {
                    echo json_encode(['success' => false, 'message' => 'La imagen debe ser menor a 2MB']);
                    break;
                }

                // Crear nombre único para el archivo
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'perfil_' . $usuarioId . '_' . time() . '.' . $extension;
                $uploadPath = '../../uploads/perfiles/' . $filename;

                // Crear directorio si no existe
                $uploadDir = '../../uploads/perfiles/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Mover archivo
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    // Actualizar base de datos
                    $stmt = $db->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
                    $stmt->bind_param('si', $filename, $usuarioId);

                    if ($stmt->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Foto actualizada correctamente', 'filename' => $filename]);
                    } else {
                        // Eliminar archivo si no se pudo actualizar la BD
                        unlink($uploadPath);
                        echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            }
            break;

        case 'PUT':
            // Actualizar información del usuario
            header('Content-Type: application/json');
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
                break;
            }

            $action = $input['action'] ?? '';

            switch ($action) {
                case 'update_profile':
                    // Actualizar perfil básico
                    $nombre = trim($input['nombre'] ?? '');
                    $apodo = trim($input['apodo'] ?? '');
                    $direccion = trim($input['direccion'] ?? '');

                    if (empty($nombre)) {
                        echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
                        break;
                    }

                    // Verificar si el apodo ya existe (si se proporciona)
                    if (!empty($apodo)) {
                        $stmt = $db->prepare("SELECT id FROM usuarios WHERE apodo = ? AND id != ?");
                        $stmt->bind_param('si', $apodo, $usuarioId);
                        $stmt->execute();
                        if ($stmt->get_result()->num_rows > 0) {
                            echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya está en uso']);
                            break;
                        }
                    }

                    // Actualizar datos
                    $stmt = $db->prepare("UPDATE usuarios SET nombre = ?, apodo = ?, direccion = ? WHERE id = ?");
                    $stmt->bind_param('sssi', $nombre, $apodo, $direccion, $usuarioId);

                    if ($stmt->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error al actualizar el perfil']);
                    }
                    break;

                case 'update_security':
                    // Actualizar email y/o contraseña
                    $email = trim($input['email'] ?? '');
                    $currentPassword = $input['current_password'] ?? '';
                    $newPassword = $input['new_password'] ?? '';

                    // Verificar contraseña actual
                    $stmt = $db->prepare("SELECT clave FROM usuarios WHERE id = ?");
                    $stmt->bind_param('i', $usuarioId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();

                    if (!$user || !password_verify($currentPassword, $user['clave'])) {
                        echo json_encode(['success' => false, 'message' => 'Contraseña actual incorrecta']);
                        break;
                    }

                    $updates = [];
                    $params = [];
                    $types = '';

                    // Verificar si se debe actualizar el email
                    if (!empty($email)) {
                        // Verificar si el email ya existe
                        $stmt = $db->prepare("SELECT id FROM usuarios WHERE correo = ? AND id != ?");
                        $stmt->bind_param('si', $email, $usuarioId);
                        $stmt->execute();
                        if ($stmt->get_result()->num_rows > 0) {
                            echo json_encode(['success' => false, 'message' => 'El email ya está en uso']);
                            break;
                        }
                        $updates[] = "correo = ?";
                        $params[] = $email;
                        $types .= 's';
                    }

                    // Verificar si se debe actualizar la contraseña
                    if (!empty($newPassword)) {
                        if (strlen($newPassword) < 8) {
                            echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres']);
                            break;
                        }
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        $updates[] = "clave = ?";
                        $params[] = $hashedPassword;
                        $types .= 's';
                    }

                    if (empty($updates)) {
                        echo json_encode(['success' => false, 'message' => 'No hay cambios para realizar']);
                        break;
                    }

                    $params[] = $usuarioId;
                    $types .= 'i';

                    $sql = "UPDATE usuarios SET " . implode(', ', $updates) . " WHERE id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->bind_param($types, ...$params);

                    if ($stmt->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Seguridad actualizada correctamente']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error al actualizar la seguridad']);
                    }
                    break;

                default:
                    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
                    break;
            }
            break;

        case 'DELETE':
            // Eliminar cuenta de usuario (opcional)
            header('Content-Type: application/json');
            $input = json_decode(file_get_contents('php://input'), true);
            $password = $input['password'] ?? '';

            // Verificar contraseña antes de eliminar
            $stmt = $db->prepare("SELECT clave FROM usuarios WHERE id = ?");
            $stmt->bind_param('i', $usuarioId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (!$user || !password_verify($password, $user['clave'])) {
                echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
                break;
            }

            // Eliminar datos relacionados primero
            $db->begin_transaction();

            try {
                // Eliminar recordatorios
                $stmt = $db->prepare("DELETE FROM recordatorios WHERE usuario_id = ?");
                $stmt->bind_param('i', $usuarioId);
                $stmt->execute();

                // Eliminar mascotas
                $stmt = $db->prepare("DELETE FROM mascotas WHERE usuario_id = ?");
                $stmt->bind_param('i', $usuarioId);
                $stmt->execute();

                // Eliminar usuario
                $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->bind_param('i', $usuarioId);
                $stmt->execute();

                $db->commit();

                // Destruir sesión
                session_destroy();

                echo json_encode(['success' => true, 'message' => 'Cuenta eliminada correctamente']);
            } catch (Exception $e) {
                $db->rollback();
                echo json_encode(['success' => false, 'message' => 'Error al eliminar la cuenta']);
            }
            break;

        default:
            header('Content-Type: application/json');
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
    }

} catch (Exception $e) {
    error_log("Error en usuario.php: " . $e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
?>