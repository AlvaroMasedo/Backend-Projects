<?php
//Alvaro Masedo Pérez
// session_check.php

require_once __DIR__ . '/../config/basepath.php';

/**
 * Funció per iniciar sessió amb configuració correcta
 * SEMPRE usar aquesta funció per garantir que les cookies expirin al tancar el navegador
 */
function iniciar_sessio_segura() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configurar cookie de sessió per a que expiri al tancar el navegador
        session_set_cookie_params([
            'lifetime' => 0,         // La cookie expira al tancar el navegador
            'path' => '/',
            'domain' => '',
            'secure' => false,       // Canviar a true si uses HTTPS
            'httponly' => true,      // Protecció contra XSS
            'samesite' => 'Lax'     // Protecció contra CSRF
        ]);
        
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_lifetime', '0'); // Forzar cookie de sessió sin persistencia
        ini_set('session.gc_maxlifetime', '2400'); // 40 minuts
        session_start();
    }
}

// Iniciar sessió automàticament
iniciar_sessio_segura();

if (isset($_SESSION['usuari']) && !isset($_COOKIE['remember_token'])) {
    $remember_me = $_SESSION['usuari'] ? ($_SESSION['remember_me'] ?? 0) : -1;
    
    if ($remember_me == 0) {
        // Mantener un control estricto: la sesión actual debe ser reciente
        // Si há passat més d'1 minut sense peticions i NO té remember-me, cerrar
        // (Detecta navegador restaurat tras reinicio)
        if (isset($_SESSION['_last_request_time'])) {
            $time_since_last = time() - $_SESSION['_last_request_time'];
            if ($time_since_last > 60) {
                // Más de 60 segundos desde última petición - probablemente navegador se reinició
                error_log("ALERTA: Sessió sense remember-me inactiva >60s. Posible reinicio de navegador. Tancant.");
                $_SESSION['must_logout'] = true;
            }
        }
        // Actualizar timestamp de última petición
        $_SESSION['_last_request_time'] = time();
    }
}

// IMPORTANT: Si no hi ha remember_token però la sessió existeix, verificar que sigui vàlida
// (protecció contra cookies PHPSESSID que persisteixen al tancar navegador)
if (isset($_SESSION['usuari']) && !isset($_COOKIE['remember_token'])) {
    $remember_me = $_SESSION['remember_me'] ?? 0;
    
    // Si NO té remember-me actiu, forçar que expiri
    if ($remember_me == 0) {
        $session_browser_token = $_SESSION['browser_session_token'] ?? null;
        $cookie_browser_token = $_COOKIE['browser_session_token'] ?? null;
        $browser_marker = $_COOKIE['browser_marker'] ?? null;
        $session_marker = $_SESSION['browser_marker'] ?? null;
        
        // Tokens de navegador han de coincidir exactament
        if (!$session_browser_token || !$cookie_browser_token || $session_browser_token !== $cookie_browser_token) {
            error_log("ALERTA: Tokens de navegador NO coincideixen. Tancant sessió.");
            $_SESSION['must_logout'] = true;
        }
        
        //  Marcador ha de existir en AMBOS llocs
        if (!$browser_marker || !$session_marker || $browser_marker !== $session_marker) {
            error_log("ALERTA: browser_marker NO coincideix o NO existeix. Tancant sessió.");
            $_SESSION['must_logout'] = true;
        }
        
        //  Si la sessió es vella sense activitat, cerrar
        // (Detecta navegadors que restauren sesions velles)
        if (isset($_SESSION['session_created'])) {
            $timp_desde_inici = time() - $_SESSION['session_created'];
            // Si han passat +45 minuts sens activitat i NO té remember-me, cerrar
            if ($timp_desde_inici > 45 * 60) {
                error_log("ALERTA: Sessió antiga sense remember-me (" . $timp_desde_inici . "s). Tancant.");
                $_SESSION['must_logout'] = true;
            }
        }
        
        // Si alguna validació ha fallat, destruir sessió
        if (isset($_SESSION['must_logout']) && $_SESSION['must_logout'] === true) {
            require_once __DIR__ . '/../config/db_connection.php';
            require_once __DIR__ . '/../app/model/model.usuari.php';
            $controlarUsers = new ModelUsers($conn);
            
            if (isset($_SESSION['usuari']['nickname'])) {
                $controlarUsers->eliminarRememberMe($_SESSION['usuari']['nickname']);
            }
            
            session_unset();
            session_destroy();
            
            setcookie('PHPSESSID', '', time() - 3600, '/', '', false, true);
            setcookie('browser_session_token', '', time() - 3600, '/', '', false, true);
            setcookie('browser_marker', '', time() - 3600, '/', '', false, true);
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
            setcookie('session_expired', '1', time() + 60, '/');
            
            $baseUrl = BASE_PATH . '/app/view/vista.login.php?session_expired=1';
            header('Location: ' . $baseUrl);
            exit;
        }
    }
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
        // Restaurar la sessió amb remember-me actiu
        $_SESSION['usuari'] = [
            'nickname' => $usuari['nickname'],
            'nom' => $usuari['nom'],
            'cognom' => $usuari['cognom'],
            'email' => $usuari['email'],
            'administrador' => $usuari['administrador'],
            'imatge_perfil' => $usuari['imatge_perfil']
        ];
        $_SESSION['remember_me'] = 1; // Restaurat amb Remember Me
        $_SESSION['session_created'] = time(); // Establir timestamp per a control de temps
        
        error_log("DEBUG: Sessió restaurada amb remember_token");
    } else {
        // Token no vàlid o expirat, eliminar cookie
        setcookie('remember_token', '', time() - 3600, '/');
        error_log("DEBUG: remember_token invàlid o expirat");
    }
}

