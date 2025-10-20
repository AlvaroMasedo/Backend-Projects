<?php
declare(strict_types=1);

require __DIR__   . '/../config/db_connection.php';
require __DIR__ . '/../models/pdo-afegir.php';
/*require __DIR__ . '/../models/pdo-modificar.php';
require __DIR__ . '/../models/pdo-eliminar.php';
require __DIR__ . '/../models/pdo-consultar.php';*/

//Obtenir l'acció des de la URL
$action = $_GET['action'] ?? '';

//Metode per gestionar l'afegir article
if ($action === 'afegir'){
    // variables per a la vista (eviten notices)
    $dni = $nom = $cos = '';
    $errorDni = $errorNom = '';
    $enviatMissatge = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //Obtenir dades del formulari
        $dni = trim($_POST['dni'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $cos = trim($_POST['cos'] ?? '');

        // Instanciar el model i afegir
        $afegir = new afegirArticle($conn);

        if (empty($dni) || empty($nom) || empty($cos)) {
            $enviatMissatge = '<p class="error">TOTS ELS CAMPS SÓN OBLIGATORIS.</p>';
        } else if (!preg_match('/^\d{8}[-\s]?[A-Za-z]$/', $dni)) {
            $errorDni = '<p class="error">EL FORMAT DEL DNI NO ÉS VÀLID.</p>';
        } else if (!preg_match('/^[A-Za-zÀ-ÿ\s]{2,50}$/u', $nom)) {
            $errorNom = '<p class="error">EL NOM NOMÉS POT CONTENIR LLETRES I ESPAIS (2-50 CARÀCTERS).</p>';
        } else {
            $ok = $afegir->afegir($dni, $nom, $cos);

            if ($ok) {
                $enviatMissatge = '<p class="success">ARTICLE AFEGIT CORRECTAMENT.</p>';
            } else {
                $enviatMissatge = '<p class="error">ERROR EN AFEGIR UN ARTICLE.</p>';
            } 
        }
    }

    require __DIR__ . '/../views/vista_afegir.php';
    exit;
}

// Si no es 'afegir', tornar a l'índex
header('Location: ../index.php');
exit;
        
    


