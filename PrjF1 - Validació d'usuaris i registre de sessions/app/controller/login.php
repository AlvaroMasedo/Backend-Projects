<?php 
declare(strict_types=1);
//Alvaro Masedo Pérez

require __DIR__ .'/../../config/db_connection.php';
require __DIR__ .'/../model/model.usuari.php';
require __DIR__ . '/../../lib/recaptcha.php';

//Obtenir l'acció des de la URL
$action = $_GET['action'] ?? '';

//Declaració de variables
$nickname = $nom = $cognom = $email = $contrasenya = $repContrasenya = '';
$errorNickname = $errorNom = $errorCognom = $errorEmail = $errorContrasenya = $errorRepContrasenya = '';
$enviatMissatge = '';
$contadorIntents = 0;

//Funció per Iniciar Sessió amb un Usuari
function iniciarSessio(){

    global $conn, $email, $errorEmail, $errorContrasenya, $enviatMissatge, $contrasenya, $contadorIntents;  
    // Instanciar el model
    $controlarUsers = new ModelUsers($conn);

    // Iniciar sessió amb configuració segura
    require_once __DIR__ . '/../../includes/session_check.php';

    $contadorIntents = $_SESSION['contadorIntents'] ?? 0;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        //Obtenir dades del formulari
        $email = trim($_POST['email'] ?? '');
        $contrasenya = trim($_POST['contrasenya'] ?? '');
        $contrasenya_encriptada = hash('sha256', $contrasenya);

        // Si qualsevol camp està buit donarà error
        if (empty($email) || empty($contrasenya)) {
            // Incrementar el contador d'intents també per camps buits
            $contadorIntents++;
            $_SESSION['contadorIntents'] = $contadorIntents;
            
            $enviatMissatge = '<p class="error">TOTS ELS CAMPS AMB UN * SÓN OBLIGATORIS.</p>';
            
            // Si hem superat el nombre d'intents, validar reCAPTCHA
            if ($contadorIntents >= 3) {
                if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
                    $enviatMissatge .= '<p class="error">SIUSPLAU, COMPLETA EL RECAPTCHA.</p>';
                } else {
                    $resposta = $_POST['g-recaptcha-response'];
                    if (!verificar_recaptcha($resposta)) {
                        $enviatMissatge .= '<p class="error">FALLA AL VERIFICAR EL RECAPTCHA. SIUSPLAU, TORNA-HO A PROVAR.</p>';
                    }
                }
            }

        // Si hi ha 1 email o més, dona error
        } else if (!$controlarUsers->existeixEmail($email)) {
            $errorEmail = '<p class="error">NO HI HA CAP COMPTA REGISTRADA AMB AQUEST EMAIL</p>';

        // Si la contrasenya es incorrecta dona error
        } else if (!$controlarUsers->comprobarContrasenya($contrasenya_encriptada, $email)) {
            $errorContrasenya = '<p class="error">CONTRASENYA INCORRECTA, TORNA A PROVAR</p>';
            $contrasenya = '';
            // Incrementar i guardar el contador d'intents a la sessió
            $contadorIntents++;
            $_SESSION['contadorIntents'] = $contadorIntents;

            // Si hem superat el nombre d'intents, validar reCAPTCHA
            if ($contadorIntents >= 3) {
                if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
                    $errorContrasenya .= '<p class="error">SIUSPLAU, COMPLETA EL RECAPTCHA.</p>';
                } else {
                    $resposta = $_POST['g-recaptcha-response'];
                    if (!verificar_recaptcha($resposta)) {
                        $errorContrasenya .= '<p class="error">FALLA AL VERIFICAR EL RECAPTCHA. SIUSPLAU, TORNA-HO A PROVAR.</p>';
                    }
                }
            }
        
        // Si tot és correcte, abans d'iniciar sessió comprovem reCAPTCHA si s'han superat els intents
        } else {
            // Si s'han superat intents, exigir reCAPTCHA encara que la contrasenya sigui correcta
            if ($contadorIntents >= 3) {
                if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
                    $enviatMissatge = '<p class="error">SIUSPLAU, COMPLETA EL RECAPTCHA.</p>';
                } else {
                    $resposta = $_POST['g-recaptcha-response'];
                    if (!verificar_recaptcha($resposta)) {
                        $enviatMissatge = '<p class="error">FALLA AL VERIFICAR EL RECAPTCHA. SIUSPLAU, TORNA-HO A PROVAR.</p>';
                    }
                }
            }

            // Si no hi ha missatge d'error (el reCAPTCHA s'ha passat o no era necessari), procedim a fer login
            if (empty($enviatMissatge)) {
                try {
                    $ok = $controlarUsers->login($email, $contrasenya_encriptada);

                    if ($ok) {
                        //Resetem el contador d'intents
                        $contadorIntents = 0;
                        $_SESSION['contadorIntents'] = 0;

                        // La sessió ja està iniciada més amunt; regenerar id per seguretat
                        session_regenerate_id(true);

                        //Guardem les dades de l'usuari a la sessió
                        $_SESSION['usuari'] = [
                            'nickname' => $ok['nickname'],
                            'nom' => $ok['nom'],
                            'cognom' => $ok['cognom'],
                            'email' => $ok['email'],
                            'administrador' => $ok['administrador'],
                            'imatge_perfil' => $ok['imatge_perfil']
                        ];
                        $_SESSION['session_created'] = time();

                        // Si l'usuari ha marcat "recorda'm", generar i guardar token persistent
                        if (isset($_POST['recorda']) && $_POST['recorda'] === 'on') {
                            // Guardar token de Remember Me (cookie persistent de 30 dies)
                            $controlarUsers->guardarRememberMe($ok['nickname']);
                            $_SESSION['remember_me'] = 1; // Marcar que té Remember Me actiu
                            
                            // Eliminar token de navegador temporal si existeix
                            setcookie('browser_session_token', '', time() - 3600, '/', '', false, true);
                            unset($_SESSION['browser_session_token']);
                        } else {
                            // NO guardar token persistent, la sessió només durarà fins tancar el navegador
                            $_SESSION['remember_me'] = 0;
                            // Eliminar qualsevol remember_token anterior si existeix
                            $controlarUsers->eliminarRememberMe($ok['nickname']);
                            
                            // Generar token únic per a aquesta sessió de navegador
                            $browserToken = bin2hex(random_bytes(32));
                            $_SESSION['browser_session_token'] = $browserToken;
                            
                            // Cookie temporal que expira al tancar navegador
                            setcookie('browser_session_token', $browserToken, 0, '/', '', false, true);
                            
                            // Generar "marcador" de navegador (per detectar si s'ha tancat)
                            // Aquesta cookie data de quan es va login. Si desapareix, navegador es va tancar.
                            $browserMarker = bin2hex(random_bytes(16));
                            $_SESSION['browser_marker'] = $browserMarker;
                            setcookie('browser_marker', $browserMarker, 0, '/', '', false, true);
                            
                            // FORÇAR que la cookie PHPSESSID tengui lifetime=0
                            $sessionName = session_name();
                            $sessionId = session_id();
                            setcookie($sessionName, $sessionId, 0, '/', '', false, true);
                            
                            error_log("DEBUG: Cookies temporals i marcador creats per a " . $ok['nickname']);
                        }

                        //Redirigir a la pàgina principal
                        header('Location: ../../index.php');
                        exit;
                    } else {
                        $enviatMissatge = '<p class="error">ERROR A L\'INICIAR SESSIÓ.</p>';
                    }
                } catch (PDOException $e) {
                    throw new PDOException('Error a l\'iniciar sessió' . $e->getMessage());
                }
            }
        }
    }
    require __DIR__ . '/../view/vista.login.php';
    exit;
}

//Metode per iniciar sessió
if ($action === 'login'){
    iniciarSessio();
}
