<?php

declare(strict_types=1);
//Álvaro Masedo Pérez
//Controlador per gestionar la modificació de usuaris

//Carregar connexió a la BD i model
require_once __DIR__ . '/../../config/db_connection.php';

// Assegurar que la sessió està iniciada i gestionar logout
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../model/model.usuari.php';
require_once __DIR__ . '/../../lib/auth.php';

// Obtenir l'acció a realitzar (si n'hi ha)
$action = $_GET['action'] ?? '';

// Decidir quins articles carregar segons si l'usuari és administrador o no
$pdoUsers = new ModelUsers($conn);

// Modificar perfil d'usuari
if ($action === 'modificar') {
    // Assegurar que l'usuari està loguejat
    requerir_inici_sessio_o_redirigir('../view/vista.login.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtenir dades del formulari
        $nickname_actual = $_SESSION['usuari']['nickname'];
        $nickname_nou = trim($_POST['nickname'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $cognom = trim($_POST['cognom'] ?? '');

        // Instanciar el model
        $modificar = new ModelUsers($conn);

        // Validacions
        if (empty($nickname_nou) || empty($nom)) {
            $missatge = '<p class="error">EL NICKNAME I EL NOM SÓN OBLIGATORIS.</p>';
        } else if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $nickname_nou)) {
            $errorNickname = '<p class="error">EL NICKNAME NOMÉS POT CONTENIR LLETRES, NÚMEROS I GUIÓ BAIX (3-20 CARÀCTERS).</p>';
        } else if ($nickname_nou !== $nickname_actual && $pdoUsers->existeixNickname($nickname_nou)) {
            $errorNickname = '<p class="error">AQUEST NICKNAME JA EXISTEIX.</p>';
        } else if (!preg_match('/^[A-Za-zÀ-ÿ\s]{2,50}$/u', $nom)) {
            $errorNom = '<p class="error">EL NOM NOMÉS POT CONTENIR LLETRES I ESPAIS (2-50 CARÀCTERS).</p>';
        } else if (!empty($cognom) && !preg_match('/^[A-Za-zÀ-ÿ\s]{2,50}$/u', $cognom)) {
            $errorCognom = '<p class="error">EL COGNOM NOMÉS POT CONTENIR LLETRES I ESPAIS (2-50 CARÀCTERS).</p>';
        } else {
            // Si cognom està buit, el posem a null
            if (empty($cognom)) {
                $cognom = null;
            }

            // Gestionar la foto de perfil (opcional)
            $imatge_perfil = null;
            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                // Validar tipus d'arxiu
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $file_type = $_FILES['foto_perfil']['type'];

                if (!in_array($file_type, $allowed_types)) {
                    $errorFoto = '<p class="error">TIPUS D\'ARXIU NO PERMÈS (NOMÉS JPEG, PNG, GIF, WEBP).</p>';
                } else if ($_FILES['foto_perfil']['size'] > 5 * 1024 * 1024) {
                    $errorFoto = '<p class="error">LA FOTO NO POT SUPERAR ELS 5MB.</p>';
                } else {
                    // Generar nom únic per l'arxiu
                    $extension = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
                    $imatge_perfil = $nickname_nou . '_' . time() . '.' . $extension;
                    $ruta_destino = __DIR__ . '/../../uploads/img/fotos_perfil/' . $imatge_perfil;

                    // Moure arxiu pujat
                    if (!move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $ruta_destino)) {
                        $errorFoto = '<p class="error">ERROR EN PUJAR LA FOTO DE PERFIL.</p>';
                        $imatge_perfil = null;
                    }
                }
            }

            // Si no hay error en la foto, proceder con la modificación
            if (!isset($errorFoto)) {
                try {
                    $ok = $modificar->modificar($nickname_actual, $nickname_nou, $nom, $cognom, $imatge_perfil);

                    if ($ok) {
                        // Actualitzar la sessió amb les noves dades
                        $_SESSION['usuari']['nickname'] = $nickname_nou;
                        $_SESSION['usuari']['nom'] = $nom;
                        $_SESSION['usuari']['cognom'] = $cognom;
                        if ($imatge_perfil !== null) {
                            $_SESSION['usuari']['imatge_perfil'] = $imatge_perfil;
                        }

                        // Redirigir al perfil amb missatge d'èxit
                        header('Location: ../view/vista.perfil.php?success=1');
                        exit;
                    } else {
                        $missatge = '<p class="error">ERROR EN MODIFICAR EL PERFIL.</p>';
                    }
                } catch (PDOException $e) {
                    throw new PDOException('Error a la modificació del perfil: ' . $e->getMessage());
                }
            }
        }
    }

    require __DIR__ . '/../view/vista.modificarPerfil.php';
    exit;
}
