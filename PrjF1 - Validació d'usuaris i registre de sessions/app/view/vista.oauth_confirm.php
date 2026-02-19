<!--Álvaro Masedo Pérez-->
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../resources/css/style.oauth_confirm.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
    <title>Confirmar <?php echo ($context === 'login') ? 'Accés' : 'Registre'; ?> OAuth</title>
</head>
<body>
    <header>
        <ul>
            <li><a href="../../index.php"><b><img class="logoIMG" src="../../uploads/img/logo.webp" alt="home" title="Inici"></b></a></li>
            <li><a class="button" href="../view/vista.login.php" title="Iniciar sessió"><b>Iniciar Sessió</b></a></li>
        </ul>
    </header>
    <main>
        <h1>CONFIRMAR <?php echo ($context === 'login') ? 'ACCÉS' : 'REGISTRE'; ?></h1>
        <div class="separador"></div>
        
        <div class="confirm-container">
            <div class="confirm-content">
                <p class="confirm-title">
                    <?php 
                        if ($context === 'login') {
                            echo "Aquesta és la primera vegada que inicies sessió amb aquest email. Vols crear un compte nou?";
                        } else {
                            echo "¿Vols crear un compte nou amb les següents dades?";
                        }
                    ?>
                </p>
                
                <div class="user-info-box">
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($oauthData['email']); ?></p>
                    <p><strong>Nom:</strong> <?php echo htmlspecialchars($oauthData['nom']); ?></p>
                    <?php if (!empty($oauthData['cognom'])): ?>
                        <p><strong>Cognom:</strong> <?php echo htmlspecialchars($oauthData['cognom']); ?></p>
                    <?php endif; ?>
                    <p><strong>Proveïdor:</strong> <?php echo ucfirst($oauthData['provider']); ?></p>
                </div>
                
                <form method="POST" class="confirm-form">
                    <button type="submit" name="confirm" value="1" class="btn btn-success">
                        ✓ CONFIRMAR I CREAR COMPTE
                    </button>
                    <button type="submit" name="cancel" value="1" class="btn btn-danger">
                        ✗ CANCELAR
                    </button>
                </form>
                
                <p class="confirm-footer">
                    <?php 
                        if ($context === 'login') {
                            echo "No vols crear el compte? <a href='../../app/view/vista.login.php' class='link'>Torna al login</a>";
                        } else {
                            echo "Si ja tens un compte, <a href='../view/vista.login.php' class='link'>inicia sessió aquí</a>";
                        }
                    ?>
                </p>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/vista.footer.php'; ?>
</body>
</html>
