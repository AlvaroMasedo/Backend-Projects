<?php

declare(strict_types=1);
//Álvaro Masedo Pérez
//Controlador per gestionar la lògica dels articles
//S 'usa per obtenir els articles

//Carregar connexió a la BD i model
require_once __DIR__ . '/../../config/db_connection.php';

// Assegurar que la sessió està iniciada i gestionar logout
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../model/model.articles.php';
require_once __DIR__ . '/../../lib/auth.php';

// Obtenir l'acció a realitzar (si n'hi ha)
$action = $_GET['action'] ?? '';

// Decidir quins articles carregar segons si l'usuari és administrador o no
$pdoArticles = new ModelArticles($conn);

// Identifiquem el script actual (per saber si som a la vista de modificació o eliminació)
$scriptActual = basename($_SERVER['SCRIPT_NAME'] ?? ($_SERVER['SCRIPT_FILENAME'] ?? ''));

// Si s'ha cridat la vista de modificació amb un id, carreguem l'article perquè la vista
// pugui pré-omplir els camps. Això ocorre quan l'usuari obre `vista.modificarArticle.php?id=...`.
if ($scriptActual === 'vista.modificarArticle.php' && isset($_GET['id'])) {
	$idEditar = (int) $_GET['id'];
	$articleAEditar = $pdoArticles->obtenirPerId($idEditar);
	if ($articleAEditar !== null) {
		if (pot_usuari_gestionar_article($articleAEditar)) {
			// Variables que la vista utilitza per pré-omplir el formulari
			$id = $articleAEditar['id'];
			$nom = $articleAEditar['Nom'];
			$cos = $articleAEditar['Cos'];
		} else {
			$missatge = '<p class="error">No tens permisos per modificar aquest article o has d\'iniciar sessió.</p>';
		}
	} else {
		$missatge = '<p class="error">Article no trobat.</p>';
	}
}


// Paginació senzilla 
$autor = nickname_usuari_actual();

// Si l'usuari no és admin, només veurà els seus articles
// Si és admin, veurà tots els articles
$autorFilter = null;
if ($autor !== null && !usuari_es_admin()) {
	$autorFilter = $autor;
}

// Comptar articles (filtrant per autor si no és admin)
$totalArticles = $pdoArticles->contarArticles($autorFilter);

// Obtenir paràmetre de búsqueda si existeix
$busquedaTerm = trim($_GET['q'] ?? '');
$esBusqueda = !empty($busquedaTerm);

// Si hi ha búsqueda, actualitzar el total de articles
if ($esBusqueda) {
	$totalArticles = $pdoArticles->contarBusqueda($busquedaTerm, $autorFilter);
}

$scriptActual = basename($_SERVER['SCRIPT_NAME'] ?? ($_SERVER['SCRIPT_FILENAME'] ?? ''));
$perPageRaw = $_GET['per_page'] ?? null;

// Si estem a la vista d'articles i l'usuari està loguejat, per defecte volem mostrar TOTS
if ($perPageRaw === null && $scriptActual === 'vista.articles.php' && isset($_SESSION['usuari'])) {
	$perPageRaw = 'all';
}

// Determinar nombre d'articles per pàgina a index.php
if ($perPageRaw === 'all' || (is_numeric($perPageRaw) && (int)$perPageRaw === 0)) {
	// Mostrar tots els articles
	$articlesPerPagina = $totalArticles > 0 ? $totalArticles : 1;
	$perPageMode = 'all';
} else {
	// Valor numèric (o default)
	$articlesPerPagina = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 8;
	if ($articlesPerPagina < 1) {
		$articlesPerPagina = 1;
	}
	if ($articlesPerPagina > 10) {
		$articlesPerPagina = 10;
	}
	$perPageMode = 'paged';
}

$paginaActual = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Obtenir ordre dels articles
$ordreActual = $_GET['ordenar'] ?? 'recent';
$ordresPermesos = ['recent', 'antic', 'asc', 'desc'];
if (!in_array($ordreActual, $ordresPermesos)) {
	$ordreActual = 'recent';
}

// Calcul pagines 
$totalPagines = ($articlesPerPagina > 0) ? (int)ceil($totalArticles / $articlesPerPagina) : 1;
if ($paginaActual > $totalPagines) {
	$paginaActual = $totalPagines;
}

// Obtenir articles amb SQL LIMIT/OFFSET (filtrant per autor si no es admin)
$offset = ($paginaActual - 1) * $articlesPerPagina;
if ($esBusqueda) {
	$articles = $pdoArticles->buscar($busquedaTerm, $articlesPerPagina, $offset, $autorFilter, $ordreActual);
} else {
	$articles = $pdoArticles->obtenirPaginat($articlesPerPagina, $offset, $autorFilter, $ordreActual);
}

