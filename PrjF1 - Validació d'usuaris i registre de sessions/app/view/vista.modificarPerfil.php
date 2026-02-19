<!--Álvaro Masedo Pérez-->
<?php

// Verificar si la sessió ha expirat mitjançant paràmetre GET o cookie
$sessionExpired = (isset($_GET['session_expired']) && $_GET['session_expired'] == '1') || (isset($_COOKIE['session_expired']) && $_COOKIE['session_expired'] == '1');
if ($sessionExpired && isset($_COOKIE['session_expired'])) {
    setcookie('session_expired', '', time() - 3600, '/'); //Esborrar cookie després de mostrar el missatge
}
// Ara incloure session_check.php
require_once __DIR__ . '/../../includes/session_check.php';
?>
<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../resources/css/style.modificarPerfil.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
    <title>Modificar Perfil</title>
</head>

<body>
    <?php if ($sessionExpired): ?>
        <div class="alert-overlay">
            <div class="alert">
                <p>S'ha tancat la sessió.</p>
                <a class="button" href="app/view/vista.login.php">INICIAR SESSIÓ</a>
                <br>
                <a class="link" href="../../index.php">Navega com usuari anònim</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Header amb navegació -->
    <?php include __DIR__ . '/vista.header.php'; ?>

    <main>
        <div class="perfil-container">
            <h1>Modificar Perfil</h1>
            <div class="separador"></div>

            <?php if (isset($missatge)): ?>
                <?php echo $missatge; ?>
            <?php endif; ?>

            <form class="form-modificarUsuari" action="../controller/usuari.php?action=modificar" method="post" enctype="multipart/form-data">
                <?php if (isset($_SESSION['usuari'])): ?>

                    <!-- Mostrar imatge de perfil -->
                    <div class="imatgeModificarContainer">
                        <?php if (empty($_SESSION['usuari']['imatge_perfil'])): ?>
                            <img src="../../uploads/img/fotos_perfil/foto_predeterminada/null.png" alt="Imatge de perfil" class="perfil-imatge">
                        <?php else: ?>
                            <img src="../../uploads/img/fotos_perfil/<?php echo htmlspecialchars($_SESSION['usuari']['imatge_perfil']); ?>" alt="Imatge de perfil" class="perfil-imatge">
                        <?php endif; ?>

                    </div>

                    <div class="separador_gran"></div>

                    <!-- Canviar imatge de perfil -->
                    <div class="upload-container">
                        <p class="perfil-p"><strong>Modificar Foto de perfil:</strong></p>
                        <input type="file" name="foto_perfil" id="foto_perfil" accept="image/jpeg,image/png,image/gif,image/webp" hidden>
                        <div class="custom-file-div">
                            <label for="foto_perfil" class="custom-file-upload">
                                Seleccionar Foto
                            </label>
                            <span id="file-chosen" class="custom-file-span">Cap fitxer seleccionat</span>
                        </div>
                    </div>
                    <?php echo $errorFoto ?? ''; ?>
                    <div class="separador_gran"></div>

                    <!-- Mostrar Nickname de l'usuari -->
                    <p class="perfil-p"><strong>Nickname:</strong></p>
                    <input type="text" name="nickname" value="<?php echo htmlspecialchars($_SESSION['usuari']['nickname']); ?>">
                    <?php echo $errorNickname ?? ''; ?>
                    <div class="separador_gran"></div>

                    <!-- Mostrar Nom de l'usuari -->
                    <p class="perfil-p"><strong>Nom:</strong></p>
                    <input type="text" name="nom" value="<?php echo htmlspecialchars($_SESSION['usuari']['nom']); ?>">
                    <?php echo $errorNom ?? ''; ?>
                    <div class="separador_gran"></div>

                    <!-- Mostra el camp cognoms si està disponible -->
                    <p class="perfil-p"><strong>Cognom:</strong></p>
                    <?php if (!empty($_SESSION['usuari']['cognom'])): ?>
                        <input type="text" name="cognom" value="<?php echo htmlspecialchars($_SESSION['usuari']['cognom']); ?>">
                    <?php else: ?>
                        <input type="text" name="cognom" placeholder="No especificat">
                    <?php endif; ?>
                    <?php echo $errorCognom ?? ''; ?>
                    <div class="separador_gran"></div>

                    <!-- Mostrar el camp de contrasenya per canviar-la -->
                    <p class="perfil-p"><strong>Contrasenya:</strong></p>
                    <p>Escriu primer la teva contrasenya actual, després la nova contrasenya i confirma-la.</p>

                    <p class="subtitle">Contrasenya actual.</p>
                    <input type="password" name="contrasenya_actual" placeholder="Escriu la contrasenya actual">
                    <?php echo $errorContrasenyaActual ?? ''; ?>

                    <p class="subtitle">Nova contrasenya.</p>
                    <p class="subtitle">La contrasenya ha de contenir:</p>
                    <ul>
                        <li>De 12 a 20 caràcters</li>
                        <li>Mínim una majúscula i minúscula</li>
                        <li>Mínim un número</li>
                        <li>Mínim un càracter especial</li>
                    </ul>
                    <input type="password" name="nova_contrasenya" placeholder="Escriu la nova contrasenya">
                    <?php echo $errorNovaContrasenya ?? ''; ?>

                    <p class="subtitle">Confirma la nova contrasenya.</p>
                    <input type="password" name="confirma_nova_contrasenya" placeholder="Confirma la nova contrasenya">
                    <?php echo $errorConfirmaNovaContrasenya ?? ''; ?>
                    <div class="separador_gran"></div>

                    <!-- Botó per desar els canvis i tornar enrere -->
                    <?php echo $error ?? ''; ?>
                    <input class="button" type="submit" value="DESAR CANVIS">
                    <a class="cancel-button" href="../view/vista.perfil.php">TORNAR ENRERE</a>

                <?php else: ?>
                    <p>No hi ha cap usuari connectat.</p>
                <?php endif; ?>
            </form>

        </div>
    </main>

    <?php include 'vista.footer.php'; ?>

    <script>
        document.getElementById('foto_perfil').addEventListener('change', function(e) {
            if (e.target.files && e.target.files.length > 0) {
                document.getElementById('file-chosen').textContent = e.target.files[0].name;
            }
        });
    </script>
</body>

</html>