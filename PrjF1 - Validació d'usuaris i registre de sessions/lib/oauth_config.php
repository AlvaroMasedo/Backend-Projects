<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/basepath.php';

/**
 * Carregar variables d'entorn des de .env
 */
function carregarEnv(string $path): void {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentaris
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parsejar línia KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Establir variable d'entorn si no existeix
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// Carregar .env des de la arrel del projecte
carregarEnv(__DIR__ . '/../.env');

/**
 * Configuració OAuth per a Google i Apple
 * 
 * GOOGLE:
 * 1. Anar a https://console.cloud.google.com/
 * 2. Crear projecte i habilitar "Google+ API"
 * 3. Crear credencials OAuth 2.0 (pantalla de consentiment)
 * 4. Authorized redirect URIs: http://localhost/ruta/app/controller/oauth_callback.php
 * 
 * APPLE:
 * 1. Anar a https://developer.apple.com/account/
 * 2. Crear App ID i Services ID
 * 3. Configurar Sign in with Apple
 * 
 * NOTA: Les credencials es carreguen des de l'arxiu .env
 */

class OAuthConfig
{
    // GOOGLE - Carregar des de variables d'entorn
    public static string $GOOGLE_CLIENT_ID = '';
    public static string $GOOGLE_CLIENT_SECRET = '';
    public static string $GOOGLE_REDIRECT_URI = '';
    
    // APPLE - Carregar des de variables d'entorn
    public static string $APPLE_CLIENT_ID = '';
    public static string $APPLE_TEAM_ID = '';
    public static string $APPLE_KEY_ID = '';
    public static string $APPLE_PRIVATE_KEY_PATH = __DIR__ . '/apple_private_key.p8';
    public static string $APPLE_REDIRECT_URI = '';

    /**
     * Inicialitzar configuració OAuth des de variables d'entorn
     */
    public static function inicialitzar(): void
    {
        // Carregar credencials des de variables d'entorn
        self::$GOOGLE_CLIENT_ID = $_ENV['GOOGLE_CLIENT_ID'] ?? 'YOUR_GOOGLE_CLIENT_ID_HERE';
        self::$GOOGLE_CLIENT_SECRET = $_ENV['GOOGLE_CLIENT_SECRET'] ?? 'YOUR_GOOGLE_CLIENT_SECRET_HERE';
        self::$APPLE_CLIENT_ID = $_ENV['APPLE_CLIENT_ID'] ?? 'YOUR_APPLE_CLIENT_ID_HERE';
        self::$APPLE_TEAM_ID = $_ENV['APPLE_TEAM_ID'] ?? 'YOUR_APPLE_TEAM_ID_HERE';
        self::$APPLE_KEY_ID = $_ENV['APPLE_KEY_ID'] ?? 'YOUR_APPLE_KEY_ID_HERE';
        
        // Configurar URIs de redirecció dinàmicament
        self::$GOOGLE_REDIRECT_URI = BASE_URL . '/app/controller/oauth_callback.php';
        self::$APPLE_REDIRECT_URI = BASE_URL . '/app/controller/oauth_callback.php';
    }

    /**
     * Obtenir URL d'autenticació de Google
     * @param string $state Token de seguretat per evitar CSRF
     * @return string URL de redirecció a Google
     */
    public static function obtenirUrlAuthGoogle(string $state = ''): string
    {
        // Assegurar-se que la sessió està iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!$state) {
            $state = bin2hex(random_bytes(16));
            $_SESSION['oauth_state'] = $state;
            error_log("OAuth - State generat: " . $state . " | Session ID: " . session_id());
        }

        $params = [
            'client_id' => self::$GOOGLE_CLIENT_ID,
            'redirect_uri' => self::$GOOGLE_REDIRECT_URI,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'offline'
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    /**
     * Obtenir URL d'autenticació d'Apple
     * @param string $state Token de seguretat per evitar CSRF
     * @return string URL de redirecció a Apple
     */ 
    public static function obtenirUrlAuthApple(string $state = ''): string
    {
        if (!$state) {
            $state = bin2hex(random_bytes(16));
            $_SESSION['oauth_state'] = $state;
        }

        $params = [
            'client_id' => self::$APPLE_CLIENT_ID,
            'redirect_uri' => self::$APPLE_REDIRECT_URI,
            'response_type' => 'code',
            'response_mode' => 'form_post',
            'scope' => 'openid email name',
            'state' => $state
        ];

        return 'https://appleid.apple.com/auth/authorize?' . http_build_query($params);
    }
}
