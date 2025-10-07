<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller
{
    private User $users;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->users = new User();
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Redirigir si se intenta acceder directamente por GET desde navegador
            header('Location: /Proyecto_Alaska/index.php#registro');
            return;
        }

        // Soportar JSON o form-urlencoded
        $payload = [];
        if (!empty($_POST)) {
            $payload = [
                'nombre' => trim((string)($_POST['nombre'] ?? '')),
                'correo' => trim((string)($_POST['correo'] ?? '')),
                'clave' => (string)($_POST['clave'] ?? ''),
                'apodo' => trim((string)($_POST['apodo'] ?? '')),
                'direccion' => trim((string)($_POST['direccion'] ?? '')),
            ];
        } else {
            $json = $this->inputJson();
            $payload = [
                'nombre' => trim((string)($json['nombre'] ?? '')),
                'correo' => trim((string)($json['correo'] ?? '')),
                'clave' => (string)($json['clave'] ?? ''),
                'apodo' => trim((string)($json['apodo'] ?? '')),
                'direccion' => trim((string)($json['direccion'] ?? '')),
            ];
        }

        // Validaciones básicas
        if ($payload['nombre'] === '' || $payload['correo'] === '' || $payload['clave'] === '' || $payload['apodo'] === '' || $payload['direccion'] === '') {
            if (!empty($_POST)) {
                // En un flujo HTML podríamos redirigir con error; por ahora regresar al registro
                header('Location: /Proyecto_Alaska/index.php#registro');
                return;
            }
            $this->json(['success' => false, 'error' => 'Campos requeridos'], 400);
            return;
        }

        // Duplicados
        if ($this->users->findByEmail($payload['correo'])) {
            $this->json(['success' => false, 'error' => 'Correo ya registrado'], 409);
            return;
        }
        if ($this->users->findByUsername($payload['apodo'])) {
            $this->json(['success' => false, 'error' => 'Nombre de usuario ya existe'], 409);
            return;
        }

        // Hash de contraseña
        $hash = password_hash($payload['clave'], PASSWORD_DEFAULT);

        // Crear usuario
        $userId = $this->users->create([
            'nombre' => $payload['nombre'],
            'correo' => $payload['correo'],
            'clave_hashed' => $hash,
            'apodo' => $payload['apodo'],
            'direccion' => $payload['direccion'],
        ]);

        // Crear sesión
        $_SESSION['usuario'] = $payload['correo'];
        $_SESSION['nombre'] = $payload['nombre'];
        $_SESSION['usuario_id'] = (int)$userId;

        // Responder según origen
        if (!empty($_POST)) {
            header('Location: /Proyecto_Alaska/public/dashboard.php');
            return;
        }
        $this->json(['success' => true, 'user' => ['id' => (int)$userId, 'nombre' => $payload['nombre'], 'correo' => $payload['correo']]]);
    }
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Si no es POST, redirigir al formulario de login
            header('Location: /Proyecto_Alaska/public/auth/login.php');
            return;
        }

        // Soportar JSON o form-urlencoded
        $email = '';
        $password = '';
        if (isset($_POST['correo'], $_POST['clave'])) {
            $email = trim((string)$_POST['correo']);
            $password = (string)$_POST['clave'];
        } else {
            $payload = $this->inputJson();
            $email = trim((string)($payload['correo'] ?? ''));
            $password = (string)($payload['clave'] ?? '');
        }

        if ($email === '' || $password === '') {
            // Flujo HTML
            if (!empty($_POST)) {
                header('Location: /Proyecto_Alaska/public/auth/login.php?error=vacio');
                return;
            }
            // Flujo JSON
            $this->json(['success' => false, 'error' => 'Campos vacíos'], 400);
            return;
        }

        $user = $this->users->findByEmail($email);
        if ($user && password_verify($password, $user['clave'])) {
            $_SESSION['usuario'] = $user['correo'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['usuario_id'] = (int)$user['id'];

            // Flujo HTML
            if (!empty($_POST)) {
                header('Location: /Proyecto_Alaska/public/dashboard.php');
                return;
            }
            // Flujo JSON
            $this->json(['success' => true, 'user' => ['id' => (int)$user['id'], 'nombre' => $user['nombre'], 'correo' => $user['correo']] ]);
            return;
        }

        // Credenciales incorrectas
        if (!empty($_POST)) {
            header('Location: /Proyecto_Alaska/public/auth/login.php?error=credenciales');
            return;
        }
        $this->json(['success' => false, 'error' => 'Credenciales incorrectas'], 401);
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();

        // Permitir JSON o redirección
        if (isset($_GET['format']) && $_GET['format'] === 'json') {
            $this->json(['success' => true]);
            return;
        }
        header('Location: /Proyecto_Alaska/index.php');
    }
}

