<?php

/**
 * Controlador per a la Vinculació Local - Establir Contrasenya
 * 
 * Aquest controlador permet als usuaris OAuth (que només tenen compte a través de Google)
 * establir una contrasenya local per poder iniciar sessió sense dependre de Google.
 * 
 * Fluxe:
 * 1. Step 1: L'usuari solicita enviar un codi de verificació (els usuaris OAuth no l'utilitzen, pas directe)
 * 2. Step 2: Introdueix el codi rebut per email i estableix contrasenya nova
 * 
 * Nota: Els usuaris OAuth VAN DIRECTES al step 2, ja que van verificats per Google
 * Els usuaris locals que vinculen comptes fan verificació per email
 * 
 * @author Álvaro Masedo Pérez
 * @version 1.0
 */

declare(strict_types=1);

$earlyLogDir = __DIR__ . '/../../tmp';
if (!is_dir($earlyLogDir)) {
    @mkdir($earlyLogDir, 0777, true);
}
@file_put_contents(
    $earlyLogDir . '/smtp_vinculacio.log',
    json_encode([
        'time' => date('Y-m-d H:i:s'),
        'context' => 'vincularLocal.early_request',
        'data' => [
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'has_action' => isset($_POST['action']),
            'post_keys' => array_keys($_POST ?? []),
            'session_id_pre' => session_id(),
        ],
    ], JSON_UNESCAPED_UNICODE) . PHP_EOL,
    FILE_APPEND
);

// === INCLUDES I DEPENDÈNCIES ===
require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../model/model.usuari.php';
require_once __DIR__ . '/../../lib/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../lib/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../lib/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../../lib/oauth_config.php';

// Sessió simplificada per evitar tancaments agressius en fluxos de verificació per email
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Escriu traça de diagnòstic SMTP a un fitxer local del projecte.
 */
function logSmtpVincularLocalDebug(string $context, array $data = []): void
{
    $logDir = __DIR__ . '/../../tmp';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }

    $entry = [
        'time' => date('Y-m-d H:i:s'),
        'context' => $context,
        'data' => $data,
    ];

    @file_put_contents(
        $logDir . '/smtp_vinculacio.log',
        json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL,
        FILE_APPEND
    );
}

// === CARREGADOR DE VARIABLES D'ENTORN ===
// Carrega les credencials de Gmail des del fitxer .env
carregarEnv(__DIR__ . '/../../.env');

// === VALIDACIÓ DE SESIÓ ===
// Verifica que l'usuari està autenticat. Si no, redirigeix al login
if (!isset($_SESSION['usuari'])) {
    logSmtpVincularLocalDebug('vincularLocal.no_user_session', [
        'session_id' => session_id(),
        'cookie_phpsessid' => $_COOKIE['PHPSESSID'] ?? null,
    ]);
    header('Location: vista.login.php');
    exit;
}

// === INICIALITZACIÓ DE VARIABLES ===
$modelUsuaris = new ModelUsers($conn);
$sessionEmail = trim((string) ($_SESSION['usuari']['email'] ?? ''));
logSmtpVincularLocalDebug('vincularLocal.session', [
    'session_email' => $sessionEmail,
    'session_nickname' => $_SESSION['usuari']['nickname'] ?? null,
]);

if ($sessionEmail === '' || !filter_var($sessionEmail, FILTER_VALIDATE_EMAIL)) {
    logSmtpVincularLocalDebug('vincularLocal.invalid_session_email', ['session_email' => $sessionEmail]);
    header('Location: ../view/vista.vincularLocal.php?step=1&error=email_error');
    exit;
}

$usuari = $modelUsuaris->obtenirPerEmail($sessionEmail);
if (!$usuari) {
    logSmtpVincularLocalDebug('vincularLocal.user_not_found', ['session_email' => $sessionEmail]);
    header('Location: ../view/vista.vincularLocal.php?step=1&error=email_error');
    exit;
}

$nickname = $usuari['nickname'];

