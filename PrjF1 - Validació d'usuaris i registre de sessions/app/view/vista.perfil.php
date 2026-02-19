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
    <link rel="stylesheet" href="../../resources/css/style.perfil.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
    <title>Perfil</title>
</head>

<body>
    <!-- Header amb navegació -->
    <?php include __DIR__ . '/vista.header.php'; ?>

    <main>
        <!-- Alertes a dalt de la pàgina -->
        <div style="margin-top: 2rem;">
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
            
            <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
                <div class="perfil-container" style="margin-top: 0; margin-left: 20%; margin-right: 20%; margin-bottom: 1rem;">
                    <p class="success">Perfil modificat correctament!</p>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['vincular_success']) && $_GET['vincular_success'] == '1'): ?>
                <div class="perfil-container" style="margin-top: 0; margin-left: 20%; margin-right: 20%; margin-bottom: 1rem;">
                    <p class="success">✓ Compte vinculat amb Google correctament!</p>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="perfil-container" style="margin-top: 0; margin-left: 20%; margin-right: 20%; margin-bottom: 1rem;">
                    <p style="color: #d41616; background-color: #ffe6e6; padding: 10px; border-radius: 5px; border-left: 4px solid #d41616; margin: 15px 0; font-weight: bold;">
                        <?php 
                        $error = $_GET['error'];
                        $missatgesError = [
                            'vincular_no_session' => '⚠ Debes estar connectat per vincular el teu compte.',
                            'vincular_already_linked' => '⚠ Ja tens una conta OAuth vinculada.',
                            'vincular_email_mismatch' => '⚠ L\'email de Google no coincideix amb el teu email.'
                        ];
                        echo $missatgesError[$error] ?? '⚠ Ha ocorregut un error desconegut.';
                        ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['usuari'])): ?>
            <?php 
            // Obtenir informació completa de l'usuari des de la BD
            require_once __DIR__ . '/../../config/db_connection.php';
            require_once __DIR__ . '/../model/model.usuari.php';
            $modelUsuaris = new ModelUsers($conn);
            $usuariComplet = $modelUsuaris->obtenirPerNickname($_SESSION['usuari']['nickname']);
            
            // Determinar estat del compte per mostrar botons de vinculació
            $teContrasenya = !empty($usuariComplet['contrasenya']) && $usuariComplet['contrasenya'] !== 'oauth_pending';
            $teOAuth = !empty($usuariComplet['oauth_provider']) && !empty($usuariComplet['oauth_id']);
            ?>

            <!-- 
                BANNER DE VINCULACIÓ DE COMPTES
                
                Mostra un missatge diferent segons l'estat de vinculació de l'usuari:
                - Si té contrasenya local però NO OAuth: Ofereix vinclar Google
                - Si té OAuth però NO contrasenya local: Ofereix establir contrasenya
                - Si té ambdues: No mostra res (compte completament vinculat)
            -->
            <?php if (($teContrasenya && !$teOAuth) || (!$teContrasenya && $teOAuth)): ?>
                
                <!-- CASE 1: COMPTE LOCAL SEM OAUTH -->
                <!-- Usuari que es va registrar normalment amb contrasenya i email -->
                <!-- Li oferim vinclar Google per a facilitat d'accés -->
                <?php if ($teContrasenya && !$teOAuth): ?>
                    <div class="vincular-banner">
                        <div class="vincular-banner-content">
                            <div class="vincular-banner-text">
                                <h2>Vincula el teu compte amb Google</h2>
                                <p>Inicia sessió més ràpidament amb una sola clicada</p>
                            </div>
                            <!-- 
                                BOTÓ VINCULAR GOOGLE
                                
                                Fluxe:
                                1. Clica -> Va a verificarEmailVincular.php (step 1)
                                2. Solicita enviar codi al email
                                3. Verifica-ho amb el codi rebut (step 2)
                                4. Es redirigeix a Google OAuth amb context='vincular' en sessió
                                5. Retorna a oauth_callback.php que valida email_verified_for_oauth
                                6. Vincula provider+oauth_id a la compte local
                            -->
                            <a href="vista.verificarEmailVincular.php" class="vincular-banner-btn">
                                <img src="../../uploads/img/googleLogo.ico" alt="Google" class="vincular-logo">
                                Vincular amb Google
                            </a>
                        </div>
                    </div>
                
                <!-- CASE 2: COMPTE OAUTH SENSE CONTRASENYA LOCAL -->
                <!-- Usuari que es va registrar amb Google OAuth i no té contrasenya local -->
                <!-- Li oferim establir una contrasenya per poder iniciar sessió sense Google -->
                <?php elseif (!$teContrasenya && $teOAuth): ?>
                    <div class="vincular-banner">
                        <div class="vincular-banner-content">
                            <div class="vincular-banner-text">
                                <h2>Estableix una contrasenya</h2>
                                <p>Inicia sessió sense dependre de Google</p>
                            </div>
                            <!-- 
                                BOTÓ ESTABLECER CONTRASENYA LOCAL
                                
                                Fluxe:
                                1. Clica -> Va a vista.vincularLocal.php (step 1)
                                2. Es solicita enviar codi al email
                                3. Verifica-ho amb el codi rebut i estableix contrasenya (step 2)
                                4. La contrasenya s'emmagatzema encriptada amb bcrypt
                                5. Ja pot iniciar sessió amb contrasenya o Google
                            -->
                            <a href="vista.vincularLocal.php" class="vincular-banner-btn">
                                Vincular Compte Local
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>

        <div class="perfil-container">
            <div class="modificarPerfil">
                <a href="vista.modificarPerfil.php" class="button">Modificar Perfil</a>
                <h1>Perfil d'Usuari</h1>
            </div>
            <div class="separador"></div>
            
            <?php if (isset($_SESSION['usuari'])): ?>
                
                <?php if (empty($_SESSION['usuari']['imatge_perfil'])): ?>
                    <img src="../../uploads/img/fotos_perfil/foto_predeterminada/null.png" alt="Imatge de perfil" class="perfil-imatge">
                <?php else: ?>
                    <img src="../../uploads/img/fotos_perfil/<?php echo htmlspecialchars($_SESSION['usuari']['imatge_perfil']); ?>" alt="Imatge de perfil" class="perfil-imatge">
                <?php endif; ?>
                <p class="perfil-p"><strong>Nickname:</strong></p>
                <p><?php echo htmlspecialchars($_SESSION['usuari']['nickname']); ?></p>
                <div class="separador_gran"></div>

                <p class="perfil-p"><strong>Nom:</strong></p>
                <p><?php echo htmlspecialchars($_SESSION['usuari']['nom']); ?></p>
                <div class="separador_gran"></div>

                <!-- Mostra el camp cognoms si està disponible -->
                <p class="perfil-p"><strong>Cognom:</strong></p>
                <?php if (!empty($_SESSION['usuari']['cognom'])): ?>
                    <p><?php echo htmlspecialchars($_SESSION['usuari']['cognom']); ?></p>
                <?php else: ?>
                    <p>No proporcionat</p>
                <?php endif; ?>
                <div class="separador_gran"></div>

                <p class="perfil-p"><strong>Email:</strong></p>
                <p><?php echo htmlspecialchars($_SESSION['usuari']['email']); ?></p>
                <div class="separador_gran"></div>

                <p class="perfil-p"><strong>Contrasenya: </strong></p>
                <p>*************</p>
                <div class="separador_gran"></div>
            <?php else: ?>
                <p>No hi ha cap usuari connectat.</p>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'vista.footer.php'; ?>
</body>

</html>