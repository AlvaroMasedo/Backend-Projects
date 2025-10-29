# Pràctica 02 — Connexions PDO (Alvaro Masedo)

Descripció curta
- Projecte PHP que gestiona articles amb connexió a MySQL via PDO. Proporciona operacions bàsiques: afegir, consultar, modificar i eliminar (segons ID). MVC molt senzill: models per a cada acció, un controlador central i vistes per a la interacció.

Instal·lació i configuració
1. Importa la base de dades:
   - L'script SQL està a `config/Pt02_Alvaro_Masedo.sql`.
   - Importa-ho des de phpMyAdmin o via línia de comandaments.

2. Usuari per phpMyAdmin / connexió:
   - S'ha creat un usuari específic (no s'usa `root`). Per connectar-te canvia les credencials a:
     `config/db_connection.php` — línies on es defineix `$username` i `$password`.
   - També en aquest fitxer pots canviar el port si cal (actualment el DSN usa el port 3366).

Estructura del projecte (resum)
- config/
  - db_connection.php — connexió PDO i credencials.
  - Pt02_Alvaro_Masedo.sql — script de la BBDD.
- app/
  - controller/articles.php — controlador principal (gestiona actions).
  - models/
    - pdo-afegir.php — inserció d'articles.
    - pdo-consultar.php — consulta per DNI (retorna totes les files).
    - pdo-eliminar.php — eliminar per ID.
    - pdo-modificar.php — actualitzar per ID.
  - views/
    - vista_afegir.php
    - vista_consultar.php
    - vista_eliminar.php
    - vista_modificar.php
- resources/CSS/ — estils.

Disseny de la taula (resum)
- id: INT AUTO_INCREMENT PRIMARY KEY — id gestionat pel sistema per cada entrada.
- dni: VARCHAR(...) — identificador de persona; NO es marca com UNIQUE perquè una mateixa persona pot tenir varies entrades.
- nom: VARCHAR(...) — nom de la persona.
- cos: TEXT — contingut de l'article.

Com utilitzar l'aplicació (URLs)
- Obre el navegador a la ruta del projecte (per exemple):
  http://localhost/Pràctiques/Backend/Activitat%202%20-%20Connexions%20PDO/

Notes d'ús i comportament
- Validacions bàsiques al controlador:
  - DNI: regex (8 dígits + lletra).
  - Nom: només lletres i espais (2-50 caràcters).
  - Camps obligatoris: tots els formularis comproven que no estiguin buits.
- Formularis:
  - Si hi ha errors de validació, els valors introduïts es conserven i es mostra un missatge d'error dins de la mateixa vista (no hi ha redirecció a pàgina d'error).
- Consultar:
  - La consulta per DNI retorna totes les entrades coincidint amb el DNI i les munta a una taula dins d'un DIV de la vista.
- Modificar:
  - Per modificar s'introdueix l'ID que es vol canviar i els nous valors; al prémer el botó s'actualitza la fila corresponent.
- Eliminar:
  - Permet eliminar per ID; abans es comprova si l'ID existeix.
