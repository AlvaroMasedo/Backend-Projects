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
function iniciarSesio(){
    global $conn;

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

        // Si tot és correcte, iniciar sesió
        } else {
            try {
                $ok = $login->login($email, $contrasenya_encriptada);

                if ($ok) {
                     
                    //Iniciem la sessió
                    session_start();

                    //Regenerem l'id de sessió per seguretat
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
    require __DIR__ . '/../view/vista.login.php';
    exit;
}


//Metode per iniciar sessió
if ($action === 'login'){
    iniciarSesio();
}

?>