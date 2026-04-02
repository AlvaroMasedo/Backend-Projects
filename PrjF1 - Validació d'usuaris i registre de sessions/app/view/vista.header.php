<!--  Älvaro Masedo Pérez-->
<?php require_once __DIR__ . '/../../config/basepath.php'; ?>
<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/resources/css/style.header.css">
</head>

<body>
    <header>
        <ul>
            <li class="logo">
                <a href="<?php echo BASE_PATH; ?>/index.php">
                    <img src="<?php echo BASE_PATH; ?>/uploads/img/logo.webp"
                        alt="F1 logo" class="logo-img" title="Inici">
                </a>
            </li>

            <!-- Cerca amb Ajax -->
            <li class="search-bar">
                <form class="search-form" id="api-search-form" action="#" onsubmit="return false;">
                    <input type="text" id="api-search-input" placeholder="Buscar articles..." class="search-input" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                    <button type="button" class="search-btn" id="api-search-btn">Buscar</button>
                </form>
            </li>

            <li class="push-right">
                <a class="button-usuari" href="<?php echo BASE_PATH; ?>/app/view/vista.articles.php">Articles</a>
            </li>


            <?php if (isset($_SESSION['usuari'])): ?>
                <li class="menu-usuari">
                    <span class="button-usuari"><?php echo htmlspecialchars($_SESSION['usuari']['nickname']); ?> ▼</span>
                    <ul class="desplegable-usuari">
                        <li><a href="<?php echo BASE_PATH; ?>/app/view/vista.perfil.php">Perfil</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/app/view/vista.articles.php">Articles</a></li>
                        <?php if (isset($_SESSION['usuari']['administrador']) && $_SESSION['usuari']['administrador'] == 1): ?>
                            <li><a href="<?php echo BASE_PATH; ?>/app/view/vista.usuaris.php">Usuaris</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo BASE_PATH; ?>/app/controller/session_check.php?logout=1">Tancar sessió</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li class="menu-usuari">
                    <a class="button" href="<?php echo BASE_PATH; ?>/app/view/vista.login.php"
                        title="Iniciar sessió"><b>Iniciar Sessió</b></a>
                </li>
                <li>
                    <a class="button" href="<?php echo BASE_PATH; ?>/app/view/vista.signup.php"
                        title="Registrar-se"><b>Registrar-se</b></a>
                </li>
            <?php endif; ?>
        </ul>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('api-search-input');
            const button = document.getElementById('api-search-btn');
            const status = document.getElementById('api-search-status');
            const results = document.getElementById('api-search-results');
            const apiUrl = <?= json_encode(BASE_PATH . '/api/?q=') ?>;

            if (!input || !button || !status || !results) {
                return;
            }

            function escapeHtml(value) {
                return String(value)
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function pintarArticles(articles) {
                if (!Array.isArray(articles) || articles.length === 0) {
                    results.innerHTML = '';
                    status.textContent = 'No s\'han trobat articles.';
                    return;
                }

                results.innerHTML = articles.map(function (article) {
                    return '<article class="card">'
                        + '<header class="card__header"><h2 class="card__title">' + escapeHtml(article.Nom ?? '') + '</h2></header>'
                        + '<div class="card__body">'
                        + '<p>' + escapeHtml(article.Cos ?? '') + '</p>'
                        + '<p class="autor">Autor: ' + escapeHtml(article.autor ?? '') + '</p>'
                        + '</div>'
                        + '</article>';
                }).join('');

                status.textContent = 'S\'han trobat ' + articles.length + ' article(s).';
            }

            async function buscarArticles() {
                const q = input.value.trim();
                status.textContent = 'Carregant...';
                results.innerHTML = '';

                try {
                    const response = await fetch(apiUrl + encodeURIComponent(q));
                    if (!response.ok) {
                        throw new Error('Error HTTP');
                    }

                    const data = await response.json();
                    pintarArticles(data.articles);
                } catch (error) {
                    status.textContent = 'Error carregant els articles.';
                }
            }

            button.addEventListener('click', buscarArticles);
            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    buscarArticles();
                }
            });
        });
    </script>

</body>

</html>