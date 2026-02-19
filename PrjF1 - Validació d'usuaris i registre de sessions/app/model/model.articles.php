<?php

declare(strict_types=1);
//Álvaro Masedo Pérez

class ModelArticles
{
    // Propietat per a la connexió a la base de dades
    private PDO $conn;

    // Constructor per inicialitzar la connexió a la base de dades
    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////
    /////////                                   SELECTS                                  //////////
    ///////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Retorna tots els articles de la base de dades.
     * Cada article retorna amb les claus 'Nom' i 'Cos' per compatibilitat amb les vistes.
     * @return array<int,array<string,mixed>>
     */
    public function obtenirTots(): array
    {
        $sql = "SELECT id, autor, nom_article AS Nom, cos AS Cos, ultima_modificacio FROM articles";
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
    public function obtenirPerNickname(string $nickname): array
    {
        $sql = "SELECT id, autor, nom_article AS Nom, cos AS Cos, ultima_modificacio FROM articles WHERE autor = :autor";
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
    public function obtenirPerId(int $id): ?array
    {
        $sql = "SELECT id, autor, nom_article AS Nom, cos AS Cos, ultima_modificacio FROM articles WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Retorna el nombre total d'articles
     * @param string|null $autor Filtra per autor si es proporciona
     * @return int
     */
    public function contarArticles(?string $autor = null): int
    {
        if ($autor === null) {
            $sql = "SELECT COUNT(*) FROM articles";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        } else {
            $sql = "SELECT COUNT(*) FROM articles WHERE autor = :autor";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':autor' => $autor]);
        }
        $count = (int) $stmt->fetchColumn();
        return $count;
    }

    /**
     * Retorna un llistat paginat d'articles amb LIMIT/OFFSET
     * @param int $limit
     * @param int $offset
     * @param string|null $autor Filtra per autor si es proporciona
     * @param string $ordre Ordre dels articles: 'recent', 'antic', 'asc', 'desc'
     * @return array<int,array<string,mixed>>
     */
    public function obtenirPaginat(int $limit, int $offset, ?string $autor = null, string $ordre = 'recent'): array
    {
        // Determinar ORDER BY segons el tipus d'ordre
        $orderBy = match($ordre) {
            'antic' => 'ORDER BY ultima_modificacio ASC',
            'asc' => 'ORDER BY nom_article ASC',
            'desc' => 'ORDER BY nom_article DESC',
            default => 'ORDER BY ultima_modificacio DESC', // 'recent' per defecte
        };

        if ($autor === null) {
            $sql = "SELECT id, autor, nom_article AS Nom, cos AS Cos, ultima_modificacio FROM articles {$orderBy} LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $sql = "SELECT id, autor, nom_article AS Nom, cos AS Cos, ultima_modificacio FROM articles WHERE autor = :autor {$orderBy} LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':autor', $autor);
            $stmt->execute();
        }
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result ?: [];
    }

    /**
     * Busca articles per nom amb paginació
     * @param string $busqueda Terme de búsqueda
     * @param int $limit
     * @param int $offset
     * @param string|null $autor Filtra per autor si es proporciona
     * @param string $ordre Ordre dels articles
     * @return array<int,array<string,mixed>>
     */
    public function buscar(string $busqueda, int $limit, int $offset, ?string $autor = null, string $ordre = 'recent'): array
    {
        // Determinar ORDER BY segons el tipus d'ordre
        $orderBy = match($ordre) {
            'antic' => 'ORDER BY ultima_modificacio ASC',
            'asc' => 'ORDER BY nom_article ASC',
            'desc' => 'ORDER BY nom_article DESC',
            default => 'ORDER BY ultima_modificacio DESC', // 'recent' per defecte
        };

        $busqueda = '%' . $busqueda . '%';

        if ($autor === null) {
            $sql = "SELECT id, autor, nom_article AS Nom, cos AS Cos, ultima_modificacio FROM articles WHERE nom_article LIKE :busqueda {$orderBy} LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':busqueda', $busqueda);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $sql = "SELECT id, autor, nom_article AS Nom, cos AS Cos, ultima_modificacio FROM articles WHERE nom_article LIKE :busqueda AND autor = :autor {$orderBy} LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':busqueda', $busqueda);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':autor', $autor);
            $stmt->execute();
        }
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result ?: [];
    }

    /**
     * Cuenta el total d'articles que coincideixen amb la búsqueda
     * @param string $busqueda Terme de búsqueda
     * @param string|null $autor Filtra per autor si es proporciona
     * @return int
     */
    public function contarBusqueda(string $busqueda, ?string $autor = null): int
    {
        $busqueda = '%' . $busqueda . '%';

        if ($autor === null) {
            $sql = "SELECT COUNT(*) FROM articles WHERE nom_article LIKE :busqueda";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':busqueda' => $busqueda]);
        } else {
            $sql = "SELECT COUNT(*) FROM articles WHERE nom_article LIKE :busqueda AND autor = :autor";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':busqueda' => $busqueda, ':autor' => $autor]);
        }
        $count = (int) $stmt->fetchColumn();
        return $count;
    }

    //////////////////////////////////////////////////////////////////////////////////////////////
    /////////                                   INSERTS                                 //////////
    //////////////////////////////////////////////////////////////////////////////////////////////

    //Mètode per afegir un article
    public function afegir(string $autor, string $nom, string $cos): bool
    {
        $sql = "INSERT INTO articles (autor, nom_article, cos) VALUES (:autor, :nom, :cos)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':autor' => $autor,
            ':nom' => $nom,
            ':cos' => $cos
        ]);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////
    /////////                                   UPDATES                                 //////////
    //////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Modifica un article per id
     * @param int $id
     * @param string $nom
     * @param string $cos
     * @return bool
     */
    public function modificar(int $id, string $nom, string $cos): bool
    {
        $sql = "UPDATE articles SET nom_article = :nom, cos = :cos, ultima_modificacio = :data WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nom' => $nom,
            ':cos' => $cos,
            ':data' => date('Y-m-d H:i:s'),
            ':id' => $id
        ]);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////
    /////////                                   DELETES                                 //////////
    //////////////////////////////////////////////////////////////////////////////////////////////

    // Mètode per eliminar un article
    public function eliminar(string $id): bool
    {
        $sql = "DELETE FROM articles WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id' => $id
        ]);
    }

    // Mètode per eliminar tots els articles d'un autor
    public function eliminarPerAutor(string $autor): bool
    {
        $sql = "DELETE FROM articles WHERE autor = :autor";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':autor' => $autor
        ]);
    }


    //////////////////////////////////////////////////////////////////////////////////////////////
    /////////                                   COUNTSS                                 //////////
    //////////////////////////////////////////////////////////////////////////////////////////////

}
