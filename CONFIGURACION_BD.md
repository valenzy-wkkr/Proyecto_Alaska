
# Configuración de Base de Datos - Proyecto Alaska

## Pasos para configurar la base de datos en XAMPP (phpMyAdmin)

### 1. Crear la base de datos y las tablas

1. Abre phpMyAdmin en tu navegador: `http://localhost/phpmyadmin`
2. Crea una base de datos llamada `alaska`.
3. Selecciona la base de datos `alaska`.
4. Ve a la pestaña "SQL" y ejecuta el siguiente código, o abre en el navegador el archivo `php/crear_tablas.php`:

```sql
-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de mascotas
CREATE TABLE IF NOT EXISTS mascotas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    especie VARCHAR(50) NOT NULL,
    raza VARCHAR(100),
    edad DECIMAL(4,1),
    peso DECIMAL(5,2),
    estado_salud ENUM('healthy', 'attention', 'warning') DEFAULT 'healthy',
    ultima_revision DATE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de recordatorios
CREATE TABLE IF NOT EXISTS recordatorios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    mascota_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    fecha_recordatorio DATETIME NOT NULL,
    tipo ENUM('vacuna', 'cita', 'medicamento', 'alimentacion', 'paseo', 'otro') NOT NULL,
    notas TEXT,
    urgente BOOLEAN DEFAULT FALSE,
    completado BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mascota_id) REFERENCES mascotas(id) ON DELETE CASCADE
);
```

### 2. Verificar la conexión

1. Asegúrate de que XAMPP esté ejecutándose y MySQL esté activo.
2. Abre `http://localhost/Proyecto_Alaska/php/crear_tablas.php` en tu navegador para crear las tablas automáticamente.
3. Si todo es correcto, verás mensajes de confirmación.

### 3. Configurar el usuario de sesión

El sistema de login guarda los datos del usuario en `$_SESSION['usuario']`. Si necesitas hacer pruebas sin login, puedes forzar el ID de usuario en los scripts PHP.

### 4. Probar la funcionalidad

1. Accede a `http://localhost/Proyecto_Alaska/dashboard.php`.
2. Agrega mascotas y recordatorios desde la interfaz.
3. Verifica que los datos se guarden y muestren correctamente.

### 5. Archivos relevantes

- `php/crear_tablas.php`: Script para crear las tablas.
- `php/mascotas.php`: API para gestión de mascotas.
- `php/recordatorios.php`: API para recordatorios.
- `php/conexion.php`: Configuración de conexión a la base de datos.
- `dashboard.php`: Panel principal.

### 6. Estructura de la base de datos

- **usuarios**: Información de los usuarios.
- **mascotas**: Mascotas de cada usuario.
- **recordatorios**: Recordatorios asociados a cada mascota.

### 7. Solución de problemas

1. Verifica que XAMPP esté ejecutándose.
2. Revisa que la base de datos `alaska` exista.
3. Verifica las credenciales en `php/conexion.php`.
4. Revisa la consola del navegador para errores de JavaScript.
5. Verifica los logs de error de PHP en XAMPP.

---

**Última actualización:** 18 de septiembre de 2025
