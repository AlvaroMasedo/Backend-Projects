/*Elimina la base de dades si existeix*/
DROP DATABASE IF EXISTS Alvaro_Masedo_BBDD;

/*Crea la base de dades*/
CREATE DATABASE Alvaro_Masedo_BBDD;

/*Utilitza la base de dades*/
USE Alvaro_Masedo_BBDD;

/*Crea la taula d'articles*/
CREATE TABLE IF NOT EXISTS articles (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    dni VARCHAR(9) NOT NULL,
    Nom VARCHAR(50) NOT NULL,
    Cos TEXT NOT NULL
);

/*Insereix dades de prova a la taula d'articles*/
INSERT INTO articles (dni, nom, cos) VALUES
('12345678A', 'Joan Garcia', 'Aquest és un article de prova.'),
('87654321B', 'Pedri Gonzalez', 'Un altre exemple.');