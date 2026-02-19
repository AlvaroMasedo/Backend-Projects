<?php
/**
 * Controlador per a la verificació d'email per vincular compte amb Google OAuth
 * 
 * Fluxe:
 * 1. Step 1: Generar i enviar codi de 6 dígits al email de l'usuari
 * 2. Step 2: L'usuari introdueix el codi rebut
 * 3. Si és correcte, es marca la sesió amb email_verified_for_oauth = true
 * 4. Es redirigeix a Google OAuth amb context='vincular'
 * 5. Google retorna a oauth_callback.php que verifica el flag i vincula la compte
 * 
 * @author Álvaro Masedo Pérez
 * @version 1.0
 */
declare(strict_types=1);

// === INCLUDES I DEPENDÈNCIES ===
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../model/model.usuari.php';
require_once __DIR__ . '/../../lib/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../lib/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../lib/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../../lib/oauth_config.php';

use PHPMailer\PHPMailer\PHPMailer;

// === CARREGADOR DE VARIABLES D'ENTORN ===
// Carrega les credencials de Gmail des del fitxer .env
carregarEnv(__DIR__ . '/../../.env');

// === VALIDACIÓ DE SESIÓ ===
// Verifica que l'usuari està autenticat. Si no, redirigeix al login
if (!isset($_SESSION['usuari'])) {
    header('Location: ../view/vista.login.php');
    exit;
}

// === INICIALITZACIÓ DE VARIABLES ===
$modelUsuaris = new ModelUsers($conn);
$nickname = $_SESSION['usuari']['nickname'];
$usuari = $modelUsuaris->obtenirPerNickname($nickname);

// ==============================================================================
// STEP 1: GENERAR I ENVIAR CODI
// ==============================================================================
// Aquesta secció s'executa quan l'usuari fa clic a "ENVIAR CODI" en el formulari
// sense l'acció "verify" (és a dir, primera vegada que arriba)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    // === GENERAR CODI ===
    // Genera un nombre aleatori entre 0 i 999999, i l'omple amb zeros a l'esquerra per tenir exactament 6 dígits
    // Exemple: 12345 → "012345"
    $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // === ESTABLIT EXPIRACIÓ ===
    // El codi expira en 15 minuts des del moment de la generació
    $expires = date('Y-m-d H:i:s', time() + (15 * 60));
    
    // === GUARDAR EL CODI A LA BASE DE DADES ===
    // Actualitza la taula usuaris amb el codi de verificació i la seva data d'expiració
    // Reutilitza les columnes verification_code i verification_expires que ja existeixen
    $sql = "UPDATE usuaris SET verification_code = :code, verification_expires = :expires WHERE nickname = :nickname";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':code' => $code, ':expires' => $expires, ':nickname' => $nickname]);
    
    // === ENVIAR EMAIL AMB EL CODI ===
    try {
        $mail = new PHPMailer(true);
        
        // === CONFIGURACIÓ SMTP ===
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        
        // Obtenir credencials des de les variables d'entorn carregades de .env
        $emailAddress = getenv('GOOGLE_OAUTH_EMAIL');
        $emailPassword = getenv('GOOGLE_OAUTH_PASSWORD');
        
        // Validar que les credencials existeixen
        if (!$emailAddress || !$emailPassword) {
            throw new Exception("Credencials de email no configurades. Verifica el fitxer .env amb GOOGLE_OAUTH_EMAIL i GOOGLE_OAUTH_PASSWORD.");
        }
        
        // === AUTENTICACIÓ ===
        $mail->Username = $emailAddress;
        $mail->Password = $emailPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Usar TLS (més segur que 'tls' string)
        $mail->Port = 587; // Port per a TLS
        $mail->SMTPDebug = 0; // 0 = errors només, 2 = debug complet (per logs)
        $mail->Debugoutput = 'error_log'; // Enviar output de debug al log de PHP
        
        // === CONFIGURACIÓ DEL MISSATGE ===
        $mail->setFrom('noreply@f1articles.com', 'F1 Articles');
        $mail->addAddress($usuari['email'], $usuari['nom']); // Envia al email de l'usuari registrat
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8'; // Importat per a accents i caràcters especials
        $mail->Subject = 'Codi de verificació - Vincular Google';
        
        // Crea el cos del mail amb el codi destacat
        $mail->Body = "<h2 style='color: #D41616; font-family: Arial, sans-serif;'>Vincular Compte amb Google</h2>
                       <p style='font-family: Arial, sans-serif;'>Per vincular la teva compte amb Google, necessitem verificar la teva identitat.</p>
                       <p style='font-family: Arial, sans-serif;'>El teu codi:</p>
                       <p style='font-size: 32px; font-weight: bold; color: #D41616; font-family: Arial, sans-serif; letter-spacing: 5px;'>" . htmlspecialchars($code) . "</p>
                       <p style='color: #666; font-family: Arial, sans-serif;'><em>Expira en 15 minuts.</em></p>";
        
        // Versió text per a clients que no suporten HTML
        $mail->AltBody = "Codi de verificació: " . $code . "\nExpira en 15 minuts.";
        
        // === ENVIÓ DE L'EMAIL ===
        $mail->send();
        
        // Si l'email s'envia correctament, redirigeix al step 2 per introduir el codi
        header('Location: ../view/vista.verificarEmailVincular.php?step=2');
    } catch (Exception $e) {
        // Si hi ha error en l'envío, registra-ho al log d'errors amb detalls
        error_log("===== EMAIL ERROR EN VERIFICAREMAILVINCULAR =====");
        error_log("Exception: " . $e->getMessage());
        if (isset($mail)) {
            error_log("PHPMailer ErrorInfo: " . $mail->ErrorInfo);
        }
        error_log("======================================");
        header('Location: ../view/vista.perfil.php?error=email_error');
    }
    exit;
}

