<?php
declare(strict_types=1);

require __DIR__   . '/../../config/db_connection.php';
require __DIR__ . '/../models/pdo-afegir.php';
require __DIR__ . '/../models/pdo-eliminar.php';
require __DIR__ . '/../models/pdo-consultar.php';
require __DIR__ . '/../models/pdo-modificar.php';


//Obtenir l'acció des de la URL
$action = $_GET['action'] ?? '';

//Declaració de variables
$dni = $nom = $cos = '';
$errorDni = $errorNom = $errorId ='';
$enviatMissatge = '';
$resultats = []; 

//Metode per gestionar l'afegir article
if ($action === 'afegir'){

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //Obtenir dades del formulari
        $dni = trim($_POST['dni'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $cos = trim($_POST['cos'] ?? '');

        // Instanciar el model i afegir
        $afegir = new PdoAfegir($conn);

    
        if (empty($dni) || empty($nom) || empty($cos)) {        
            $enviatMissatge = '<p class="error">TOTS ELS CAMPS SÓN OBLIGATORIS.</p>';
        } else if (!preg_match('/^\d{8}[-\s]?[A-Za-z]$/', $dni)) {
            $errorDni = '<p class="error">EL FORMAT DEL DNI NO ÉS VÀLID.</p>';
        } else if (!preg_match('/^[A-Za-zÀ-ÿ\s]{2,50}$/u', $nom)) {
            $errorNom = '<p class="error">EL NOM NOMÉS POT CONTENIR LLETRES I ESPAIS (2-50 CARÀCTERS).</p>';
        } else {
            try{
                $ok = $afegir->afegir($dni, $nom, $cos);

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


//Metode per gestionar l'eliminar article
if ($action === 'eliminar'){
    
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        //Obtenir dades del formulari
        $id = trim($_POST['id'] ?? '');

        // Instanciar el model i eliminar
        $eliminar = new PdoEliminar($conn);
        if (empty($id)){
            $enviatMissatge = '<p class="error">EL CAMP ID ÉS OBLIGATORI PER CERCAR.</p>';
        } else if (!preg_match('/^\d+$/', $id)) {
            $errorId = '<p class="error">EL FORMAT DE L\'ID NO ÉS VÀLID.</p>';
        } else if(!$eliminar->existeixId($id)) {
            $errorId = '<p class="error">L\'ID INTRODUÏT NO EXISTEIX A LA BASE DE DADES.</p>';
        }else {
            try{
                $ok = $eliminar->eliminar($id);

                if ($ok) {
                    $enviatMissatge = '<p class="success">ARTICLE ELIMINAT CORRECTAMENT.</p>';
                } else {
                    $enviatMissatge = '<p class="error">ERROR EN ELIMINAR L\'ARTICLE. COMPROVA QUE EL DNI ÉS CORRECTE.</p>';
                } 
            } catch (PDOException $e) {
                throw new PDOException('Error a l\'eliminar l\'article: ' . $e->getMessage());
            }
        }
    }

    require __DIR__ . '/../views/vista_eliminar.php';
    exit;
}

//Metode per gestionar el consultar article
if ($action === 'consultar'){
    
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        //Obtenir dades del formulari
        $dni = trim($_POST['dni'] ?? '');

        // Instanciar el model i consultar
        $consultar = new PdoConsultar($conn);
        if (empty($dni)){
            $errorDni = '<p class="error">EL CAMP DNI ÉS OBLIGATORI PER CERCAR.</p>';
        } else if (!preg_match('/^\d{8}?[A-Za-z]$/', $dni)) {
            $errorDni = '<p class="error">EL FORMAT DEL DNI NO ÉS VÀLID.</p>';
        } else if(!$consultar->existeixDNI($dni)) {
            $errorDni = '<p class="error">EL DNI INTRODUÏT NO EXISTEIX A LA BASE DE DADES.</p>';
        }else {
            try{
                $resultats = $consultar->consultar($dni);

            } catch (PDOException $e) {
                throw new PDOException('Error a l\'consultar l\'article: ' . $e->getMessage());
            }
        }
    }

    require __DIR__ . '/../views/vista_consultar.php';
    exit;
}

//Metode per gestionar el modificar article
if ($action === 'modificar'){
    //Obtenir dades del formulari
    $id = trim($_POST['id'] ?? '');

    // Instanciar el model i modificar
    $modificar = new PdoModificar($conn);
    if (empty($id)){
        $enviatMissatge = '<p class="error">EL CAMP ID ÉS OBLIGATORI PER CERCAR.</p>';
    } else if (!preg_match('/^\d+$/', $id)) {
        $errorId = '<p class="error">EL FORMAT DE L\'ID NO ÉS VÀLID.</p>';
    } else if(!$modificar->existeixId((int)$id)) {
        $errorId = '<p class="error">L\'ID INTRODUÏT NO EXISTEIX A LA BASE DE DADES.</p>';
    } else {
        $dni = trim($_POST['dni'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $cos = trim($_POST['cos'] ?? '');

        if (empty($dni) || empty($nom) || empty($cos)) {
            $enviatMissatge = '<p class="error">TOTS ELS CAMPS SÓN OBLIGATORIS.</p>';
        } else if (!preg_match('/^\d{8}[-\s]?[A-Za-z]$/', $dni)) {
            $errorDni = '<p class="error">EL FORMAT DEL DNI NO ÉS VÀLID.</p>';
        } else if (!preg_match('/^[A-Za-zÀ-ÿ\s]{2,50}$/u', $nom)) {
            $errorNom = '<p class="error">EL NOM NOMÉS POT CONTENIR LLETRES I ESPAIS (2-50 CARÀCTERS).</p>';
        } else {
            try{
                $ok = $modificar->modificar((int)$id, $dni, $nom, $cos);

                if ($ok) {
                    $enviatMissatge = '<p class="success">ARTICLE MODIFICAT CORRECTAMENT.</p>';
                } else {
                    $enviatMissatge = '<p class="error">ERROR EN MODIFICAR L\'ARTICLE. COMPROVA QUE EL DNI ÉS CORRECTE.</p>';
                } 
            } catch (PDOException $e) {
                throw new PDOException('Error a l\'modificar l\'article: ' . $e->getMessage());
            }
        }
    }
    require __DIR__ . '/../views/vista_modificar.php';
    exit;
}


