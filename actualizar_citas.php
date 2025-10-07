<?php
// Script para actualizar la estructura de la tabla citas
require_once __DIR__ . '/app/core/Autoloader.php';

use App\Core\Database;

try {
    $db = Database::getConnection();
    echo "<h2>Actualizando estructura de la tabla citas</h2>";
    
    // Verificar si la columna mascota_id ya existe
    $result = $db->query("SHOW COLUMNS FROM citas LIKE 'mascota_id'");
    
    if ($result->num_rows == 0) {
        echo "<p>🔧 Agregando columna mascota_id...</p>";
        
        // Agregar la columna mascota_id
        $sql = "ALTER TABLE citas ADD COLUMN mascota_id INT NULL AFTER usuario_id";
        if ($db->query($sql)) {
            echo "<p>✅ Columna mascota_id agregada exitosamente</p>";
            
            // Agregar clave foránea
            $sql = "ALTER TABLE citas ADD FOREIGN KEY (mascota_id) REFERENCES mascotas(id) ON DELETE SET NULL";
            if ($db->query($sql)) {
                echo "<p>✅ Clave foránea agregada exitosamente</p>";
            } else {
                echo "<p>⚠️ Error al agregar clave foránea: " . $db->error . "</p>";
            }
        } else {
            echo "<p>❌ Error al agregar columna: " . $db->error . "</p>";
        }
    } else {
        echo "<p>✅ La columna mascota_id ya existe</p>";
    }
    
    // Mostrar estructura actual
    echo "<h3>Estructura actual de la tabla citas:</h3>";
    $result = $db->query("DESCRIBE citas");
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
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>