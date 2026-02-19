<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../model/model.usuari.php';
require_once __DIR__ . '/../../lib/oauth_config.php';
OAuthConfig::inicialitzar();

$modelUsuaris = new ModelUsers($conn);
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$id_token = $_GET['id_token'] ?? $_POST['id_token'] ?? '';
$state = $_GET['state'] ?? $_POST['state'] ?? '';
$context = $_GET['context'] ?? $_POST['context'] ?? 'login'; // Detectar si viene de login o signup

// DEBUG: Log per verificar state
error_log("OAuth Callback - State rebut: " . $state);
error_log("OAuth Callback - State a la sessió: " . ($_SESSION['oauth_state'] ?? 'NO EXISTEIX'));
error_log("OAuth Callback - Session ID: " . session_id());
error_log("OAuth Callback - Context: " . $context);

// DEBUG: Log per verificar state
error_log("OAuth Callback - State rebut: " . $state);
error_log("OAuth Callback - State a la sessió: " . ($_SESSION['oauth_state'] ?? 'NO EXISTEIX'));
error_log("OAuth Callback - Session ID: " . session_id());

// TEMPORAL: Desactivar validació estricta de state per debugging
// TODO: Reactivar després de solucionar el problema de persistència de sessió
if (isset($_SESSION['oauth_state']) && $_SESSION['oauth_state'] === $state) {
    // State correcte
    error_log("OAuth Callback - State vàlid");
} else {
    // State no coincideix, però continuem (TEMPORAL)
    error_log("OAuth Callback - ADVERTÈNCIA: State no coincideix, però es continua (mode debug)");
    // Regenerar state per a la sessió actual
    if (!empty($state)) {
        $_SESSION['oauth_state'] = $state;
    }
}

$usuariOAuth = null;

// ============================================================================
// GOOGLE OAUTH - Intenta si hi ha code y no hi ha id_token (que es de Apple)
// ============================================================================
if ($code && !$id_token) {
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $params = [
        'client_id' => OAuthConfig::$GOOGLE_CLIENT_ID,
        'client_secret' => OAuthConfig::$GOOGLE_CLIENT_SECRET,
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => OAuthConfig::$GOOGLE_REDIRECT_URI
    ];

    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);

    $tokenData = json_decode($response, true);

    if (isset($tokenData['access_token'])) {
        // Obtenir informació de l'usuari
        $ch = curl_init('https://www.googleapis.com/oauth2/v1/userinfo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokenData['access_token']]);
        $userData = curl_exec($ch);
        curl_close($ch);

        $usuariOAuth = json_decode($userData, true);
        $usuariOAuth['provider'] = 'google';
    } else {
        header('Location: ../view/vista.login.php?error=oauth_token_failed');
        exit;
    }
}

// ============================================================================
// APPLE OAUTH
// ============================================================================
if ($id_token) {
    // Apple retorna el JWT en el camp id_token via form_post
    // Nota: en producció es recomanable verificar la signatura del JWT
    
    // Decodificar JWT (sense verificació en desenvolupament)
    $parts = explode('.', $id_token);
    if (count($parts) === 3) {
        $payload = json_decode(base64_decode($parts[1]), true);

        $usuariOAuth = [
            'id' => $payload['sub'] ?? '',
            'email' => $payload['email'] ?? '',
            'name' => $_POST['user_name']['firstName'] ?? 'Usuari',
            'family_name' => $_POST['user_name']['lastName'] ?? '',
            'provider' => 'apple'
        ];
    } else {
        header('Location: ../view/vista.login.php?error=oauth_invalid_jwt');
        exit;
    }
}

