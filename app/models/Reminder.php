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

    /**
     * Lista recordatorios del usuario
     * @return array<int, array>
     */
    public function listByUser(int $usuarioId): array
    {
        $sql = "SELECT r.id, r.mascota_id, r.titulo, r.fecha_recordatorio, r.tipo, r.notas, r.urgente, r.completado,
                       m.nombre AS nombre_mascota
                FROM recordatorios r
                LEFT JOIN mascotas m ON m.id = r.mascota_id AND m.usuario_id = r.usuario_id
                WHERE r.usuario_id = ?
                ORDER BY r.fecha_recordatorio ASC, r.id ASC";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('i', $usuarioId);
        if (!$stmt->execute()) {
            return [];
        }
        $res = $stmt->get_result();
        $out = [];
        while ($row = $res->fetch_assoc()) {
            $fecha = (string)$row['fecha_recordatorio'];
            $out[] = [
                'id'        => (int)$row['id'],
                'title'     => (string)$row['titulo'],
                'date'      => str_replace(' ', 'T', substr($fecha, 0, 19)),
                'type'      => (string)$row['tipo'],
                'petId'     => (int)$row['mascota_id'],
                'petName'   => (string)($row['nombre_mascota'] ?? ''),
                'notes'     => (string)$row['notas'],
                'urgent'    => (bool)$row['urgente'],
                'completed' => (bool)$row['completado'],
            ];
        }
        return $out;
    }

    /**
     * Actualiza un recordatorio del usuario
     */
    public function update(int $usuarioId, int $id, array $data): bool
    {
        // Campos a actualizar si vienen en $data
        $titulo    = isset($data['title']) ? trim((string)$data['title']) : null;
        $fechaIn   = isset($data['date']) ? (string)$data['date'] : null;
        $tipoIn    = isset($data['type']) ? (string)$data['type'] : null;
        $notas     = array_key_exists('notes', $data) ? (string)$data['notes'] : null;
        $mascotaId = isset($data['petId']) ? (int)$data['petId'] : null;
        $urgenteIn = array_key_exists('urgent', $data) ? (bool)$data['urgent'] : null;
        $completadoIn = array_key_exists('completed', $data) ? (bool)$data['completed'] : null;

        // Normalizar fecha si viene
        $fechaSql = null;
        if ($fechaIn !== null) {
            if ($fechaIn === '') {
                $fechaSql = date('Y-m-d H:i:s');
            } else {
                $tmp = str_replace('T', ' ', substr($fechaIn, 0, 19));
                $fechaSql = preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $tmp) ? ($tmp . ':00') : $tmp;
            }
        }

        // Validar/normalizar tipo si viene
        $tipo = null;
        if ($tipoIn !== null) {
            $tiposValidos = ['vacuna','cita','medicamento','alimentacion','paseo','otro'];
            $tipo = in_array($tipoIn, $tiposValidos, true) ? $tipoIn : 'otro';
        }

        // Construir SET dinámico
        $sets = [];
        $params = [];
        $types = '';

        if ($titulo !== null) { $sets[] = 'titulo = ?'; $params[] = $titulo; $types .= 's'; }
        if ($fechaSql !== null) { $sets[] = 'fecha_recordatorio = ?'; $params[] = $fechaSql; $types .= 's'; }
        if ($tipo !== null) { $sets[] = 'tipo = ?'; $params[] = $tipo; $types .= 's'; }
        if ($notas !== null) { $sets[] = 'notas = ?'; $params[] = $notas; $types .= 's'; }
        if ($mascotaId !== null) { $sets[] = 'mascota_id = ?'; $params[] = $mascotaId; $types .= 'i'; }
        if ($urgenteIn !== null) { $sets[] = 'urgente = ?'; $params[] = $urgenteIn ? 1 : 0; $types .= 'i'; }
        if ($completadoIn !== null) { $sets[] = 'completado = ?'; $params[] = $completadoIn ? 1 : 0; $types .= 'i'; }

        if (empty($sets)) {
            return true; // Nada que actualizar
        }

        $sql = 'UPDATE recordatorios SET ' . implode(', ', $sets) . ' WHERE id = ? AND usuario_id = ?';
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $types .= 'ii';
        $params[] = $id;
        $params[] = $usuarioId;

        // bind_param requiere params por referencia
        $bindParams = [];
        $bindParams[] = & $types;
        foreach ($params as $k => $v) {
            $bindParams[] = & $params[$k];
        }
        // @phpstan-ignore-next-line
        call_user_func_array([$stmt, 'bind_param'], $bindParams);

        return $stmt->execute();
    }

    /**
     * Elimina un recordatorio del usuario
     */
    public function delete(int $usuarioId, int $id): bool
    {
        $sql = 'DELETE FROM recordatorios WHERE id = ? AND usuario_id = ?';
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('ii', $id, $usuarioId);
        return $stmt->execute();
    }
}
