<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Pet;

class MascotasController extends Controller
{
    private Pet $petModel;

    public function __construct()
    {
        // Configurar headers para JSON
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        
        // Manejar preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
        
        $this->petModel = new Pet();
    }

    public function handle(): void
    {
        try {
            // Asegurar sesión y obtener el usuario actual
            if (session_status() === PHP_SESSION_NONE) {
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
                    $pets = $this->petModel->allByUser($usuarioId);
                    $this->json($pets);
                    break;

                case 'POST':
                    $data = $this->inputJson();
                    $created = $this->petModel->create($usuarioId, $data);
                    if ($created !== false) {
                        $this->json(['success' => true, 'mascota' => $created]);
                    } else {
                        $this->json(['success' => false, 'error' => 'Error al crear la mascota'], 400);
                    }
                    break;

                case 'PUT':
                    $data = $this->inputJson();
                    $id = isset($data['id']) ? (int)$data['id'] : 0;
                    if ($id <= 0) {
                        $this->json(['success' => false, 'error' => 'ID inválido'], 400);
                        return;
                    }
                    $ok = $this->petModel->update($usuarioId, $id, $data);
                    $this->json($ok ? ['success' => true] : ['success' => false, 'error' => 'Error al actualizar'], $ok ? 200 : 400);
                    break;

            case 'DELETE':
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                if ($id <= 0) {
                    $this->json(['success' => false, 'error' => 'ID inválido'], 400);
                    return;
                }
                
                // Debug: Log del intento de eliminación
                error_log("Intentando eliminar mascota ID: $id para usuario: $usuarioId");
                
                $ok = $this->petModel->delete($usuarioId, $id);
                
                // Debug: Log del resultado
                error_log("Resultado de eliminación: " . ($ok ? 'exitoso' : 'fallido'));
                
                if ($ok) {
                    $this->json(['success' => true, 'message' => 'Mascota eliminada exitosamente']);
                } else {
                    $this->json(['success' => false, 'error' => 'No se encontró la mascota o no se pudo eliminar'], 404);
                }
                break;                default:
                    $this->json(['success' => false, 'error' => 'Método no permitido'], 405);
            }
        } catch (Exception $e) {
            error_log("Error en MascotasController: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Error interno del servidor'], 500);
        }
    }
}

