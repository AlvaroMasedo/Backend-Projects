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
    $errorDni = $errorNom = $errorCos = '';
    $enviatMissatge = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //Obtenir dades del formulari
        $dni = trim($_POST['dni'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $cos = trim($_POST['cos'] ?? '');

        // Instanciar el model i afegir
        $afegir = new afegirArticle($conn);
        $ok = $afegir->afegir($dni, $nom, $cos);

        if ($ok) {
            $enviatMissatge = '<p>Article afegit correctament.</p>';
            // opcional: netejar camps
            $dni = $nom = $cos = '';
        } else {
            $enviatMissatge = '<p>Error en afegir article.</p>';
        }
    }

    require __DIR__ . '/../views/vista_afegir.php';
    exit;
}

// Si no es 'afegir', tornar a l'índex
header('Location: ../index.php');
exit;
        
    


