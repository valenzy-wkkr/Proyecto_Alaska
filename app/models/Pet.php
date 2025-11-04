<?php
namespace App\Models;

use App\Core\Database;
use mysqli;
use Exception;

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
        if (!$stmt) {
            throw new Exception('Error preparando INSERT mascota: ' . $this->db->error);
        }
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
                'healthNotes' => $row['observaciones_salud'] ?? '',
                'lastCheckup' => $row['ultima_revision'],
            ];
        }
        return $pets;
    }

    public function create(int $usuarioId, array $data): array|false
    {
        // Inserta incluyendo observaciones de salud (requiere columna observaciones_salud)
        $sql = "INSERT INTO mascotas (usuario_id, nombre, especie, raza, edad, peso, estado_salud, observaciones_salud, ultima_revision)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error preparando UPDATE mascota: ' . $this->db->error);
        }
        $edad = (float)($data['age'] ?? 0);
        $peso = (float)($data['weight'] ?? 0);
        $nombre = $data['name'] ?? '';
        $especie = $data['species'] ?? '';
        $raza = $data['breed'] ?? '';
        $estado = $data['healthStatus'] ?? 'healthy';
        $ultima = $data['lastCheckup'] ?? date('Y-m-d');
        $observaciones = substr((string)($data['healthNotes'] ?? ''), 0, 300);
        $stmt->bind_param('isssddsss', $usuarioId, $nombre, $especie, $raza, $edad, $peso, $estado, $observaciones, $ultima);
        if ($stmt->execute()) {
            return [
                'id' => $this->db->insert_id,
                'name' => $nombre,
                'species' => $especie,
                'breed' => $raza,
                'age' => $edad,
                'weight' => $peso,
                'healthStatus' => $estado,
                'healthNotes' => $observaciones,
                'lastCheckup' => $ultima,
            ];
        }
        return false;
    }

    public function update(int $usuarioId, int $id, array $data): bool
    {
        // Actualiza incluyendo observaciones de salud (requiere columna observaciones_salud)
        $sql = "UPDATE mascotas SET nombre = ?, especie = ?, raza = ?, edad = ?, peso = ?, estado_salud = ?, observaciones_salud = ?, ultima_revision = ?
                WHERE id = ? AND usuario_id = ?";
        $stmt = $this->db->prepare($sql);
        $edad = (float)($data['age'] ?? 0);
        $peso = (float)($data['weight'] ?? 0);
        $nombre = $data['name'] ?? '';
        $especie = $data['species'] ?? '';
        $raza = $data['breed'] ?? '';
        $estado = $data['healthStatus'] ?? 'healthy';
        $ultima = $data['lastCheckup'] ?? date('Y-m-d');
        $observaciones = substr((string)($data['healthNotes'] ?? ''), 0, 300);
        $stmt->bind_param('sssddsssii', $nombre, $especie, $raza, $edad, $peso, $estado, $observaciones, $ultima, $id, $usuarioId);
        return $stmt->execute();
    }

    public function delete(int $usuarioId, int $id): bool
    {
        // Iniciar transacción para asegurar que ambas eliminaciones se hagan o ninguna
        $this->db->autocommit(false);
        
        try {
            // Primero eliminar las citas asociadas a esta mascota
            $sqlCitas = "DELETE FROM citas WHERE mascota_id = ? AND usuario_id = ?";
            $stmtCitas = $this->db->prepare($sqlCitas);
            
            if (!$stmtCitas) {
                throw new Exception("Error preparando query DELETE citas: " . $this->db->error);
            }
            
            $stmtCitas->bind_param('ii', $id, $usuarioId);
            $resultCitas = $stmtCitas->execute();
            
            if (!$resultCitas) {
                throw new Exception("Error ejecutando DELETE citas: " . $stmtCitas->error);
            }
            
            $citasEliminadas = $stmtCitas->affected_rows;
            error_log("Citas eliminadas para mascota ID $id: $citasEliminadas");
            
            // Luego eliminar la mascota
            $sqlMascota = "DELETE FROM mascotas WHERE id = ? AND usuario_id = ?";
            $stmtMascota = $this->db->prepare($sqlMascota);
            
            if (!$stmtMascota) {
                throw new Exception("Error preparando query DELETE mascota: " . $this->db->error);
            }
            
            $stmtMascota->bind_param('ii', $id, $usuarioId);
            $resultMascota = $stmtMascota->execute();
            
            if (!$resultMascota) {
                throw new Exception("Error ejecutando DELETE mascota: " . $stmtMascota->error);
            }
            
            $mascotasEliminadas = $stmtMascota->affected_rows;
            error_log("Mascotas eliminadas: $mascotasEliminadas");
            
            // Si todo salió bien, confirmar la transacción
            if ($mascotasEliminadas > 0) {
                $this->db->commit();
                error_log("Eliminación exitosa: mascota ID $id y $citasEliminadas citas asociadas");
                return true;
            } else {
                $this->db->rollback();
                error_log("No se eliminó ninguna mascota con ID $id");
                return false;
            }
            
        } catch (Exception $e) {
            // En caso de error, hacer rollback
            $this->db->rollback();
            error_log("Error en eliminación de mascota: " . $e->getMessage());
            return false;
        } finally {
            // Restaurar autocommit
            $this->db->autocommit(true);
        }
    }
}
