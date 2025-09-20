<?php
namespace App\Models;

use App\Core\Database;
use mysqli;

class Pet
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function allByUser(int $usuarioId): array
    {
        $sql = "SELECT * FROM mascotas WHERE usuario_id = ? ORDER BY fecha_creacion DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        $pets = [];
        while ($row = $result->fetch_assoc()) {
            $pets[] = [
                'id' => (int)$row['id'],
                'name' => $row['nombre'],
                'species' => $row['especie'],
                'breed' => $row['raza'],
                'age' => (float)$row['edad'],
                'weight' => (float)$row['peso'],
                'healthStatus' => $row['estado_salud'],
                'lastCheckup' => $row['ultima_revision'],
            ];
        }
        return $pets;
    }

    public function create(int $usuarioId, array $data): array|false
    {
        $sql = "INSERT INTO mascotas (usuario_id, nombre, especie, raza, edad, peso, estado_salud, ultima_revision)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $edad = (float)($data['age'] ?? 0);
        $peso = (float)($data['weight'] ?? 0);
        $nombre = $data['name'] ?? '';
        $especie = $data['species'] ?? '';
        $raza = $data['breed'] ?? '';
        $estado = $data['healthStatus'] ?? 'healthy';
        $ultima = $data['lastCheckup'] ?? date('Y-m-d');
        $stmt->bind_param('isssddss', $usuarioId, $nombre, $especie, $raza, $edad, $peso, $estado, $ultima);
        if ($stmt->execute()) {
            return [
                'id' => $this->db->insert_id,
                'name' => $nombre,
                'species' => $especie,
                'breed' => $raza,
                'age' => $edad,
                'weight' => $peso,
                'healthStatus' => $estado,
                'lastCheckup' => $ultima,
            ];
        }
        return false;
    }

    public function update(int $usuarioId, int $id, array $data): bool
    {
        $sql = "UPDATE mascotas SET nombre = ?, especie = ?, raza = ?, edad = ?, peso = ?, estado_salud = ?, ultima_revision = ?
                WHERE id = ? AND usuario_id = ?";
        $stmt = $this->db->prepare($sql);
        $edad = (float)($data['age'] ?? 0);
        $peso = (float)($data['weight'] ?? 0);
        $nombre = $data['name'] ?? '';
        $especie = $data['species'] ?? '';
        $raza = $data['breed'] ?? '';
        $estado = $data['healthStatus'] ?? 'healthy';
        $ultima = $data['lastCheckup'] ?? date('Y-m-d');
        $stmt->bind_param('sssddssii', $nombre, $especie, $raza, $edad, $peso, $estado, $ultima, $id, $usuarioId);
        return $stmt->execute();
    }

    public function delete(int $usuarioId, int $id): bool
    {
        $sql = "DELETE FROM mascotas WHERE id = ? AND usuario_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $id, $usuarioId);
        return $stmt->execute();
    }
}
