<?php

declare(strict_types=1);
//Álvaro Masedo Pérez
// Controlador per gestionar la lògica dels articles

require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/session_check.php';
require_once __DIR__ . '/../model/model.articles.php';
require_once __DIR__ . '/../../lib/auth.php';

// Obtenir l'acció a realitzar (si n'hi ha)
$action = $_GET['action'] ?? '';
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
$autorFilter = null;
if ($autor !== null && !usuari_es_admin()) {
	$autorFilter = $autor;
}

$totalArticles = $pdoArticles->contarArticles($autorFilter);
$busquedaTerm = trim($_GET['q'] ?? '');
$esBusqueda = !empty($busquedaTerm);
if ($esBusqueda) {
	$totalArticles = $pdoArticles->contarBusqueda($busquedaTerm, $autorFilter);
}

$perPageRaw = $_GET['per_page'] ?? null;
if ($perPageRaw === null && $scriptActual === 'vista.articles.php' && isset($_SESSION['usuari'])) {
	$perPageRaw = 'all';
}

if ($perPageRaw === 'all' || (is_numeric($perPageRaw) && (int)$perPageRaw === 0)) {
	$articlesPerPagina = $totalArticles > 0 ? $totalArticles : 1;
} else {
	$articlesPerPagina = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 8;
	if ($articlesPerPagina < 1) {
		$articlesPerPagina = 1;
	}
	if ($articlesPerPagina > 10) {
		$articlesPerPagina = 10;
	}
}

$paginaActual = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$ordreActual = $_GET['ordenar'] ?? 'recent';
$ordresPermesos = ['recent', 'antic', 'asc', 'desc'];
if (!in_array($ordreActual, $ordresPermesos, true)) {
	$ordreActual = 'recent';
}

$totalPagines = ($articlesPerPagina > 0) ? (int) ceil($totalArticles / $articlesPerPagina) : 1;
if ($paginaActual > $totalPagines) {
	$paginaActual = $totalPagines;
}

$offset = ($paginaActual - 1) * $articlesPerPagina;
if ($esBusqueda) {
	$articles = $pdoArticles->buscar($busquedaTerm, $articlesPerPagina, $offset, $autorFilter, $ordreActual);
} else {
	$articles = $pdoArticles->obtenirPaginat($articlesPerPagina, $offset, $autorFilter, $ordreActual);
}

$baseUrl = 'index.php';
$prevPage = max(1, $paginaActual - 1);
$nextPage = min($totalPagines, $paginaActual + 1);
$perPageForUrl = $_GET['per_page'] ?? (string) $articlesPerPagina;
$busquedaParam = $esBusqueda ? '&q=' . urlencode($busquedaTerm) : '';
$prevUrl = $baseUrl . '?page=' . $prevPage . '&per_page=' . $perPageForUrl . '&ordenar=' . $ordreActual . $busquedaParam;
$nextUrl = $baseUrl . '?page=' . $nextPage . '&per_page=' . $perPageForUrl . '&ordenar=' . $ordreActual . $busquedaParam;

// API JSON simple per a Ajax
if ($action === 'api') {
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode([
		'ok' => true,
		'articles' => $articles,
	], JSON_UNESCAPED_UNICODE);
	exit;
}

// Afegir article
if ($action === 'afegir') {
	requerir_inici_sessio_o_redirigir('../view/vista.login.php');

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$nom = trim($_POST['Nom'] ?? '');
		$cos = trim($_POST['Cos'] ?? '');

		if (empty($nom) || empty($cos)) {
			$missatge = '<p class="error">TOTS ELS CAMPS AMB UN * SÓN OBLIGATORIS.</p>';
		} else if (!preg_match('/^[A-Za-zÀ-ÿ\s]{2,50}$/u', $nom)) {
			$errorNom = '<p class="error">EL NOM NOMÉS POT CONTENIR LLETRES I ESPAIS (2-50 CARÀCTERS).</p>';
		} else {
			try {
				$ok = $pdoArticles->afegir((string) $autor, $nom, $cos);
				if ($ok) {
					header('Location: ../view/vista.articles.php?added=1');
					exit;
				}
				header('Location: ../view/vista.articles.php?error=add');
				exit;
			} catch (PDOException $e) {
				throw new PDOException('Error a l\'afegir l\'article: ' . $e->getMessage());
			}
		}
	}

	require __DIR__ . '/../view/vista.afegirArticle.php';
	exit;
}

// Modificar article
if ($action === 'modificar') {
	requerir_inici_sessio_o_redirigir('../view/vista.login.php');

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$id = (int) ($_POST['id'] ?? 0);
		$nom = trim($_POST['Nom'] ?? '');
		$cos = trim($_POST['Cos'] ?? '');

		if (empty($nom) || empty($cos)) {
			$missatge = '<p class="error">TOTS ELS CAMPS AMB UN * SÓN OBLIGATORIS.</p>';
		} else if (!preg_match('/^[A-Za-zÀ-ÿ\s]{2,50}$/u', $nom)) {
			$errorNom = '<p class="error">EL NOM NOMÉS POT CONTENIR LLETRES I ESPAIS (2-50 CARÀCTERS).</p>';
		} else {
			try {
				$ok = $pdoArticles->modificar($id, $nom, $cos);
				if ($ok) {
					header('Location: ../view/vista.articles.php?modified=1');
					exit;
				}
				header('Location: ../view/vista.articles.php?error=modify');
				exit;
			} catch (PDOException $e) {
				throw new PDOException('Error a la modificació de l\'article: ' . $e->getMessage());
			}
		}
	}

	require __DIR__ . '/../view/vista.modificarArticle.php';
	exit;
}

// Eliminar article (només autor o admin)
if ($action === 'eliminar') {
	requerir_inici_sessio_o_redirigir('../view/vista.login.php');

	$id = (int) ($_GET['id'] ?? 0);
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
		$ok = $pdoArticles->eliminar((string) $id);
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