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
    <link rel="stylesheet" href="../../resources/css/style.eliminarArticle.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
    <title>Perfil</title>
</head>
<body>
    <!-- Contingut principal de la pàgina d'eliminació d'Articles -->
    <div class="eliminar-article-container">
        <!-- Missatge d'error/avís (si n'hi ha) -->
        <?php echo $missatge ?? ''; ?>

        <!-- Botó d'eliminació (en un formulari separat) -->
        <form action="../controller/articles.php?action=eliminar" method="post" style="margin-top:1rem;">
            <h2>Estàs segur que vols eliminar aquest article?</h2>
            <p>Un cop eliminat, no podràs recuperar-lo.</p>
            <input type="hidden" name="id" value="<?= htmlspecialchars($id ?? '') ?>">
            <input type="submit" value="ELIMINAR ARTICLE">
            <a class="cancel-button" href="../view/vista.articles.php">TORNAR ENRERE</a>
        </form>
    </div>
</body>
</html>    