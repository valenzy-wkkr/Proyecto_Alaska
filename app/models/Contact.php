<?php
namespace App\Models;

use App\Core\Database;
use mysqli;

class Contact
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO contactos (nombre, email, telefono, asunto, mensaje) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $nombre = $data['nombre-contacto'] ?? '';
        $email = $data['email-contacto'] ?? '';
        $telefono = $data['telefono-contacto'] ?? '';
        $asunto = $data['asunto-contacto'] ?? '';
        $mensaje = $data['mensaje-contacto'] ?? '';
        $stmt->bind_param('sssss', $nombre, $email, $telefono, $asunto, $mensaje);
        return $stmt->execute();
    }
}
