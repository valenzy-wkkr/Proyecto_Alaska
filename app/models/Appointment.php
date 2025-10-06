<?php
namespace App\Models;

use App\Core\Database;
use mysqli;

class Appointment
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(int $usuarioId, array $data): int|false
    {
        $sql = "INSERT INTO citas (usuario_id, tipo_mascota, nombre_mascota, fecha_cita, motivo, notas, estado, fecha_creacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        $tipoMascota = $data['petId'] ?? '';
        $nombreMascota = $data['petName'] ?? '';
        $fechaCita = $data['appointmentDate'] ?? '';
        $motivo = $data['reason'] ?? '';
        $notas = $data['notes'] ?? '';
        $estado = $data['status'] ?? 'programada';
        $stmt->bind_param('issssss', $usuarioId, $tipoMascota, $nombreMascota, $fechaCita, $motivo, $notas, $estado);
        if ($stmt->execute()) {
            return (int)$this->db->insert_id;
        }
        return false;
    }

    public function listByUser(int $usuarioId, ?string $filtroMascota = null): array
    {
        $sql = "SELECT * FROM citas WHERE usuario_id = ?";
        $params = [$usuarioId];
        $types = 'i';
        if (!empty($filtroMascota) && $filtroMascota !== 'todas') {
            $sql .= " AND tipo_mascota = ?";
            $params[] = $filtroMascota;
            $types .= 's';
        }
        $sql .= " ORDER BY fecha_cita DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        while ($fila = $result->fetch_assoc()) {
            $rows[] = [
                'id' => (int)$fila['id'],
                'tipo_mascota' => $fila['tipo_mascota'],
                'nombre_mascota' => $fila['nombre_mascota'] ?? '',
                'fecha_cita' => $fila['fecha_cita'],
                'motivo' => $fila['motivo'],
                'notas' => $fila['notas'],
                'estado' => $fila['estado'],
                'diagnostico' => $fila['diagnostico'] ?? '',
                'tratamiento' => $fila['tratamiento'] ?? '',
                'veterinario' => $fila['veterinario'] ?? '',
                'fecha_creacion' => $fila['fecha_creacion'],
            ];
        }
        return $rows;
    }

    public function update(int $usuarioId, int $id, array $data): bool
    {
        $fields = [];
        $values = [];
        $types = '';

        $map = [
            'tipo_mascota' => 's',
            'fecha_cita' => 's',
            'motivo' => 's',
            'notas' => 's',
            'estado' => 's',
            'diagnostico' => 's',
            'tratamiento' => 's',
            'veterinario' => 's',
        ];

        foreach ($map as $key => $t) {
            if (isset($data[$key])) {
                $fields[] = "$key = ?";
                $values[] = $data[$key];
                $types .= $t;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE citas SET " . implode(', ', $fields) . " WHERE id = ? AND usuario_id = ?";
        $stmt = $this->db->prepare($sql);
        $types .= 'ii';
        $values[] = $id;
        $values[] = $usuarioId;
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    public function delete(int $usuarioId, int $id): bool
    {
        $sql = "DELETE FROM citas WHERE id = ? AND usuario_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $id, $usuarioId);
        return $stmt->execute();
    }
}

