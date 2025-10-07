<?php
require_once __DIR__ . '/app/core/Autoloader.php';
use App\Core\Database;

try {
    $db = Database::getConnection();
    
    echo "<h2>Información de la base de datos</h2>";
    
    // Mostrar tablas
    echo "<h3>Tablas en la base de datos 'alaska':</h3>";
    $result = $db->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        echo "- " . $row[0] . "<br>";
    }
    
    // Mostrar estructura de mascotas
    echo "<h3>Estructura de la tabla 'mascotas':</h3>";
    $result = $db->query("DESCRIBE mascotas");
    if ($result) {
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
    } else {
        echo "<p style='color: red;'>Error al describir la tabla mascotas: " . $db->error . "</p>";
    }
    
    // Mostrar datos de mascotas
    echo "<h3>Datos en la tabla 'mascotas':</h3>";
    $result = $db->query("SELECT * FROM mascotas");
    if ($result) {
        if ($result->num_rows > 0) {
            echo "<table border='1'>";
            $first = true;
            while ($row = $result->fetch_assoc()) {
                if ($first) {
                    echo "<tr>";
                    foreach (array_keys($row) as $key) {
                        echo "<th>$key</th>";
                    }
                    echo "</tr>";
                    $first = false;
                }
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No hay mascotas registradas</p>";
        }
    } else {
        echo "<p style='color: red;'>Error al consultar mascotas: " . $db->error . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
table { border-collapse: collapse; margin: 20px 0; }
th, td { padding: 8px; text-align: left; }
h2, h3 { color: #333; }
</style>