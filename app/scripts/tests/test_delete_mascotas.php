<?php
// Test directo del API de mascotas
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

echo "<h2>Test API Mascotas - DELETE</h2>";

if (isset($_GET['test_id'])) {
    $testId = (int)$_GET['test_id'];
    echo "<h3>Probando eliminar mascota ID: $testId</h3>";
    
    // Simular el request DELETE
    $_SERVER['REQUEST_METHOD'] = 'DELETE';
    $_GET['id'] = $testId;
    
    require_once __DIR__ . '/../../core/Autoloader.php';
    
    try {
        $controller = new \App\Controllers\MascotasController();
        ob_start();
        $controller->handle();
        $output = ob_get_clean();
        
        echo "<h4>Respuesta del API:</h4>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    // Mostrar mascotas disponibles para test
    require_once __DIR__ . '/../../core/Autoloader.php';
    
    $petModel = new \App\Models\Pet();
    $mascotas = $petModel->allByUser($_SESSION['usuario_id']);
    
    echo "<h3>Mascotas disponibles para test:</h3>";
    if (count($mascotas) > 0) {
        foreach ($mascotas as $mascota) {
            echo "<p>";
            echo "ID: " . $mascota['id'] . " - " . htmlspecialchars($mascota['name']);
            echo " <a href='?test_id=" . $mascota['id'] . "' style='color: red;'>[TEST DELETE]</a>";
            echo "</p>";
        }
    } else {
        echo "<p>No hay mascotas para probar.</p>";
    }
    
    echo "<hr>";
    echo "<p><a href='debug_mascotas.php'>← Volver al debug principal</a></p>";
}
?>