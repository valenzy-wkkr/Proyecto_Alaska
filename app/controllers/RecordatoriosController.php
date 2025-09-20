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
        // En producción, recuperar desde sesión
        $usuarioId = 1;
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'POST':
                $data = $this->inputJson();
                $created = $this->model->create($usuarioId, $data);
                if ($created !== false) {
                    $this->json(['success' => true, 'recordatorio' => $created], 201);
                } else {
                    $this->json(['success' => false, 'error' => 'Error al crear el recordatorio'], 400);
                }
                break;
            default:
                $this->json(['error' => 'Método no permitido'], 405);
        }
    }
}
