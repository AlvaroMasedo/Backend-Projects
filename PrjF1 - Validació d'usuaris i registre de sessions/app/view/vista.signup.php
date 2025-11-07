<!--Álvaro Masedo Pérez-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../resources/css/style.signup.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
    <title>SignUp</title>        
</head>
<body>
    <header>
        <ul>
            <li><a href="../../index.php"><b><img src="../../uploads/img/home.webp" alt="home"></b></a></li>
            <li><a class="button" href="../view/vista.login.php"><b>Login</b></a></li>
        </uL>
    </header>
    <h1>Registrar-se</h1>
    <div class="separador"></div>
    <form method="POST" action ="../controller/users.php?action=registre">
        <!-- Primer camp del formulari (Nom)-->
        <label for="nom">Nom: </label>
        <input type="text" name="nom" id="nom" value="<?php echo htmlspecialchars($nom ?? ''); ?>">
        <?php echo $errorNom ?? ''; ?>

        <!-- Segon camp del formulari (Cognom)-->
        <label for="Cognom">Cognom: (Opcional) </label>
        <input type="text" id="cognom" name="cognom" value="<?php echo htmlspecialchars($cognom ?? ''); ?>">
        <?php echo $errorCognom ?? ''; ?>

        <!-- Tercer camp del formulari (NickName)-->
        <label for="nickname">Nickname: </label>
        <textarea name="nickname" id="nickname"><?php echo htmlspecialchars($nickname ?? ''); ?></textarea>
        <?php echo $errorNickname ?? ''; ?>

        <!-- Quart camp del formulari (email)-->
        <label for="email">Email: </label>
        <input type="email" id="email" name="email" placeholder="correu@exemple.com" value="<?php echo htmlspecialchars($email ?? ''); ?>">

        <!-- Cinqué camp del formulari (Contrasenya)-->
        <label for="contrasenya">Contrasenya: </label>
        <textarea name="contrasenya" id="contrasenya"><?php echo htmlspecialchars($contrasenya ?? ''); ?></textarea>
        <?php echo $errorContrasenya ?? ''; ?>
        <p>La contrasenya ha de contenir:</p>
        <ul>
            <li>De 12 a 20 caràcters</li>
            <li>Mínim una majúscula i minúscula</li>
            <li>Mínim un número</li>
            <li>Mínim un càracter especial</li>
        </ul>

        <!-- Sisé camp del formulari (Repetir Contrasenya)-->
        <label for="repContrasenya">Repeteix la Contrasenya: </label>
        <textarea name="repContrasenya" id="repContrasenya"><?php echo htmlspecialchars($repContrasenya ?? ''); ?></textarea>
        <?php echo $errorRepContrasenya ?? ''; ?>

        <!-- Enviar cap a la base de dades-->
        <input type="submit" name="btn-enviar" value="REGISTRAR-SE">
    </form>
</body>
</html>