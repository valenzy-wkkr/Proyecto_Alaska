<?php
require_once __DIR__ . '/../../../app/core/Autoloader.php';

use App\Models\PasswordReset;
use App\Models\User;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Proyecto_Alaska/public/auth/forgot.php');
    exit;
}

$token = isset($_POST['token']) ? (string)$_POST['token'] : '';
$clave = isset($_POST['clave']) ? (string)$_POST['clave'] : '';
$clave2 = isset($_POST['clave2']) ? (string)$_POST['clave2'] : '';

if ($token === '') {
    header('Location: /Proyecto_Alaska/public/auth/forgot.php');
    exit;
}

if (strlen($clave) < 8) {
    header('Location: /Proyecto_Alaska/public/auth/reset.php?token=' . urlencode($token) . '&error=' . urlencode('La contraseña debe tener al menos 8 caracteres'));
    exit;
}

if ($clave !== $clave2) {
    header('Location: /Proyecto_Alaska/public/auth/reset.php?token=' . urlencode($token) . '&error=' . urlencode('Las contraseñas no coinciden'));
    exit;
}

$resetModel = new PasswordReset();
$entry = $resetModel->validateToken($token);
if (!$entry) {
    header('Location: /Proyecto_Alaska/public/auth/forgot.php');
    exit;
}

$userId = (int)$entry['user_id'];
$hash = password_hash($clave, PASSWORD_DEFAULT);

$userModel = new User();
$userModel->updatePassword($userId, $hash);
$resetModel->markUsed((int)$entry['id']);

// Redirigir a login con mensaje de éxito simple
header('Location: /Proyecto_Alaska/public/auth/login.php');
exit;
