<?php
declare(strict_types=1);
//Álvaro Masedo Pérez

class PdoLogin{
    // Propietat per a la connexió a la base de dades
    private PDO $conn;

    // Constructor per inicialitzar la connexió a la base de dades
    public function __construct(PDO $conn){
        $this->conn = $conn;
    }

    // Mètode per retornar les dades de l'usuari per mostrar al seu perfil 
    public function login(string $email, string $contrasenya): ?array {

        //Obtenir les dades de l'usuari per mostrar al seu perfil
        $sql = "SELECT * FROM usuaris WHERE email = :email AND contrasenya = :contrasenya LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':contrasenya' => $contrasenya
        ]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Retorna l'array de l'usuari si existeix
        return $usuario ?: null;
    }
}