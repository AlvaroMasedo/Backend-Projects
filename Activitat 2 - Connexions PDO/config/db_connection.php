<?php
//Conexió a la base de dades amb PDO

//Dades de connexió
$host = 'localhost';
$dbname = 'pt02_Alvaro_Masedo';
$username = 'root';
$password = '';

try {
    //Crear l'objecte PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    //Configurar els errors com excepcions
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //Missatge de connexió exitosa
    echo "Connexió exitosa a la base de dades.";
} catch (PDOException $e) {
    //Missatge d'error en cas de fallada
    echo "Error de connexió: " . $e->getMessage();
}
?>