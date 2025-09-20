<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Contact;

class ContactoController extends Controller
{
    private Contact $model;

    public function __construct()
    {
        $this->model = new Contact();
    }

    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Construir redirección relativa al proyecto (evita /html/... en localhost)
            $base = rtrim(dirname(dirname(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))), '/');
            $target = $base . '/html/contacto.html';
            header('Location: ' . $target, true, 303);
            exit;
        }

        // Aceptar formulario (application/x-www-form-urlencoded) por compatibilidad
        $data = $_POST;
        $ok = $this->model->create($data);

        // Si es una solicitud AJAX o el cliente acepta JSON, responder con JSON y no redirigir
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xhr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($xhr || stripos($accept, 'application/json') !== false) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => (bool)$ok,
                'message' => $ok
                    ? '¡Tu mensaje fue enviado correctamente!'
                    : 'Hubo un problema al enviar tu mensaje. Inténtalo de nuevo.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Construir URL base del proyecto a partir de la ruta actual
        $base = rtrim(dirname(dirname(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))), '/');
        $target = $base . '/html/contacto.html?enviado=' . ($ok ? '1' : '0');
        // Redirigir de vuelta con mensaje de estado
        header('Location: ' . $target, true, 303);
        exit;
    }
}
