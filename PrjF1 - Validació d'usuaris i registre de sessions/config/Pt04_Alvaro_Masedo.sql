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
    cos TEXT NOT NULL
);

/*Insereix dades de prova a la taula d'articles*/
INSERT INTO articles (autor, nom_article, cos) VALUES
('AlvaroMasedo', 'Fernando Alonso', 'Fernando Alonso és un pilot espanyol, dues vegades campió del món amb Renault els anys 2005 i 2006. Conegut per la seva intel·ligència en cursa i la seva gran constància, ha competit amb equips com Ferrari, McLaren i Aston Martin.'),
('AlvaroMasedo', 'Lewis Hamilton', 'Lewis Hamilton és un pilot britànic amb set campionats del món, igualant el rècord de Michael Schumacher. És reconegut per la seva velocitat, la seva constància i el seu activisme fora de la pista.'),
('AlvaroMasedo', 'Max Verstappen', 'Max Verstappen és un pilot neerlandès que es va convertir en el campió mundial més jove de la història. Destaca per la seva agressivitat controlada, talent natural i domini amb Red Bull Racing.'),
('AlvaroMasedo', 'Charles Leclerc', 'Charles Leclerc, originari de Mònaco, és pilot de Ferrari i una de les joves promeses més brillants de la graella. El seu estil de conducció és net, precís i molt ràpid en classificació.'),
('AlvaroMasedo', 'Sebastian Vettel', 'Sebastian Vettel és un pilot alemany, quatre vegades campió del món amb Red Bull entre 2010 i 2013. Va ser conegut per la seva disciplina, intel·ligència estratègica i gran capacitat per dominar curses.'),
('AlvaroMasedo', 'Michael Schumacher', 'Michael Schumacher és considerat un dels millors pilots de la història, amb set títols mundials. La seva era daurada amb Ferrari va marcar una de les etapes més exitoses de la F1 moderna.'),
('AlvaroMasedo', 'Ayrton Senna', 'Ayrton Senna va ser un pilot brasiler llegendari, tres vegades campió del món. Admirat pel seu talent sota la pluja i el seu carisma, el seu llegat continua sent un dels més inspiradors de l’automobilisme.'),
('AlvaroMasedo', 'Niki Lauda', 'Niki Lauda va ser un pilot austríac, tres vegades campió del món. Va sobreviure a un greu accident el 1976 i va tornar a competir només sis setmanes després, símbol de coratge i determinació.'),
('AlvaroMasedo', 'Kimi Räikkönen', 'Kimi Räikkönen, conegut com “Iceman”, és un pilot finlandès campió del món amb Ferrari l’any 2007. Famos per la seva personalitat reservada i el seu estil de conducció pur i directe.'),
('AlvaroMasedo', 'Jenson Button', 'Jenson Button és un pilot britànic que va guanyar el campionat mundial el 2009 amb Brawn GP. Reconegut per la seva suavitat al volant i la seva habilitat per cuidar els pneumàtics en condicions difícils.');

CREATE TABLE IF NOT EXISTS usuaris (
    nickname VARCHAR(15) NOT NULL PRIMARY KEY,
    nom VARCHAR(25) NOT NULL,
    cognom VARCHAR (25),
    email VARCHAR(100) NOT NULL UNIQUE,
    contrasenya VARCHAR (255) NOT NULL
);
