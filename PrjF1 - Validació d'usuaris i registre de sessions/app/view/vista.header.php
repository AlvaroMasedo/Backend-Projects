<!--  Älvaro Masedo Pérez-->
<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../resources/css/style.header.css">
</head>

<body>
    <header>
        <ul>
            <li class="logo">
                <a href="/Pràctiques/Backend/PrjF1 - Validació d'usuaris i registre de sessions/index.php">
                    <img src="/Pràctiques/Backend/PrjF1 - Validació d'usuaris i registre de sessions/uploads/img/logo.webp"
                        alt="F1 logo" class="logo-img" title="Inici">
                </a>
            </li>

            <!-- Barra de búsqueda -->
            <li class="search-bar">
                <form method="get" action="/Pràctiques/Backend/PrjF1 - Validació d'usuaris i registre de sessions/index.php" class="search-form">
                    <input type="text" name="q" placeholder="Buscar articles..." class="search-input" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                    <button type="submit" class="search-btn">Buscar</button>
                </form>
            </li>

            <?php if (isset($_SESSION['usuari'])): ?>
                <li class="menu-usuari push-right">
                    <span class="button-usuari"><?php echo htmlspecialchars($_SESSION['usuari']['nickname']); ?> ▼</span>
                    <ul class="desplegable-usuari">
                        <li><a href="/Pràctiques/Backend/PrjF1 - Validació d'usuaris i registre de sessions/app/view/vista.perfil.php">Perfil</a></li>
                        <li><a href="/Pràctiques/Backend/PrjF1 - Validació d'usuaris i registre de sessions/app/view/vista.articles.php">Artícles</a></li>
                        <?php if (isset($_SESSION['usuari']['administrador']) && $_SESSION['usuari']['administrador'] == 1): ?>
                            <li><a href="/Pràctiques/Backend/PrjF1 - Validació d'usuaris i registre de sessions/app/view/vista.usuaris.php">Usuaris</a></li>
                        <?php endif; ?>
                        <li><a href="/Pràctiques/Backend/PrjF1 - Validació d'usuaris i registre de sessions/includes/session_check.php?logout=1">Tancar sessió</a></li>
                        <li><a href="/Pràctiques/Backend/PrjF1 - Validació d'usuaris i registre de sessions/includes/session_check.php?logout=1&forget_device=1">Tancar sessió i oblidar dispositiu</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li class="push-right">
                    <a class="button" href="/Pràctiques/Backend/PrjF1 - Validació d'usuaris i registre de sessions/app/view/vista.login.php" 
                        title="Iniciar sessió"><b>Login</b></a>
                </li>
                <li>
                    <a class="button" href="/Pràctiques/Backend/PrjF1 - Validació d'usuaris i registre de sessions/app/view/vista.signup.php" 
                        title="Registrar-se"><b>Sign Up</b></a>
                </li>
            <?php endif; ?>
        </ul>
    </header>

</body>

</html>