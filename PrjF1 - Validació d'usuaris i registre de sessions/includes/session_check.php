<?php
//Alvaro Masedo Pérez
// session_check.php

// IMPORTANT: Configurar la cookie de sessió per a que expiri quan es tanqui el navegador
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_lifetime', '0'); // expira al tancar navegador
    ini_set('session.gc_maxlifetime', '1440');
    session_start();
}

// En cada petició, si la sessió és sense Remember Me, validem el navegador
if (isset($_SESSION['usuari']) && !isset($_COOKIE['remember_token'])) {
    $remember = $_SESSION['remember_me'] ?? 0;
    $sessBrowser = $_SESSION['browser_instance'] ?? null;
    $cookieBrowser = $_COOKIE['browser_instance'] ?? null;

    // Si no coincideix (o no existeix al client) i no volia Remember, matem sessió
    if ($remember == 0 && (!$cookieBrowser || !$sessBrowser || $cookieBrowser !== $sessBrowser)) {
        session_unset();
        session_destroy();
        setcookie('PHPSESSID', '', time() - 3600, '/');
        setcookie('browser_instance', '', time() - 3600, '/');
    }
}
// Tancar sessió manualment
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    require_once __DIR__ . '/../config/db_connection.php';
    require_once __DIR__ . '/../app/model/model.usuari.php';
    
    $controlarUsers = new ModelUsers($conn);
    
    // Eliminar token de Remember Me si hi ha usuari en sessió
    if (isset($_SESSION['usuari']['nickname'])) {
        $controlarUsers->eliminarRememberMe($_SESSION['usuari']['nickname']);
    }
    
    session_unset();
    session_destroy();
    setcookie('session_expired', '1', time() + 60, '/');
    $baseUrl = '/Pràctiques/Backend/PrjF1 - Validació d\'usuaris i registre de sessions/index.php?session_expired=1';
    header('Location: ' . $baseUrl);
    exit;
}

// Si no hi ha sessió iniciada, intentar login automàtic amb Remember Me TOKEN
if (!isset($_SESSION['usuari']) && isset($_COOKIE['remember_token'])) {
    require_once __DIR__ . '/../config/db_connection.php';
    require_once __DIR__ . '/../app/model/model.usuari.php';
    
    $controlarUsers = new ModelUsers($conn);
    $token = $_COOKIE['remember_token'];
    
    // Verificar token en la base de dades
    $usuari = $controlarUsers->obtenirUsuariPerToken($token);
    
    if ($usuari) {
        // Restaurar la sessió
        $_SESSION['usuari'] = [
            'nickname' => $usuari['nickname'],
            'nom' => $usuari['nom'],
            'cognom' => $usuari['cognom'],
            'email' => $usuari['email'],
            'administrador' => $usuari['administrador'],
            'imatge_perfil' => $usuari['imatge_perfil']
        ];
        $_SESSION['remember_me'] = 1; // Restaurat amb Remember Me
    } else {
        // Token no vàlid o expirat, eliminar cookie
        setcookie('remember_token', '', time() - 3600, '/');
    }
}
?>
