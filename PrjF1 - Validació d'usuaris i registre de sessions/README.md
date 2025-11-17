# Pràctica: Validació d'usuaris i registre de sessions (Projecte F1)

Aquest repositori conté una pràctica de backend en PHP que implementa gestió d'usuaris, autenticació per sessió i un CRUD bàsic d'articles amb permisos segons rol (administrador/usuari normal). Està pensat per executar-se en un entorn local com XAMPP o qualsevol servidor web amb PHP i una base de dades MySQL.

## Contingut i resum del projecte

- `index.php` — Pàgina principal que carrega articles públics o mostra opcions segons si l'usuari està autenticat.
- `app/controller/articles.php` — Controlador central per a la lògica dels articles: càrrega, paginació i accions (afegir, modificar, eliminar).
- `app/model/pdo.articles.php` — Model que encapsula les operacions PDO contra la taula `articles` (obtenir, afegir, modificar, eliminar).
- `app/view/` — Vistes PHP per les diferents pantalles:
	- `vista.articles.php` — Llista d'articles (amb opcions de modificar/eliminar per a l'usuari corresponent).
	- `vista.modificarArticle.php` — Formulari per modificar un article (pré-omplert pel controlador).
	- `vista.afegirArticle.php` — Formulari per afegir un article.
	- `vista.eliminarArticle.php` — Confirmació d'eliminació (formulari POST amb hidden id).
	- Vistes d'autenticació i perfil (`vista.login.php`, `vista.signup.php`, `vista.perfil.php`, `vista.registrat.php`).
- `app/model/pdo.*.php` — Models relacionats amb l'autenticació i registre d'usuaris.
- `config/db_connection.php` — Configuració de connexió PDO a MySQL (edita per posar usuari/contrassenya/host/BD).
- `includes/session_check.php` — Gestió de sessió i tancament (logout). Aquest fitxer s'inclou a les vistes/controladors per garantir que `$_SESSION` estigui disponible.
- `resources/css/` — Estils CSS per les diferents vistes.

## Estructura de carpetes

```
index.php
app/
	controller/
		articles.php
	model/
		pdo.articles.php
		pdo.loginUser.php
		pdo.registrarUser.php
		pdo.consultarUser.php
	view/
		vista.articles.php
		vista.modificarArticle.php
		vista.afegirArticle.php
		vista.eliminarArticle.php
		vista.login.php
		vista.signup.php
		vista.perfil.php
config/
	db_connection.php
includes/
	session_check.php
resources/
	css/
uploads/
	img/
```

## Requeriments

- Extensió PDO i driver PDO_MySQL.
- Servidor MySQL (o MariaDB).
- Entorn d'execució local com XAMPP, WAMP o similar (per a Windows: XAMPP és la via recomanada).

## Configuració ràpida

1. Copia o edita `config/db_connection.php` amb les credencials de la teva base de dades (host, nom d'usuari, contrasenya, nom de BD).
2. Importa l'script SQL `config/Pt04_Alvaro_Masedo.sql` a la teva base de dades per crear les taules i dades inicials.
	 - En XAMPP pots fer-ho amb phpMyAdmin o amb la línia de comandes:

```sql
-- Exemple (phpMyAdmin o client MySQL)
SOURCE path/to/config/Pt04_Alvaro_Masedo.sql;
```

3. Coloca el projecte dins de la carpeta pública del teu servidor (per XAMPP: `C:\xampp\htdocs\NomCarpeta`).
4. Obre el navegador a `http://localhost/NomCarpeta/`.

## Fluix de l'aplicació (com funciona)

1. L'usuari pot registrar-se i fer login. Quan inicia sessió, es guarda `$_SESSION['usuari']` amb informació com `nickname`, `administrador` (0/1), `nom`, `cognom`, `email`.
2. Quan s'accedeix a la pàgina d'articles, el controlador `app/controller/articles.php` carrega els articles segons el rol:
	 - Si l'usuari és administrador (`administrador == 1`): es mostren tots els articles.
	 - Si l'usuari és normal: es mostren només els articles creats pel seu `nickname`.
	 - Si és usuari anònim i estàs a `index.php`, es poden mostrar articles públics .
3. Les operacions CRUD d'articles s'exposen via `app/controller/articles.php` mitjançant el paràmetre GET `action` (`afegir`, `modificar`, `eliminar`). Els formularis fan POST cap al mateix controlador, que valida i fa les crides al model (`PdoArticles`).
4. Per a `modificar` i `eliminar`, abans de fer canvi al BD el controlador verifica que l'usuari té permisos: o bé és administrador o bé és l'autor de l'article.

## Flux detallat de la web (pas a pas)

Aquest apartat descriu com flueixen les peticions i on es comproven permisos — útil per entendre el projecte.

1) Arrencada i sessió
	- Les vistes inclouen `includes/session_check.php` per assegurar que `session_start()` està cridat i per gestionar el logout.
	- Quan un usuari fa login, el sistema guarda en `$_SESSION['usuari']` un array amb dades principals. Exemples de claus:

