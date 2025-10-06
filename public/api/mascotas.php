<?php
require_once __DIR__ . '/../../app/core/Autoloader.php';

use App\Controllers\MascotasController;

$controller = new MascotasController();
$controller->handle();
