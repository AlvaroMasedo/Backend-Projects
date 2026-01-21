<?php

declare(strict_types=1);
//Álvaro Masedo Pérez

class ModelUsers
{


    // Propietat per a la connexió a la base de dades
    private PDO $conn;

    // Constructor per inicialitzar la connexió a la base de dades
    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    //////////////////////////////////////////////////////////////////////////////////////////////
    /////////                                   SELECTS                                 //////////
    //////////////////////////////////////////////////////////////////////////////////////////////

    /* Mètode per fer login
     * Retorna un array amb les dades de l'usuari si l'email i contrasenya són correctes
     * Retorna null si no hi ha cap usuari amb aquestes credencials
     */
    public function login(string $email, string $contrasenya): ?array
    {
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

    /* Mètode per obtenir tots els usuaris
     * Retorna un array amb tots els usuaris
     */
    public function obtenirTots(): array
    {
        $sql = "SELECT nickname, nom, cognom, email, administrador, imatge_perfil FROM usuaris ORDER BY nickname ASC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Mètode per obtenir un usuari per nickname
     * Retorna un array amb les dades de l'usuari o null si no existeix
     */
    public function obtenirPerNickname(string $nickname): ?array
    {
        $sql = "SELECT * FROM usuaris WHERE nickname = :nickname LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':nickname' => $nickname]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        return $usuario ?: null;
    }

    //////////////////////////////////////////////////////////////////////////////////////////////
    /////////                                   INSERTS                                 //////////
    //////////////////////////////////////////////////////////////////////////////////////////////

    /* Mètode per registrar un nou usuari
     * Retorna true si l'usuari s'ha registrat correctament, false en cas contrari
     */
    public function registrar(string $nickname, string $nom, string $cognom, string $email, string $contrasenya, int $administrador): bool
    {
        $sql = "INSERT INTO usuaris (nickname, nom, cognom, email, contrasenya, administrador) VALUES (:nickname, :nom, :cognom, :email, :contrasenya, :administrador)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nickname' => $nickname,
            ':nom' => $nom,
            ':cognom' => $cognom,
            ':email' => $email,
            ':contrasenya' => $contrasenya,
            ':administrador' => $administrador
        ]);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////
    /////////                                   UPDATES                                 //////////
    //////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Modifica un usuari per Nickname
     * @param string $nickname_actual Nickname actual de l'usuari
     * @param string $nickname_nou Nou nickname
     * @param string $nom Nou nom
     * @param string|null $cognom Nou cognom (opcional)
     * @param string|null $imatge_perfil Nova ruta de la imatge de perfil (opcional)
     * @return bool
     */
    public function modificar(string $nickname_actual, string $nickname_nou, string $nom, ?string $cognom = null, ?string $imatge_perfil = null): bool
    {
        if ($imatge_perfil !== null) {
            $sql = "UPDATE usuaris SET nickname = :nickname_nou, nom = :nom, cognom = :cognom, imatge_perfil = :imatge WHERE nickname = :nickname_actual";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':nickname_nou' => $nickname_nou,
                ':nom' => $nom,
                ':cognom' => $cognom,
                ':imatge' => $imatge_perfil,
                ':nickname_actual' => $nickname_actual
            ]);
        } else {
            $sql = "UPDATE usuaris SET nickname = :nickname_nou, nom = :nom, cognom = :cognom WHERE nickname = :nickname_actual";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':nickname_nou' => $nickname_nou,
                ':nom' => $nom,
                ':cognom' => $cognom,
                ':nickname_actual' => $nickname_actual
            ]);
        }
    }


    //////////////////////////////////////////////////////////////////////////////////////////////
    /////////                                   DELETES                                 //////////
    //////////////////////////////////////////////////////////////////////////////////////////////

    /* Mètode per eliminar un usuari per nickname
     * Retorna true si s'ha eliminat correctament, false en cas contrari
     */
    public function eliminar(string $nickname): bool
    {
        $sql = "DELETE FROM usuaris WHERE nickname = :nickname";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':nickname' => $nickname]);
    }


    //////////////////////////////////////////////////////////////////////////////////////////////
    /////////                                   COUNTSS                                 //////////
    //////////////////////////////////////////////////////////////////////////////////////////////
    
    /* Mètode per comprovar si la contrasenya és correcta per a un email donat
     * Retorna true si la contrasenya és correcta, false en cas contrari
     */
    public function comprobarContrasenya(string $contrasenya, string $email): bool
    {
        $sql = "SELECT COUNT(*) FROM usuaris WHERE email = :email AND contrasenya = :contrasenya";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':contrasenya' => $contrasenya
        ]);
        $count = $stmt->fetchColumn();
        return $count > 0;
    }

    /* Mètode per comprovar si l'email existeix
     * Retorna true si l'email existeix, false en cas contrari
     */
    public function existeixEmail(string $email): bool
    {
        $sql = "SELECT COUNT(*) FROM usuaris WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $count = $stmt->fetchColumn();
        return $count > 0;
    }

    /* Mètode per comprovar si el nickname existeix
     * Retorna true si el nickname existeix, false en cas contrari
     */
    public function existeixNickname(string $nickname): bool
    {
        $sql = "SELECT COUNT(*) FROM usuaris WHERE nickname = :nickname";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':nickname' => $nickname]);
        $count = $stmt->fetchColumn();
        return $count > 0;
    }

    //////////////////////////////////////////////////////////////////////////////////////////////
    /////////                         REMEMBER ME CON TOKENS                           //////////
    //////////////////////////////////////////////////////////////////////////////////////////////

    /* Mètode per generar un token aleatori segur
     */
    private function generarToken(): string
    {
        return bin2hex(random_bytes(32)); // Token de 64 caràcters hexadecimals
    }

    /* Mètode per guardar token de Remember Me en la base de dades
     * @param string $nickname Nickname de l'usuari
     * @return string Token generat
     */
    public function guardarRememberMe(string $nickname): string
    {
        $token = $this->generarToken();
        $expiry = date('Y-m-d H:i:s', strtotime('+30 days')); // Token valid 30 dies
        
        $sql = "UPDATE usuaris SET remember_token = :token, remember_expiry = :expiry WHERE nickname = :nickname";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':token' => $token,
            ':expiry' => $expiry,
            ':nickname' => $nickname
        ]);
        
        // Guardar token en cookie (NO les credencials)
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
        
        return $token;
    }

    /* Mètode per verificar i obtenir usuari per token de Remember Me
     * @param string $token Token de la cookie
     * @return array|null Dades de l'usuari o null si el token no és vàlid
     */
    public function obtenirUsuariPerToken(string $token): ?array
    {
        $sql = "SELECT * FROM usuaris WHERE remember_token = :token AND remember_expiry > NOW() LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':token' => $token]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        return $usuario ?: null;
    }

    /* Mètode per eliminar token de Remember Me
     * @param string $nickname Nickname de l'usuari
     */
    public function eliminarRememberMe(string $nickname = null): void
    {
        // Eliminar cookie
        setcookie('remember_token', '', time() - 3600, '/');
        
        // Si tenim nickname, eliminar token de la BBDD
        if ($nickname !== null) {
            $sql = "UPDATE usuaris SET remember_token = NULL, remember_expiry = NULL WHERE nickname = :nickname";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':nickname' => $nickname]);
        }
    }

}


