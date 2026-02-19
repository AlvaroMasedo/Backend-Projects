<?php
/**
 * Controlador: OAuth Callback - Google OAuth
 * 
 * Gestiona la resposta de Google OAuth. Es crida quan Google redirigeix
 * de tornada a l'aplicació després que l'usuari s'autentique amb Google.
 * 
 * Fluxes supportats:
 * 1. login: Usuari normal iniciant sessió amb Google
 * 2. signup: Usuari nou registrant-se amb Google
 * 3. vincular: Usuari existent (amb contrasenya) vinculant Google OAuth
 * 
 * @author Álvaro Masedo Pérez
 * @version 1.0
 */
declare(strict_types=1);

// === INCLUDES ===
require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../model/model.usuari.php';
require_once __DIR__ . '/../../lib/oauth_config.php';

// === INICIALITZACIÓ ===
OAuthConfig::inicialitzar();
$modelUsuaris = new ModelUsers($conn);

// === RECOLLIR PARÀMETRES ===
// Obté el codi d'autorització de Google i l'estat (CSRF protection)
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$state = $_GET['state'] ?? $_POST['state'] ?? '';

// === OBTENCIÓ DE CONTEXT ===
// El context pot ser:
// - 'login': Iniciar sessió normal
// - 'signup': Crear compte nou
// - 'vincular': Vincular a compte existent (llegit des de SESSION, sent per verificarEmailVincular.php)
$context = $_SESSION['oauth_context'] ?? 'login';

// === DEBUGGING ===
// Logs per verificar que els paràmetres arriben correctament
error_log("OAuth Callback - State rebut: " . $state);
error_log("OAuth Callback - State a la sessió: " . ($_SESSION['oauth_state'] ?? 'NO EXISTEIX'));
error_log("OAuth Callback - Session ID: " . session_id());
error_log("OAuth Callback - Context: " . $context);

// === VALIDACIÓ DE STATE (CSRF PROTECTION) ===
// State és un token aleatori per prevenir CSRF attacks
// Si els states coincideixen, significa que Google és de confiança
// Actualment en mode DEBUG (TODO: Reactivar strict validation després)
if (isset($_SESSION['oauth_state']) && $_SESSION['oauth_state'] === $state) {
    // State correcte - seguir
    error_log("OAuth Callback - State vàlid");
} else {
    // State no coincideix - AVÍS temporal
    error_log("OAuth Callback - ADVERTÈNCIA: State no coincideix (modo debug, permissiu)");
    if (!empty($state)) {
        $_SESSION['oauth_state'] = $state;
    }
}

// === INICIALITZACIÓ DE VARIABLES ===
$usuariOAuth = null;

