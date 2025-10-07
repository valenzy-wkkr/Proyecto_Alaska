<?php
// Verificar estructura de tabla mascotas y test de eliminación
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo "Usuario no logueado";
    exit;
}

require_once __DIR__ . '/app/core/Autoloader.php';
use App\Core\Database;
use App\Models\Pet;

try {
    $db = Database::getConnection();
    $usuarioId = $_SESSION['usuario_id'];
    
    echo "<h2>Verificación de estructura de mascotas</h2>";
    echo "<p><strong>Usuario ID:</strong> $usuarioId</p>";
    
    // Verificar si la tabla existe
    $result = $db->query("SHOW TABLES LIKE 'mascotas'");
    if ($result->num_rows == 0) {
        echo "<p style='color: red;'>❌ La tabla 'mascotas' no existe</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ La tabla 'mascotas' existe</p>";
    
    // Mostrar estructura
    echo "<h3>Estructura de la tabla:</h3>";
    $result = $db->query("DESCRIBE mascotas");
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Contar mascotas del usuario
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM mascotas WHERE usuario_id = ?");
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo "<h3>Mascotas del usuario:</h3>";
    echo "<p><strong>Total:</strong> " . $row['total'] . "</p>";
    
    // Listar mascotas
    if ($row['total'] > 0) {
        $stmt = $db->prepare("SELECT * FROM mascotas WHERE usuario_id = ?");
        $stmt->bind_param('i', $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Especie</th><th>Acciones</th></tr>";
        while ($mascota = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $mascota['id'] . "</td>";
            echo "<td>" . htmlspecialchars($mascota['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($mascota['especie']) . "</td>";
            echo "<td>";
            echo "<button onclick='testDirectDelete(" . $mascota['id'] . ")'>Test DELETE</button>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test directo de eliminación
    if (isset($_GET['test_delete'])) {
        $testId = (int)$_GET['test_delete'];
        echo "<hr>";
        echo "<h3>Test DELETE directo para ID: $testId</h3>";
        
        // Verificar que la mascota existe y pertenece al usuario
        $stmt = $db->prepare("SELECT * FROM mascotas WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param('ii', $testId, $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            echo "<p style='color: red;'>❌ Mascota no encontrada o no pertenece al usuario</p>";
        } else {
            $mascota = $result->fetch_assoc();
            echo "<p><strong>Mascota encontrada:</strong> " . htmlspecialchars($mascota['nombre']) . "</p>";
            
            // Test con query directa
            $stmt = $db->prepare("DELETE FROM mascotas WHERE id = ? AND usuario_id = ?");
            $stmt->bind_param('ii', $testId, $usuarioId);
            $result = $stmt->execute();
            
            echo "<p><strong>Resultado:</strong> " . ($result ? 'Exitoso' : 'Error') . "</p>";
            echo "<p><strong>Filas afectadas:</strong> " . $stmt->affected_rows . "</p>";
            
            if (!$result) {
                echo "<p style='color: red;'><strong>Error:</strong> " . $stmt->error . "</p>";
            } else {
                echo "<p style='color: green;'>✅ Eliminación exitosa</p>";
            }
        }
        
        echo "<p><a href='?" . http_build_query([]) . "'>Recargar</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<script>
function testDirectDelete(id) {
    if (confirm('¿Hacer test de DELETE directo para ID ' + id + '? ESTO ELIMINARÁ LA MASCOTA REALMENTE.')) {
        window.location.href = '?test_delete=' + id;
    }
}
</script>

<style>
table { border-collapse: collapse; margin: 20px 0; }
th, td { padding: 10px; text-align: left; }
button { background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; }
h2, h3 { color: #333; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
</style>