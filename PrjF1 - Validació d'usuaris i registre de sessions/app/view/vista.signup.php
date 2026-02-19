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
    <?php
    // Verificar si hi ha dades prèvies del formulari per omplir els camps
    require_once __DIR__ . '/../../includes/session_check.php';
    require_once __DIR__ . '/../../lib/oauth_config.php';
    OAuthConfig::inicialitzar();
    $formData = $_SESSION['form_data'] ?? [];
    $nom = $formData['nom'] ?? '';
    $cognom = $formData['cognom'] ?? '';
    $nickname = $formData['nickname'] ?? '';
    $email = $formData['email'] ?? '';
    $contrasenya = $formData['contrasenya'] ?? '';
    $repContrasenya = $formData['repContrasenya'] ?? '';
    
    // Gestionar errors OAuth des de paràmetres GET
    $enviatMissatge = '';
    if (isset($_GET['error'])) {
        switch ($_GET['error']) {
            case 'oauth_account_exists':
                $enviatMissatge = '<p class="error">AQUEST COMPTE JA EXISTEIX. INICIA SESSIÓ NORMALMENT I LLAVORS VINCULA GOOGLE DES DEL TEU PERFIL.</p>';
                break;
            case 'oauth_no_data':
                $enviatMissatge = '<p class="error">ERROR: Dades de registre OAuth no vàlides.</p>';
                break;
        }
    }
    ?>
    <header>
        <ul>
            <li><a href="../../index.php"><b><img class="logoIMG" src="../../uploads/img/logo.webp" alt="home" title="Inici"></b></a>
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

            <!-- Missatge d'èxit o error -->
            <?php echo $enviatMissatge ?? ''; ?>

            <!-- Enviar cap a la base de dades-->
            <input type="submit" name="btn-enviar" value="REGISTRAR-SE">
            <p>Ja tens un compte? <a class="link" href="../view/vista.login.php">Inicia sessió</a></p>
            <div class="separator">
                <span>o</span>
            </div>

            <!-- Registrar-se amb Google -->
            <div class="social-login">
                <div class="google-btn">
                    <img class="social-img" src="../../uploads/img/googleLogo.ico" alt="Logo de Google">
                    <a href="<?php echo OAuthConfig::obtenirUrlAuthGoogle('', 'signup'); ?>">
                        Registra't amb Google
                    </a>
                </div>
            </div>
                </div>
            </div>
        </form>
    </main>
    <!-- Footer -->
    <?php include __DIR__ . '/vista.footer.php'; ?>
</body>

</html>