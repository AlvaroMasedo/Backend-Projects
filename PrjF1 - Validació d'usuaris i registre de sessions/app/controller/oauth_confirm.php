<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../model/model.usuari.php';

$modelUsuaris = new ModelUsers($conn);

// Obtenir dades de la sessió
$oauthData = $_SESSION['oauth_pending_data'] ?? null;
$context = $oauthData['context'] ?? 'signup';  // Per defecte signup, però pot ser login

if (!$oauthData) {
    header('Location: ../../app/view/vista.signup.php?error=oauth_no_data');
    exit;
}

// Si l'usuari confirma la creació
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === '1') {
    $email = $oauthData['email'];
    $nom = $oauthData['nom'];
    $cognom = $oauthData['cognom'];
    $provider = $oauthData['provider'];
    $oauthId = $oauthData['oauth_id'];
    
    // SEMPRE usar foto predeterminada
    $foto = 'uploads/img/fotos_perfil/foto_predeterminada/null.png';
    
    // Crear usuari
    $usuariNou = $modelUsuaris->guardarUsuariOAuth($email, $nom, $cognom, $foto, $provider, $oauthId);
    
    if ($usuariNou) {
        // Iniciar sessió
        session_regenerate_id(true);
        $_SESSION['usuari'] = [
            'nickname' => $usuariNou['nickname'],
            'nom' => $usuariNou['nom'],
            'cognom' => $usuariNou['cognom'],
            'email' => $usuariNou['email'],
            'administrador' => $usuariNou['administrador'],
            'imatge_perfil' => $usuariNou['imatge_perfil']
        ];
        $_SESSION['session_created'] = time();
        $_SESSION['remember_me'] = 0;
        $_SESSION['contadorIntents'] = 0;
        $_SESSION['_last_request_time'] = time();
        $_SESSION['oauth_login'] = true;
        
        // Generar tokens de navegador
        $browserToken = bin2hex(random_bytes(32));
        $_SESSION['browser_session_token'] = $browserToken;
        setcookie('browser_session_token', $browserToken, 0, '/', '', false, true);
        
        $browserMarker = bin2hex(random_bytes(16));
        $_SESSION['browser_marker'] = $browserMarker;
        setcookie('browser_marker', $browserMarker, 0, '/', '', false, true);
        
        $sessionName = session_name();
        $sessionId = session_id();
        setcookie($sessionName, $sessionId, 0, '/', '', false, true);
        
        // Netejar dades pendents
        unset($_SESSION['oauth_pending_data']);
        unset($_SESSION['oauth_state']);
        
        // Redirigir segons el context
        if ($context === 'login') {
            // Si ve del login, redirigir a index amb missatge d'èxit de login
            header('Location: ../../index.php?oauth_login=1');
        } else {
            // Si ve del signup, redirigir a index amb missatge de registre
            header('Location: ../../index.php?oauth_registered=1');
        }
        exit;
    } else {
        header('Location: ../../app/view/vista.signup.php?error=oauth_register_failed');
        exit;
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel'])) {
    // Si l'usuari cancela
    $cancelContext = $_SESSION['oauth_pending_data']['context'] ?? 'signup';
    unset($_SESSION['oauth_pending_data']);
    
    if ($cancelContext === 'login') {
        header('Location: ../../app/view/vista.login.php');
    } else {
        header('Location: ../../app/view/vista.signup.php');
    }
    exit;
}

// Mostrar página de confirmació
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../resources/css/style.signup.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
    <title>Confirmar <?php echo ($context === 'login') ? 'Accés' : 'Registre'; ?> OAuth</title>
</head>
<body>
    <header>
        <ul>
            <li><a href="../../index.php"><b><img class="logoIMG" src="../../uploads/img/logo.webp" alt="home" title="Inici"></b></a></li>
            <li><a class="button" href="../view/vista.login.php" title="Iniciar sessió"><b>Iniciar Sessió</b></a></li>
        </ul>
    </header>
    <main>
        <h1>CONFIRMAR <?php echo ($context === 'login') ? 'ACCÉS' : 'REGISTRE'; ?></h1>
        <div class="separador"></div>
        
        <div style="text-align: center; padding: 2rem;">
            <p style="font-size: 1.1rem; margin: 1rem 0;">
                <?php 
                    if ($context === 'login') {
                        echo "Aquesta és la primera vegada que inicies sessió amb aquest email. Vols crear un compte nou?";
                    } else {
                        echo "¿Vols crear un compte nou amb les següents dades?";
                    }
                ?>
            </p>
            
            <div style="background: #f0f0f0; padding: 1.5rem; border-radius: 8px; margin: 2rem 0; text-align: left; max-width: 400px; margin-left: auto; margin-right: auto;">
                <p><strong>Email:</strong> <?php echo htmlspecialchars($oauthData['email']); ?></p>
                <p><strong>Nom:</strong> <?php echo htmlspecialchars($oauthData['nom']); ?></p>
                <?php if (!empty($oauthData['cognom'])): ?>
                    <p><strong>Cognom:</strong> <?php echo htmlspecialchars($oauthData['cognom']); ?></p>
                <?php endif; ?>
                <p><strong>Proveïdor:</strong> <?php echo ucfirst($oauthData['provider']); ?></p>
            </div>
            
            <form method="POST" style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
                <button type="submit" name="confirm" value="1" class="button" style="padding: 0.8rem 2rem; background: #28a745; color: white; border: none; cursor: pointer; border-radius: 4px; font-weight: bold;">
                    ✓ CONFIRMAR I CREAR COMPTE
                </button>
                <button type="submit" name="cancel" value="1" class="button" style="padding: 0.8rem 2rem; background: #dc3545; color: white; border: none; cursor: pointer; border-radius: 4px; font-weight: bold;">
                    ✗ CANCELAR
                </button>
            </form>
            
            <p style="margin-top: 2rem; font-size: 0.9rem; color: #666;">
                <?php 
                    if ($context === 'login') {
                        echo "No vols crear el compte? <a href='../../app/view/vista.login.php' class='link'>Torna al login</a>";
                    } else {
                        echo "Si ja tens un compte, <a href='../view/vista.login.php' class='link'>inicia sessió aquí</a>";
                    }
                ?>
            </p>
        </div>
    </main>
    <?php include __DIR__ . '/../view/vista.footer.php'; ?>
</body>
</html>
