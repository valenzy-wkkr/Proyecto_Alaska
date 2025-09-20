<?php
spl_autoload_register(function ($class) {
    // Cargar solo clases del namespace App\
    if (strpos($class, 'App\\') !== 0) {
        return;
    }
    $baseDir = __DIR__ . '/..'; // app/
    $relative = str_replace('App\\', '', $class);
    $path = $baseDir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});
