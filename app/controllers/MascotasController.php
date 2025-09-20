<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Pet;

class MascotasController extends Controller
{
    private Pet $petModel;

    public function __construct()
    {
        $this->petModel = new Pet();
    }

    public function handle(): void
    {
        // En producción, recuperar desde sesión
        $usuarioId = 1;
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
                $ok = $this->petModel->delete($usuarioId, $id);
                $this->json($ok ? ['success' => true] : ['success' => false, 'error' => 'Error al eliminar'], $ok ? 200 : 400);
                break;

            default:
                $this->json(['error' => 'Método no permitido'], 405);
        }
    }
}