// ==============================================================================
// STEP 2: VERIFICAR CODI I MARCAR EMAIL VERIFICAT
// ==============================================================================
// Aquesta secció s'executa quan l'usuari submíteix el codi en el formulari de verificació
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'verify') {
    $code = $_POST['code'] ?? '';
    
    // === VALIDACIÓ DEL FORMAT DEL CODI ===
    // Comprova que el codi introduït és exactament 6 dígits numèrics
    if (!preg_match('/^[0-9]{6}$/', $code)) {
        header('Location: ../view/vista.verificarEmailVincular.php?step=2&error=invalid_code');
        exit;
    }
    
    // === OBTENIR EL CODI DE LA BASE DE DADES ===
    // Busca el codi guardat per a aquest usuari i la seva data d'expiració
    $sql = "SELECT verification_code, verification_expires FROM usuaris WHERE nickname = :nickname";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':nickname' => $nickname]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // === VALIDACIÓ DEL CODI ===
    // Verifica que el codi introduït coincideix exactament amb el guardat a la BD
    if (!$row || $row['verification_code'] !== $code) {
        header('Location: ../view/vista.verificarEmailVincular.php?step=2&error=invalid_code');
        exit;
    }
    
    // === VALIDACIÓ DE L'EXPIRACIÓ ===
    // Comprova que el codi no ha expirat comparant la data d'expiració amb l'hora actual
    if (strtotime($row['verification_expires']) < time()) {
        header('Location: ../view/vista.verificarEmailVincular.php?step=2&error=expired');
        exit;
    }
    
    // === NETEJAR EL CODI DE LA BASE DE DADES ===
    // Una vegada verificat, esborrem el codi per evitar reutilitzacions
    $sql = "UPDATE usuaris SET verification_code = NULL, verification_expires = NULL WHERE nickname = :nickname";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':nickname' => $nickname]);
    
    // === MARCAR EMAIL COM A VERIFICAT EN LA SESIÓ ===
    // Aquesta bandera indica que l'usuari ha verificat el seu email correctament
    // Es necessària per procedir amb la vinculació amb Google OAuth
    $_SESSION['email_verified_for_oauth'] = true;
    
    // === REDIRIGIR A GOOGLE OAUTH ===
    // Una vegada verificat l'email, disparem el fluxe d'autenticació amb Google
    // context='vincular' indica a oauth_callback.php que es vinculan dues comptes
    OAuthConfig::inicialitzar();
    header('Location: ' . OAuthConfig::obtenirUrlAuthGoogle('', 'vincular'));
    exit;
}

// === REDIRECCCIÓ PER DEFECTE ===
// Si es crida aquest fit sense POST o amb paràmetres incorrectes, retorna al perfil
header('Location: ../view/vista.perfil.php');
?>

