<?php
require_once __DIR__ . '/../../app/core/Autoloader.php';

use App\Controllers\RecordatoriosController;

$controller = new RecordatoriosController();
$controller->handle();
