<!--  Älvaro Masedo Pérez-->
<?php require_once __DIR__ . '/../../config/basepath.php'; ?>
<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/resources/css/style.header.css">
    <script>
        // Si NO hi ha cookie remember_token, mata la sessió quan es detecti una nova sessió de navegador.
        (function () {
            var hasRemember = document.cookie.indexOf('remember_token=') !== -1;
            var markerKey = 'sessionAliveMarker';
            if (!sessionStorage.getItem(markerKey)) {
                sessionStorage.setItem(markerKey, '1');
                if (!hasRemember) {
                    var url = '<?php echo BASE_PATH; ?>/includes/close_on_new_session.php';
                    if (navigator.sendBeacon) {
                        navigator.sendBeacon(url);
                    } else {
                        fetch(url, { method: 'POST', keepalive: true });
                    }
                }
            }
        })();
    </script>
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

            <!-- Barra de búsqueda -->
            <li class="search-bar">
                <form class="search-form" method="get" action="<?php echo BASE_PATH; ?>/index.php">
                    <input type="text" name="q" placeholder="Buscar articles..." class="search-input" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                    <button type="submit" class="search-btn">Buscar</button>
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
                        <li><a href="<?php echo BASE_PATH; ?>/includes/session_check.php?logout=1">Tancar sessió</a></li>
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

</body>

</html>