// ==============================================================================
// STEP 1: GENERAR I ENVIAR CODI DE VERIFICACIÓ
// ==============================================================================
// Aquesta secció s'executa quan l'usuari fa clic a "ENVIAR CODI" (primer pas)
// Els usuaris OAuth solen saltar aquest pas i anar directe al step 2
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    // === GENERAR CODI SEGUR ===
    // Crea un codi de 6 dígits aleatori
    $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // === ESTABLIR EXPIRACIÓ ===
    // El codi expira en 15 minuts (900 segons)
    $expires = date('Y-m-d H:i:s', time() + (15 * 60));

    // === GUARDAR A LA BASE DE DADES ===
    // Emmagatzema el codi en les columnes verification_code i verification_expires
    $sql = "UPDATE usuaris SET verification_code = :code, verification_expires = :expires WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':code' => $code, ':expires' => $expires, ':email' => $sessionEmail]);

    // === ENVIAR EMAIL AMB EL CODI ===
    try {
        $mail = new PHPMailer(true);

        // Mateixa configuració robusta del fluxe de recuperació de contrasenya
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // === CONFIGURACIÓ SMTP ===
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        // Obtenir credencials de les variables d'entorn (del .env)
        // Si la contrasenya d'app ve amb espais (format habitual de Google), els eliminem.
        $emailAddress = trim((string) ($_ENV['GOOGLE_OAUTH_EMAIL'] ?? ''));
        $emailPassword = str_replace(' ', '', trim((string) ($_ENV['GOOGLE_OAUTH_PASSWORD'] ?? '')));

        // Validar que s'han configurat les credencials
        if ($emailAddress === '' || $emailPassword === '') {
            throw new Exception("Credencials de email no configurades. Afegeix GOOGLE_OAUTH_EMAIL i GOOGLE_OAUTH_PASSWORD al fitxer .env.");
        }

        // === AUTENTICACIÓ SMTP ===
        $mail->Username = $emailAddress;
        $mail->Password = $emailPassword;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587; // Port per a TLS
        $mail->SMTPDebug = 0; // 0 = errors només
        $mail->Debugoutput = 'error_log'; // Enviar debug al log de PHP

        // === COMPOSICIÓ DEL MISSATGE ===
        // Important: amb Gmail SMTP el remitent ha de ser el compte autenticat (o un alias verificat).
        $mail->setFrom($emailAddress, 'F1 Articles');
        $recipientEmail = trim((string) ($usuari['email'] ?? $sessionEmail));
        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email de destinatari no vàlid per a verificació.');
        }
        logSmtpVincularLocalDebug('vincularLocal.send_attempt', [
            'recipient' => $recipientEmail,
            'smtp_user' => $emailAddress,
            'code' => $code,
        ]);
        $mail->addAddress($recipientEmail); // Envia al email registrat de l'usuari
        $mail->isHTML(true);
        $mail->Subject = 'Codi de verificació - Vincular Compte Local';

        // Crea el cos del email amb el codi destacat i format millorat
        $mail->Body = "<h2 style='color: #D41616; font-family: Arial, sans-serif;'>Vincular Compte Local</h2>
                       <p style='font-family: Arial, sans-serif;'>Per establir una contrasenya local per a la teva compte, necessitem verificar-te.</p>
                       <p style='font-family: Arial, sans-serif;'>El teu codi:</p>
                       <p style='font-size: 32px; font-weight: bold; color: #D41616; font-family: Arial, sans-serif; letter-spacing: 5px;'>" . htmlspecialchars($code) . "</p>
                       <p style='color: #666; font-family: Arial, sans-serif;'><em>Expira en 15 minuts.</em></p>";

        // Versió text simple
        $mail->AltBody = "Codi de verificació: " . $code . "\nExpira en 15 minuts.";

        // === ENVIÓ ===
        $mail->send();
        logSmtpVincularLocalDebug('vincularLocal.send_success', [
            'recipient' => $recipientEmail,
        ]);

        // Si l'email s'envia correctament, passa al step 2
        header('Location: ../view/vista.vincularLocal.php?step=2');
    } catch (\Throwable $e) {
        // Si hi ha error, registra'l amb més detalls
        logSmtpVincularLocalDebug('vincularLocal.send_error', [
            'recipient' => $recipientEmail ?? null,
            'smtp_user' => $emailAddress ?? null,
            'exception' => $e->getMessage(),
            'phpmailer_error' => isset($mail) ? $mail->ErrorInfo : null,
        ]);
        error_log("===== EMAIL ERROR EN VINCULARLOCAL =====");
        error_log("Recipient Email: " . ($recipientEmail ?? 'NO_DEFINIT'));
        error_log("SMTP Username: " . ($emailAddress ?? 'NO_CONFIGURAT'));
        error_log("Exception: " . $e->getMessage());
        if (isset($mail)) {
            error_log("PHPMailer ErrorInfo: " . $mail->ErrorInfo);
        }
        error_log("======================================");
        header('Location: ../view/vista.vincularLocal.php?step=1&error=email_error');
    }
    exit;
}

