# Pràctica 03 – Paginació

Aquesta pràctica consisteix a desenvolupar un petit sistema web que mostra articles emmagatzemats a una base de dades **MySQL** i implementa **paginació dinàmica**, permetent que l’usuari triï quants articles vol veure per pàgina.

El projecte està desenvolupat amb **PHP** i **HTML + CSS** 

---

## Estructura del projecte

Activitat 3 - Paginació/
│
├── index.php # Controlador principal (lògica PHP)
├── config/
│       └── db_connection.php # Connexió a la base de dades
│       └── Pt03_Alvaro Masedo.sql # Script per crear la base de dades amb algunes entrades
│
├── app/
│ └── view/
│       ├── vista.index.php # Vista principal (HTML + PHP)
│       └── vista.error.php # Vista d’error en cas de no haver-hi dades
│
├── resources/
│ └── css/
│       └── style.css # Estils propis (base + targetes + paginació)
│
└── README.md # Document explicatiu del projecte

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

