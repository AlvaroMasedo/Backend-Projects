/*Elimina la base de dades si existeix*/
DROP DATABASE IF EXISTS pt02_alvaro_masedo;

/*Crea la base de dades*/
CREATE DATABASE pt02_alvaro_masedo;

/*Utilitza la base de dades*/
USE pt02_alvaro_masedo;

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