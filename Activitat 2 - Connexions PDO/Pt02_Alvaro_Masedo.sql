/*Elimina la base de dades si existeix*/
DROP DATABASE IF EXISTS Pt02_Alvaro_Masedo;

/*Crea la base de dades*/
CREATE DATABASE Pt02_Alvaro_Masedo;

/*Utilitza la base de dades*/
USE Pt02_Alvaro_Masedo;

/*Crea la taula d'articles*/
CREATE TABLE IF NOT EXISTS articles (
    dni VARCHAR(9) NOT NULL PRIMARY KEY,
    Nom VARCHAR(50) NOT NULL,
    Cos TEXT NOT NULL
);

/*Insereix dades de prova a la taula d'articles*/
INSERT INTO articles (dni, nom, cos) VALUES
('12345678A', 'Joan Garcia', 'Aquest és un article de prova.'),
('87654321B', 'Pedri Gonzalez', 'Un altre exemple.');