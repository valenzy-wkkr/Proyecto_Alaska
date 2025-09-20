<?php
require_once __DIR__ . '/../../../app/core/Autoloader.php';

use App\Controllers\AuthController;

$controller = new AuthController();
$controller->login();
