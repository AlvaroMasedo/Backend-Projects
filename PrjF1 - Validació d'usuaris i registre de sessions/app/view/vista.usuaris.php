<!--Álvaro Masedo Pérez-->
<?php

// Verificar si la sessió ha expirat mitjançant paràmetre GET o cookie
$sessionExpired = (isset($_GET['session_expired']) && $_GET['session_expired'] == '1') || (isset($_COOKIE['session_expired']) && $_COOKIE['session_expired'] == '1');
if ($sessionExpired && isset($_COOKIE['session_expired'])) {
    setcookie('session_expired', '', time() - 3600, '/'); //Eliminar cookie després de mostrar missatge
}
// Ara incloure session_check.php
require_once __DIR__ . '/../../includes/session_check.php';

// Verificar que l'usuari sigui administrador
if (!isset($_SESSION['usuari']) || $_SESSION['usuari']['administrador'] != 1) {
    header('Location: ../../index.php');
    exit;
}

// Carregar controlador d'usuaris per obtenir la llista d'usuaris
require_once __DIR__ . '/../controller/usuari.php';
?>
<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../resources/css/style.usuaris.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
    <title>Usuaris</title>
</head>

<body>
    <!--Si la sessió ha expirat, mostrar missatge-->
    <?php if ($sessionExpired): ?>
        <div class="alert-overlay">
            <div class="alert">
                <p>S'ha tancat la sessió.</p>
                <a class="button" href="vista.login.php">INICIAR SESSIÓ</a>
                <br>
                <a class="link" href="../../index.php">Navega com usuari anònim</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Header amb navegació -->
    <?php include __DIR__ . '/vista.header.php'; ?>

    <main>
        <h1>Usuaris</h1>
        <!-- Contingut principal de la pàgina d'usuaris -->
        <div class="articles-container">
            <!-- Mostrar missatges de success/error -->
            <?php if (isset($_GET['deleted']) && $_GET['deleted'] == '1'): ?>
                <div class="message-success">
                    <strong>✓</strong> Usuari eliminat correctament (incloent articles i foto de perfil).
                </div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="message-error">
                    <strong>✗</strong> Error en eliminar l'usuari.
                </div>
            <?php endif; ?>

            <?php if (empty($usuaris)): ?>
                <div class="no-articles">
                    <h3>No hi ha cap usuari per mostrar</h3>
                </div>
            <?php else: ?>
                <!-- div amb els usuaris -->
                <div class="cards-grid">
                    <?php foreach ($usuaris as $u): ?>
                        <article class="card card-horizontal">
                            <div class="card__info">
                                <h2 class="card__title"><?= htmlspecialchars($u['nickname']) ?></h2>
                                <p class="card__author"><?= htmlspecialchars($u['nom']) ?></p>
                            </div>
                            <div class="card__actions">
                                <!-- Botó d'eliminar amb confirmació -->
                                <a href="../controller/usuari.php?action=eliminar&nickname=<?= urlencode($u['nickname']) ?>" 
                                   class="eliminar-btn"
                                   title="Eliminar" 
                                   onclick="return confirm('Vols eliminar aquest usuari?\n\n ATENCIÓ: S\'eliminaran també:\n• Tots els articles associats\n• La foto de perfil\n\nAquesta acció no es pot desfer.');">
                                    <img class="eliminar-icon" src="../../uploads/img/eliminar.png" alt="Eliminar">
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/vista.footer.php'; ?>
</body>

</html>