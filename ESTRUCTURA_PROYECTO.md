# Estructura del Proyecto: Proyecto Alaska

Este documento describe detalladamente la estructura de carpetas y archivos del proyecto **Proyecto Alaska**, explicando la función de cada componente y su relación dentro de la aplicación.

---

## Raíz del Proyecto

- **index.html**: Página principal del sitio web.
- **package.json**: Archivo de configuración para dependencias y scripts de Node.js (si aplica).
- **README.md**: Documentación general del proyecto.
- **CONFIGURACION_BD.md**: Instrucciones para la configuración de la base de datos.
- **INSTALL.md**: Guía de instalación y requisitos del sistema.
- **manifest.webmanifest**: Archivo de manifiesto para aplicaciones web progresivas (PWA).

---

## Carpetas Principales

### assets/
- **css/**: Hojas de estilo CSS para diferentes secciones (blog, citas, dashboard, login, usuario, estilos adicionales y generales).
- **img/**: Imágenes utilizadas en la interfaz, como fotos de mascotas, íconos y banners.
- **js/**: Scripts JavaScript para la lógica del frontend, incluyendo:
  - `agenda.js`: Lógica de la agenda de citas.
  - `app.js`: Inicialización y lógica general de la app.
  - `blog.js`: Funcionalidad del blog.
  - `citas.js`: Gestión de citas.
  - `dashboard.js`: Lógica del panel principal.
  - `historial-visitas.js`: Historial de visitas de mascotas.
  - `recordatorios.js`: Gestión de recordatorios.
  - `server.js`: (Si aplica) Servidor Node.js para desarrollo o pruebas.
  - `sw.js`: Service Worker para funcionalidades PWA.

### controllers/
- **UserController.js**: Controlador para la gestión de usuarios (registro, login, etc.).

### data/
- **blog-articles.json**: Archivo JSON con los artículos del blog.

### html/
- Páginas HTML secundarias:
  - `404.html`: Página de error.
  - `agenda.html`: Agenda de citas.
  - `blog.html`: Sección de blog.
  - `citas.html`: Gestión de citas.
  - `contacto.html`: Formulario de contacto.

### img/
- Imágenes adicionales para la web (adopción, alimentación, higiene, entrenamiento, etc.).

### php/
- Scripts PHP para la lógica de backend y conexión con la base de datos:
  - `agregar_columna_nombre_mascota.php`: Script para modificar la estructura de la base de datos.
  - `botonera.php`: Componente de botones reutilizable.
  - `conexion.php`: Conexión a la base de datos MySQL.
  - `crear_tabla_recordatorios.php`: Script para crear la tabla de recordatorios.
  - `crear_tablas.php`: Script para crear todas las tablas necesarias.
  - `dashboard.php`: Lógica y vista del panel principal.
  - `login.php`, `logout.php`, `registro.php`, `validar_login.php`: Autenticación y gestión de sesiones.
  - `mascotas.php`: API para gestión de mascotas.
  - `procesar_citas.php`: Procesamiento de citas.
  - `procesar_contacto.php`: Procesamiento de formularios de contacto.
  - `recordatorios.php`: API para recordatorios.
  - `ver_contactos.php`: Visualización de contactos.
  - `verificar_conexion.php`: Verificación de la conexión a la base de datos.

### utils/
- Utilidades JavaScript reutilizables:
  - `auth.js`: Funciones de autenticación.
  - `chatbot.js`: Lógica del chatbot.
  - `ErrorHandler.js`: Manejo de errores.
  - `Logger.js`: Registro de logs.
  - `storage.js`: Manejo de almacenamiento local.
  - `Validator.js`: Validaciones de formularios y datos.

### views/
- Vistas JavaScript para componentes de la interfaz:
  - `ButtonView.js`: Vista de botones.
  - `FormView.js`: Vista de formularios.
  - `MenuView.js`: Vista del menú de navegación.

---

## Relación entre Componentes

- **Frontend**: Utiliza HTML, CSS y JS desde las carpetas `html/`, `assets/` y `views/` para mostrar la interfaz y manejar la interacción del usuario.
- **Backend**: Los scripts PHP en `php/` gestionan la lógica de negocio, acceso a la base de datos y exponen APIs para el frontend.
- **Controladores y utilidades**: Los controladores y utilidades en `controllers/` y `utils/` ayudan a organizar la lógica y reutilizar código.
- **Datos**: La carpeta `data/` almacena información estática como artículos de blog.

---

## Flujo General de la Aplicación

1. El usuario accede a la web (por ejemplo, `index.html` o alguna página en `html/`).
2. El frontend realiza peticiones AJAX a los scripts PHP para obtener o modificar datos (mascotas, citas, recordatorios, etc.).
3. Los scripts PHP interactúan con la base de datos MySQL y devuelven respuestas al frontend.
4. El frontend actualiza la interfaz según la respuesta recibida.
5. Utilidades y vistas JS ayudan a mantener el código organizado y modular.

---

## Observaciones

- El proyecto está pensado para ejecutarse en un entorno local con XAMPP (PHP + MySQL).
- Incluye funcionalidades de autenticación, gestión de mascotas, citas, recordatorios, blog y contacto.
- La estructura modular facilita la escalabilidad y el mantenimiento del código.

---

**Última actualización:** 18 de septiembre de 2025
