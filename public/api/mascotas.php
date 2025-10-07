<?php
// Limpiar cualquier salida previa y configurar output buffering
if (ob_get_level()) {
    ob_clean();
}
ob_start();

// Configurar headers antes de cualquier salida
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../app/core/Autoloader.php';

use App\Controllers\MascotasController;

try {
    $controller = new MascotasController();
    $controller->handle();
} catch (Exception $e) {
    error_log("Error en API mascotas: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
}
