<?php
session_start();
$_SESSION['usuario_id'] = 1; // Simular usuario logueado

echo "<h2>Test URL API Mascotas</h2>";

// Test de la URL exacta que usa el dashboard
$baseUrl = "http://localhost:8080";
$apiUrl = "/Proyecto_Alaska/public/api/mascotas.php";

echo "<h3>Test 1: GET mascotas</h3>";
$url = $baseUrl . $apiUrl;
echo "<p><strong>URL:</strong> $url</p>";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Cookie: " . $_SERVER['HTTP_COOKIE'] ?? ''
    ]
]);

$response = file_get_contents($url, false, $context);
echo "<p><strong>Respuesta GET:</strong></p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Decodificar para obtener IDs
$data = json_decode($response, true);
if ($data && $data['success'] && !empty($data['data'])) {
    $testId = $data['data'][0]['id'];
    
    echo "<h3>Test 2: DELETE mascota ID = $testId</h3>";
    $deleteUrl = $url . "?id=" . $testId;
    echo "<p><strong>URL DELETE:</strong> $deleteUrl</p>";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'DELETE',
            'header' => "Cookie: " . $_SERVER['HTTP_COOKIE'] ?? ''
        ]
    ]);
    
    $deleteResponse = file_get_contents($deleteUrl, false, $context);
    echo "<p><strong>Respuesta DELETE:</strong></p>";
    echo "<pre>" . htmlspecialchars($deleteResponse) . "</pre>";
} else {
    echo "<p>No hay mascotas para probar DELETE</p>";
}
?>

<style>
h2, h3 { color: #333; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>