<?php
require_once __DIR__ . '/../../../app/core/Autoloader.php';

use App\Controllers\AuthController; // not used here but autoloader pattern similar
use App\Models\User;
use App\Models\PasswordReset;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Proyecto_Alaska4/public/auth/forgot.php');
    exit;
}

$correo = isset($_POST['correo']) ? trim((string)$_POST['correo']) : '';
if ($correo === '') {
    header('Location: /Proyecto_Alaska4/public/auth/forgot.php');
    exit;
}

$userModel = new User();
$user = $userModel->findByEmail($correo);

// Para no filtrar existencia del correo, siempre respondemos éxito.
$token = '';
if ($user) {
    $reset = new PasswordReset();
    $token = $reset->createToken((int)$user['id']);
}

// En este entorno de desarrollo, redirigimos con el token para que puedas usar el formulario.
$query = 'success=1';
if ($token !== '') { $query .= '&token=' . urlencode($token); }
header('Location: /Proyecto_Alaska4/public/auth/forgot.php?' . $query);
exit;
