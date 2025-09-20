<?php
namespace App\Models;

use App\Core\Database;
use mysqli;

class Reminder
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Crea un nuevo recordatorio en la base de datos
     * @param int   $usuarioId  ID del usuario (desde sesión)
     * @param array $data       Datos del recordatorio
     * @return array|false      Registro creado o false en error
     */
    public function create(int $usuarioId, array $data): array|false
    {
        $mascotaId = (int)($data['petId'] ?? 0);
        $titulo    = trim((string)($data['title'] ?? ''));
        $fechaIn   = (string)($data['date'] ?? '');
        $tipoIn    = (string)($data['type'] ?? 'otro');
        $notas     = (string)($data['notes'] ?? '');
        $urgenteIn = isset($data['urgent']) ? (bool)$data['urgent'] : null; // si viene desde UI

        if ($mascotaId <= 0 || $titulo === '') {
            return false;
        }

        // Normalizar fecha (datetime-local suele venir con 'T')
        if ($fechaIn === '') {
            $fechaSql = date('Y-m-d H:i:s');
        } else {
            // Tomar solo YYYY-mm-ddTHH:ii[:ss] -> YYYY-mm-dd HH:ii:ss
            $tmp = str_replace('T', ' ', substr($fechaIn, 0, 19));
            // Si no trae segundos, agregar :00
            $fechaSql = preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $tmp) ? ($tmp . ':00') : $tmp;
        }

        // Validar/normalizar tipo
        $tiposValidos = ['vacuna','cita','medicamento','alimentacion','paseo','otro'];
        $tipo = in_array($tipoIn, $tiposValidos, true) ? $tipoIn : 'otro';

        // Si no viene urgent, calcular si es dentro de 24h para marcar urgente
        if ($urgenteIn === null) {
            $tsRecordatorio = strtotime($fechaSql) ?: time();
            $urgente = ($tsRecordatorio - time()) <= 24 * 60 * 60 ? 1 : 0;
        } else {
            $urgente = $urgenteIn ? 1 : 0;
        }
        $completado = 0;

        // Insertar en BD
        $sql = "INSERT INTO recordatorios (usuario_id, mascota_id, titulo, fecha_recordatorio, tipo, notas, urgente, completado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('iissssii', $usuarioId, $mascotaId, $titulo, $fechaSql, $tipo, $notas, $urgente, $completado);
        if (!$stmt->execute()) {
            return false;
        }

        $idInsertado = (int)$this->db->insert_id;

        // Obtener nombre de la mascota para el frontend
        $petName = '';
        $sqlPet = "SELECT nombre FROM mascotas WHERE id = ? AND usuario_id = ? LIMIT 1";
        if ($petStmt = $this->db->prepare($sqlPet)) {
            $petStmt->bind_param('ii', $mascotaId, $usuarioId);
            if ($petStmt->execute()) {
                $res = $petStmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $petName = (string)$row['nombre'];
                }
            }
        }

        return [
            'id'        => $idInsertado,
            'title'     => $titulo,
            'date'      => str_replace(' ', 'T', substr($fechaSql, 0, 19)), // devolver en formato ISO local para UI
            'type'      => $tipo,
            'petId'     => $mascotaId,
            'petName'   => $petName,
            'notes'     => $notas,
            'urgent'    => (bool)$urgente,
            'completed' => (bool)$completado,
        ];
    }
}
