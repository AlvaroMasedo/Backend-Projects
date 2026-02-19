<!--Álvaro Masedo Pérez-->
<?php
// Verificar si la sessió ha expirat mitjançant paràmetre GET o cookie
$sessionExpired = (isset($_GET['session_expired']) && $_GET['session_expired'] == '1') || (isset($_COOKIE['session_expired']) && $_COOKIE['session_expired'] == '1');

if ($sessionExpired && isset($_COOKIE['session_expired'])) {
    setcookie('session_expired', '', time() - 3600, '/'); // Borrar cookie després de mostrar missatge
}

// SEMPRE requerir oauth_config per a la vista
require_once __DIR__ . '/../../lib/oauth_config.php';
require_once __DIR__ . '/../../includes/session_check.php';

// Inicialitzar contadorIntents si no existeix (per si s'accedeix directament a la vista)
if (!isset($contadorIntents)) {
    OAuthConfig::inicialitzar();
    $contadorIntents = $_SESSION['contadorIntents'] ?? 0;
} else {
    // Si ve del controlador, també inicialitzar OAuth
    OAuthConfig::inicialitzar();
}

// Gestionar errors OAuth des de paràmetres GET
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'oauth_state_invalid':
            $enviatMissatge = '<p class="error">ERROR DE SEGURETAT: Sessió OAuth no vàlida.</p>';
            break;
        case 'oauth_token_failed':
            $enviatMissatge = '<p class="error">ERROR: No s\'ha pogut obtenir el token d\'autenticació.</p>';
            break;
        case 'oauth_register_failed':
            $enviatMissatge = '<p class="error">ERROR: No s\'ha pogut registrar el compte.</p>';
            break;
        case 'oauth_invalid':
            $enviatMissatge = '<p class="error">ERROR: Dades OAuth invàlides.</p>';
            break;
        case 'oauth_different_provider':
            $enviatMissatge = '<p class="error">AQUEST COMPTE JA EXISTEIX VINCULAT A UN ALTRE PROVEÏDOR. INICIA SESSIÓ AMB EL PROVEÏDOR ORIGINAL O VINCULA\'L DES DEL TEU PERFIL.</p>';
            break;
        case 'oauth_need_normal_login':
            $enviatMissatge = '<p class="error">AQUEST COMPTE VA SER CREAT AMB EMAIL I CONTRASENYA. INICIA SESSIÓ NORMALMENT I DESPRÉS VINCULA GOOGLE DES DEL TEU PERFIL.</p>';
            break;
        default:
            $enviatMissatge = '<p class="error">ERROR DESCONEGUT: ' . htmlspecialchars($_GET['error']) . '</p>';
            break;
    }
}

?>
<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../resources/css/style.login.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>

    <!-- Google reCAPTCHA client script (centralitzat) -->
    <?php require_once __DIR__ . '/../../lib/recaptcha.php';
    imprimir_recaptcha_script(); ?>
    <title>Iniciar Sessió</title>
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
            <li><a class="button" href="../view/vista.signup.php" title="Registrar-se"><b>Registrar-se</b></a></li>
        </uL>
    </header>
    <main>
        <h1>INICIAR SESSIÓ</h1>
        <div class="separador"></div>
        
        <form method="POST" action="../controller/login.php?action=login">

            <!-- Primer camp del formulari (email)-->
            <label for="email">Email: </label>
            <p class="requirit"> *</p>
            <input type="email" id="email" name="email" placeholder="correu@exemple.com"
                value="<?php echo htmlspecialchars($email ?? ''); ?>">
            <?php echo $errorEmail ?? ''; ?>

            <!-- Segon camp del formulari (Contrasenya)-->
            <label for="contrasenya">Contrasenya: </label>
            <p class="requirit"> *</p>
            <input type="password" name="contrasenya" id="contrasenya"
                value="<?php echo htmlspecialchars($contrasenya ?? ''); ?>">
            <?php echo $errorContrasenya ?? ''; ?>

            <!-- Missatge d'èxit o error -->
            <?php echo $enviatMissatge ?? ''; ?>

            <!-- Mostrar reCAPTCHA si s'han superat els intents permesos -->
            <div class="recaptcha">
                <?php mostrar_recaptcha_si_es_necessita($contadorIntents); ?>
            </div>

            <ul class="login">
                <li>
                    <label for="recorda">Recorda'm</label>
                    <input type="checkbox" id="recorda" name="recorda">
                </li>
                <li><a href="vista.recuperarContrasenya.php">Has oblidat la contrasenya?</a></li>
            </ul>

            <!-- Preguntar a la BBDD si existeix l'email registrat per iniciar sesió -->
            <input type="submit" name="btn-enviar" value="INICIAR SESSIÓ">
            <p>No tens un compte? <a class="link" href="../view/vista.signup.php">Registrar-se</a></p>
            <div class="separator">
                <span>o</span>
            </div>

            <!-- Iniciar sessió amb Google o Apple -->
            <div class="social-login">
                <div class="google-btn">
                    <img class="social-img" src="../../uploads/img/googleLogo.ico" alt="Logo de Google">
                    <a href="<?php 
                        try {
                            echo OAuthConfig::obtenirUrlAuthGoogle('', 'login');
                        } catch (Exception $e) {
                            echo '#error-google';
                        }
                    ?>">
                        Inicia sessió amb Google
                    </a>
                </div>
                <div class="apple-btn">
                    <img class="social-img" src="../../uploads/img/appleLogo.ico" alt="Logo d'Apple">
                    <a href="<?php 
                        try {
                            echo OAuthConfig::obtenirUrlAuthApple('', 'login');
                        } catch (Exception $e) {
                            echo '#error-apple';
                        }
                    ?>">
                        Inicia sessió amb Apple
                    </a>
                </div>
            </div>
        </form>
    </main>
    <?php include __DIR__ . '/vista.footer.php'; ?>
</body>

</html>