```php
$_SESSION['usuari'] = [
  'nickname' => 'Masedo',
  'administrador' => 0, // 1 per admin
  'nom' => 'Álvaro',
  'cognom' => 'Masedo',
  'email' => 'alvaro@example.com'
];
```

2) Carrega de la llista d'articles
	- `index.php` i `app/view/vista.articles.php` inclouen `app/controller/articles.php` per obtenir les variables `$articles`, `$paginaActual`, `$totalPagines`, etc.
	- El controlador (`articles.php`) decideix quins articles carregar:
	  - Admin: crida `PdoArticles->obtenirTots()`.
	  - Usuari normal: crida `PdoArticles->obtenirPerNickname($nickname)`.
	  - Anònim: per la pàgina `index.php` pot mostrar tots els articles, però a `vista.articles.php` l'usuari anònim rep un missatge que ha d'iniciar sessió.
	- Paginació: el controlador suporta `per_page` (número o `all`). Si `per_page=all` el controlador retorna tots els articles en una sola pàgina; si no, fa `array_slice()` sobre el llistat resultant

3) Afegir un article
	- Vista: `vista.afegirArticle.php` mostra un formulari amb `action="../controller/articles.php?action=afegir"` i `method="post"`.
	- Controlador: a l'acció `afegir` comprova que hi ha sessió (`$autor` no és null), valida camps i crida `$pdoArticles->afegir($autor, $nom, $cos)`.
	- Resposta: en cas d'èxit el controlador defineix un missatge d'èxit o redirigeix a la llista d'articles.

4) Modificar un article
	- Accés: des de la llista l'enllaç a modificar passa `?id=NN` a `vista.modificarArticle.php`.
	- Prefill: quan la vista inclou el controlador, aquest detecta `vista.modificarArticle.php?id=...` i crida `obtenirPerId($id)` per pré-omplir `$nom`, `$cos` i `$id` si l'usuari té permisos (autor o admin).
	- Enviament: el formulari fa POST amb `action=modificar` i el controlador valida i actualitza amb `PdoArticles->modificar($id,$nom,$cos)`.

5) Eliminar un article
	- Flux de confirmació: hi ha dues variants (modal a la vista o pàgina de confirmació `vista.eliminarArticle.php?id=...`). La pràctica actual implementa la pàgina de confirmació que rep `?id=NN`, carrega l'article i comprova permisos.
	- Formulari de confirmació: conté un `input type="hidden" name="id" value="..."` i fa POST a `action=eliminar`.
	- A l'acció `eliminar`, el controlador torna a validar existència i permisos abans de cridar `PdoArticles->eliminar($id)`.
	- Si el `id` arriba buit o l'usuari no té permisos, el controlador estableix `$missatge` i la vista el mostra (missatge d'error explicatiu).

6) Missatges i errors
	- Les vistes mostren variables com `$missatge`, `$errorNom`, etc., que el controlador defineix quan cal.

## Endpoints / Paràmetres importants

- `app/controller/articles.php?action=afegir` (POST): afegir article. Params: `Nom`, `Cos`.
- `app/controller/articles.php?action=modificar` (POST): modificar article. Params: `id`, `Nom`, `Cos`.
- `app/controller/articles.php?action=eliminar` (POST): eliminar article. Params: `id`.

Les vistes de formulari acostumen a cridar el controlador amb la ruta relativa `../controller/articles.php?action=...` (com a l'arbre de vistes actual).

## Proves i comprovacions ràpides

- Prova que la sessió arranca correctament: inicia sessió amb un usuari i comprova que `$_SESSION['usuari']` existeix.
- Prova CRUD:
	1. Crear un article amb un usuari normal.
	2. Desconnectar-se i entrar com a admin: comprova que l'admin veu tots els articles.
	3. Des d'un usuari diferent sense permisos, intenta accedir a `vista.modificarArticle.php?id=...` i comprova que reps un missatge d'error o que no es permet la modificació.
	4. Prova l'eliminació: la vista de confirmació ha de rebre el `id` i, al fer POST, el controlador ha de validar i eliminar.

## Arxius SQL

- `config/Pt04_Alvaro_Masedo.sql` — script amb l'estructura i dades inicials de la base de dades.

