<?php 
declare(strict_types=1);
//Alvaro Masedo Pérez

require __DIR__ .'/../../config/db_connection.php';
require __DIR__ .'/../model/pdo.registrarUser.php';
require __DIR__ .'/../model/pdo.consultarUser.php';

//Obtenir l'acció des de la URL
$action = $_GET['action'] ?? '';

//Declaració de variables
$nickname = $nom = $cognom = $email = $contrasenya = $repContrasenya = '';
$errorNickname = $errorNom = $errorCognom = $errorEmail = $errorContrasenya = $errorRepContrasenya = '';
$enviatMissatge = '';

//Funció per registrar un Usuari
function registrarUsuari(){
    global $conn;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //Obtenir dades del formulari
        $nickname = trim($_POST['nickname'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $cognom = trim($_POST['cognom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contrasenya = trim($_POST['contrasenya'] ?? '');
        $repContrasenya = trim($_POST['repContrasenya'] ?? '');
        $contrasenya_encriptada = hash('sha256', $contrasenya);

        // Instanciar els models i afegir
        $registrar = new PdoRegistrar($conn);
        $consultar = new PdoConsultarUser($conn);
    
        // Si qualsevol camp està buit donarà error
        if (empty($nickname) || empty($nom) || empty($email) || empty($contrasenya) || empty($repContrasenya)) {        
            $enviatMissatge = '<p class="error">TOTS ELS CAMPS SÓN OBLIGATORIS.</p>';

        // Si el nom no té concordança amb el regex donarà error        
        } else if (!preg_match('/^[A-Za-zÀ-ÿ\s]{2,25}$/u', $nom)) {
            $errorNom = '<p class="error">EL NOM NOMÉS POT CONTENIR LLETRES I ESPAIS (2-25 CARÀCTERS).</p>';

        // Si el cognom no és buit i no té concordança amb el regex donarà error
        } else if (!empty($cognom) && !preg_match('/^[A-Za-zÀ-ÿ\s]{2,25}$/u', $cognom)) {
            $errorCognom = '<p class="error">EL COGNOM NOMÉS POT CONTENIR LLETRES I ESPAIS (2-25 CARÀCTERS).</p>';

        // Si el nickname no té concordança amb el regex donarà error
        } else if (!preg_match('/^[A-Za-z0-9._]{3,15}$/u', $nickname)) {
            $errorNickname = '<p class="error">EL NICKNAME HA DE TENIR ENTRE 3 I 15 CARÀCTERS (LLETRES, NÚMEROS, PUNTS O GUIÓ BAIX).</p>';

        // Si la contrasenya no té concordança amb el regex donarà error
        } else if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])([A-Za-z\d$@$!%*?&]|[^ ]){12,20}$/u', $contrasenya)){
            $errorContrasenya = '<p class="error">LA CONTRASENYA NO COMPLEIX ELS REQUISITS MÍNIMS.</p>';

        // Si la contrasenya i la repetició no coincideixen donarà error
        } else if ($contrasenya !== $repContrasenya){
            $errorRepContrasenya = '<p class="error">LES CONTRASENYES NO COINCIDEIXEN.</p>';

        // Si hi ha 1 email o més, dona error
        } else if ($consultar->existeixEmail($email)) {
            $errorEmail = '<p class="error">JA HI HA UNA COMPTA AMB AQUEST EMAIL.</p>';

        // Si el nickname ja existeix dona error
        } else if ($consultar->existeixNickname($nickname)) {
            $errorNickname = '<p class="error">JA HI HA UNA COMPTA AMB AQUEST NICKNAME.</p>';

        // Si tot és correcte, registrar usuari
        } else {
            try {
                $ok = $registrar->registrar($nickname, $nom, $cognom, $email, $contrasenya_encriptada, 0);

                if ($ok) {
                    $enviatMissatge = '<p class="success">T\'HAS REGISTRAT CORRECTAMENT.</p>';
                } else {
                    $enviatMissatge = '<p class="error">ERROR AL REGISTRAR-SE.</p>';
                }
            } catch (PDOException $e) {
                throw new PDOException('Error a l\'afegir l\'usuari: ' . $e->getMessage());
            }
        }
    }
    require __DIR__ . '/../view/vista.signup.php';
    exit;
}

//Metode per registrar usuari
if ($action === 'registre'){
    registrarUsuari();
}

?>