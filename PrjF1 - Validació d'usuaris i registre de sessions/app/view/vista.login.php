<!--Álvaro Masedo Pérez-->
<?php
// Verificar si la sessió ha expirat mitjançant paràmetre GET o cookie
$sessionExpired = (isset($_GET['session_expired']) && $_GET['session_expired'] == '1') || (isset($_COOKIE['session_expired']) && $_COOKIE['session_expired'] == '1');

if ($sessionExpired && isset($_COOKIE['session_expired'])) {
    setcookie('session_expired', '', time() - 3600, '/'); // Borrar cookie tras mostrar mensaje
}

// Contador d'intents per reCAPTCHA
$contadorIntents = $contadorIntents ?? 0;

?>
<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../resources/css/style.login.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>

    <!-- Google reCAPTCHA client script (centralitzat) -->
    <?php require_once __DIR__ . '/../../lib/recaptcha.php'; imprimir_recaptcha_script(); ?>
    <title>LogIn</title>
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
            <li><a href="../../index.php"><b><img src="../../uploads/img/logo.webp" alt="home" title="Inici"></b></a>
            </li>
            <li><a class="button" href="../view/vista.signup.php" title="Registrar-se"><b>SignUp</b></a></li>
        </uL>
    </header>
    <main>
        <h1>INICIAR SESSIÓ</h1>
        <div class="separador"></div>
        <form method="POST" action="../controller/login.php?action=login">
            
            <!-- Primer camp del formulari (email)-->
            <label for="email">Email: </label>
            <input type="email" id="email" name="email" placeholder="correu@exemple.com"
                value="<?php echo htmlspecialchars($email ?? ''); ?>">
            <?php echo $errorEmail ?? ''; ?>

            <!-- Segon camp del formulari (Contrasenya)-->
            <label for="contrasenya">Contrasenya: </label>
            <input type="password" name="contrasenya" id="contrasenya"
                value="<?php echo htmlspecialchars($contrasenya ?? ''); ?>">
            <?php echo $errorContrasenya ?? ''; ?>

            <ul class="login">
                <li><label for="Recorda'm">Recorda'm</label><input type="checkbox" id="recorda" name="recorda"></li>
                <li><a href="#">Has oblidat la contrasenya?</a></li>
                <li><a href="../view/vista.signup.php">Crear nou compte</a></li>
            </ul>

            <!-- Missatge d'èxit o error -->
            <?php echo $enviatMissatge ?? ''; ?>

            <!-- Mostrar reCAPTCHA si s'han superat els intents permesos -->
            <?php mostrar_recaptcha_si_es_necessita($contadorIntents); ?>

            <!-- Preguntar a la BBDD si existeix l'email registrat per iniciar sesió -->
            <input type="submit" name="btn-enviar" value="INICIAR SESSIÓ">
        </form>
    </main>
    <?php include __DIR__ . '/vista.footer.php'; ?>
</body>

</html>