// ==============================================================================
// STEP 2: VERIFICAR CODI I ESTABLIR CONTRASENYA
// ==============================================================================
// Aquesta secció s'executa quan l'usuari submíteix el codi i contrasenya
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'verify') {
    // === RECOLLIR DADES DEL FORMULARI ===
    $code = $_POST['code'] ?? '';
    $contrasenya = $_POST['contrasenya'] ?? '';
    $repContrasenya = $_POST['repContrasenya'] ?? '';

    // === VALIDACIÓ 1: FORMAT DEL CODI ===
    // Comprova que el codi és exactament 6 dígits numèrics
    if (!preg_match('/^[0-9]{6}$/', $code)) {
        header('Location: ../view/vista.vincularLocal.php?step=2&error=invalid_code');
        exit;
    }

    // === VALIDACIÓ 2: OBTENIR CODI DE LA BD ===
    // Busca el codi guardat per a aquest usuari
    $sql = "SELECT verification_code, verification_expires FROM usuaris WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':email' => $sessionEmail]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // === VALIDACIÓ 3: COINCIDÈNCIA DE CODI ===
    // Verifica que el codi introduït coincideix amb el guardat
    if (!$row || $row['verification_code'] !== $code) {
        header('Location: ../view/vista.vincularLocal.php?step=2&error=invalid_code');
        exit;
    }

    // === VALIDACIÓ 4: EXPIRACIÓ ===
    // Comprova que el codi no ha expirat
    if (strtotime($row['verification_expires']) < time()) {
        header('Location: ../view/vista.vincularLocal.php?step=2&error=expired');
        exit;
    }

    // === VALIDACIÓ 5: COINCIDÈNCIA DE CONTRASENYES ===
    // Verifica que ambdós camps de contrasenya són idèntics
    if ($contrasenya !== $repContrasenya) {
        header('Location: ../view/vista.vincularLocal.php?step=2&error=mismatch');
        exit;
    }

    // === VALIDACIÓ 6: FORÇA DE LA CONTRASENYA ===
    // Regex que valida:
    // - Almenys una lletra minúscula: (?=.*[a-z])
    // - Almenys una lletra majúscula: (?=.*[A-Z])
    // - Almenys un dígit: (?=.*\d)
    // - Almenys un caràcter especial: (?=.*[@$!%*?&])
    // - Longitud entre 12 i 20 caràcters: [a-zA-Z\d@$!%*?&]{12,20}
    // === VALIDACIÓ 6: FORÇA DE LA CONTRASENYA ===
    // Regex que valida:
    // - Almenys una lletra minúscula: (?=.*[a-z])
    // - Almenys una lletra majúscula: (?=.*[A-Z])
    // - Almenys un dígit: (?=.*\d)
    // - Almenys un caràcter especial: (?=.*[@$!%*?&])
    // - Longitud entre 12 i 20 caràcters: [a-zA-Z\d@$!%*?&]{12,20}
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[a-zA-Z\d@$!%*?&]{12,20}$/', $contrasenya)) {
        header('Location: ../view/vista.vincularLocal.php?step=2&error=invalid_password');
        exit;
    }

    // === GUARDAR CONTRASENYA ENCRIPTADA ===
    // Encripta la contrasenya amb l'algoritme bcrypt de PHP
    // PASSWORD_BCRYPT: Algoritme de hash segur que inclou salt automàticament
    // Una contrasenya encriptada no pot desencriptar-se, només comparar-se
    $hashed = password_hash($contrasenya, PASSWORD_BCRYPT);

    // === ACTUALITZAR LA BASE DE DADES ===
    // Guarda la contrasenya encriptada i neteja els codis de verificació
    // ja que ja no són necessaris
    $sql = "UPDATE usuaris SET contrasenya = :contrasenya, verification_code = NULL, verification_expires = NULL WHERE nickname = :nickname";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':contrasenya' => $hashed, ':nickname' => $nickname]);

    // === REDIRIGIR A ÈXIT ===
    // Mostra el missatge de èxit a la vista
    header('Location: ../view/vista.vincularLocal.php?step=2&success=1');
    exit;
}

// === REDIRECCCIÓ PER DEFECTE ===
// Si es crida sense POST o amb paràmetres incorrectes, retorna a la vista principal
header('Location: ../view/vista.vincularLocal.php');
