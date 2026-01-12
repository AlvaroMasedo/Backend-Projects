<?php
//Alvaro Masedo Pérez
// session_check.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tancar sessió manualment
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    // Comprovar si l'usuari vol olvidar aquest dispositiu
    $forgetDevice = isset($_GET['forget_device']) && $_GET['forget_device'] == '1';
    
    if ($forgetDevice && isset($_COOKIE['remember_user'])) {
        require_once __DIR__ . '/../config/db_connection.php';
        require_once __DIR__ . '/../app/model/model.usuari.php';
        
        try {
            $modelUsers = new ModelUsers($conn);
            $modelUsers->eliminarRememberToken($_COOKIE['remember_user']);
        } catch (Exception $e) {
            // Si falla, només continuem amb el logout
        }
        
        // Eliminar cookies
        setcookie('remember_token', '', time() - 3600, '/');
        setcookie('remember_user', '', time() - 3600, '/');
    } else {
        // Logout normal: mantenir el token pero prevenir auto-login per 5 minuts
        setcookie('prevent_auto_login', '1', time() + 300, '/');
    }
    
    session_unset();
    session_destroy();
    setcookie('session_expired', '1', time() + 60, '/');
    $baseUrl = '/Pràctiques/Backend/PrjF1 - Validació d\'usuaris i registre de sessions/index.php?session_expired=1';
    header('Location: ' . $baseUrl);
    exit;
}

// Comprovar si hi ha token de "recorda'm" vàlid (però no si acabem de fer logout)
if (!isset($_SESSION['usuari']) && 
    isset($_COOKIE['remember_token']) && 
    isset($_COOKIE['remember_user']) && 
    !isset($_COOKIE['prevent_auto_login'])) {
    
    require_once __DIR__ . '/../config/db_connection.php';
    require_once __DIR__ . '/../app/model/model.usuari.php';
    
    try {
        $modelUsers = new ModelUsers($conn);
        $usuari = $modelUsers->obtenirPerToken($_COOKIE['remember_token']);
        
        if ($usuari !== null && $usuari['nickname'] === $_COOKIE['remember_user']) {
            // Token vàlid, restaurar sessió de l'usuari
            $_SESSION['usuari'] = [
                'nickname' => $usuari['nickname'],
                'nom' => $usuari['nom'],
                'cognom' => $usuari['cognom'],
                'email' => $usuari['email'],
                'administrador' => $usuari['administrador'],
                'imatge_perfil' => $usuari['imatge_perfil']
            ];
        } else {
            // Token no vàlid, eliminar cookies
            setcookie('remember_token', '', time() - 3600, '/');
            setcookie('remember_user', '', time() - 3600, '/');
        }
    } catch (Exception $e) {
        // Si falla, només continuem sense restaurar sessió
    }
}
?>
