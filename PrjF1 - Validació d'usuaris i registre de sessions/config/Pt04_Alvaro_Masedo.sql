/*Álvaro Masedo Pérez*/
/*Elimina la base de dades si existeix*/
DROP DATABASE IF EXISTS `articles_f1`;

/*Creació de la base de dades d'articles de F1*/
CREATE DATABASE IF NOT EXISTS `articles_f1`;

USE `articles_f1`;

/*Crea la taula d'articles*/
CREATE TABLE IF NOT EXISTS articles (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    autor VARCHAR(25) NOT NULL,
    nom_article VARCHAR(25) NOT NULL,
    cos TEXT NOT NULL,
    data_publicacio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultima_modificacio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS usuaris (
    nickname VARCHAR(15) NOT NULL PRIMARY KEY,
    nom VARCHAR(25) NOT NULL,
    cognom VARCHAR (25),
    email VARCHAR(100) NOT NULL UNIQUE,
    contrasenya VARCHAR (255) NOT NULL,
    administrador TINYINT(1) NOT NULL,
    imatge_perfil VARCHAR(255),
    remember_token VARCHAR(64) NULL,
    remember_expiry DATETIME NULL,
    reset_token VARCHAR(64) NULL,
    reset_expiry DATETIME NULL,
    oauth_provider VARCHAR(20) NULL,
    oauth_id VARCHAR(255) NULL,
    verification_code VARCHAR(6) NULL,
    verification_expires DATETIME NULL,
    UNIQUE KEY unique_oauth (oauth_provider, oauth_id)
);

