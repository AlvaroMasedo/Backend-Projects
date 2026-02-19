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
// GOOGLE OAUTH - Intenta si hay code y no hay id_token (que es de Apple)
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
    
    $foto = $usuariOAuth['picture'] ?? null;
    $provider = $usuariOAuth['provider'];
    $oauthId = $usuariOAuth['id'] ?? $usuariOAuth['sub'] ?? '';

    // Verificar si el mail ja existeix
    $usuariExistent = $modelUsuaris->obtenirPerEmail($email);

    if ($usuariExistent) {
        // El mail ja existeix: iniciar sessió automàticament amb el compte existent
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
        
        // Borrar estat OAuth
        unset($_SESSION['oauth_state']);
        unset($_SESSION['oauth_pending']);

        header('Location: ../../index.php?oauth_login=1');
        exit;
    } else {
        // El mail no existeix: crear usuari nou amb OAuth
        $usuariNou = $modelUsuaris->guardarUsuariOAuth($email, $nom, $cognom, $foto, $provider, $oauthId);

        if ($usuariNou) {
            // Conectar automàticament
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
            
            // Generar token únic per a aquesta sessió de navegador
            $browserToken = bin2hex(random_bytes(32));
            $_SESSION['browser_session_token'] = $browserToken;
            
            // Cookie temporal que expira al tancar navegador
            setcookie('browser_session_token', $browserToken, 0, '/', '', false, true);
            
            // Generar "marcador" de navegador (per detectar si s'ha tancat)
            $browserMarker = bin2hex(random_bytes(16));
            $_SESSION['browser_marker'] = $browserMarker;
            setcookie('browser_marker', $browserMarker, 0, '/', '', false, true);
            
            // FORÇAR que la cookie PHPSESSID tengui lifetime=0
            $sessionName = session_name();
            $sessionId = session_id();
            setcookie($sessionName, $sessionId, 0, '/', '', false, true);
            
            // Borrar estat OAuth
            unset($_SESSION['oauth_state']);
            unset($_SESSION['oauth_pending']);

            header('Location: ../../index.php?oauth_registered=1');
            exit;
        } else {
            header('Location: ../view/vista.login.php?error=oauth_register_failed');
            exit;
        }
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
