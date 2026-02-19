<!--Álvaro Masedo Pérez-->
<?php
if (!isset($tokenValido)) {
    $tokenValido = false;
}
?>
<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../resources/css/style.recuperarContrasenya.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
    <title>Restablir Contrasenya</title>
</head>

<body>
    <header>
        <ul>
            <li><a href="../../index.php"><b><img class="logo-img" src="../../uploads/img/logo.webp" alt="home" title="Inici"></b></a>
            </li>
            <li><a class="button" href="../view/vista.login.php" title="Iniciar Sessio"><b>Iniciar Sessio</b></a>
                <a class="button" href="../view/vista.signup.php" title="Registrar-se"><b>Registrar-se</b></a></li>
        </uL>
    </header>
    <main>
        <h1>RESTABLIR CONTRASENYA</h1>
        <div class="separador"></div>

        <?php echo $errorToken ?? ''; ?>
        <?php echo $enviatMissatge ?? ''; ?>

        <?php if ($tokenValido): ?>
            <form method="POST" action="../controller/recuperarContrasenya.php?action=actualitzarContrasenya">
                <p class="Text">Introdueix la nova contrasenya</p>
                <p class="subText">Minim 12 caracters, amb majuscules, minuscules, numero i simbol</p>

                <label for="nova_contrasenya">Nova contrasenya: </label>
                <p class="requirit"> *</p>
                <input type="password" id="nova_contrasenya" name="nova_contrasenya" placeholder="Nova contrasenya">
                <?php echo $errorContrasenya ?? ''; ?>

                <label for="rep_contrasenya">Repeteix la contrasenya: </label>
                <p class="requirit"> *</p>
                <input type="password" id="rep_contrasenya" name="rep_contrasenya" placeholder="Repeteix la contrasenya">
                <?php echo $errorRepContrasenya ?? ''; ?>

                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">

                <input type="submit" value="CANVIAR CONTRASENYA">
            </form>
        <?php endif; ?>
    </main>
    <?php include __DIR__ . '/vista.footer.php'; ?>
</body>

</html>
