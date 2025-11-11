<!--Álvaro Masedo Pérez-->
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../resources/css/style.login.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
    <title>LogIn</title>        
</head>
<body>
    <header>
        <ul>
            <li><a href="../../index.php"><b><img src="../../uploads/img/home.webp" alt="home"></b></a></li>
            <li><a class="button" href="../view/vista.signup.php"><b>SignUp</b></a></li>
        </uL>
    </header>
    <h1>INICIAR SESSIÓ</h1>
    <div class="separador"></div>
    <form method="POST" action ="../controller/login.php?action=login">
        <!-- Primer camp del formulari (email)-->
        <label for="email">Email: </label>
        <input type="email" id="email" name="email" placeholder="correu@exemple.com" value="<?php echo htmlspecialchars($email ?? ''); ?>">
        <?php echo $errorEmail ?? ''; ?>

        <!-- Segon camp del formulari (Contrasenya)-->
        <label for="contrasenya">Contrasenya: </label>
        <input type="password" name="contrasenya" id="contrasenya" value="<?php echo htmlspecialchars($contrasenya ?? ''); ?>">
        <?php echo $errorContrasenya ?? ''; ?>

        <!-- Missatge d'èxit o error -->
        <?php echo $enviatMissatge ?? ''; ?>

        <!-- Preguntar a la BBDD si existeix l'email registrat per iniciar sesió -->
        <input type="submit" name="btn-enviar" value="INICIAR SESSIÓ">
    </form>
</body>
</html>