<?php
// Test simple directo del controlador
require_once __DIR__ . '/app/core/Autoloader.php';

use App\Controllers\MascotasController;
use App\Models\Pet;

echo "<h2>Test Directo del Controlador</h2>";

// Simular sesión
session_start();
$_SESSION['usuario_id'] = 1;

// Test del modelo Pet
echo "<h3>Test del modelo Pet:</h3>";
try {
    $pet = new Pet();
    $mascotas = $pet->allByUser(1);
    echo "<p>Mascotas encontradas: " . count($mascotas) . "</p>";
    
    if (count($mascotas) > 0) {
        echo "<pre>" . print_r($mascotas[0], true) . "</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error en Pet model: " . $e->getMessage() . "</p>";
}

// Test del controlador
echo "<h3>Test del controlador MascotasController:</h3>";

try {
    // Simular GET request
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    ob_start();
    $controller = new MascotasController();
    $controller->handle();
    $output = ob_get_clean();
    
    echo "<p>Output del controlador:</p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Verificar JSON
    $decoded = json_decode($output, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<p style='color: green;'>✅ JSON válido</p>";
    } else {
        echo "<p style='color: red;'>❌ JSON inválido: " . json_last_error_msg() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error en controlador: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Test de eliminación simulada:</h3>";

if (isset($mascotas) && count($mascotas) > 0) {
    $petId = $mascotas[0]['id'];
    echo "<p>Intentando eliminar mascota con ID: $petId</p>";
    
    try {
        // Simular DELETE request
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_GET['id'] = $petId;
        
        ob_start();
        $controller = new MascotasController();
        $controller->handle();
        $deleteOutput = ob_get_clean();
        
        echo "<p>Output de eliminación:</p>";
        echo "<pre>" . htmlspecialchars($deleteOutput) . "</pre>";
        
        $deleteDecoded = json_decode($deleteOutput, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<p style='color: green;'>✅ DELETE JSON válido</p>";
        } else {
            echo "<p style='color: red;'>❌ DELETE JSON inválido: " . json_last_error_msg() . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error en DELETE: " . $e->getMessage() . "</p>";
    }
}
?>