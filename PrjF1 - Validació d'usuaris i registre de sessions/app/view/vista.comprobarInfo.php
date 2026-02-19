<!--Alvaro Masedo Pérez -->
<?php
require_once __DIR__ . '/../../includes/session_check.php';

// Comprovar que hi hagi dades de registre
if (!isset($_SESSION['dades_registre'])) {
    header('Location: vista.signup.php');
    exit;
}

// Obtenir les dades de la sessió
$dades = $_SESSION['dades_registre'];
$nickname = htmlspecialchars($dades['nickname']);
$nom = htmlspecialchars($dades['nom']);
$cognom = htmlspecialchars($dades['cognom']);
$email = htmlspecialchars($dades['email']);
?>
<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirma la teva informació</title>
    <link rel="stylesheet" href="../../resources/css/style.comprobarInfo.css">
    <link rel="stylesheet" href="../../resources/css/style.header.css">
</head>

<body>
    <?php include __DIR__ . '/../view/vista.header.php'; ?>
    
    <main>
        <div class="confirm-wrapper">
            <div class="section-title">
                <h1>CONFIRMA LES TEVES DADES</h1>
            </div>
            <div class="separador"></div>

            <div class="confirm-layout">
                <h2>Revisa que la informació sigui correcta</h2>
                
                <div class="info-grid">
                    <!-- Nom -->
                    <div class="info-item">
                        <span class="info-label">Nom Complet</span>
                        <p class="info-value"><?php echo $nom . ' ' . ($cognom ?: ''); ?></p>
                    </div>

                    <!-- Nickname -->
                    <div class="info-item">
                        <span class="info-label">Nickname</span>
                        <p class="info-value"><?php echo $nickname; ?></p>
                    </div>

                    <!-- Email -->
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <p class="info-value"><?php echo $email; ?></p>
                    </div>

                    <!-- Contrasenya -->
                    <div class="info-item">
                        <span class="info-label">Contrasenya</span>
                        <p class="info-value">••••••••••••••••</p>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="action-box">
                    <p>Tot sembla correcte?</p>
                    
                    <form method="POST" action="../controller/registre.php?action=confirmar" class="action-form">
                        <button type="submit" class="btn-primary">CONFIRMAR I REGISTRAR-SE</button>
                    </form>
                    
                    <a href="../view/vista.signup.php" class="btn-secondary">Corregir Dades</a>
                </div>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../view/vista.footer.php'; ?>
</body>

</html>