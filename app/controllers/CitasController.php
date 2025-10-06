<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Appointment;

class CitasController extends Controller
{
    private Appointment $model;

    public function __construct()
    {
        $this->model = new Appointment();
        // Headers CORS básicos y JSON
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    private function respuesta(bool $exito, string $mensaje, $datos = null, int $status = 200): void
    {
        http_response_code($status);
        echo json_encode([
            'exito' => $exito,
            'mensaje' => $mensaje,
            'datos' => $datos,
        ], JSON_UNESCAPED_UNICODE);
    }

    public function handle(): void
    {
        // Asegurar sesión y obtener usuario
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $usuarioId = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : 0;
        if ($usuarioId <= 0) {
            $this->respuesta(false, 'No autenticado', null, 401);
            return;
        }

        $method = $_SERVER['REQUEST_METHOD'];
        try {
            switch ($method) {
                case 'GET':
                    $filtro = isset($_GET['mascota']) ? (string)$_GET['mascota'] : null;
                    $citas = $this->model->listByUser($usuarioId, $filtro);
                    $this->respuesta(true, 'Citas obtenidas exitosamente', $citas);
                    break;

                case 'POST':
                    $data = $this->inputJson();
                    $id = $this->model->create($usuarioId, $data);
                    if ($id !== false) {
                        $this->respuesta(true, 'Cita creada exitosamente', ['id' => $id], 201);
                    } else {
                        $this->respuesta(false, 'Error al crear la cita', null, 400);
                    }
                    break;

                case 'PUT':
                    $data = $this->inputJson();
                    if (!isset($data['id'])) {
                        $this->respuesta(false, 'ID de cita requerido', null, 400);
                        return;
                    }
                    $ok = $this->model->update($usuarioId, (int)$data['id'], $data);
                    if ($ok) {
                        $this->respuesta(true, 'Cita actualizada exitosamente');
                    } else {
                        $this->respuesta(false, 'No se encontró la cita o no hubo cambios', null, 400);
                    }
                    break;

                case 'DELETE':
                    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                    if ($id <= 0) {
                        $this->respuesta(false, 'ID de cita requerido', null, 400);
                        return;
                    }
                    $ok = $this->model->delete($usuarioId, $id);
                    if ($ok) {
                        $this->respuesta(true, 'Cita eliminada exitosamente');
                    } else {
                        $this->respuesta(false, 'No se encontró la cita', null, 404);
                    }
                    break;

                default:
                    $this->respuesta(false, 'Método no permitido', null, 405);
            }
        } catch (\Throwable $e) {
            $this->respuesta(false, 'Error interno del servidor: ' . $e->getMessage(), null, 500);
        }
    }
}

