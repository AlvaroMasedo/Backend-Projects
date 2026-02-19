<!--Álvaro Masedo Pérez-->

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
    <?php
    require_once __DIR__ . '/../../includes/session_check.php';
    require_once __DIR__ . '/../../config/db_connection.php';
    require_once __DIR__ . '/../model/model.usuari.php';
    
    if (!isset($_SESSION['usuari'])) {
        header('Location: vista.login.php');
        exit;
    }
    
    $modelUsuaris = new ModelUsers($conn);
    $usuari = $modelUsuaris->obtenirPerNickname($_SESSION['usuari']['nickname']);
    
    $teContrasenya = !empty($usuari['contrasenya']) && $usuari['contrasenya'] !== 'oauth_pending';
    $teOAuth = !empty($usuari['oauth_provider']) && !empty($usuari['oauth_id']);
    
    if (!$teOAuth || $teContrasenya) {
        header('Location: vista.perfil.php');
        exit;
    }
    
    $step = $_GET['step'] ?? '1';
    $error = '';
    $success = '';
    
    if (isset($_GET['error'])) {
        $msgs = [
            'invalid_code' => 'Codi incorrecte. Intenta de nou.',
            'expired' => 'Codi expirat. Solicita un de nou.',
            'mismatch' => 'Les contrasenyes no coincideixen.',
            'invalid_password' => 'Contrasenya no vàlida.',
            'email_error' => 'Error enviant email.'
        ];
        $error = '<p style="color: #d41616; font-weight: bold; background-color: #ffe6e6; padding: 10px; border-radius: 5px; margin: 15px 0;">' . ($msgs[$_GET['error']] ?? 'Error desconegut') . '</p>';
    }
    
    if (isset($_GET['success']) && $_GET['success'] == '1') {
        $success = '<p style="color: #00a800; font-weight: bold; background-color: #e6ffe6; padding: 10px; border-radius: 5px; margin: 15px 0;">✓ Contrasenya establerta correctament!</p>';
    }
    ?>
    
    <header>
        <ul>
            <li><a href="../../index.php"><img class="logoIMG" src="../../uploads/img/logo.webp" alt="home"></a></li>
            <li><a class="button" href="vista.perfil.php"><b>Torna al Perfil</b></a></li>
        </ul>
    </header>
    
    <main>
        <h1>VINCULAR COMPTE LOCAL</h1>
        <div class="separador"></div>
        
        <?php if ($step === '1'): ?>
            <form method="POST" action="../controller/vincularLocal.php">
                <p style="text-align: center; margin-bottom: 1.5rem;">Enviarem un codi de verificació al teu email</p>
                
                <label>Email:</label>
                <input type="email" value="<?php echo htmlspecialchars($usuari['email']); ?>" disabled>
                
                <?php echo $error; ?>
                
                <input type="submit" value="ENVIAR CODI">
                <p><a class="link" href="vista.perfil.php">Cancelar</a></p>
            </form>
            
        <?php else: ?>
            <form method="POST" action="../controller/vincularLocal.php">
                <input type="hidden" name="action" value="verify">
                
                <label>Codi (6 dígits):</label>
                <input type="text" name="code" placeholder="123456" maxlength="6" required>
                
                <label>Nova Contrasenya:</label>
                <input type="password" name="contrasenya" required>
                
                <label>Repeteix Contrasenya:</label>
                <input type="password" name="repContrasenya" required>
                <p class="missatge">12-20 caràcters, majúscula, minúscula, número, especial</p>
                
                <?php echo $error; ?>
                <?php echo $success; ?>
                
                <input type="submit" value="VERIFICAR I ESTABLIR">
                <p><a class="link" href="vista.vincularLocal.php">Tornar atrás</a></p>
            </form>
        <?php endif; ?>
    </main>
    
    <?php include 'vista.footer.php'; ?>
</body>
</html>
