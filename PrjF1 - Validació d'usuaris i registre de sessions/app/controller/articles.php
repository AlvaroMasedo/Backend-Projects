<?php
declare(strict_types=1);
//Álvaro Masedo Pérez
//Controlador per gestionar la lògica dels articles
//S 'usa per obtenir els articles

//Carregar connexió a la BD i model
require_once __DIR__ . '/../../config/db_connection.php';

// Assegurar que la sessió està iniciada i gestionar logout
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../model/pdo.articles.php';

// Obtenir l'acció a realitzar (si n'hi ha)
$action = $_GET['action'] ?? '';

// Decidir quins articles carregar segons si l'usuari és administrador o no
$pdoArticles = new PdoArticles($conn);

// Identifiquem el script actual (per saber si som a la vista de modificació o eliminació)
$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? ($_SERVER['SCRIPT_FILENAME'] ?? ''));

// Si s'ha cridat la vista de modificació amb un id, carreguem l'article perquè la vista
// pugui pré-omplir els camps. Això ocorre quan l'usuari obre `vista.modificarArticle.php?id=...`.
if ($currentScript === 'vista.modificarArticle.php' && isset($_GET['id'])) {
	$idToEdit = (int) $_GET['id'];
	$articleToEdit = $pdoArticles->obtenirPerId($idToEdit);
	if ($articleToEdit !== null) {
		// Permisos: si l'usuari està loguejat i és admin o és l'autor, deixem editar
		$canEdit = false;
		if (isset($_SESSION['usuari'])) {
			$isAdmin = (int) ($_SESSION['usuari']['administrador'] ?? 0);
			$nickname = $_SESSION['usuari']['nickname'] ?? null;
			if ($isAdmin === 1 || $articleToEdit['autor'] === $nickname) {
				$canEdit = true;
			}
		}

		if ($canEdit) {
			// Variables que la vista utilitza per pré-omplir el formulari
			$id = $articleToEdit['id'];
			$nom = $articleToEdit['Nom'];
			$cos = $articleToEdit['Cos'];
		} else {
			$missatge = '<p class="error">No tens permisos per modificar aquest article o has d\'iniciar sessió.</p>';
		}
	} else {
		$missatge = '<p class="error">Article no trobat.</p>';
	}
}

// Si s'ha cridat la vista d'eliminació amb un id, carreguem l'article perquè la vista
// pugui pré-omplir el formulari d'eliminació i fer comprovacions de permisos.
if ($currentScript === 'vista.eliminarArticle.php' && isset($_GET['id'])) {
	$idToDelete = (int) $_GET['id'];
	$articleToDelete = $pdoArticles->obtenirPerId($idToDelete);
	if ($articleToDelete === null) {
		$missatge = '<p class="error">Article no trobat.</p>';
	} else {
		// comprovar permisos: admin o autor
		$canDelete = false;
		if (isset($_SESSION['usuari'])) {
			$isAdmin = (int) ($_SESSION['usuari']['administrador'] ?? 0);
			$nickname = $_SESSION['usuari']['nickname'] ?? null;
			if ($isAdmin === 1 || $articleToDelete['autor'] === $nickname) {
				$canDelete = true;
			}
		}
		if (! $canDelete) {
			$missatge = '<p class="error">No tens permisos per eliminar aquest article.</p>';
		} else {
			$id = $articleToDelete['id'];
		}
	}
}

// Si l'usuari està loguejat, determinem si és administrador
$allArticles = [];
if (isset($_SESSION['usuari'])) {
	$isAdmin = (int) ($_SESSION['usuari']['administrador'] ?? 0);
	if ($isAdmin === 1) {
		// Usuari admin: tots els articles
		$allArticles = $pdoArticles->obtenirTots();
	} else {
		// Usuari normal: només els seus articles (si tenim nickname)
		$nickname = $_SESSION['usuari']['nickname'] ?? null;
		if ($nickname !== null) {
			$allArticles = $pdoArticles->obtenirPerNickname($nickname);
		} else {
			// No tenim nickname; segurament no hauria de passar, ja que si no estás logejat no pots modificar articles
			$allArticles = [];
		}
	}
} else {
	// Usuari anònim: mostrar tots els articles només si som a la pàgina principal
	$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? ($_SERVER['SCRIPT_FILENAME'] ?? ''));
	if ($currentScript === 'index.php') {
		$allArticles = $pdoArticles->obtenirTots();
	} else {
		$allArticles = [];
	}
}

// Nombre total d'articles segons la selecció/privil·legis
$totalArticles = count($allArticles);

// Identifiquem el script actual (per saber si som a la vista d'articles o a l'index)
$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? ($_SERVER['SCRIPT_FILENAME'] ?? ''));

$perPageRaw = $_GET['per_page'] ?? null;
// Si estem a la vista d'articles i l'usuari està loguejat, per defecte volem mostrar TOTS
// els articles assignats (no paginar) a menys que s'especifiqui explícitament per_page
if ($perPageRaw === null && $currentScript === 'vista.articles.php' && isset($_SESSION['usuari'])) {
	$perPageRaw = 'all';
}
if ($perPageRaw === 'all' || (is_numeric($perPageRaw) && (int)$perPageRaw === 0)) {
	// Mostrar tots els articles
	$articlesPerPagina = $totalArticles > 0 ? $totalArticles : 1;
	$perPageMode = 'all';
} else {
	// Valor numèric (o default)
	$articlesPerPagina = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 4;
	if ($articlesPerPagina < 1) {
		$articlesPerPagina = 1;
	}
	if ($articlesPerPagina > 10) {
		$articlesPerPagina = 10;
	}
	$perPageMode = 'paged';
}

