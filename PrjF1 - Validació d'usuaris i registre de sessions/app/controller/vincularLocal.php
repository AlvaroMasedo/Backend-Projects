<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../model/model.usuari.php';
require_once __DIR__ . '/../../lib/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../lib/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../lib/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

if (!isset($_SESSION['usuari'])) {
    header('Location: vista.login.php');
    exit;
}

$modelUsuaris = new ModelUsers($conn);
$nickname = $_SESSION['usuari']['nickname'];
$usuari = $modelUsuaris->obtenirPerNickname($nickname);

// STEP 1: Send code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires = date('Y-m-d H:i:s', time() + (15 * 60));
    
    // Guardar código en BD
    $sql = "UPDATE usuaris SET verification_code = :code, verification_expires = :expires WHERE nickname = :nickname";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':code' => $code, ':expires' => $expires, ':nickname' => $nickname]);
    
    // Enviar email
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = getenv('GOOGLE_OAUTH_EMAIL') ?: 'test@gmail.com';
        $mail->Password = getenv('GOOGLE_OAUTH_PASSWORD') ?: 'test';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        
        $mail->setFrom('noreply@f1articles.com', 'F1 Articles');
        $mail->addAddress($usuari['email'], $usuari['nom']);
        $mail->isHTML(true);
        $mail->Subject = 'Codi de verificació - Vincular Compte';
        $mail->Body = "<h2>Vincular Compte Local</h2><p>El teu codi: <strong style='font-size:24px;'>" . $code . "</strong></p><p>Expira en 15 minuts.</p>";
        $mail->send();
        
        header('Location: vista.vincularLocal.php?step=2');
    } catch (Exception $e) {
        error_log("Email error: " . $e->getMessage());
        header('Location: vista.vincularLocal.php?step=1&error=email_error');
    }
    exit;
}

// STEP 2: Verify code and save password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'verify') {
    $code = $_POST['code'] ?? '';
    $contrasenya = $_POST['contrasenya'] ?? '';
    $repContrasenya = $_POST['repContrasenya'] ?? '';
    
    // Validar código
    if (!preg_match('/^[0-9]{6}$/', $code)) {
        header('Location: vista.vincularLocal.php?step=2&error=invalid_code');
        exit;
    }
    
    // Obtener código en BD
    $sql = "SELECT verification_code, verification_expires FROM usuaris WHERE nickname = :nickname";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':nickname' => $nickname]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Validar
    if (!$row || $row['verification_code'] !== $code) {
        header('Location: vista.vincularLocal.php?step=2&error=invalid_code');
        exit;
    }
    
    if (strtotime($row['verification_expires']) < time()) {
        header('Location: vista.vincularLocal.php?step=2&error=expired');
        exit;
    }
    
    // Validar contraseñas
    if ($contrasenya !== $repContrasenya) {
        header('Location: vista.vincularLocal.php?step=2&error=mismatch');
        exit;
    }
    
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[a-zA-Z\d@$!%*?&]{12,20}$/', $contrasenya)) {
        header('Location: vista.vincularLocal.php?step=2&error=invalid_password');
        exit;
    }
    
    // Guardar contraseña
    $hashed = password_hash($contrasenya, PASSWORD_BCRYPT);
    $sql = "UPDATE usuaris SET contrasenya = :contrasenya, verification_code = NULL, verification_expires = NULL WHERE nickname = :nickname";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':contrasenya' => $hashed, ':nickname' => $nickname]);
    
    header('Location: vista.vincularLocal.php?step=2&success=1');
    exit;
}

header('Location: vista.vincularLocal.php');
?>