// Verificar si la sessió ha expirat per temps (40 minuts d'inactivitat)
if (isset($_SESSION['usuari']) && isset($_SESSION['session_created'])) {
    $temps_transcorregut = time() - $_SESSION['session_created'];
    $temps_maxim = 40 * 60; // 40 minuts (2400 segons)
    
    if ($temps_transcorregut > $temps_maxim) {
        require_once __DIR__ . '/../config/db_connection.php';
        require_once __DIR__ . '/../app/model/model.usuari.php';
        
        $controlarUsers = new ModelUsers($conn);
        
        // Eliminar token de Remember Me si hi ha
        if (isset($_SESSION['usuari']['nickname'])) {
            $controlarUsers->eliminarRememberMe($_SESSION['usuari']['nickname']);
        }
        
        // Destruir sessió
        session_unset();
        session_destroy();
        
        // Eliminar totes les cookies de sessió i remember
        setcookie('PHPSESSID', '', time() - 3600, '/', '', false, true);
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        setcookie('browser_session_token', '', time() - 3600, '/', '', false, true);
        setcookie('browser_marker', '', time() - 3600, '/', '', false, true);
        setcookie('session_expired', '1', time() + 60, '/');
        
        // Redirigir al login i ATURAR EXECUCIÓ
        $baseUrl = BASE_PATH . '/app/view/vista.login.php?session_expired=1';
        header('Location: ' . $baseUrl);
        exit; // CRÍTIC: aturar execució per evitar que es restauri la sessió
    } else {
        // Si la sessió encara és vàlida, actualitzar el timestamp
        $_SESSION['session_created'] = time();
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
    
    // Eliminar totes les cookies
    setcookie('PHPSESSID', '', time() - 3600, '/', '', false, true);
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    setcookie('browser_session_token', '', time() - 3600, '/', '', false, true);
    setcookie('browser_marker', '', time() - 3600, '/', '', false, true);
    setcookie('session_expired', '1', time() + 60, '/');
    
    $baseUrl = BASE_PATH . '/index.php?session_expired=1';
    header('Location: ' . $baseUrl);
    exit;
}
?>
