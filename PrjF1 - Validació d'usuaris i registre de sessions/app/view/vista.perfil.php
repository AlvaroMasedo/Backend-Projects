<!--Álvaro Masedo Pérez-->
<?php

// Verificar si la sessió ha expirat mitjançant paràmetre GET o cookie
$sessionExpired = (isset($_GET['session_expired']) && $_GET['session_expired'] == '1') || (isset($_COOKIE['session_expired']) && $_COOKIE['session_expired'] == '1');
if ($sessionExpired && isset($_COOKIE['session_expired'])) {
    setcookie('session_expired', '', time() - 3600, '/'); //Eliminar cookie després de mostrar missatge
}
// Ara incloure session_check.php
require_once __DIR__ . '/../../includes/session_check.php'; 
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../resources/css/style.perfil.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
    <title>Perfil</title>
</head>
<body>
    <?php if ($sessionExpired): ?>
    <div class="alert-overlay">
        <div class="alert">
            <p>S'ha tancat la sessió.</p>
            <a class="button" href="app/view/vista.login.php">INICIAR SESSIÓ</a>
            <br>
            <a class="link" href="../../index.php">Navega com usuari anònim</a>
        </div>
    </div>
<?php endif; ?>
    <header>
        <ul class="house-icon">
            <li><a href="../../index.php"><b><img src="../../uploads/img/home.webp" alt="home" title="Inici"></b></a></li>
        </ul>

        <ul class="menu-right">
            <?php if (isset($_SESSION['usuari'])): ?>
                <li class="menu-usuari">
                    <span class="button-usuari"><?php echo htmlspecialchars($_SESSION['usuari']['nickname']); ?> ▼</span>
                    <ul class="desplegable-usuari">
                        <li><a href="vista.perfil.php">Perfil</a></li>
                        <li><a href="vista.articles.php">Artícles</a></li>
                        <li><a href="../../includes/session_check.php?logout=1">Tancar sessió</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li><a class="button" href="vista.login.php"><b>Login</b></a></li>
                <li><a class="button" href="vista.signup.php"><b>Sign Up</b></a></li>
            <?php endif; ?>
        </ul>
    </header>
        <div class="perfil-container">
            <h1>Perfil d'Usuari</h1>
            <div class="separador"></div>
            <?php if (isset($_SESSION['usuari'])): ?>
            <p class="perfil-p"><strong>Nickname:</strong></p>
            <p><?php echo htmlspecialchars($_SESSION['usuari']['nickname']); ?></p>
            <div class="separador_gran"></div>
            
            <p class="perfil-p"><strong>Nom:</strong></p>
            <p><?php echo htmlspecialchars($_SESSION['usuari']['nom']); ?></p>
            <div class="separador_gran"></div>

            <!-- Mostra el camp cognoms si està disponible -->
            <p class="perfil-p"><strong>Cognom:</strong></p>
            <?php if (!empty($_SESSION['usuari']['cognom'])): ?> 
               <p><?php echo htmlspecialchars($_SESSION['usuari']['cognom']); ?></p>
            <?php else: ?>
                <p>No proporcionat</p>
            <?php endif; ?>
            <div class="separador_gran"></div>
                    
            <p class="perfil-p"><strong>Email:</strong></p>
            <p><?php echo htmlspecialchars($_SESSION['usuari']['email']); ?></p>
            <div class="separador_gran"></div>

            <p class="perfil-p"><strong>Contrasenya: </strong></p>
            <p>*************</p>
        <?php else: ?>
           <p>No hi ha cap usuari connectat.</p>
        <?php endif; ?>
    </div>
</body>
</html>