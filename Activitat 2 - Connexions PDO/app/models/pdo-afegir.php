<?php
declare(strict_types=1);

class PdoAfegir{
    // Propietat per a la connexió a la base de dades
    private PDO $conn;

    // Constructor per inicialitzar la connexió a la base de dades
    public function __construct(PDO $conn){
        $this->conn = $conn;
    }

    // Mètode per afegir un article
    public function afegir(string $dni, string $nom, string $cos): bool {
        
        $sql = "INSERT INTO articles (dni, nom, cos) VALUES (:dni, :nom, :cos)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':dni' => $dni,
            ':nom' => $nom,
            ':cos' => $cos
        ]);
    }

}