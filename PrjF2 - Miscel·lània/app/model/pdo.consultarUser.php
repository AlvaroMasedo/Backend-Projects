<?php
declare(strict_types=1);
//Álvaro Masedo Pérez

class PdoConsultarUser{
    // Propietat per a la connexió a la base de dades
    private PDO $conn;

    // Constructor per inicialitzar la connexió a la base de dades
    public function __construct(PDO $conn){
        $this->conn = $conn;
    }
    
    //Mètode per comprovar si el Nickname existeix
    public function existeixNickname(string $nickname): bool {
        $sql = "SELECT COUNT(*) FROM usuaris WHERE nickname = :nickname";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':nickname' => $nickname]);
        $count = $stmt->fetchColumn();
        return $count > 0;
    }

    //Mètode per comprovar si el email existeix
    public function existeixEmail(string $email): bool {
        $sql = "SELECT COUNT(*) FROM usuaris WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $count = $stmt->fetchColumn();
        return $count > 0;
    }

    //Mètode per comprovar la contrasenya segons l'email
    public function comprobarContrasenya(string $contrasenya, string $email): bool {
        $sql = "SELECT COUNT(*) FROM usuaris WHERE email = :email AND contrasenya = :contrasenya";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':contrasenya' => $contrasenya
        ]);
        $count = $stmt->fetchColumn();
        return $count > 0;
    }
}
