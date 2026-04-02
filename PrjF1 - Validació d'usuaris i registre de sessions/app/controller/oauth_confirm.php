<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/session_check.php';
require_once __DIR__ . '/../model/model.usuari.php';

$modelUsuaris = new ModelUsers($conn);

/**
 * Genera un nickname base a partir de l'email.
 */
function generarNicknameBaseDesdeEmail(string $email): string
{
    $nickname = explode('@', $email)[0] ?? '';
    $nickname = preg_replace('/[^a-zA-Z0-9_]/', '_', $nickname) ?? '';
    $nickname = substr($nickname, 0, 15);

    return $nickname !== '' ? $nickname : 'usuari';
}

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
    $cognom = trim((string) ($oauthData['cognom'] ?? ''));
    $provider = $oauthData['provider'];
    $oauthId = $oauthData['oauth_id'];

    // Preparar dades de negoci fora del model
    $nicknameBase = generarNicknameBaseDesdeEmail($email);
    $nickname = $nicknameBase;
    $contador = 1;
    while ($modelUsuaris->existeixNickname($nickname)) {
        $nickname = substr($nicknameBase, 0, 12) . $contador;
        $contador++;
    }
    $cognomFinal = ($cognom === '') ? null : $cognom;
    
    // SEMPRE usar foto predeterminada
    // Guardar NULL perà que les vistes mostrin la imatge predeterminada
    $foto = null;
    
    // Crear usuari
    $okGuardar = $modelUsuaris->guardarUsuariOAuth($nickname, $email, $nom, $cognomFinal, $foto, $provider, $oauthId);
    
    if ($okGuardar) {
        $usuariNou = $modelUsuaris->obtenirPerNickname($nickname);
        if (!$usuariNou) {
            header('Location: ../../app/view/vista.signup.php?error=oauth_register_failed');
            exit;
        }

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

// Mostrar la vista
require __DIR__ . '/../view/vista.oauth_confirm.php';
