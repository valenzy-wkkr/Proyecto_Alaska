<?php
// Script para agregar la columna foto_perfil a la tabla usuarios

require_once __DIR__ . '/../../core/Autoloader.php';
use App\Core\Database;

try {
    $db = Database::getConnection();
    
    echo "<h2>Actualizando estructura de la base de datos...</h2>";
    
    // Verificar si la columna foto_perfil ya existe
    $result = $db->query("SHOW COLUMNS FROM usuarios LIKE 'foto_perfil'");
    
    if ($result->num_rows == 0) {
        // La columna no existe, agregarla
        $sql = "ALTER TABLE usuarios ADD COLUMN foto_perfil VARCHAR(255) NULL AFTER direccion";
        
        if ($db->query($sql)) {
            echo "<p style='color: green;'>✅ Columna 'foto_perfil' agregada exitosamente a la tabla usuarios.</p>";
        } else {
            echo "<p style='color: red;'>❌ Error al agregar la columna: " . $db->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ La columna 'foto_perfil' ya existe en la tabla usuarios.</p>";
    }
    
    // Mostrar la estructura actual de la tabla
    echo "<h3>Estructura actual de la tabla usuarios:</h3>";
    $result = $db->query("DESCRIBE usuarios");
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><strong>¡Actualización completada!</strong></p>";
    echo "<p><a href='/Proyecto_Alaska/html/perfil.php'>Ir al Perfil</a> | <a href='/Proyecto_Alaska/app/scripts/debug/debug_session.php'>Debug sesión</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>