<!--Álvaro Masedo Pérez-->

<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../resources/css/style.signup.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
    <title>Registrar-se</title>
</head>

<body>
    <header>
        <ul>
            <li><a href="../../index.php"><b><img src="../../uploads/img/logo.webp" alt="home" title="Inici"></b></a>
            </li>
            <li><a class="button" href="../view/vista.login.php" title="Iniciar sessió"><b>Iniciar Sessió</b></a></li>
        </uL>
    </header>
    <main>
        <h1>REGISTRAR-SE</h1>
        <div class="separador"></div>
        <form method="POST" action="../controller/registre.php?action=registre">

            <!-- Primer camp del formulari (Nom)-->
            <label for="nom">Nom:</label><p class="requirit"> *</p>
            <input type="text" name="nom" id="nom" value="<?php echo htmlspecialchars($nom ?? ''); ?>">
            <?php echo $errorNom ?? ''; ?>

            <!-- Segon camp del formulari (Cognom)-->
            <label for="cognom">Cognom:  </label>
            <input type="text" id="cognom" name="cognom" value="<?php echo htmlspecialchars($cognom ?? ''); ?>">
            <?php echo $errorCognom ?? ''; ?>

            <!-- Tercer camp del formulari (NickName)-->
            <label for="nickname">Nickname: </label><p class="requirit"> *</p>
            <input type="text" name="nickname" id="nickname" value="<?php echo htmlspecialchars($nickname ?? ''); ?>"> 
            <?php echo $errorNickname ?? ''; ?>

            <!-- Quart camp del formulari (email)-->
            <label for="email">Email: </label><p class="requirit"> *</p>
            <input type="email" id="email" name="email" placeholder="correu@exemple.com"
                value="<?php echo htmlspecialchars($email ?? ''); ?>">
            <?php echo $errorEmail ?? ''; ?>

            <!-- Cinqué camp del formulari (Contrasenya)-->
            <label for="contrasenya">Contrasenya: </label><p class="requirit"> *</p>
            <input type="password" name="contrasenya" id="contrasenya"
                value="<?php echo htmlspecialchars($contrasenya ?? ''); ?>">
            <?php echo $errorContrasenya ?? ''; ?>
            <p class="missatge">La contrasenya ha de contenir:</p>
            <ul>
                <li>De 12 a 20 caràcters</li>
                <li>Mínim una majúscula i minúscula</li>
                <li>Mínim un número</li>
                <li>Mínim un càracter especial</li>
            </ul>

            <!-- Sisé camp del formulari (Repetir Contrasenya)-->
            <label for="repContrasenya">Repeteix la Contrasenya: </label><p class="requirit"> *</p>
            <input type="password" name="repContrasenya" id="repContrasenya"
                value="<?php echo htmlspecialchars($repContrasenya ?? ''); ?>">
            <?php echo $errorRepContrasenya ?? ''; ?>

            <a href="../view/vista.login.php">Ja tens un compte?</a>

            <!-- Missatge d'èxit o error -->
            <?php echo $enviatMissatge ?? ''; ?>

            <!-- Enviar cap a la base de dades-->
            <input type="submit" name="btn-enviar" value="REGISTRAR-SE">
        </form>
    </main>
    <!-- Footer -->
    <?php include __DIR__ . '/vista.footer.php'; ?>
</body>

</html>