<?php
require_once __DIR__ . '/../../app/core/Autoloader.php';

use App\Controllers\ContactoController;

$controller = new ContactoController();
$controller->handle();
