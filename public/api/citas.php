<?php
require_once __DIR__ . '/../../app/core/Autoloader.php';

use App\Controllers\CitasController;

$controller = new CitasController();
$controller->handle();
