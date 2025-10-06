git clone https://github.com/tu-usuario/alaska-pets.git
# Guía de Instalación - Proyecto Alaska

## Requisitos del sistema

- XAMPP (PHP 7.x+, MySQL/MariaDB)
- Navegador web moderno

## Pasos de instalación

1. Descarga o clona el repositorio en tu máquina local.
2. Copia la carpeta del proyecto a `C:/xampp/htdocs/`.
3. Inicia Apache y MySQL desde el panel de XAMPP.
4. Abre `http://localhost/phpmyadmin` y crea una base de datos llamada `alaska`.
5. Ejecuta el script `php/crear_tablas.php` desde el navegador o copia el SQL de `CONFIGURACION_BD.md` en phpMyAdmin para crear las tablas.
6. Verifica y ajusta las credenciales de conexión en `php/conexion.php` según tu entorno local.
7. Accede a la aplicación desde `http://localhost/Proyecto_Alaska/index.html` o a las páginas PHP correspondientes.

## Estructura recomendada de carpetas

Consulta el archivo `ESTRUCTURA_PROYECTO.md` para una descripción detallada de la estructura y función de cada carpeta y archivo.

## Resolución de problemas

- Si no puedes conectar a la base de datos, revisa las credenciales y que MySQL esté activo.
- Si ves errores en la web, revisa los logs de PHP en XAMPP y la consola del navegador.

## Soporte

Para soporte adicional, contacta al equipo de desarrollo o revisa la documentación incluida.