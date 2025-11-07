# Pràctica 03 – Paginació

Aquesta pràctica consisteix a desenvolupar un petit sistema web que mostra articles emmagatzemats a una base de dades **MySQL** i implementa **paginació dinàmica**, permetent que l’usuari triï quants articles vol veure per pàgina.

El projecte està desenvolupat amb **PHP** i **HTML + CSS** 

---

## Funcionament general

1. **`index.php`** és el punt d’entrada.  
   Gestiona la connexió, rep els paràmetres de la URL (`page` i `per_page`), calcula la paginació i obté els articles necessaris mitjançant SQL amb `LIMIT` i `OFFSET`.

2. **`vista.index.php`** s’encarrega únicament de mostrar la informació en format HTML.  
   Utilitza bucles PHP per imprimir els articles, mantenint el nombre d’elements per pàgina seleccionat per l’usuari.

3. **`db_connection.php`** crea la connexió a MySQL mitjançant PDO i gestiona possibles errors amb `try...catch`.

---

Autor

Álvaro Masedo
Alumne de Desenvolupament d’Aplicacions Web (DAW)

