<?php
// Conexión directa a la base de datos
$servidor = "localhost";
$usuario = "root";
$clave = "";
$basedatos = "alaska";
$port = '3306';

$conexion = mysqli_connect($servidor, $usuario, $clave, $basedatos, $port);

if (!$conexion) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}

// Crear la tabla de recordatorios si no existe
$sql = "CREATE TABLE IF NOT EXISTS recordatorios (
    id INT(11) NOT NULL AUTO_INCREMENT,
    usuario_id INT(11) NOT NULL,
    mascota_id INT(11) NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    fecha_recordatorio DATETIME NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    notas TEXT,
    urgente TINYINT(1) DEFAULT 0,
    completado TINYINT(1) DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($conexion, $sql)) {
    echo "Tabla 'recordatorios' creada correctamente o ya existía.";
} else {
    echo "Error al crear la tabla: " . mysqli_error($conexion);
}

// Crear la tabla de mascotas si no existe
$sql_mascotas = "CREATE TABLE IF NOT EXISTS mascotas (
    id INT(11) NOT NULL AUTO_INCREMENT,
    usuario_id INT(11) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    raza VARCHAR(100),
    fecha_nacimiento DATE,
    genero VARCHAR(20),
    peso DECIMAL(5,2),
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($conexion, $sql_mascotas)) {
    echo "<br>Tabla 'mascotas' creada correctamente o ya existía.";
} else {
    echo "<br>Error al crear la tabla de mascotas: " . mysqli_error($conexion);
}

// Insertar una mascota de ejemplo si no hay ninguna
$check_mascotas = "SELECT COUNT(*) as total FROM mascotas";
$result = mysqli_query($conexion, $check_mascotas);
$row = mysqli_fetch_assoc($result);

if ($row['total'] == 0) {
    $insert_mascota = "INSERT INTO mascotas (usuario_id, nombre, tipo) VALUES (1, 'Merlin', 'perro')";
    if (mysqli_query($conexion, $insert_mascota)) {
        echo "<br>Mascota de ejemplo creada correctamente.";
    } else {
        echo "<br>Error al crear mascota de ejemplo: " . mysqli_error($conexion);
    }
}

mysqli_close($conexion);

echo "<br><br><a href='../dashboard.php'>Volver al Dashboard</a>";
?>