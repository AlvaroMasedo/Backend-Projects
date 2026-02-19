<?php
//Conexió a la base de dades amb PDO
declare(strict_types=1);

//Dades de connexió
$host = 'localhost';
$dbname = 'Alvaro_Masedo_BBDD';
$username = 'alvaro';
$password = 'alvaro1234';
$dsn = "mysql:host=$host;port=3366;dbname=$dbname;charset=utf8mb4";

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