// ============================================================================
// PROCESSAMENT DE GOOGLE OAUTH
// ============================================================================
// Aquesta secció intercanvia el codi per un token d'accés i obté les dades de l'usuari
if ($code) {
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
// PROCESSAR DADES DE USUARI OAUTH
// ============================================================================
// Si tenim dades de Google, procedim a processar-les
if ($usuariOAuth && isset($usuariOAuth['email'])) {
    // === EXTRAHIR DADES ===
    $email = $usuariOAuth['email'];
    $nom = $usuariOAuth['name'] ?? '';
    $cognom = $usuariOAuth['family_name'] ?? '';
    
    // === NETEJAR COGNOM ===
    // Si el cognom és buit o només espais, establir-lo com a cadena buida
    $cognom = trim($cognom);
    if (empty($cognom)) {
        $cognom = '';
    }
    
    // === FOTO DE PERFIL ===
    // POLÍTICA: NO usar les fotos de Google. Sempre usar predeterminada.
    // Raó: Proteger privacitat i perquè les fotos de Google són accessibles de forma pública
    // així que guardant NULL, les vistes mostraran la imatge estàndard
    $foto = null;
    
    // === INFORMACIÓ DE PROVIDER ===
    $provider = $usuariOAuth['provider']; // Exemple: 'google'
    $oauthId = $usuariOAuth['id'] ?? $usuariOAuth['sub'] ?? ''; // ID únic de Google

    // ========================================================================
    // CONTEXT: VINCULAR - Vincular compte OAuth a compte LOCAL EXISTENT
    // ========================================================================
    // Es cridada quan un usuari local amb contrasenya vol vincular Google OAuth
    // Fluxe:
    // 1. Usuari fa clic a "Vincular amb Google" en perfil
    // 2. Es mostra formulari de verificació d'email (verificarEmailVincular.php)
    // 3. L'usuari verifica el seu email rebent codi
    // 4. Es marca $_SESSION['email_verified_for_oauth'] = true
    // 5. Es redirigeix a Google OAuth amb context='vincular' en sessió
    // 6. Google retorna aquí (oauth_callback.php) amb context='vincular'
    // 7. Es valida que email_verified_for_oauth és true
    // 8. Es vincula el provider+oauth_id al compte local
    if ($context === 'vincular') {
        // === VALIDACIÓ 1: USUARI AUTENTICAT ===
        // Verifica que hi ha un usuari en sesió (no pot vincular sense estar loguejat)
        if (!isset($_SESSION['usuari']) || empty($_SESSION['usuari']['nickname'])) {
            unset($_SESSION['oauth_context']);
            header('Location: ../view/vista.login.php?error=vincular_no_session');
            exit;
        }
        
        // === VALIDACIÓ 2: EMAIL VERIFICAT ===
        // Comprova que l'usuari ha completat la verificació d'email
        // Aquesta bandera és establerta per verificarEmailVincular.php
        // després de validar el codi correct
        if (!isset($_SESSION['email_verified_for_oauth']) || $_SESSION['email_verified_for_oauth'] !== true) {
            unset($_SESSION['oauth_context']);
            unset($_SESSION['email_verified_for_oauth']);
            header('Location: ../view/vista.perfil.php?error=email_not_verified');
            exit;
        }
        
        // Obtenir la informació completa del compte actual
        $usuariActual = $modelUsuaris->obtenirPerNickname($_SESSION['usuari']['nickname']);
        
        // Verificar que l'email de Google coincideix amb l'email de la sessió
        if ($usuariActual && $usuariActual['email'] === $email) {
            // Email coincideix - procedir amb la vinculació
            $teOAuthActual = !empty($usuariActual['oauth_provider']) && !empty($usuariActual['oauth_id']);
            
            if ($teOAuthActual) {
                // Ja té OAuth vinculat
                unset($_SESSION['oauth_context']);
                unset($_SESSION['email_verified_for_oauth']);
                header('Location: ../view/vista.perfil.php?error=vincular_already_linked');
                exit;
            }
            
            // Vincular el provider OAuth a aquest compte
            $modelUsuaris->conectarOAuthAUsuari($usuariActual['nickname'], $provider, $oauthId);
            
            // Actualitzar la sessió amb la nova informació
            $_SESSION['oauth_login'] = true;
            
            // Borrar estat OAuth i banderes de verificació
            unset($_SESSION['oauth_state']);
            unset($_SESSION['oauth_pending']);
            unset($_SESSION['oauth_context']);
            unset($_SESSION['email_verified_for_oauth']);
            
            // Redirigir al perfil amb missatge d'èxit
            header('Location: ../view/vista.perfil.php?vincular_success=1');
            exit;
        } else {
            // Email no coincideix
            unset($_SESSION['oauth_context']);
            unset($_SESSION['email_verified_for_oauth']);
            header('Location: ../view/vista.perfil.php?error=vincular_email_mismatch');
            exit;
        }
    }

    // Verificar si el mail ja existeix
    $usuariExistent = $modelUsuaris->obtenirPerEmail($email);

    if ($usuariExistent) {
        // El compte ja existeix
        $teOAuth = !empty($usuariExistent['oauth_provider']) && !empty($usuariExistent['oauth_id']);
        
        if ($context === 'signup') {
            // Si ve del formulari de registre, mostrar error
            unset($_SESSION['oauth_context']);
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
            unset($_SESSION['oauth_context']);

            header('Location: ../../index.php?oauth_login=1');
            exit;
        } else if ($teOAuth && $usuariExistent['oauth_provider'] !== $provider) {
            // Compte ja existeix amb OAuth, però d'altre provider
            unset($_SESSION['oauth_context']);
            header('Location: ../view/vista.login.php?error=oauth_different_provider');
            exit;
        } else {
            // Compte ja existeix però sense OAuth (registrat normalment)
            unset($_SESSION['oauth_context']);
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

unset($_SESSION['oauth_context']);
header('Location: ../view/vista.login.php?error=oauth_invalid');
exit;
