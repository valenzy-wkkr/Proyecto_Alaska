<?php
// Script de debug para eliminar mascotas
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo "<h2>Error: Usuario no logueado</h2>";
    exit;
}

require_once __DIR__ . '/app/core/Autoloader.php';
use App\Models\Pet;

$usuarioId = $_SESSION['usuario_id'];
$petModel = new Pet();

echo "<h2>Debug - Eliminación de Mascotas</h2>";
echo "<p><strong>Usuario ID:</strong> $usuarioId</p>";

// Listar mascotas actuales
$mascotas = $petModel->allByUser($usuarioId);
echo "<h3>Mascotas actuales del usuario:</h3>";

if (count($mascotas) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Especie</th><th>Acciones</th></tr>";
    foreach ($mascotas as $mascota) {
        echo "<tr>";
        echo "<td>" . $mascota['id'] . "</td>";
        echo "<td>" . htmlspecialchars($mascota['name']) . "</td>";
        echo "<td>" . htmlspecialchars($mascota['species']) . "</td>";
        echo "<td>";
        echo "<button onclick='testDelete(" . $mascota['id'] . ")'>Eliminar</button>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay mascotas registradas para este usuario.</p>";
}

// Verificar si se envió una eliminación
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    echo "<hr>";
    echo "<h3>Intentando eliminar mascota ID: $deleteId</h3>";
    
    $result = $petModel->delete($usuarioId, $deleteId);
    
    if ($result) {
        echo "<p style='color: green;'>✅ Mascota eliminada exitosamente</p>";
    } else {
        echo "<p style='color: red;'>❌ Error al eliminar mascota</p>";
    }
    
    // Mostrar lista actualizada
    echo "<h4>Mascotas después de la eliminación:</h4>";
    $mascotasActualizadas = $petModel->allByUser($usuarioId);
    if (count($mascotasActualizadas) > 0) {
        foreach ($mascotasActualizadas as $mascota) {
            echo "<p>ID: " . $mascota['id'] . " - " . htmlspecialchars($mascota['name']) . "</p>";
        }
    } else {
        echo "<p>No hay mascotas.</p>";
    }
}
?>

<script>
function testDelete(id) {
    if (confirm('¿Eliminar mascota ID ' + id + '?')) {
        window.location.href = window.location.pathname + '?delete_id=' + id;
    }
}
</script>

<style>
table { border-collapse: collapse; margin: 20px 0; }
th, td { padding: 10px; text-align: left; }
button { background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; }
</style>