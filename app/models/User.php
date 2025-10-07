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

    public function findById(int $userId): ?array
    {
        $sql = "SELECT id, nombre, correo, apodo, direccion, fecha_creacion FROM usuarios WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            return $row;
        }
        return null;
    }

    public function updateProfile(int $userId, array $data): bool
    {
        $sql = "UPDATE usuarios SET nombre = ?, apodo = ?, direccion = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('sssi', $data['nombre'], $data['apodo'], $data['direccion'], $userId);
        return $stmt->execute();
    }

    public function updateEmail(int $userId, string $email): bool
    {
        $sql = "UPDATE usuarios SET correo = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('si', $email, $userId);
        return $stmt->execute();
    }

    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        if ($excludeUserId) {
            $sql = "SELECT id FROM usuarios WHERE correo = ? AND id != ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('si', $email, $excludeUserId);
        } else {
            $sql = "SELECT id FROM usuarios WHERE correo = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $email);
        }
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function usernameExists(string $username, ?int $excludeUserId = null): bool
    {
        if ($excludeUserId) {
            $sql = "SELECT id FROM usuarios WHERE apodo = ? AND id != ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('si', $username, $excludeUserId);
        } else {
            $sql = "SELECT id FROM usuarios WHERE apodo = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $username);
        }
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function updateProfilePicture(int $userId, string $filename): bool
    {
        $sql = "UPDATE usuarios SET foto_perfil = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('si', $filename, $userId);
        return $stmt->execute();
    }

    public function getProfilePicture(int $userId): ?string
    {
        $sql = "SELECT foto_perfil FROM usuarios WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['foto_perfil'];
        }
        return null;
    }
}
