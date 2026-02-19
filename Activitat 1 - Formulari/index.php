<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Incloem les classes de PHPMailer
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

//Declaració de variables
$nom = "";
$email = "";
$missatge = "";
$errorNom = "";
$errorEmail = "";
$errorMissatge = "";    

// validem que s'hagin enviat les dades mitjançant el mètode POST
if (isset($_POST['btn-enviar'])) {
    $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $missatge = isset($_POST['missatge']) ? trim($_POST['missatge']) : '';

    //validem que els camps no estiguin buits
    if (empty($nom)) {
        $errorNom = "<p>El camp Nom no pot estar buit.</p>";
    }

    if (empty($email)) {    
        $errorEmail = "<p>El camp Email no pot estar buit.</p>";
    }

    if (empty($missatge)) {
        $errorMissatge = "<p>El camp Missatge no pot estar buit.</p>";
    }

    if ($nom != '' && $email != '' && $missatge != '') {
        $mail = new PHPMailer(true); 
        try {
            $mail->SMTPOptions = array(
                'ssl' => array(    
                    'verify_peer' => false,  
                    'verify_peer_name' => false, 
                    'allow_self_signed' => true  
                )
            );

            $mail->SMTPDebug = 0; 
            $mail->isSMTP();
            $mail->Host       = "smtp.gmail.com";
            $mail->SMTPAuth   = true;
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
    
            $mail->Username   = 'a.masedo@sapalomera.cat'; 
            $mail->Password   = 'febx klgw ptfw lfsb'; 
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom($email, $nom); 
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Missatge de $nom";
            $mail->Body    = nl2br($missatge);
            $mail->AltBody = $missatge;


            $mail->send();

        } catch (Exception $e) {
            echo "<script>alert('Error al enviar: {$mail->ErrorInfo}');</script>";
        }

        $enviatMissatge = "<h3> Missatge enviat correctament!</h3>";
    }
}

//incluim la vista del formulari
include_once 'vista.php';
