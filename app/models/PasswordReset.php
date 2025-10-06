<?php
namespace App\Models;

use App\Core\Database;
use mysqli;

class PasswordReset
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            used TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (user_id),
            UNIQUE KEY (token)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->db->query($sql);
    }

    public function createToken(int $userId, int $ttlMinutes = 30): string
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = (new \DateTimeImmutable("+{$ttlMinutes} minutes"))->format('Y-m-d H:i:s');

        $sql = "INSERT INTO password_resets (user_id, token, expires_at, used) VALUES (?, ?, ?, 0)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('iss', $userId, $token, $expiresAt);
        $stmt->execute();

        return $token;
    }

    public function validateToken(string $token): ?array
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $sql = "SELECT id, user_id, token, expires_at, used FROM password_resets WHERE token = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if ((int)$row['used'] === 0 && $row['expires_at'] >= $now) {
                return $row;
            }
        }
        return null;
    }

    public function markUsed(int $id): void
    {
        $sql = "UPDATE password_resets SET used = 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
}
