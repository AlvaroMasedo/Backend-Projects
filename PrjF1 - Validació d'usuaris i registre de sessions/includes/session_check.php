<?php
//Alvaro Masedo Pérez
// session_check.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tancar sessió manualment
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    session_unset();
    session_destroy();
    setcookie('session_expired', '1', time() + 60, '/');
    $baseUrl = '/Pràctiques/Backend/PrjF1 - Validació d\'usuaris i registre de sessions/index.php?session_expired=1';
    header('Location: ' . $baseUrl);
    exit;
}
?>
