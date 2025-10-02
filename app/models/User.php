<?php
namespace App\Models;

use App\Core\Database;
use mysqli;

class User
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT id, nombre, correo, clave FROM usuarios WHERE correo = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            return $row;
        }
        return null;
    }

    public function findByUsername(string $username): ?array
    {
        $sql = "SELECT id, nombre, correo, clave, apodo FROM usuarios WHERE apodo = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            return $row;
        }
        return null;
    }

    public function create(array $data): int
    {
        // Expected keys: nombre, correo, clave_hashed, apodo, direccion
        $sql = "INSERT INTO usuarios (nombre, correo, clave, apodo, direccion) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            'sssss',
            $data['nombre'],
            $data['correo'],
            $data['clave_hashed'],
            $data['apodo'],
            $data['direccion']
        );
        $stmt->execute();
        return $this->db->insert_id;
    }

    public function updatePassword(int $userId, string $hashedPassword): bool
    {
        $sql = "UPDATE usuarios SET clave = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('si', $hashedPassword, $userId);
        return $stmt->execute();
    }
}
