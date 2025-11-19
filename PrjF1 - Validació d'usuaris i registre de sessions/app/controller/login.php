<?php 
declare(strict_types=1);
//Alvaro Masedo Pérez

require __DIR__ .'/../../config/db_connection.php';
require __DIR__ .'/../model/pdo.consultarUser.php';
require __DIR__ .'/../model/pdo.loginUser.php';

//Obtenir l'acció des de la URL
$action = $_GET['action'] ?? '';

//Declaració de variables
$nickname = $nom = $cognom = $email = $contrasenya = $repContrasenya = '';
$errorNickname = $errorNom = $errorCognom = $errorEmail = $errorContrasenya = $errorRepContrasenya = '';
$enviatMissatge = '';

//Funció per Iniciar Sessió amb un Usuari
function iniciarSessio(){
    global $conn;
    // Iniciar sessió per fer persistents els intents i dades de reCAPTCHA
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $contadorIntents = $_SESSION['contadorIntents'] ?? 0;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        //Obtenir dades del formulari
        $email = trim($_POST['email'] ?? '');
        $contrasenya = trim($_POST['contrasenya'] ?? '');
        $contrasenya_encriptada = hash('sha256', $contrasenya);

        // Instanciar els models i afegir
        $login = new PdoLogin($conn);
        $consultar = new PdoConsultarUser($conn);

        // Si qualsevol camp està buit donarà error
        if (empty($email) || empty($contrasenya)) {        
            $enviatMissatge = '<p class="error">TOTS ELS CAMPS SÓN OBLIGATORIS.</p>';

        // Si hi ha 1 email o més, dona error
        } else if (!$consultar->existeixEmail($email)) {
            $errorEmail = '<p class="error">NO HI HA CAP COMPTA REGISTRADA AMB AQUEST EMAIL</p>';

        // Si la contrasenya es incorrecta dona error
        } else if (!$consultar->comprobarContrasenya($contrasenya_encriptada, $email)) {
            $errorContrasenya = '<p class="error">CONTRASENYA INCORRECTA, TORNA A PROVAR</p>';
            // Incrementar i guardar el contador d'intents a la sessió
            $contadorIntents++;
            $_SESSION['contadorIntents'] = $contadorIntents;

            // Si hem superat el nombre d'intents, validar reCAPTCHA
            if ($contadorIntents >= 3) {
                if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
                    $enviatMissatge = '<p class="error">SIUSPLAU, COMPLETA EL RECAPTCHA.</p>';
                } else {
                    $key = "6LfP6hEsAAAAAE3pwc5I7cu_1OM3wW_PPT0DXyra"; // secret key
                    $resposta = $_POST['g-recaptcha-response'];

                    $url = "https://www.google.com/recaptcha/api/siteverify";
                    $data = [
                        'secret' => $key,
                        'response' => $resposta
                    ];

                    $options = [
                        'http' => [
                            'method' => 'POST',
                            'header' => 'Content-type: application/x-www-form-urlencoded\\r\\n',
                            'content' => http_build_query($data)
                        ]
                    ];
                    $context = stream_context_create($options);
                    $resultat = @file_get_contents($url, false, $context);
                    $resultatJson = $resultat ? json_decode($resultat, true) : null;

                    if (empty($resultatJson) || empty($resultatJson['success'])) {
                        $enviatMissatge = '<p class="error">FALLA AL VERIFICAR EL RECAPTCHA. SIUSPLAU, TORNA-HO A PROVAR.</p>';
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
                    $key = "6LfP6hEsAAAAAE3pwc5I7cu_1OM3wW_PPT0DXyra"; // secret key
                    $resposta = $_POST['g-recaptcha-response'];

                    $url = "https://www.google.com/recaptcha/api/siteverify";
                    $data = [
                        'secret' => $key,
                        'response' => $resposta
                    ];

                    $options = [
                        'http' => [
                            'method' => 'POST',
                            'header' => 'Content-type: application/x-www-form-urlencoded\r\n',
                            'content' => http_build_query($data)
                        ]
                    ];
                    $context = stream_context_create($options);
                    $resultat = @file_get_contents($url, false, $context);
                    $resultatJson = $resultat ? json_decode($resultat, true) : null;

                    if (empty($resultatJson) || empty($resultatJson['success'])) {
                        $enviatMissatge = '<p class="error">FALLA AL VERIFICAR EL RECAPTCHA. SIUSPLAU, TORNA-HO A PROVAR.</p>';
                    }
                }
            }

            // Si no hi ha missatge d'error (el reCAPTCHA s'ha passat o no era necessari), procedim a fer login
            if (empty($enviatMissatge)) {
                try {
                    $ok = $login->login($email, $contrasenya_encriptada);

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
                            'administrador' => $ok['administrador']
                        ];

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

?>