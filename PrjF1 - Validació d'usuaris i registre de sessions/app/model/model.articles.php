<?php
declare(strict_types=1);
//Álvaro Masedo Pérez

class PdoArticles{
    // Propietat per a la connexió a la base de dades
    private PDO $conn;

    // Constructor per inicialitzar la connexió a la base de dades
    public function __construct(PDO $conn){
        $this->conn = $conn;
    }

    /**
     * Retorna tots els articles de la base de dades.
     * Cada article retorna amb les claus 'Nom' i 'Cos' per compatibilitat amb les vistes.
     * @return array<int,array<string,mixed>>
     */
    public function obtenirTots(): array {
        $sql = "SELECT id, autor, nom_article AS Nom, cos AS Cos FROM articles";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result ?: [];
    }

    /**
     * Retorna els articles escrits per un autor (nickname) concret.
     * @param string $nickname
     * @return array<int,array<string,mixed>>
     */
    public function obtenirPerNickname(string $nickname): array {
        $sql = "SELECT id, autor, nom_article AS Nom, cos AS Cos FROM articles WHERE autor = :autor";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':autor' => $nickname]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result ?: [];
    }

    /**
     * Retorna un article per id
     * @param int $id
     * @return array<string,mixed>|null
     */
    public function obtenirPerId(int $id): ?array {
        $sql = "SELECT id, autor, nom_article AS Nom, cos AS Cos FROM articles WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    // Mètode per eliminar un article
    public function eliminar(string $id): bool {
        $sql = "DELETE FROM articles WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id' => $id
        ]);
    }

    //Mètode per afegir un article
    public function afegir(string $autor, string $nom, string $cos): bool {
        $sql = "INSERT INTO articles (autor, nom_article, cos) VALUES (:autor, :nom, :cos)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':autor' => $autor,
            ':nom' => $nom,
            ':cos' => $cos
        ]);
    }

    /**
     * Modifica un article per id
     * @param int $id
     * @param string $nom
     * @param string $cos
     * @return bool
     */
    public function modificar(int $id, string $nom, string $cos): bool {
        $sql = "UPDATE articles SET nom_article = :nom, cos = :cos WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nom' => $nom,
            ':cos' => $cos,
            ':id' => $id
        ]);
    }

     /**
     * Retorna el nombre total d'articles
     * @param string|null $autor
     * @return int
     */
    public function contarArticles(): int {
        $sql = "SELECT COUNT(*) FROM articles";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $count = (int) $stmt->fetchColumn();
        return $count;
    }

    /**
     * Retorna un llistat paginat d'articles amb LIMIT/OFFSET
     * @param int $offset
     * @param string|null $autor
     * @return array<int,array<string,mixed>>
     */
    public function obtenirPaginat(int $limit, int $offset): array {
        $sql = "SELECT id, autor, nom_article AS Nom, cos AS Cos FROM articles ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);

        // Bind numèrics amb PDO::PARAM_INT
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result ?: [];
    }

}
