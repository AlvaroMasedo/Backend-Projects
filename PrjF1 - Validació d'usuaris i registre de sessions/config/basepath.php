<?php
declare(strict_types=1);

/**
 * Configuració de ruta base dinàmica
 * Permet que el projecte funcioni en localhost i en hosting remot
 */

// Detectar si estem en local o en producció
$isLocal = in_array($_SERVER['HTTP_HOST'] ?? 'localhost', ['localhost', '127.0.0.1'], true) || 
           isset($_SERVER['ARGV']) && in_array('--local', $_SERVER['ARGV']);

if ($isLocal) {
    // LOCAL: la URL és http://localhost/Pràctiques/Backend/PrjF1%20-%20...
    $basePath = '/Pràctiques/Backend/PrjF1 - Validació d\'usuaris i registre de sessions';
} else {
    // PRODUCCIÓ (Infinity): la URL és http://teudomini.com/ (arrel)
    $basePath = '';
}

// Assegurar format correcte
$basePath = '/' . trim($basePath, '/');
$basePath = rtrim($basePath, '/');
if (empty($basePath) || $basePath === '/') {
    $basePath = '';
}

// Definir constants globals per a ús fàcil
define('BASE_PATH', $basePath);
define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . BASE_PATH);
