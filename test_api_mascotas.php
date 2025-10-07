<?php
// Test simple del API
session_start();

// Simular una sesión válida para el test
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = 1; // ID de test
    $_SESSION['nombre'] = 'Usuario Test';
}

echo "<h2>Test API Mascotas</h2>";
echo "<p>Usuario ID de sesión: " . ($_SESSION['usuario_id'] ?? 'No definido') . "</p>";

// Test GET
echo "<h3>Test GET (listar mascotas):</h3>";
$url = 'http://localhost/Proyecto_Alaska/public/api/mascotas.php';

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            'Content-Type: application/json',
            'Cookie: ' . session_name() . '=' . session_id()
        ]
    ]
]);

$result = @file_get_contents($url, false, $context);
if ($result === false) {
    echo "<p style='color: red;'>Error: No se pudo conectar al API</p>";
} else {
    echo "<pre>" . htmlspecialchars($result) . "</pre>";
    
    // Verificar si es JSON válido
    $decoded = json_decode($result, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<p style='color: green;'>✅ JSON válido</p>";
        
        // Si hay mascotas, probar DELETE
        if (is_array($decoded) && count($decoded) > 0) {
            $firstPet = $decoded[0];
            if (isset($firstPet['id'])) {
                echo "<h3>Test DELETE con mascota ID: " . $firstPet['id'] . "</h3>";
                
                $deleteUrl = $url . '?id=' . $firstPet['id'];
                $deleteContext = stream_context_create([
                    'http' => [
                        'method' => 'DELETE',
                        'header' => [
                            'Content-Type: application/json',
                            'Cookie: ' . session_name() . '=' . session_id()
                        ]
                    ]
                ]);
                
                $deleteResult = @file_get_contents($deleteUrl, false, $deleteContext);
                if ($deleteResult === false) {
                    echo "<p style='color: red;'>Error: No se pudo ejecutar DELETE</p>";
                } else {
                    echo "<p>Resultado DELETE:</p>";
                    echo "<pre>" . htmlspecialchars($deleteResult) . "</pre>";
                    
                    $deleteDecoded = json_decode($deleteResult, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        echo "<p style='color: green;'>✅ DELETE devolvió JSON válido</p>";
                    } else {
                        echo "<p style='color: red;'>❌ DELETE devolvió JSON inválido</p>";
                    }
                }
            }
        }
    } else {
        echo "<p style='color: red;'>❌ JSON inválido. Error: " . json_last_error_msg() . "</p>";
    }
}

echo "<hr>";
echo "<p><a href='debug_mascotas.php'>← Volver al debug</a></p>";
?>