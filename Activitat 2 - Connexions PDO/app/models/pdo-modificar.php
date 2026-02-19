<?php
declare(strict_types=1);

class PdoModificar {
    // Propietat per a la connexió a la base de dades
    private PDO $conn;

    // Constructor per inicialitzar la connexió a la base de dades
    public function __construct(PDO $conn){
        $this->conn = $conn;
    }

     /**
     * Modifica un article segons el seu ID.
     * Retorna true si la consulta s'ha executat correctament.
     * Si no es canvia cap valor (mateixos valors que abans) execute() pot ser true però rowCount() pot ser 0.
     */
    public function modificar(int $id, string $dni, string $nom, string $cos): bool {
        $sql = "UPDATE articles
                SET dni = :dni,
                    nom = :nom,
                    cos = :cos
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':dni' => $dni,
            ':nom' => $nom,
            ':cos' => $cos,
            ':id'  => $id
        ]);
    }

    //Comprova si un ID existeix a la taula articles. 
    public function existeixId(int $id): bool {
        $sql = "SELECT COUNT(*) FROM articles WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $count = (int)$stmt->fetchColumn();
        return $count > 0;
    }
}