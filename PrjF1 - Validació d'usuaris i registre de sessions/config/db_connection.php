<?php
declare(strict_types=1);
//Álvaro Masedo Pérez

//Connexió a la base de dades amb PDO

//Dades de connexió (local vs hosting)
$isLocal = in_array($_SERVER['HTTP_HOST'] ?? 'localhost', ['localhost', '127.0.0.1'], true);

if ($isLocal) {
  $host = 'localhost';
  $port = 3366;
  $dbname = 'articles_f1';
  $username = 'alvaro';
  $password = 'alvaro1234';
} else {
  $host = 'sql306.infinityfree.com';
  $port = 3306;
  $dbname = 'if0_41190374_f1backendalvaro';
  $username = 'if0_41190374';
  $password = 'AiwkSkdeU873Heyd8iE';
}

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

//Opcions de connexió
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    //Crear l'objecte PDO
    $conn = new PDO($dsn, $username, $password, $options);

    //Missatge de connexió exitosa
} catch (PDOException $e) {
    //Codi d'estat HTTP 500 en cas d'error
    http_response_code(500);

    //Missatge genèric d'error
    die("Error de connexió a la base de dades.");
}
?>