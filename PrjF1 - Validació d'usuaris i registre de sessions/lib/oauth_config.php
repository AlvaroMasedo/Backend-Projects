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
 * Configuració OAuth per a Google
 * 
 * GOOGLE:
 * 1. Anar a https://console.cloud.google.com/
 * 2. Crear projecte i habilitar "Google+ API"
 * 3. Crear credencials OAuth 2.0 (pantalla de consentiment)
 * 4. Authorized redirect URIs: http://localhost/ruta/app/controller/oauth_callback.php
 * 
 * NOTA: Les credencials es carreguen des de l'arxiu .env
 */

class OAuthConfig
{
    // GOOGLE - Carregar des de variables d'entorn
    public static string $GOOGLE_CLIENT_ID = '';
    public static string $GOOGLE_CLIENT_SECRET = '';
    public static string $GOOGLE_REDIRECT_URI = '';

    /**
     * Inicialitzar configuració OAuth des de variables d'entorn
     */
    public static function inicialitzar(): void
    {
        // Carregar credencials des de variables d'entorn
        self::$GOOGLE_CLIENT_ID = $_ENV['GOOGLE_CLIENT_ID'] ?? 'YOUR_GOOGLE_CLIENT_ID_HERE';
        self::$GOOGLE_CLIENT_SECRET = $_ENV['GOOGLE_CLIENT_SECRET'] ?? 'YOUR_GOOGLE_CLIENT_SECRET_HERE';
        
        // Configurar URI de redirecció dinàmicament
        self::$GOOGLE_REDIRECT_URI = BASE_URL . '/app/controller/oauth_callback.php';
    }

    /**
     * Obtenir URL d'autenticació de Google
     * @param string $state Token de seguretat per evitar CSRF
     * @param string $context 'login' o 'signup' per indicar origen
     * @return string URL de redirecció a Google
     */
    public static function obtenirUrlAuthGoogle(string $state = '', string $context = 'login'): string
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
            'access_type' => 'offline',
            'context' => $context
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }
}
