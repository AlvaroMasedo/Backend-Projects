<!--Álvaro Masedo Pérez-->
<?php

// Verificar si la sessió ha expirat mitjançant paràmetre GET o cookie
$sessionExpired = (isset($_GET['session_expired']) && $_GET['session_expired'] == '1') || (isset($_COOKIE['session_expired']) && $_COOKIE['session_expired'] == '1');
if ($sessionExpired && isset($_COOKIE['session_expired'])) {
    setcookie('session_expired', '', time() - 3600, '/'); //Esborrar cookie després de mostrar el missatge
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
    <link rel="stylesheet" href="../../resources/css/style.modificarArticle.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
    <title>Modificar Article</title>
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
        <h1>Modificar Article</h1>
        <!-- Contingut principal de la pàgina de modificació d'Articles -->
        <div class="modificar-article-container">
            <form class="form-modificar" action="../controller/articles.php?action=modificar" method="post">

                <!-- Camp ocult amb l'id de l'article -->
                <input type="hidden" name="id" value="<?= htmlspecialchars($id ?? '') ?>">

                <!-- Camp Nom de l'Article -->
                <label for="Nom">Nom de l'Article:</label><p class="requirit"> *</p>
                <br>
                <input type="text" id="Nom" name="Nom" value="<?php echo htmlspecialchars($nom ?? ''); ?>">
                <?php echo $errorNom ?? ''; ?>

                <!-- Camp Cos de l'Article -->
                <br>
                <label for="Cos">Cos de l'Article:</label><p class="requirit"> *</p>
                <br>
                <textarea id="Cos" name="Cos" rows="10" cols="50"><?= htmlspecialchars($cos ?? '') ?></textarea>
                <!-- Missatge d'èxit o error -->
                <?php echo $missatge ?? ''; ?>

                <!-- Botó d'enviament -->
                <input type="submit" value="MODIFICAR ARTICLE">
            </form>

            <a class="cancel-button" href="../view/vista.articles.php">TORNAR ENRERE</a>
        </div>
    </main>
    <!-- Footer -->
    <?php include __DIR__ . '/vista.footer.php'; ?>

</body>

</html>