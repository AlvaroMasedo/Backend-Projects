<?php
declare(strict_types=1);
//Álvaro Masedo Pérez
// Clau pública (site key) i privada (secret).
if (!defined('RECAPTCHA_SITE_KEY')) {
    define('RECAPTCHA_SITE_KEY', '6LfP6hEsAAAAAF8UvGTJukRxIklH3jJg1BmfcCLX');
}
if (!defined('RECAPTCHA_SECRET_KEY')) {
    define('RECAPTCHA_SECRET_KEY', '6LfP6hEsAAAAAE3pwc5I7cu_1OM3wW_PPT0DXyra');
}

/** Imprimeix l'script client necessari per a reCAPTCHA */
function imprimir_recaptcha_script(): void {
    echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>' . PHP_EOL;
}

/** Mostra el widget si cal  */
function mostrar_recaptcha_si_es_necessita(int $contadorIntents): void {
    if ($contadorIntents >= 3) {
        echo '<div class="g-recaptcha" data-sitekey="' . RECAPTCHA_SITE_KEY . '"></div>' . PHP_EOL;
    }
}

/** Verifica la resposta de reCAPTCHA amb el servidor de Google. Retorna true si OK. */
function verificar_recaptcha(string $response): bool {
    if (empty($response)) {
        return false;
    }

    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $response
    ];

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded\r\n',
            'content' => http_build_query($data),
            'timeout' => 5
        ]
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    $json = $result ? json_decode($result, true) : null;

    return !empty($json) && !empty($json['success']);
}

?>
