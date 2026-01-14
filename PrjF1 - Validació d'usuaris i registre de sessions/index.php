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
    <link rel="stylesheet" href="resources/css/style.header.css">
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
    <?php include __DIR__ . '/app/view/vista.header.php'; ?>

    <main>
        <img class="F1-title" src="uploads/img/Formula1.webp" alt="Formula1-logo">
        
        <!-- Missatge de registre exitoso -->
        <?php if (isset($_GET['registered']) && $_GET['registered'] == '1'): ?>
            <div class="message-success">
                Registre completat amb èxit. Benvingut/da!
            </div>
            <script>
                // Eliminar el parámetro de la URL després de mostrar el mensaje
                if (window.history.replaceState) {
                    const url = new URL(window.location);
                    url.searchParams.delete('registered');
                    window.history.replaceState({}, document.title, url);
                }
            </script>
        <?php endif; ?>
        
        <?php if (empty($articles)): ?>
            <div class="no-articles" style="padding:2rem; text-align:center;">
                <?php if ($esBusqueda): ?>
                    <h3>No s'han trobat articles per a "<?= htmlspecialchars($busquedaTerm) ?>"</h3>
                    <p>Prova amb altres paraules clau.</p>
                    <a class="button" href="index.php">Veure tots els articles</a>
                <?php else: ?>
                    <h3>No hi ha cap article per mostrar</h3>
                    <p>Si vols, pots veure la llista d'articles o crear-ne de nous.</p>
                    <a class="button" href="app/view/vista.articles.php">Veure articles</a>
                <?php endif; ?>
            </div>
        <?php else: ?>

            <!-- Secció de selecció de articles per pàgina -->
            <div class="articles">
                <div>
                    <h3>Artícles per pàgina: </h3>

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
                        <input type="hidden" name="ordenar" value="<?= $ordreActual ?>">
                        <?php if ($esBusqueda): ?>
                            <input type="hidden" name="q" value="<?= htmlspecialchars($busquedaTerm) ?>">
                        <?php endif; ?>
                    </form>
                </div>

                <div>
                    <h3>Ordenar segons: </h3>

                    <!-- Formulari per seleccionar ordre dels articles -->
                    <form method="get">
                        <!-- Selector amb opcions d'ordre -->
                        <select name="ordenar" id="ordenar" onchange="this.form.submit()">
                            <option value="recent" <?= ($ordreActual === 'recent' ? 'selected' : '') ?>>Més recents
                            </option>
                            <option value="antic" <?= ($ordreActual === 'antic' ? 'selected' : '') ?>>Més antics
                            </option>
                            <option value="asc" <?= ($ordreActual === 'asc' ? 'selected' : '') ?>>Alfabeticament (ASC)
                            </option>
                            <option value="desc" <?= ($ordreActual === 'desc' ? 'selected' : '') ?>>Alfabeticament (DESC)
                            </option>
                        </select>
                        
                        <!-- Camp ocult per mantenir la pàgina actual quan es canvia l'ordre -->
                        <input type="hidden" name="page" value="<?= $paginaActual ?>">
                        <input type="hidden" name="per_page" value="<?= $articlesPerPagina ?>">
                        <?php if ($esBusqueda): ?>
                            <input type="hidden" name="q" value="<?= htmlspecialchars($busquedaTerm) ?>">
                        <?php endif; ?>
                    </form>
                </div>
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
                            <p class="autor">Última modificació: <?= htmlspecialchars(date('d/m/Y', strtotime($a['ultima_modificacio']))) ?></p>
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