<!--Álvaro Masedo Pérez-->
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar <?php echo ($context === 'login') ? 'Accés' : 'Registre'; ?> OAuth</title>
    <link rel="stylesheet" href="../../resources/css/style.oauth_confirm.css">
    <link rel="stylesheet" href="../../resources/css/style.header.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
</head>
<body>
    <?php include __DIR__ . '/vista.header.php'; ?>
    
    <main>
        <div class="confirm-wrapper">
            <div class="section-title">
                <h1>CONFIRMAR <?php echo ($context === 'login') ? 'ACCÉS' : 'REGISTRE'; ?></h1>
            </div>
            <div class="separador"></div>

            <div class="confirm-layout">
                <h2><?php 
                    if ($context === 'login') {
                        echo "Primera vegada amb aquest email";
                    } else {
                        echo "Confirma les teves dades";
                    }
                ?></h2>
                
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <p class="info-value"><?php echo htmlspecialchars($oauthData['email']); ?></p>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Nom</span>
                        <p class="info-value"><?php echo htmlspecialchars($oauthData['nom']); ?></p>
                    </div>

                    <?php if (!empty($oauthData['cognom'])): ?>
                    <div class="info-item">
                        <span class="info-label">Cognom</span>
                        <p class="info-value"><?php echo htmlspecialchars($oauthData['cognom']); ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="info-item">
                        <span class="info-label">Proveïdor</span>
                        <p class="info-value"><?php echo ucfirst($oauthData['provider']); ?></p>
                    </div>
                </div>

                <div class="action-box">
                    <p>Tot sembla correcte?</p>
                    
                    <form method="POST" class="action-form">
                        <button type="submit" name="confirm" value="1" class="btn-primary">CONFIRMAR I CREAR COMPTE</button>
                    </form>
                    
                    <a href="<?php echo ($context === 'login') ? '../../app/view/vista.login.php' : '../../app/view/vista.signup.php'; ?>" class="btn-secondary">
                        <?php echo ($context === 'login') ? 'Torna al login' : 'Corregir Dades'; ?>
                    </a>
                </div>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/vista.footer.php'; ?>
</body>
</html>
