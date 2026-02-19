<!--Álvaro Masedo Pérez-->
<?php
// Verificar si la sessió ha expirat mitjançant paràmetre GET o cookie
$sessionExpired = (isset($_GET['session_expired']) && $_GET['session_expired'] == '1') || (isset($_COOKIE['session_expired']) && $_COOKIE['session_expired'] == '1');

if ($sessionExpired && isset($_COOKIE['session_expired'])) {
    setcookie('session_expired', '', time() - 3600, '/'); // Borrar cookie tras mostrar mensaje
}

// Inicializar contadorIntents si no existe (por si se accede directamente a la vista)
if (!isset($contadorIntents)) {
    // Usar funció centralitzada per garantir configuració correcta
    require_once __DIR__ . '/../../includes/session_check.php';
    $contadorIntents = $_SESSION['contadorIntents'] ?? 0;
}

?>
<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../resources/css/style.recuperarContrasenya.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
    <title>Recuperar Contrasenya</title>
</head>

<body>
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
    <header>
        <ul>
            <li><a href="../../index.php"><b><img class="logo-img" src="../../uploads/img/logo.webp" alt="home" title="Inici"></b></a>
            </li>
            <li><a class="button"href="../view/vista.login.php" title="Iniciar Sessió"><b>Iniciar Sessió</b></a>
                <a class="button" href="../view/vista.signup.php" title="Registrar-se"><b>Registrar-se</b></a></li>
        </uL>
    </header>
    <main>
        <h1>RECUPERAR CONTRASENYA</h1>
        <div class="separador"></div>
        
        <form method="POST" action="../controller/recuperarContrasenya.php?action=recuperarContrasenya">
            <p class="Text">Has oblidat la contrasenya?</p>
            <p class="subText">Reseteja la teva contrasenya amb dues passes</p>
            <!-- Primer camp del formulari (email)-->
            <label for="email">Email: </label>
            <p class="requirit"> *</p>
            <input type="text" id="email" name="email" placeholder="correu@exemple.com"
                value="<?php echo htmlspecialchars($email ?? ''); ?>">
            <?php echo $errorEmail ?? ''; ?>

            <!-- Missatge d'èxit o error -->
            <?php echo $enviatMissatge ?? ''; ?>

            <input type="submit" name="btn-enviar" value="ENVIAR CORREU">
        </form>
    </main>
    <?php include __DIR__ . '/vista.footer.php'; ?>
</body>

</html>