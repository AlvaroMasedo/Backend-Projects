<!--
    Vista: Vincular Compte Local
    
    Mostra un formulari en dos steps per a usuaris OAuth que volen establir una contrasenya local.
    
    Step 1: Pedir confirmació per enviar codi al email
    Step 2: Introduir codi i establir contrasenya nova
    
    Només accessible per a usuaris:
    - Que no tenen contrasenya (són usuaris OAuth)
    - Que sí que tenen oauth_provider i oauth_id
    
    @author Álvaro Masedo Pérez
-->
<?php
// === VALIDACIÓ I INICIALITZACIÓ ===
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../model/model.usuari.php';

// === VALIDACIÓ DE SESIÓ ===
// Verifica que l'usuari està autenticat
if (!isset($_SESSION['usuari'])) {
    header('Location: vista.login.php');
    exit;
}

// === OBTENCIÓ DE DADES DE L'USUARI ===
$modelUsuaris = new ModelUsers($conn);
$usuari = $modelUsuaris->obtenirPerNickname($_SESSION['usuari']['nickname']);

// === VERIFICACIÓ D'ELEGIBILITAT ===
// Comprova si l'usuari té contrasenya (exclou oauth_pending)
$teContrasenya = !empty($usuari['contrasenya']) && $usuari['contrasenya'] !== 'oauth_pending';

// Comprova si l'usuari té vinculatge OAuth
$teOAuth = !empty($usuari['oauth_provider']) && !empty($usuari['oauth_id']);

// Els usuaris que ja tienen contrasenya O que no té OAuth no accedeixen aquí
// (no necessiten establir contrasenya local)
if (!$teOAuth || $teContrasenya) {
    header('Location: vista.perfil.php');
    exit;
}

// === VARIABLES DE CONTROL ===
$step = $_GET['step'] ?? '1'; // Step del formulari (1 o 2)
$error = '';
$success = '';

// === PROCESSAMENT D'ERRORS ===
// Si hi ha paràmetre d'error, mostrar el missatge apropiat
if (isset($_GET['error'])) {
    $msgs = [
        'invalid_code' => 'Codi incorrecte. Intenta de nou.',
        'expired' => 'Codi expirat. Solicita un de nou.',
        'mismatch' => 'Les contrasenyes no coincideixen.',
        'invalid_password' => 'Contrasenya no vàlida. Requereix: 12-20 caràcters, majúscula, minúscula, número, especial',
        'email_error' => 'Error enviant email. Torna a intentar.'
    ];
    // Mostra el missatge d'error en una caixa de color vermell
    $error = '<p style="color: #d41616; font-weight: bold; background-color: #ffe6e6; padding: 10px; border-radius: 5px; margin: 15px 0;">' . ($msgs[$_GET['error']] ?? 'Error desconegut') . '</p>';
}

// === PROCESSAMENT D'ÈXIT ===
// Si el formulari s'ha completat correctament, mostrar confirmació
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success = '<p style="color: #00a800; font-weight: bold; background-color: #e6ffe6; padding: 10px; border-radius: 5px; margin: 15px 0;">✓ Contrasenya establerta correctament!</p>';
}
?>
<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../resources/css/style.signup.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
    <title>Vincular Compte Local</title>
</head>

<body>
    <!-- === HEADER === -->
    <header>
        <ul>
            <!-- Logo: Enllaç a home -->
            <li><a href="../../index.php"><img class="logoIMG" src="../../uploads/img/logo.webp" alt="home"></a></li>
            <!-- Botó de tornada al perfil -->
            <li><a class="button" href="vista.perfil.php"><b>Torna al Perfil</b></a></li>
        </ul>
    </header>

    <main>
        <!-- === TÍTOL === -->
        <h1>VINCULAR COMPTE LOCAL</h1>
        <div class="separador"></div>
        
        <!-- === STEP 1: CONFIRMAR I ENVIAR CODI === -->
        <?php if ($step === '1'): ?>
            <form method="POST" action="../controller/vincularLocal.php">
                <p style="text-align: center; margin-bottom: 1.5rem;">Enviarem un codi de verificació al teu email</p>
                
                <!-- === CAMP EMAIL === -->
                <!-- Mostra l'email registrat en mode lectura (no pot canviar-se) -->
                <label>Email:</label>
                <input 
                    type="email" 
                    value="<?php echo htmlspecialchars($usuari['email']); ?>" 
                    disabled
                >
                
                <!-- === MOSTRAR ERROR === -->
                <?php echo $error; ?>
                
                <!-- === BOTÓ ENVIAR === -->
                <input type="submit" value="ENVIAR CODI">
                
                <!-- === BOTÓ CANCELAR === -->
                <p><a class="link" href="vista.perfil.php">Cancelar</a></p>
            </form>
            
        <!-- === STEP 2: VERIFICAR CODI I ESTABLIR CONTRASENYA === -->
        <?php else: ?>
            <form method="POST" action="../controller/vincularLocal.php">
                <!-- === ACCIÓ AMAGADA === -->
                <!-- Indica al controlador que es tracta del step 2 (verificació) -->
                <input type="hidden" name="action" value="verify">
                
                <!-- === CAMP CODI === -->
                <!-- Input que accepta exactament 6 dígits numèrics -->
                <label>Codi (6 dígits):</label>
                <input 
                    type="text" 
                    name="code" 
                    placeholder="123456" 
                    maxlength="6" 
                    pattern="[0-9]{6}"
                    required
                >
                
                <!-- === CAMP CONTRASENYA === -->
                <!-- Contrasenya nova que l'usuari vol establir -->
                <label>Nova Contrasenya:</label>
                <input 
                    type="password" 
                    name="contrasenya" 
                    required
                >
                
                <!-- === CONFIRMACIÓ CONTRASENYA === -->
                <!-- Confirmació per evitar errors de tipatge -->
                <label>Repeteix Contrasenya:</label>
                <input 
                    type="password" 
                    name="repContrasenya" 
                    required
                >
                
                <!-- === REQUISITS DE CONTRASENYA === -->
                <!-- Mostra els requisits mínims per a la contrasenya -->
                <p class="missatge">12-20 caràcters, majúscula, minúscula, número, especial</p>
                
                <!-- === MOSTRAR ERRORS === -->
                <?php echo $error; ?>
                
                <!-- === MOSTRAR ÈXIT === -->
                <?php echo $success; ?>
                
                <!-- === BOTÓ ENVIAR === -->
                <input type="submit" value="VERIFICAR I ESTABLIR">
                
                <!-- === BOTÓ ENRERRERE === -->
                <!-- Permet tornar a step 1 si l'usuari vol sol·licitar un nou codi -->
                <p><a class="link" href="vista.vincularLocal.php?step=1">Enrerrere</a></p>
            </form>
        <?php endif; ?>
    </main>
    
    <?php include 'vista.footer.php'; ?>
</body>
</html>
