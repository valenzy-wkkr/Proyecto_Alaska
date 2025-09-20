# Proyecto Alaska - Plataforma de Cuidado de Mascotas

Esta aplicación web ayuda a los dueños de mascotas a gestionar información veterinaria, recordatorios, citas y más, a través de una interfaz intuitiva y un backend en PHP/MySQL.

## Características principales

- Registro y autenticación de usuarios
- Gestión de mascotas (alta, edición, eliminación)
- Agenda de citas y recordatorios
- Blog de artículos informativos
- Panel de control (dashboard) con estadísticas
- Formulario de contacto
- Funcionalidad PWA (instalable y offline)

## Estructura del proyecto

Consulta el archivo `ESTRUCTURA_PROYECTO.md` para una descripción detallada de carpetas y archivos.

## Tecnologías utilizadas

- PHP 7.x+
- MySQL/MariaDB
- HTML5, CSS3, JavaScript (ES6+)
- XAMPP (entorno local recomendado)

## Instalación y configuración

1. Clona el repositorio en tu entorno local:
   ```
   git clone <URL-del-repositorio>
   ```
2. Coloca la carpeta en el directorio `htdocs` de XAMPP.
3. Inicia Apache y MySQL desde el panel de XAMPP.
4. Crea la base de datos y tablas siguiendo las instrucciones de `CONFIGURACION_BD.md`.
5. Configura las credenciales de la base de datos en `php/conexion.php`.
6. Accede a `http://localhost/Proyecto_Alaska/index.html` o a las páginas PHP según corresponda.

## Uso

1. Regístrate o inicia sesión.
2. Agrega tus mascotas y gestiona sus datos.
3. Programa citas y recordatorios.
4. Consulta el blog y utiliza el panel de control.

## Mantenimiento y soporte

- Para problemas de conexión, revisa la configuración en XAMPP y el archivo `php/conexion.php`.
- Para soporte adicional, contacta al equipo de desarrollo o revisa la documentación incluida.

## Licencia

Este proyecto está licenciado bajo la licencia MIT.