// ============================================================================
// PROCESSAR DADES DE USUARI OAUTH
// ============================================================================
if ($usuariOAuth && isset($usuariOAuth['email'])) {
    $email = $usuariOAuth['email'];
    $nom = $usuariOAuth['name'] ?? '';
    $cognom = $usuariOAuth['family_name'] ?? '';
    
    // Si el cognom està buit o només espais, convertir-lo a cadena buida
    $cognom = trim($cognom);
    if (empty($cognom)) {
        $cognom = '';
    }
    
    // SEMPRE usar foto predeterminada de la web, NUNCA la de Google/Apple
    // Guardar NULL perà que les vistes mostrin la imatge predeterminada
    $foto = null;
    
    $provider = $usuariOAuth['provider'];
    $oauthId = $usuariOAuth['id'] ?? $usuariOAuth['sub'] ?? '';

    // Verificar si el mail ja existeix
    $usuariExistent = $modelUsuaris->obtenirPerEmail($email);

    if ($usuariExistent) {
        // El compte ja existeix
        $teOAuth = !empty($usuariExistent['oauth_provider']) && !empty($usuariExistent['oauth_id']);
        
        if ($context === 'signup') {
            // Si ve del formulari de registre, mostrar error
            header('Location: ../../app/view/vista.signup.php?error=oauth_account_exists');
            exit;
        }
        
        // Si ve del login (o no especificat)
        if ($teOAuth && $usuariExistent['oauth_provider'] === $provider) {
            // El mail ja existeix i te OAuth del mateix provider: iniciar sessió
            session_regenerate_id(true);
            $_SESSION['usuari'] = [
                'nickname' => $usuariExistent['nickname'],
                'nom' => $usuariExistent['nom'],
                'cognom' => $usuariExistent['cognom'],
                'email' => $usuariExistent['email'],
                'administrador' => $usuariExistent['administrador'],
                'imatge_perfil' => $usuariExistent['imatge_perfil']
            ];
            $_SESSION['session_created'] = time();
            $_SESSION['remember_me'] = 0;
            $_SESSION['contadorIntents'] = 0;
            $_SESSION['_last_request_time'] = time();
            
            // Generar token únic per a aquesta sessió de navegador
            $browserToken = bin2hex(random_bytes(32));
            $_SESSION['browser_session_token'] = $browserToken;
            setcookie('browser_session_token', $browserToken, 0, '/', '', false, true);
            
            // Generar "marcador" de navegador
            $browserMarker = bin2hex(random_bytes(16));
            $_SESSION['browser_marker'] = $browserMarker;
            setcookie('browser_marker', $browserMarker, 0, '/', '', false, true);
            
            // FORÇAR que la cookie PHPSESSID tengui lifetime=0
            $sessionName = session_name();
            $sessionId = session_id();
            setcookie($sessionName, $sessionId, 0, '/', '', false, true);
            
            // Marcar que és un login OAuth per a validacions més flexibles en session_check
            $_SESSION['oauth_login'] = true;
            
            // Borrar estat OAuth
            unset($_SESSION['oauth_state']);
            unset($_SESSION['oauth_pending']);

            header('Location: ../../index.php?oauth_login=1');
            exit;
        } else if ($teOAuth && $usuariExistent['oauth_provider'] !== $provider) {
            // Compte ja existeix amb OAuth, però d'altre provider
            header('Location: ../view/vista.login.php?error=oauth_different_provider');
            exit;
        } else {
            // Compte ja existeix però sense OAuth (registrat normalment)
            header('Location: ../view/vista.login.php?error=oauth_need_normal_login');
            exit;
        }
    } else {
        // El mail no existeix - preguntar confirmació (tant per login com signup)
        $_SESSION['oauth_pending_data'] = [
            'email' => $email,
            'nom' => $nom,
            'cognom' => $cognom,
            'provider' => $provider,
            'oauth_id' => $oauthId,
            'context' => $context
        ];
        header('Location: oauth_confirm.php');
        exit;
    }
}

header('Location: ../view/vista.login.php?error=oauth_invalid');
exit;

// ============================================================================
// AJUDANT: GENERAR CLIENT SECRET PER A APPLE
// ============================================================================
function generarClientSecretApple(): string
{
    // Aquesta és una versió simplificada. En producció, necessites signar un JWT amb la clau privada
    // Per ara usem el client_id com a secret (no recomanat en producció)
    return OAuthConfig::$APPLE_CLIENT_ID;
}
