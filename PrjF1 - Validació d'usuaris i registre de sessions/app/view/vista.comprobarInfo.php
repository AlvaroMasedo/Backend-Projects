<!--Alvaro Masedo Pérez -->
<?php
session_start();

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
    <title>Comprobar Informació</title>

    <link rel="stylesheet" href="../../resources/css/style.comprobarInfo.css">

</head>

<body>
    <?php include __DIR__ . '/../view/vista.header.php'; ?>
    <main>
        <h1>CONFIRMA LES TEVES DADES</h1>
        <div class="separador"></div>
        <h2>Revisa que la informació sigui correcta abans de registrar-te:</h2>
        <div class="dades-container">
            <!-- Primer camp del formulari (Nom)-->
            <div class="camp">
                <label>Nom:</label>
                <span><?php echo $nom; ?></span>
            </div>

            <!-- Segon camp del formulari (Cognom)-->
            <div class="camp">
                <label>Cognom:</label>
                <span><?php echo $cognom ? $cognom : '(No especificat)'; ?></span>
            </div>

            <!-- Tercer camp del formulari (NickName)-->
            <div class="camp">
                <label>Nickname:</label>
                <span><?php echo $nickname; ?></span>
            </div>

            <!-- Quart camp del formulari (email)-->
            <div class="camp">
                <label>Email:</label>
                <span><?php echo $email; ?></span>
            </div>

            <!-- Cinqué camp del formulari (Contrasenya)-->
            <div class="camp">
                <label>Contrasenya:</label>
                <span>••••••••••••</span>
            </div>

            <!-- Confirmar i enviar cap a la base de dades-->
            <div class="botones">
                <form method="POST" action="../controller/registre.php?action=confirmar" >
                    <input class="button" type="submit" name="btn-confirmar" value="CONFIRMAR I REGISTRAR-SE">
                </form>
                <br>
                <a class="button" href="../view/vista.signup.php">TORNAR ENRRERE</a>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../view/vista.footer.php'; ?>
</body>

</html>