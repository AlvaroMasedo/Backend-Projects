<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../model/model.usuari.php';
require_once __DIR__ . '/../../includes/session_check.php';

function carregarPHPMailer(): ?string
{
    $candidats = [
        __DIR__ . '/../../lib/phpmailer/src/PHPMailer.php',
        __DIR__ . '/../../lib/PHPMailer/src/PHPMailer.php',
        __DIR__ . '/../../lib/phpmailer/PHPMailer.php',
        __DIR__ . '/../../lib/PHPMailer/PHPMailer.php'
    ];

    $loaded = false;
    foreach ($candidats as $path) {
        if (is_file($path)) {
            require_once $path;
            $loaded = true;
            break;
        }
    }

    if ($loaded) {
        $smtpPaths = [
            __DIR__ . '/../../lib/phpmailer/src/SMTP.php',
            __DIR__ . '/../../lib/PHPMailer/src/SMTP.php'
        ];
        $exceptionPaths = [
            __DIR__ . '/../../lib/phpmailer/src/Exception.php',
            __DIR__ . '/../../lib/PHPMailer/src/Exception.php'
        ];

        foreach ($smtpPaths as $path) {
            if (is_file($path)) {
                require_once $path;
                break;
            }
        }
        foreach ($exceptionPaths as $path) {
            if (is_file($path)) {
                require_once $path;
                break;
            }
        }
    }

    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        return 'PHPMailer\\PHPMailer\\PHPMailer';
    }
    if (class_exists('PHPMailer')) {
        return 'PHPMailer';
    }

    return null;
}

$action = $_GET['action'] ?? '';

$mailerClass = carregarPHPMailer();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../model/model.usuari.php';
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../lib/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../lib/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../../lib/phpmailer/src/Exception.php';

$action = $_GET['action'] ?? '';

$email = '';
$errorEmail = '';
$enviatMissatge = '';
$errorContrasenya = '';
$errorRepContrasenya = '';
$errorToken = '';
$token = '';
$tokenValido = false;

function construirEnllacRecuperacio(string $token): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    return $scheme . '://' . $host . $basePath . '/recuperarContrasenya.php?action=resetForm&token=' . urlencode($token);
}

if ($action === 'recuperarContrasenya') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');

        if ($email === '') {
            $errorEmail = '<p class="error">EL CAMP EMAIL ES OBLIGATORI.</p>';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorEmail = '<p class="error">EL FORMAT DE L\'EMAIL NO ES VALID.</p>';
        } else {
            $modelUsers = new ModelUsers($conn);

            if (!$modelUsers->existeixEmail($email)) {
                $errorEmail = '<p class="error">NO HI HA CAP COMPTA REGISTRADA AMB AQUEST EMAIL.</p>';
            } else {
                $tokenInfo = $modelUsers->guardarTokenRecuperacio($email, 30);
                $token = $tokenInfo['token'];
                $expiry = $tokenInfo['expiry'];
                $resetLink = construirEnllacRecuperacio($token);

                if ($mailerClass === null) {
                    $errorEmail = '<p class="error">NO S\'HA POGUT CARREGAR LA LLIBRERIA PHPMailer.</p>';
                    require __DIR__ . '/../view/vista.recuperarContrasenya.php';
                    exit;
                }

                $mail = new $mailerClass(true);
                $mail = new PHPMailer(true);

                try {
                    $mail->SMTPOptions = [
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        ]
                    ];

                    $mail->SMTPDebug = 0;
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->CharSet = 'UTF-8';
                    $mail->Encoding = 'base64';

                    $mail->Username = 'a.masedo@sapalomera.cat';
                    $mail->Password = 'febx klgw ptfw lfsb';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('a.masedo@sapalomera.cat', 'F1 Articles');
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = 'Recuperacio de contrasenya';

                    $expiraText = date('d/m/Y H:i', strtotime($expiry));
                    $buttonStyle = 'display:inline-block;padding:12px 20px;background:#D41616;color:#ffffff;text-decoration:none;border-radius:20px;font-weight:800;';
                    $body = '';
                    $body .= '<p>Has demanat recuperar la contrasenya.</p>';
                    $body .= '<p>Fes clic al botó per establir una nova contrasenya:</p>';
                    $body .= '<p><a style="' . $buttonStyle . '" href="' . htmlspecialchars($resetLink) . '">Restablir contrasenya</a></p>';
                    $body .= '<p>Si el botó no funciona, copia i enganxa aquest enllaç:</p>';
                    $body .= '<p>' . htmlspecialchars($resetLink) . '</p>';
                    $body .= '<p>Validesa fins: ' . $expiraText . '</p>';
                    $body .= '<p>Si no ho has demanat, ignora aquest correu.</p>';

                    $mail->Body = $body;
                    $mail->AltBody = "Enllac: {$resetLink}\nValidesa fins: {$expiraText}";

                    $mail->send();

                    $enviatMissatge = '<p class="success">CORREU ENVIAT CORRECTAMENT.</p>';
                } catch (Exception $e) {
                    $errorEmail = '<p class="error">ERROR EN ENVIAR EL CORREU.</p>';
                }
            }
        }
    }

    require __DIR__ . '/../view/vista.recuperarContrasenya.php';
    exit;
}

