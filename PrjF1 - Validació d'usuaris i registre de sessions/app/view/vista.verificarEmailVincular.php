<!--
    Vista de Verificació d'Email per a Vincular Google OAuth
    
    Mostra un formulari en dos pasos:
    - Step 1: Pedir confirmació i enviar email amb codi
    - Step 2: Introduir el codi rebut
    
    @author Álvaro Masedo Pérez
-->
<?php
// === INCLUSIÓ DE FITXERS REQUERITS ===
require_once __DIR__ . '/../../includes/session_check.php';

// === VALIDACIÓ DE SESIÓ ===
// Verifica que l'usuari està autenticat. Si no, redirigeix al login
if (!isset($_SESSION['usuari'])) {
    header('Location: vista.login.php');
    exit;
}

// === VARIABLES DE CONTROL ===
// Obté el número de step del paràmetre GET (per defecte step 1)
$step = $_GET['step'] ?? '1';

// Obté el codi d'error del paràmetre GET (si n'hi ha)
$error = $_GET['error'] ?? '';

// === MAPA DE MISSATGES D'ERROR ===
// Traducció d'errors a missatges amigables per a l'usuari
$errorMessages = [
    'invalid_code' => 'El codi és incorrecte',
    'expired' => 'El codi ha expirat. Demana un de nou',
    'email_error' => 'Error enviante email. Torna a intentar',
];
?>
<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../resources/css/style.comprobarInfo.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
    <title>Verificar Email - Vincular Google</title>
</head>

<body>
    <!-- === HEADER === -->
    <!-- Inclou el menú de navegació superior -->
    <?php include __DIR__ . '/vista.header.php'; ?>

    <main>
        <div class="main-container">
            <?php if ($step === '1'): ?>
                <!-- 
                    ===================================================================
                    STEP 1: CONFIRMACIÓ I ENVIÓ DE CODI
                    ===================================================================
                    En aquest step, es demana la confirmació de l'usuari per enviar 
                    el codi de verificació al seu email.
                -->
                <form method="POST" action="../controller/verificarEmailVincular.php" class="form-container">
                    <h2>Verificar Email</h2>
                    <p>Se enviarà un codi de verificació al teu email per confirmar la vinculació amb Google.</p>
                    
                    <!-- === CAMP EMAIL === -->
                    <!-- Mostra l'email de l'usuari en mode lectura (disabled) -->
                    <label for="email">Email:</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?php echo htmlspecialchars($_SESSION['usuari']['email'] ?? ''); ?>" 
                        disabled
                    >
                    
                    <!-- === BOTÓ ENVIAR === -->
                    <!-- En fer clic, es genera el codi i s'envia per email -->
                    <button type="submit" class="btn-enviar">ENVIAR CODI</button>
                </form>
                
                <!-- === BOTÓ CANCELAR === -->
                <a href="vista.perfil.php" class="link-cancelar">Cancelar</a>
                
            <?php elseif ($step === '2'): ?>
                <!-- 
                    ===================================================================
                    STEP 2: VALIDACIÓ DE CODI
                    ===================================================================
                    En aquest step, l'usuari introdueix el codi rebut al seu email.
                    Si és correcte, es marca la sessió com a verificada i es redirigeix
                    a Google OAuth.
                -->
                <form method="POST" action="../controller/verificarEmailVincular.php" class="form-container">
                    <h2>Verificar Email</h2>
                    <p>Escriu el codi que hem enviat al teu email.</p>
                    
                    <!-- === MOSTRAR ERROR SI N'HI HA === -->
                    <!-- Si el codi és incorrecte o ha expirat, mostra el missatge d'error -->
                    <?php if ($error && isset($errorMessages[$error])): ?>
                        <div class="error-message">
                            <?php echo htmlspecialchars($errorMessages[$error]); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- === CAMP DE CODI === -->
                    <!-- Input que accepta exactament 6 dígits numèrics -->
                    <label for="code">Codi (6 dígits):</label>
                    <input 
                        type="text" 
                        id="code" 
                        name="code" 
                        maxlength="6" 
                        pattern="[0-9]{6}" 
                        placeholder="000000" 
                        required
                    >
                    
                    <!-- === BOTÓ VERIFICAR === -->
                    <!-- Submíteix el formulari amb action='verify' al controlador -->
                    <button type="submit" class="btn-enviar">VERIFICAR</button>
                    
                    <!-- === ACCIÓ AMAGADA === -->
                    <!-- Paràmetre que indica al controlador que és el step 2 de verificació -->
                    <input type="hidden" name="action" value="verify">
                </form>
                
                <!-- 
                    === BOTÓ REENVIAR CODI ===
                    Permet a l'usuari solicitar un nou codi si no va rebre el primer
                    o si ha expirat. Simplement submíteix sense l'action, el que
                    farà que el controlador regeneri i reenvií el codi
                -->
                <form method="POST" action="../controller/verificarEmailVincular.php" style="margin-top: 1rem;">
                    <button type="submit" class="btn-reenviar">No vaig rebre el codi? Reenvia</button>
                </form>
                
                <!-- === BOTÓ CANCELAR === -->
                <a href="vista.perfil.php" class="link-cancelar">Cancelar</a>
            <?php endif; ?>
        </div>
    </main>

    <!-- === FOOTER === -->
    <!-- Inclou el peu de pàgina -->
    <?php include __DIR__ . '/vista.footer.php'; ?>
</body>

</html>
