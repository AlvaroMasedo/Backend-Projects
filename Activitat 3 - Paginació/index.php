<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db_connection.php';

/* Defineix els valors permesos pel selector d'articles per pàgina */
$permitits = range(1, 10);
$perDefecte = 5;

/* Obté el nombre d'articles per pàgina des de la petició GET
   Si no és vàlid, utilitza el valor per defecte */
$articlesPerPagina = (isset($_GET['per_page']) && in_array((int)$_GET['per_page'], $permitits, true))
    ? (int)$_GET['per_page']
    : $perDefecte;

/* Obté el número de pàgina actual des de la petició GET
   Si no és vàlid, utilitza la primera pàgina */
$paginaActual = (isset($_GET['page']) && is_numeric($_GET['page']) && (int)$_GET['page'] > 0)
    ? (int)$_GET['page']
    : 1;

/* Compta el nombre total d'articles a la base de dades */
$total = (int)$conn->query("SELECT COUNT(*) FROM articles")->fetchColumn();

// Si no hi ha articles, redirigeix a la pàgina d'error
if ($total === 0) {
    header("Location: /Pràctiques/Backend/Activitat%203%20-%20Paginació/app/view/vista.error.php");
    exit;
}

/* Calcula el nombre total de pàgines i ajusta la pàgina actual si cal */
$totalPagines = (int)max(1, ceil($total / $articlesPerPagina));
if ($paginaActual > $totalPagines) { 
    $paginaActual = $totalPagines; 
}

// Calcula l'offset per la consulta SQL
$offset = ($paginaActual - 1) * $articlesPerPagina;

/* Consulta SQL per obtenir només els articles de la pàgina actual*/
$sql = "SELECT id, Nom, Cos
        FROM articles
        ORDER BY id ASC
        LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':limit',  $articlesPerPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$articles = $stmt->fetchAll();

/* Genera els enllaços per la paginació
   Manté el paràmetre per_page al canviar de pàgina */
$queryBase = http_build_query(['per_page' => $articlesPerPagina]);
$prevUrl = "?{$queryBase}&page=" . max(1, $paginaActual - 1);
$nextUrl = "?{$queryBase}&page=" . min($totalPagines, $paginaActual + 1);

// Inclou la vista que mostrarà els articles
include_once __DIR__ . '/app/view/vista.index.php';