if ($action === 'resetForm') {
    $token = trim($_GET['token'] ?? '');
    $modelUsers = new ModelUsers($conn);

    if ($token === '') {
        $errorToken = '<p class="error">TOKEN INVALID O INEXISTENT.</p>';
        $tokenValido = false;
    } else {
        $usuari = $modelUsers->obtenirUsuariPerTokenRecuperacio($token);
        if ($usuari === null) {
            $errorToken = '<p class="error">TOKEN EXPIRAT O INVALID.</p>';
            $tokenValido = false;
        } else {
            $tokenValido = true;
        }
    }

    require __DIR__ . '/../view/vista.resetContrasenya.php';
    exit;
}

if ($action === 'actualitzarContrasenya') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = trim($_POST['token'] ?? '');
        $novaContrasenya = trim($_POST['nova_contrasenya'] ?? '');
        $repContrasenya = trim($_POST['rep_contrasenya'] ?? '');

        $modelUsers = new ModelUsers($conn);
        $usuari = ($token === '') ? null : $modelUsers->obtenirUsuariPerTokenRecuperacio($token);

        if ($usuari === null) {
            $errorToken = '<p class="error">TOKEN EXPIRAT O INVALID.</p>';
            $tokenValido = false;
        } elseif ($novaContrasenya === '' || $repContrasenya === '') {
            $errorContrasenya = '<p class="error">TOTS ELS CAMPS AMB * SON OBLIGATORIS.</p>';
            $tokenValido = true;
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])([A-Za-z\d$@$!%*?&]|[^ ]){12,20}$/u', $novaContrasenya)) {
            $errorContrasenya = '<p class="error">LA NOVA CONTRASENYA NO COMPLEIX ELS REQUISITS.</p>';
            $tokenValido = true;
        } elseif ($novaContrasenya !== $repContrasenya) {
            $errorRepContrasenya = '<p class="error">LES CONTRASENYES NO COINCIDEIXEN.</p>';
            $tokenValido = true;
        } else {
            $ok = $modelUsers->actualitzarContrasenya($usuari['nickname'], hash('sha256', $novaContrasenya));
            if ($ok) {
                $modelUsers->netejarTokenRecuperacio($usuari['email']);
                $enviatMissatge = '<p class="success">CONTRASENYA ACTUALITZADA CORRECTAMENT.</p>';
                $tokenValido = false;
            } else {
                $errorContrasenya = '<p class="error">ERROR EN ACTUALITZAR LA CONTRASENYA.</p>';
                $tokenValido = true;
            }
        }
    }

    require __DIR__ . '/../view/vista.resetContrasenya.php';
    exit;
}

header('Location: ../view/vista.recuperarContrasenya.php');
exit;
