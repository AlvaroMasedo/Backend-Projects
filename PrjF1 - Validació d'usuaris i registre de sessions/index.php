<!--Álvaro Masedo Pérez-->
<?php

// Verificar si la sessió ha expirat mitjançant paràmetre GET o cookie
$sessionExpired = (isset($_GET['session_expired']) && $_GET['session_expired'] == '1') || (isset($_COOKIE['session_expired']) && $_COOKIE['session_expired'] == '1');
if ($sessionExpired && isset($_COOKIE['session_expired'])) {
    setcookie('session_expired', '', time() - 3600, '/'); //Eliminar cookie després de mostrar missatge
}
// Ara incloure session_check.php
require_once __DIR__ . '/includes/session_check.php';
require_once __DIR__ . '/app/controller/articles.php';
?>
<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="resources/css/style.index.css">
    <link rel="stylesheet" href="resources/css/style.footer.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
    <title>Artícles F1</title>
</head>

<body>
    <?php if ($sessionExpired): ?>
        <div class="alert-overlay">
            <div class="alert">
                <p>S'ha tancat la sessió.</p>
                <a class="button" href="app/view/vista.login.php">INICIAR SESSIÓ</a>
                <br>
                <a class="link" href="index.php">Navega com usuari anònim</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <header>
        <ul>
            <li><a href="index.php"><img class="logo-img" src="uploads/img/logo.webp" alt="F1 logo" title="Inici"></a>
            </li>
            <?php if (isset($_SESSION['usuari'])): ?>
                <li class="menu-usuari">
                    <span class="button-usuari"><?php echo htmlspecialchars($_SESSION['usuari']['nickname']); ?> ▼</span>
                    <ul class="desplegable-usuari">
                        <li><a href="app/view/vista.perfil.php">Perfil</a></li>
                        <li><a href="app/view/vista.articles.php">Artícles</a></li>
                        <li><a href="includes/session_check.php?logout=1">Tancar sessió</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li><a class="button" href="app/view/vista.login.php" title="Iniciar sessió"><b>Login</b></a></li>
                <li><a class="button" href="app/view/vista.signup.php" title="Registrar-se"><b>Sign Up</b></a></li>
            <?php endif; ?>
        </ul>
    </header>
    <main>
        <?php if (empty($articles)): ?>
            <div class="no-articles" style="padding:2rem; text-align:center;">
                <h3>No hi ha cap article per mostrar</h3>
                <p>Si vols, pots veure la llista d'articles o crear-ne de nous.</p>
                <a class="button" href="app/view/vista.articles.php">Veure articles</a>
            </div>
        <?php else: ?>

            <!-- Secció de selecció de articles per pàgina -->
            <div class="articles">
                <h3>Selecciona els artícles que vols veure per pàgina</h3>

                <!-- Formulari per seleccionar nombre de articles -->
                <form method="get">
                    <!-- Selector amb opcions de 1 al 10 -->
                    <select name="per_page" id="articles" onchange="this.form.submit()">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <!-- Opció seleccionada segons el valor actual -->
                            <option value="<?= $i ?>" <?= ($i === $articlesPerPagina ? 'selected' : '') ?>>
                                <?= $i ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <!-- Camp ocult per mantenir la pàgina 1 quan es canvia el nombre d'articles -->
                    <input type="hidden" name="page" value="1">
                </form>
            </div>

            <!-- Quadrícula d'articles -->
            <div class="cards-grid">
                <?php foreach ($articles as $a): ?>
                    <!-- Targeta individual per cada article -->
                    <article class="card">
                        <!-- Capçalera de la targeta amb el títol -->
                        <header class="card__header">
                            <h2 class="card__title"><?= htmlspecialchars($a['Nom']) ?></h2>
                        </header>
                        <!-- Cos de la targeta amb el contingut -->
                        <div class="card__body">
                            <p><?= nl2br(htmlspecialchars($a['Cos'])) ?></p>
                            <p class="autor">Autor: <?= htmlspecialchars($a['autor']) ?> </p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Controls de paginació -->
            <div class="paginacio">
                <!-- Botó anterior (desactivat a la primera pàgina) -->
                <a class="page-btn <?= ($paginaActual == 1 ? 'is-disabled' : '') ?>" href="<?= $prevUrl ?>">&laquo;
                    Anterior</a>
                <!-- Indicador de pàgina actual -->
                <span class="page-state">Pàgina <?= $paginaActual ?> de <?= $totalPagines ?></span>
                <!-- Botó següent (desactivat a l'última pàgina) -->
                <a class="page-btn <?= ($paginaActual == $totalPagines ? 'is-disabled' : '') ?>"
                    href="<?= $nextUrl ?>">Següent
                    &raquo;</a>
            </div>
        <?php endif; ?>
    </main>
    <?php
    include __DIR__ . '/app/view/vista.footer.php';
    ?>
</body>

</html>