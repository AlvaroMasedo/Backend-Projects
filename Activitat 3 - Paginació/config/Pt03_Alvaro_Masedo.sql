/*Elimina la base de dades si existeix*/
DROP DATABASE IF EXISTS `Alvaro_Masedo_BBDD`;

/*Creació de la base de dades pt03_Alvaro_Masedo*/
CREATE DATABASE IF NOT EXISTS `Alvaro_Masedo_BBDD`;

USE `Alvaro_Masedo_BBDD`;

/*Crea la taula d'articles*/
CREATE TABLE IF NOT EXISTS articles (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    dni VARCHAR(9) NOT NULL,
    Nom VARCHAR(50) NOT NULL,
    Cos TEXT NOT NULL
);

/*Insereix dades de prova a la taula d'articles*/
INSERT INTO articles (dni, nom, cos) VALUES
('12345678A', 'Introducció al HTML', 'HTML és el llenguatge bàsic per estructurar pàgines web. Permet definir el contingut i la seva jerarquia.'),
('23456789B', 'CSS: Disseny i estil', 'Amb CSS podem donar estil a les pàgines web, definint colors, mides, marges i dissenys responsive.'),
('34567890C', 'Aprenent PHP', 'PHP és un llenguatge molt utilitzat per al desenvolupament web del costat del servidor.'),
('45678901D', 'Què és MySQL?', 'MySQL és un sistema de gestió de bases de dades relacional utilitzat àmpliament per aplicacions web.'),
('56789012E', 'Connexió amb PDO', 'PDO és una extensió de PHP que permet connectar-se a bases de dades de forma segura i flexible.'),
('67890123F', 'CRUD amb PHP i MySQL', 'El CRUD (Create, Read, Update, Delete) és la base de la gestió de dades en aplicacions web.'),
('78901234G', 'Sessions en PHP', 'Les sessions permeten mantenir informació de l’usuari mentre navega per la web.'),
('89012345H', 'Cookies: què són?', 'Les cookies guarden dades petites al navegador per recordar preferències o iniciar sessió.'),
('90123456I', 'JavaScript per principiants', 'JavaScript permet afegir interactivitat a les pàgines web de manera dinàmica.'),
('11223344J', 'DOM i manipulació d’elements', 'El DOM és una representació del document HTML que permet modificar-lo amb JavaScript.'),
('22334455K', 'Bootstrap: disseny ràpid', 'Bootstrap és un framework CSS que facilita la creació de dissenys moderns i responsius.'),
('33445566L', 'Git i GitHub', 'Git és un sistema de control de versions. GitHub permet col·laborar i compartir projectes.'),
('44556677M', 'SEO bàsic', 'El SEO ajuda a millorar la visibilitat d’un lloc web als motors de cerca.'),
('55667788N', 'APIs i JSON', 'Les APIs permeten la comunicació entre aplicacions. JSON és el format de dades més utilitzat.'),
('66778899O', 'Laravel per a principiants', 'Laravel és un framework PHP que simplifica la creació d’aplicacions web robustes.'),
('77889900P', 'Node.js i Express', 'Node.js permet executar JavaScript al servidor, i Express facilita la creació d’APIs.'),
('88990011Q', 'React: la revolució del front-end', 'React és una llibreria de JavaScript creada per Facebook per construir interfícies modernes.'),
('99001122R', 'Accessibilitat web', 'Fer webs accessibles garanteix que tothom pugui navegar-hi, independentment de les seves capacitats.'),
('10111213S', 'Paginació amb PHP', 'La paginació divideix els resultats grans en pàgines petites per millorar el rendiment i la usabilitat.'),
('12131415T', 'Autenticació i registre', 'Permet gestionar usuaris, iniciar sessió i protegir contingut privat.'),
('13141516U', 'Formularis segurs', 'Validar formularis és essencial per evitar errors i atacs d’injecció de codi.'),
('14151617V', 'Desplegament al servidor', 'Desplegar una web implica pujar arxius, configurar dominis i assegurar el funcionament del projecte.');
