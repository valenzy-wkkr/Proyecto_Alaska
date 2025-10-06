<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Reminder;

class RecordatoriosController extends Controller
{
    private Reminder $model;

    public function __construct()
    {
        $this->model = new Reminder();
    }

    public function handle(): void
    {
        // Recuperar usuario desde la sesión
        if (session_status() === \PHP_SESSION_NONE) {
            session_start();
        }
        $usuarioId = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : 0;
        if ($usuarioId <= 0) {
            $this->json(['success' => false, 'error' => 'No autenticado'], 401);
            return;
        }
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':
                $list = $this->model->listByUser($usuarioId);
                $this->json(['success' => true, 'datos' => $list], 200);
                break;
            case 'POST':
                $data = $this->inputJson();
                $created = $this->model->create($usuarioId, $data);
                if ($created !== false) {
                    $this->json(['success' => true, 'recordatorio' => $created], 201);
                } else {
                    $this->json(['success' => false, 'error' => 'Error al crear el recordatorio'], 400);
                }
                break;
            case 'PUT':
                $data = $this->inputJson();
                $id = isset($data['id']) ? (int)$data['id'] : 0;
                if ($id <= 0) {
                    $this->json(['success' => false, 'error' => 'ID inválido'], 400);
                    return;
                }
                $updated = $this->model->update($usuarioId, $id, $data);
                if ($updated) {
                    $this->json(['success' => true]);
                } else {
                    $this->json(['success' => false, 'error' => 'No se pudo actualizar'], 400);
                }
                break;
            case 'DELETE':
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                if ($id <= 0) {
                    // Intentar leer cuerpo JSON como fallback
                    $data = $this->inputJson();
                    $id = isset($data['id']) ? (int)$data['id'] : 0;
                }
                if ($id <= 0) {
                    $this->json(['success' => false, 'error' => 'ID inválido'], 400);
                    return;
                }
                $deleted = $this->model->delete($usuarioId, $id);
                if ($deleted) {
                    $this->json(['success' => true]);
                } else {
                    $this->json(['success' => false, 'error' => 'No se pudo eliminar'], 400);
                }
                break;
            default:
                $this->json(['error' => 'Método no permitido'], 405);
        }
    }
}
