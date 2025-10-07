<?php
session_start();

require_once __DIR__ . '/app/core/Autoloader.php';
use App\Core\Database;

echo "<h2>Información de la Sesión</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['usuario_id'])) {
    echo "<h3>Intentando obtener datos del usuario...</h3>";
    
    try {
        $db = Database::getConnection();
        $usuarioId = $_SESSION['usuario_id'];
        
        echo "<p>✅ <strong>Conexión a BD exitosa</strong></p>";
        echo "<p>🔍 Buscando usuario con ID: " . $usuarioId . "</p>";
        
        // Verificar estructura de la tabla
        echo "<h4>Estructura de la tabla usuarios:</h4>";
        $result = $db->query("DESCRIBE usuarios");
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td></tr>";
        }
        echo "</table>";
        
        // Intentar obtener datos del usuario
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->bind_param('i', $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        
        if ($userData) {
            echo "<h4>✅ Datos del usuario encontrados:</h4>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            foreach ($userData as $campo => $valor) {
                echo "<tr><td><strong>" . htmlspecialchars($campo) . "</strong></td><td>" . htmlspecialchars($valor ?? 'NULL') . "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p>❌ <strong>NO se encontró usuario con ID: " . $usuarioId . "</strong></p>";
            
            // Verificar si hay usuarios en la tabla
            $result = $db->query("SELECT COUNT(*) as total FROM usuarios");
            $count = $result->fetch_assoc();
            echo "<p>📊 Total de usuarios en la base de datos: " . $count['total'] . "</p>";
            
            if ($count['total'] > 0) {
                echo "<h4>👥 Usuarios disponibles (primeros 5):</h4>";
                $result = $db->query("SELECT id, nombre, correo FROM usuarios LIMIT 5");
                echo "<table border='1' style='border-collapse: collapse;'>";
                echo "<tr><th>ID</th><th>Nombre</th><th>Correo</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['correo']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p>❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
} else {
    echo "<p>❌ <strong>No hay usuario_id en la sesión</strong></p>";
    echo "<p>Necesitas hacer login primero en: <a href='/Proyecto_Alaska/public/auth/login.php'>Login</a></p>";
}
?>