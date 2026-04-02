<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../app/model/model.articles.php';

header('Content-Type: application/json; charset=utf-8');

$pdoArticles = new ModelArticles($conn);
$articles = $pdoArticles->obtenirTots();

echo json_encode([
	'ok' => true,
	'articles' => $articles,
], JSON_UNESCAPED_UNICODE);
