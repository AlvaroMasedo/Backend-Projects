<!--Álvaro Masedo Pérez-->
<?php 
declare(strict_types=1);

require __DIR__ .'../../config/db_connection.php';
require __DIR__ .'../model/pdo.registrarUser.php';
require __DIR__ .'../model/pdo.consultarUser.php';

//Obtenir l'acció des de la URL
$action = $_GET['action'] ?? '';

//Declaració de variables
$nickname = $nom = $cognom = $email = $contrasenya = $repContrasenya = '';
$errorNickname = $errorNom = $errorCognom = $errorEmail = $errorContrasenya = $errorRepContrasenya = '';
$enviatMissatge = '';

//Metode per registrar usuari
if ($action === 'registre'){

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //Obtenir dades del formulari
        $nickname = trim($_POST['nickname'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $cognom = trim($_POST['cognom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contrasenya = trim($_POST['contrasenya'] ?? '');
        $repContrasenya = trim($_POST['repContrasenya'] ?? '');

        // Instanciar el model i afegir
        $registrar = new PdoRegistrar($conn);
    
        //Si hi ha 1 email o més, dona error
        if (existeixEmail($email) >= 1){
            $errorEmail = '<p class="error">JA HI HA UNA COMPTA AMB AQUEST EMAIL</p>'
        } else if (existeixNickname($nickname) >= 1){
            $errorNickname = '<p class="error"< JA HI HA UNA COMPTA AMB AQUEST NICKNAME</p>'
        } else if (empty($nickname) || empty($nom) || empty($email) || empty($contrasenya) || empty($repContrasenya)) {        
            $enviatMissatge = '<p class="error">TOTS ELS CAMPS SÓN OBLIGATORIS.</p>';
        } else if (!preg_match('/^[A-Za-zÀ-ÿ\s]{2,25}$/u', $cognom)) {
            $errorNom = '<p class="error">EL COGNOM NOMÉS POT CONTENIR LLETRES I ESPAIS (2-25 CARÀCTERS).</p>';
        } else if (!preg_match('/^[A-Za-zÀ-ÿ\s]{2,25}$/u', $nom)) {
            $errorNom = '<p class="error">EL NOM NOMÉS POT CONTENIR LLETRES I ESPAIS (2-25 CARÀCTERS).</p>';
        } else if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])([A-Za-z\d$@$!%*?&]|[^ ]){12,20}$/u', $contrasenya)){
            $errorContrasenya = '<p class="error"> LA CONTRASENYA NO COMPLEIX ELS REQUISITS MÍNIMS </p>';
        }else if ($contrasenya != $repContrasenya){
            $errorRepContrasenya = '<p class="error"> LES CONTRASENYES NO COINCIDEIXEN</p>';
        } else {
            try{
                $ok = $registrar->registrar($dni, $nom, $cos);

                if ($ok) {
                    $enviatMissatge = '<p class="success">ARTICLE AFEGIT CORRECTAMENT.</p>';
                } else {
                    $enviatMissatge = '<p class="error">ERROR EN AFEGIR UN ARTICLE.</p>';
                } 
            } catch (PDOException $e) {
                throw new PDOException('Error a l\'afegir l\'article: ' . $e->getMessage());
            }
        }
    }

    require __DIR__ . '/../views/vista_afegir.php';
    exit;
}


?>