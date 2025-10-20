<?php
declare(strict_types=1);

class PdoConsultar{
    // Propietat per a la connexió a la base de dades
    private PDO $conn;

    // Constructor per inicialitzar la connexió a la base de dades
    public function __construct(PDO $conn){
        $this->conn = $conn;
    }

    // Mètode per afegir un article
    public function consultar(string $dni): array {
        
        $sql = "SELECT * FROM articles WHERE dni = :dni";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':dni' => $dni]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }


    //Mètode per comprovar si el DNI existeix
    public function existeixDNI(string $dni): bool {
        $sql = "SELECT COUNT(*) FROM articles WHERE dni = :dni";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':dni' => $dni]);
        $count = $stmt->fetchColumn();
        return $count > 0;
    }
}