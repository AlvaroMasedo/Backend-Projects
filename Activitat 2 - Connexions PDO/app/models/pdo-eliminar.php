<?php
declare(strict_types=1);

class PdoEliminar{
    // Propietat per a la connexió a la base de dades
    private PDO $conn;

    // Constructor per inicialitzar la connexió a la base de dades
    public function __construct(PDO $conn){
        $this->conn = $conn;
    }

    // Mètode per afegir un article
    public function eliminar(string $id): bool {
        
        $sql = "DELETE FROM articles WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id' => $id
        ]);
    }

    //Mètode per comprovar si l'ID existeix
    public function existeixId(string $id): bool {
        $sql = "SELECT COUNT(*) FROM articles WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $count = $stmt->fetchColumn();
        return $count > 0;
    }
}