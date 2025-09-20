<?php
namespace App\Core;

use mysqli;
use Exception;

class Database
{
    private static ?mysqli $instance = null;

    public static function getConnection(): mysqli
    {
        if (self::$instance === null) {
            $host = 'localhost';
            $user = 'root';
            $pass = '';
            $db   = 'alaska';
            $port = 3306;

            $conn = @new mysqli($host, $user, $pass, $db, $port);
            if ($conn->connect_errno) {
                throw new Exception('Error al conectar con la base de datos: ' . $conn->connect_error);
            }
            $conn->set_charset('utf8mb4');
            self::$instance = $conn;
        }
        return self::$instance;
    }
}