$paginaActual = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Calcul pagines (si per_page == all, tindrem 1 pàgina)
$totalPagines = ($articlesPerPagina > 0) ? (int)ceil($totalArticles / $articlesPerPagina) : 1;
if ($perPageMode === 'all') {
	$totalPagines = 1;
	$paginaActual = 1;
}
if ($paginaActual > $totalPagines) {
	$paginaActual = $totalPagines;
}

// Obtenir slice d'articles a mostrar
if ($perPageMode === 'all') {
	$articles = $allArticles;
} else {
	$offset = ($paginaActual - 1) * $articlesPerPagina;
	$articles = array_slice($allArticles, $offset, $articlesPerPagina);
}

// URLs per a controls de paginació (mantenint per_page)
$baseUrl = 'index.php';
$prevPage = max(1, $paginaActual - 1);
$nextPage = min($totalPagines, $paginaActual + 1);
$perPageForUrl = $_GET['per_page'] ?? (string)$articlesPerPagina;
$prevUrl = $baseUrl . '?page=' . $prevPage . '&per_page=' . $perPageForUrl;
$nextUrl = $baseUrl . '?page=' . $nextPage . '&per_page=' . $perPageForUrl;


$autor = $_SESSION['usuari']['nickname'] ?? null;

// Afegir articles 
if ($action === 'afegir'){

	// Assegurar que l'usuari està loguejat abans d'afegir un article
	if ($autor === null) {
		// Redirigir a la pàgina d'inici de sessió
		header('Location: ../view/vista.login.php');
		exit;
	}

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //Obtenir dades del formulari
        $nom = trim($_POST['Nom'] ?? '');
        $cos = trim($_POST['Cos'] ?? '');

        // Instanciar el model i afegir
        $afegir = new PdoArticles($conn);

    
        if ( empty($nom) || empty($cos)) {        
            $missatge = '<p class="error">TOTS ELS CAMPS SÓN OBLIGATORIS.</p>';
        } else if (!preg_match('/^[A-Za-zÀ-ÿ\s]{2,50}$/u', $nom)) {
            $errorNom = '<p class="error">EL NOM NOMÉS POT CONTENIR LLETRES I ESPAIS (2-50 CARÀCTERS).</p>';
        } else {
            try{
                $ok = $afegir->afegir($autor, $nom, $cos);

                if ($ok) {
                    $missatge = '<p class="success">ARTICLE AFEGIT CORRECTAMENT.</p>';
                } else {
                    $missatge = '<p class="error">ERROR EN AFEGIR UN ARTICLE.</p>';
                } 
            } catch (PDOException $e) {
                throw new PDOException('Error a l\'afegir l\'article: ' . $e->getMessage());
            }
        }
    }

    require __DIR__ . '/../view/vista.afegirArticle.php';
    exit;
}

// Modificar articles
if ($action === "modificar"){
	// Assegurar que l'usuari està loguejat abans de modificar un article
	if ($autor === null) {
		// Redirigir a la pàgina d'inici de sessió
		header('Location: ../view/vista.login.php');
		exit;
	}

	if($_SERVER['REQUEST_METHOD'] === 'POST'){
		//Obtenir dades del formulari
		$id = (int)($_POST['id'] ?? 0);
		$nom = trim($_POST['Nom'] ?? '');
		$cos = trim($_POST['Cos'] ?? '');

		//Instanciar el model i modificar
		$modificar = new PdoArticles($conn);

		if ( empty($nom) || empty($cos)) {        
			$missatge = '<p class="error">TOTS ELS CAMPS SÓN OBLIGATORIS.</p>';
		} else if (!preg_match('/^[A-Za-zÀ-ÿ\s]{2,50}$/u', $nom)) {
			$errorNom = '<p class="error">EL NOM NOMÉS POT CONTENIR LLETRES I ESPAIS (2-50 CARÀCTERS).</p>';
		} else {
			try{
				$ok = $modificar->modificar($id, $nom, $cos);

				if ($ok) {
					//Redirigir a la vista d'articles després de modificar
					header('Location: ../view/vista.articles.php');
					exit;
				} else {
					$missatge = '<p class="error">ERROR EN MODIFICAR L\'ARTICLE.</p>';
				} 
			} catch (PDOException $e) {
				throw new PDOException('Error a la modificació de l\'article: ' . $e->getMessage());
			}
		}
	}
	require __DIR__ . '/../view/vista.modificarArticle.php';
	exit;
}

//Eliminar articles
if ($action === 'eliminar'){
	// Assegurar que l'usuari està loguejat abans d'eliminar un article
	if ($autor === null) {
		// Redirigir a la pàgina d'inici de sessió
		header('Location: ../view/vista.login.php');
		exit;
	}

	if($_SERVER['REQUEST_METHOD'] === 'POST'){
		//Obtenir id de l'article a eliminar
		$id = (int)($_POST['id'] ?? 0);

		//Instanciar el model i eliminar
		$eliminar = new PdoArticles($conn);
		try{
			$ok = $eliminar->eliminar((string)$id);

			if ($ok) {
				//Redirigir a la vista d'articles després d'eliminar
				header('Location: ../view/vista.articles.php');
				exit;
			} else {
				$missatge = '<p class="error">ERROR EN ELIMINAR L\'ARTICLE.</p>';
			} 
		} catch (PDOException $e) {
			throw new PDOException('Error a l\'eliminació de l\'article: ' . $e->getMessage());
		}
	}
}