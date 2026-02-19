<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

//Álvaro Masedo Pérez
//Controlador per gestionar usuaris: modificació de perfil i administració

//Carregar connexió a la BD i models
require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../model/model.usuari.php';
require_once __DIR__ . '/../model/model.articles.php';
require_once __DIR__ . '/../../lib/auth.php';

// Obtenir l'acció a realitzar (si n'hi ha)
$action = $_GET['action'] ?? '';

// Instanciar models
$pdoUsers = new ModelUsers($conn);
$pdoArticles = new ModelArticles($conn);

// ============================================================================
// ACCIÓ: modificar perfil de l'usuari actual
// ============================================================================
if ($action === 'modificar') {
    // Assegurar que l'usuari està loguejat
    requerir_inici_sessio_o_redirigir('../view/vista.login.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtenir dades del formulari
        $nickname_actual = $_SESSION['usuari']['nickname'];
        $nickname_nou = trim($_POST['nickname'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $cognom = trim($_POST['cognom'] ?? '');
        $contrasenyaActual = trim($_POST['contrasenya_actual'] ?? '');
        $contrasenyaActualHash = hash('sha256', $contrasenyaActual);
        $novaContrasenya = trim($_POST['nova_contrasenya'] ?? '');
        $confirmaNovaContrasenya = trim($_POST['confirma_nova_contrasenya'] ?? '');

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
            // Validar canvi de contrasenya si s'intent fer
            $errorCanviContrasenya = false;

            // Validacions de les tres condicions per canviar contrasenya
            if (!empty($contrasenyaActual) && ((empty($novaContrasenya) || empty($confirmaNovaContrasenya)))) {
                $missatge = '<p class="error">PER CANVIAR LA CONTRASENYA, CAL INTRODUIR TOTES LES DADES RELACIONADES.</p>';
                $errorCanviContrasenya = true;
            } else if (empty($contrasenyaActual) && (!empty($novaContrasenya) || !empty($confirmaNovaContrasenya))) {
                $missatge = '<p class="error">CAL INTRODUIR LA CONTRASENYA ACTUAL PER CANVIAR-LA.</p>';
                $errorCanviContrasenya = true;
            } else if (!empty($contrasenyaActual) && !empty($novaContrasenya) && !empty($confirmaNovaContrasenya)) {
                // Verificar contrasenya actual
                if (!$pdoUsers->verificarContrasenya($nickname_actual, $contrasenyaActualHash)) {
                    $errorContrasenyaActual = '<p class="error">LA CONTRASENYA ACTUAL ÉS INCORRECTA.</p>';
                    $errorCanviContrasenya = true;
                } else if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])([A-Za-z\d$@$!%*?&]|[^ ]){12,20}$/u', $novaContrasenya)) {
                    $errorNovaContrasenya = '<p class="error">LA NOVA CONTRASENYA NO COMPLEIX ELS REQUISITS MÍNIMS.</p>';
                    $errorCanviContrasenya = true;
                } else if ($novaContrasenya !== $confirmaNovaContrasenya) {
                    $errorConfirmaNovaContrasenya = '<p class="error">LA NOVA CONTRASENYA I LA SEVA CONFIRMACIÓ NO COINCIDEIXEN.</p>';
                    $errorCanviContrasenya = true;
                }
            }

            // Si no hi ha error en el canvi de contrasenya, procedir amb la modificació
            if (!$errorCanviContrasenya) {
                // Actualitzar la contrasenya si es va intentar canviar
                if (!empty($contrasenyaActual) && !empty($novaContrasenya) && !empty($confirmaNovaContrasenya)) {
                    try {
                        $pdoUsers->actualitzarContrasenya($nickname_actual, hash('sha256', $novaContrasenya));
                    } catch (PDOException $e) {
                        throw new PDOException('Error a l\'actualització de la contrasenya: ' . $e->getMessage());
                    }
                }

                // Si cognom està buit, el posem a null
                if (empty($cognom)) {
                    $cognom = null;
                }
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
                        // Obtener la foto antigua del usuario (si existe)
                        $usuari_actual = $pdoUsers->obtenirPerNickname($nickname_actual);
                        $foto_antigua = $usuari_actual['imatge_perfil'] ?? null;

                        // Generar nom únic per l'arxiu
                        $extension = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
                        $imatge_perfil = $nickname_nou . '_' . time() . '.' . $extension;
                        $ruta_destino = __DIR__ . '/../../uploads/img/fotos_perfil/' . $imatge_perfil;

                        // Moure arxiu pujat
                        if (!move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $ruta_destino)) {
                            $errorFoto = '<p class="error">ERROR EN PUJAR LA FOTO DE PERFIL.</p>';
                            $imatge_perfil = null;
                        } else {
                            // Eliminar foto antiga si existeix i no és la predeterminada
                            if ($foto_antigua && !empty($foto_antigua)) {
                                $ruta_foto_antigua = __DIR__ . '/../../uploads/img/fotos_perfil/' . $foto_antigua;
                                if (file_exists($ruta_foto_antigua) && is_file($ruta_foto_antigua)) {
                                    @unlink($ruta_foto_antigua);
                                    error_log("Foto antiga eliminada: " . $ruta_foto_antigua);
                                }
                            }
                        }
                    }
                }

                // Si no hay error en la foto, proceder con la modificación
                if (!isset($errorFoto)) {
                    try {
                        $ok = $pdoUsers->modificar($nickname_actual, $nickname_nou, $nom, $cognom, $imatge_perfil);

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

}

// ============================================================================
// ACCIÓ: llistar usuaris (només admin)
// ============================================================================
if ($action === 'llistar' || empty($action)) {
    // Verificar que l'usuari sigui administrador
    if (!isset($_SESSION['usuari']) || $_SESSION['usuari']['administrador'] != 1) {
        header('Location: ../../index.php');
        exit;
    }

    // Obtenir tots els usuaris per mostrar a la vista
    $usuaris = $pdoUsers->obtenirTots();

    require __DIR__ . '/../view/vista.usuaris.php';
    exit;
}

// ============================================================================
// ACCIÓ: eliminar usuari (només admin)
// ============================================================================
if ($action === 'eliminar') {
    // Assegurar que l'usuari està loguejat i és admin
    requerir_inici_sessio_o_redirigir('../view/vista.login.php');

    if (!usuari_es_admin()) {
        header('Location: ../view/vista.usuaris.php?error=forbidden');
        exit;
    }

    $nickname = $_GET['nickname'] ?? '';

    if (empty($nickname)) {
        header('Location: ../view/vista.usuaris.php?error=invalid');
        exit;
    }

    // No permitir que el admin s'elimini a si mateix
    if ($nickname === $_SESSION['usuari']['nickname']) {
        header('Location: ../view/vista.usuaris.php?error=self_delete');
        exit;
    }

    try {
        // 1. Obtenir la informació de l'usuari per saber si té foto de perfil
        $usuari = $pdoUsers->obtenirPerNickname($nickname);

        if ($usuari === null) {
            header('Location: ../view/vista.usuaris.php?error=notfound');
            exit;
        }

        // 2. Eliminar la foto de perfil si existeix
        if (!empty($usuari['imatge_perfil'])) {
            $rutaFoto = __DIR__ . '/../../uploads/img/fotos_perfil/' . $usuari['imatge_perfil'];
            if (file_exists($rutaFoto)) {
                unlink($rutaFoto);
            }
        }

        // 3. Eliminar tots els articles de l'usuari
        $pdoArticles->eliminarPerAutor($nickname);

        // 4. Eliminar l'usuari de la BD
        $ok = $pdoUsers->eliminar($nickname);

        if ($ok) {
            header('Location: ../view/vista.usuaris.php?deleted=1');
            exit;
        } else {
            header('Location: ../view/vista.usuaris.php?error=delete');
            exit;
        }
    } catch (PDOException $e) {
        throw new PDOException('Error a l\'eliminació de l\'usuari: ' . $e->getMessage());
    }
}