// URLs per a controls de paginació (mantenint per_page i ordre)
$baseUrl = 'index.php';
$prevPage = max(1, $paginaActual - 1);
$nextPage = min($totalPagines, $paginaActual + 1);
$perPageForUrl = $_GET['per_page'] ?? (string)$articlesPerPagina;
$busquedaParam = $esBusqueda ? '&q=' . urlencode($busquedaTerm) : '';
$prevUrl = $baseUrl . '?page=' . $prevPage . '&per_page=' . $perPageForUrl . '&ordenar=' . $ordreActual . $busquedaParam;
$nextUrl = $baseUrl . '?page=' . $nextPage . '&per_page=' . $perPageForUrl . '&ordenar=' . $ordreActual . $busquedaParam;

// Afegir articles 
if ($action === 'afegir') {
	// Assegurar que l'usuari està loguejat abans d'afegir un article
	requerir_inici_sessio_o_redirigir('../view/vista.login.php');

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		//Obtenir dades del formulari
		$nom = trim($_POST['Nom'] ?? '');
		$cos = trim($_POST['Cos'] ?? '');

		// Instanciar el model i afegir
		$afegir = new ModelArticles($conn);


		if (empty($nom) || empty($cos)) {
			$missatge = '<p class="error">TOTS ELS CAMPS AMB UN * SÓN OBLIGATORIS.</p>';
		} else if (!preg_match('/^[A-Za-zÀ-ÿ\s]{2,50}$/u', $nom)) {
			$errorNom = '<p class="error">EL NOM NOMÉS POT CONTENIR LLETRES I ESPAIS (2-50 CARÀCTERS).</p>';
		} else {
			try {
				$ok = $afegir->afegir($autor, $nom, $cos);

				if ($ok) {
					header('Location: ../view/vista.articles.php?added=1');
					exit;
				} else {
					header('Location: ../view/vista.articles.php?error=add');
					exit;
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
if ($action === "modificar") {
	// Assegurar que l'usuari està loguejat abans de modificar un article
	requerir_inici_sessio_o_redirigir('../view/vista.login.php');

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		//Obtenir dades del formulari
		$id = (int)($_POST['id'] ?? 0);
		$nom = trim($_POST['Nom'] ?? '');
		$cos = trim($_POST['Cos'] ?? '');

		//Instanciar el model i modificar
		$modificar = new ModelArticles($conn);

		if (empty($nom) || empty($cos)) {
			$missatge = '<p class="error">TOTS ELS CAMPS AMB UNN * SÓN OBLIGATORIS.</p>';
		} else if (!preg_match('/^[A-Za-zÀ-ÿ\s]{2,50}$/u', $nom)) {
			$errorNom = '<p class="error">EL NOM NOMÉS POT CONTENIR LLETRES I ESPAIS (2-50 CARÀCTERS).</p>';
		} else {
			try {
				$ok = $modificar->modificar($id, $nom, $cos);

				if ($ok) {
					//Redirigir a la vista d'articles després de modificar
					header('Location: ../view/vista.articles.php?modified=1');
					exit;
				} else {
					header('Location: ../view/vista.articles.php?error=modify');
					exit;
				}
			} catch (PDOException $e) {
				throw new PDOException('Error a la modificació de l\'article: ' . $e->getMessage());
			}
		}
	}
	require __DIR__ . '/../view/vista.modificarArticle.php';
	exit;
}

//Eliminar articles (només autor o admin). Endpoint directe via GET amb confirm al client.
if ($action === 'eliminar') {
	// Assegurar que l'usuari està loguejat abans d'eliminar un article
	requerir_inici_sessio_o_redirigir('../view/vista.login.php');

	$id = (int)($_GET['id'] ?? 0);
	if ($id <= 0) {
		header('Location: ../view/vista.articles.php?error=invalid');
		exit;
	}

	$article = $pdoArticles->obtenirPerId($id);
	if ($article === null) {
		header('Location: ../view/vista.articles.php?error=notfound');
		exit;
	}

	if (!pot_usuari_gestionar_article($article)) {
		header('Location: ../view/vista.articles.php?error=forbidden');
		exit;
	}

	try {
		$ok = $pdoArticles->eliminar((string)$id);
		if ($ok) {
			header('Location: ../view/vista.articles.php?deleted=1');
			exit;
		}
		header('Location: ../view/vista.articles.php?error=delete');
		exit;
	} catch (PDOException $e) {
		throw new PDOException('Error a l\'eliminació de l\'article: ' . $e->getMessage());
	}
}
