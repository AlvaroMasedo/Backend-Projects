<!--Álvaro Masedo Pérez-->
<?php

// Verificar si la sessió ha expirat mitjançant paràmetre GET o cookie
$sessionExpired = (isset($_GET['session_expired']) && $_GET['session_expired'] == '1') || (isset($_COOKIE['session_expired']) && $_COOKIE['session_expired'] == '1');
if ($sessionExpired && isset($_COOKIE['session_expired'])) {
    setcookie('session_expired', '', time() - 3600, '/'); //Eliminar cookie després de mostrar missatge
}
// Ara incloure session_check.php
require_once __DIR__ . '/../../includes/session_check.php';
// Si no hi ha sessió volem ocultar els botons d'autenticació al header d'aquesta vista
$ocultarBotons = !isset($_SESSION['usuari']);
// Carregar controlador d'articles per obtenir $articles i paginació
require_once __DIR__ . '/../controller/articles.php';
?>
<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../resources/css/style.articles.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
    <title>Articles</title>
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
        <h1>Articles</h1>
        <!-- Contingut principal de la pàgina d'articles -->
        <div class="articles-container">
            <!-- Missatges d'èxit i error -->
            <?php if (isset($_GET['added']) && $_GET['added'] == '1'): ?>
                <div class="message-success">
                    Article afegit correctament.
                </div>
            <?php elseif (isset($_GET['modified']) && $_GET['modified'] == '1'): ?>
                <div class="message-success">
                    Article modificat correctament.
                </div>
            <?php elseif (isset($_GET['deleted']) && $_GET['deleted'] == '1'): ?>
                <div class="message-success">
                    Article eliminat correctament.
                </div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="message-error">
                    Error: No s'ha pogut completar l'operació.
                </div>
            <?php endif; ?>

            <!-- Enllaç per obrir vista de creació d'articles -->
            <div class="afegir-article-div">
                <a href="vista.afegirArticle.php" class="button">AFEGIR ARTÍCLE</a>
            </div>

            <!-- Si no hi ha sessió, mostrar missatge d'inici de sessió -->
            <?php if (!isset($_SESSION['usuari'])): ?>
                <div class="no-session">
                    <h3>Has d'iniciar sessió per veure els articles.</h3>
                    <p>Accedeix amb el teu compte per veure i gestionar els articles.</p>
                    <a class="button" href="vista.login.php">Iniciar sessió</a>
                </div>
            <?php else: ?>
                <!-- Si hi ha sessió, mostrar els articles amb opcions de modificació/eliminació -->
                <?php if (empty($articles)): ?>
                    <div class="no-articles">
                        <h3 class="textWhite">No hi ha cap article per mostrar</h3>
                    </div>
                <?php endif; ?>

                <!-- div amb els articles -->
                <div class="cards-grid">
                    <?php foreach ($articles as $a): ?>
                        <article class="card card-horizontal">
                            <div class="card__info">
                                <h2 class="card__title"><?= htmlspecialchars($a['Nom']) ?></h2>
                                <p class="card__author">Autor: <?= htmlspecialchars($a['autor']) ?></p>
                            </div>
                            <div class="card__actions">
                                <p>Modificar / Eliminar</p>
                                <!-- Botó de modificar: obrim la vista de modificació passant l'id -->
                                <a href="vista.modificarArticle.php?id=<?= urlencode($a['id']) ?>" class="modificar-btn"
                                    title="Modificar"><img class="modificar-icon" src="../../uploads/img/modificar.png"
                                        alt="Modificar"></a>
                                <a href="../controller/articles.php?action=eliminar&id=<?= urlencode($a['id']) ?>" class="eliminar-btn"
                                    title="Eliminar" onclick="return confirm('Vols eliminar aquest article?');"><img class="eliminar-icon" src="../../uploads/img/eliminar.png"
                                        alt="Eliminar"></a>
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