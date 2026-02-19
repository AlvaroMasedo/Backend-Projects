# PrjF1 - Validació d'usuaris i registre de sessions

Aquest repositori conté una pràctica de backend en PHP que implementa gestió d'usuaris, autenticació per sessió i un CRUD bàsic d'articles amb permisos segons rol (administrador/usuari normal). Està pensat per executar-se en un entorn local com XAMPP o qualsevol servidor web amb PHP i una base de dades MySQL.

## Contingut i resum de la fase del projecte

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
.htaccess
ReadMe.md
admin/
app/
	controller/
		articles.php
		login.php
		registre.php
		usuari.php
	model/
		model.articles.php
		model.usuari.php
	view/
		vista.afegirArticle.php
		vista.articles.php
		vista.comprobarInfo.php
		vista.footer.php
		vista.header.php
		vista.login.php
		vista.modificarArticle.php
		vista.modificarPerfil.php
		vista.perfil.php
		vista.signup.php
		vista.usuaris.php
config/
	db_connection.php
	Pt04_Alvaro_Masedo.sql
includes/
	session_check.php
lib/
	auth.php
	recaptcha.php
resources/
	css/
		style.afegirArticle.css
		style.articles.css
		style.comprobarInfo.css
		style.footer.css
		style.header.css
		style.index.css
		style.login.css
		style.modificarArticle.css
		style.modificarPerfil.css
		style.perfil.css
		style.signup.css
		style.usuaris.css
	fonts/
		F1/
			Formula1-Bold_web_0.ttf
			Formula1-Regular_web_0.ttf
			Formula1-Wide_web_0.ttf
uploads/
	img/
		fons/
		fotos_perfil/
			foto_predeterminada/
				null.png
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

## Contingut i resum de la fase del projecte

---

# PrjF2 - Miscel·lània

## Credencials d'administrador (prova)

- **Nickname:** `Administrador`
- **Email (fictici):** `admin@gmail.com`
- **Password:** `Administr@d0r`

---

## Noves funcionalitats implementades

### 1. Sistema de "Recorda'm" (Remember Me)

**Descripció:** Sistema de persistència de sessió que permet als usuaris mantenir-se autenticats durant 30 dies sense necessitat de fer login en cada connexió.

**Com funciona:**
- Quan l'usuari marca la casella "Recorda'm" al login, es guarda un token encriptat a la base de dades (taula `usuaris`, columnes `remember_token` i `remember_expires`).
- El token es guarda també en una cookie HTTP-only.
- Quan l'usuari tanca sessió, pot tornar al navegador i ser autenticat automàticament si el token és vàlid i no ha vençut.
- La sessió es restaura automàticament gràcies a `session_check.php` que valida el token en cada petició.
- Per desactivar l'auto-login, hi ha la opció "Tancar sessió i oblidar dispositiu" que elimina el token.

**Fitxers implicats:**
- `app/controller/login.php` — Gestiona la captura del checkbox "recordar" i la creació del token.
- `app/model/model.usuari.php` — Mètodes `guardarRememberToken()`, `obtenirPerToken()`, `eliminarRememberToken()`.
- `includes/session_check.php` — Detecta i valida el token en cada petició.

---

### 2. Modificació de Perfil d'Usuari

**Descripció:** Els usuaris autenticats poden modificar les seves dades de perfil: nickname, nom, cognom i foto de perfil.

**Validacions:**
- **Foto de Perfil:** Tipus permesos: JPEG, PNG, GIF, WEBP. Mida màxima: 5 MB.

**Com funciona:**
- L'usuari accedeix a "Perfil" → "Modificar Perfil".
- El formulari pre-omple les dades actuals de l'usuari.
- Quan envia, el controlador valida els camps i, si tot és correcte, actualitza la base de dades i la sessió.
- La foto anterior (si existia) és reemplaçada per la nova.

**Fitxers implicats:**
- `app/controller/usuari.php?action=modificar` — Gestiona el formulari de modificació.
- `app/view/vista.modificarPerfil.php` — Vista amb formulari de modificació.
- `app/model/model.usuari.php` — Mètode `modificar()`.

---

### 3. Filtrat i Ordenació d'Articles

**Descripció:** La llista d'articles suporta múltiples opcions de visualització i ordenació.

**Funcionalitats:**
- **Ordenació:** 
  - Més recents (data descendent)
  - Més antics (data ascendent)
  - Alfabèticament (A-Z)
  - Alfabèticament (Z-A)
- **Filtrat per autor:** 
  - Usuaris normals: veuen només els seus articles.
  - Administradors: veuen tots els articles.
- **Marca de data:** Cada article mostra la data de la última modificació en format `dd/mm/yyyy`.

**Fitxers implicats:**
- `app/controller/articles.php` — Lògica de paginació, filtrat i ordenació.
- `app/model/model.articles.php` — Mètode `obtenirPaginat()`.
- `app/view/vista.articles.php` — Visulització dels articles amb controls de paginació i ordenació.

---

### 4. Barra de Búsqueda d'Articles

**Descripció:** Sistema de búsqueda per nom d'article disponible a la barra de navegació (header).

**Com funciona:**
- La barra de búsqueda és visible a totes les pàgines (en el header).
- L'usuari pot buscar articles per nom (búsqueda case-insensitive, coincidències parcials).
- Els resultats es mostren amb la mateixa paginació i ordenació que els articles normals.
- El paràmetre de búsqueda (`q`) es manté a través de la paginació i canvis d'ordenació.
- Si no hi ha resultats, es mostra un missatge indicant que no s'han trobat articles.

**Fitxers implicats:**
- `app/controller/articles.php` — Gestiona el paràmetre `q` i crida als mètodes de búsqueda.
- `app/model/model.articles.php` — Mètodes `buscar()` i `contarBusqueda()`.
- `app/view/vista.header.php` — Barra de búsqueda HTML amb formulari.
- `resources/css/style.header.css` — Estilos per a la barra de búsqueda.
- `index.php` — Mostra missatge especial quan no hi ha resultats de búsqueda.

---

### 5. Gestió d'Usuaris (Admin Only)

**Descripció:** Els administradors poden veure, gestionar i eliminar usuaris del sistema.

**Funcionalitats:**
- **Llistar usuaris:** Vista amb targetes que mostren nickname (gran), nom (petit) i botó d'eliminació.
- **Eliminar usuaris:** L'admin pot eliminar qualsevol usuari excepte si mateix.
- **Eliminació en cascada:** Quan s'elimina un usuari, es borra automàticament tot el seu registre (veure secció "Eliminació en cascada" per més detalls).

**Accés:**
- Només administradors (`administrador == 1`) can accedir a aquesta secció.
- Al desplegable del menú de usuari hi apareix una opció "Usuaris" (només visible per admin).

**Fitxers implicats:**
- `app/controller/usuari.php?action=llistar` — Carrega la llista d'usuaris.
- `app/controller/usuari.php?action=eliminar` — Elimina un usuari amb neteja en cascada.
- `app/view/vista.usuaris.php` — Vista amb llista de usuaris i botó d'eliminació.
- `app/model/model.usuari.php` — Mètodes `obtenirTots()`, `obtenirPerNickname()`, `eliminar()`.
- `app/model/model.articles.php` — Mètode `eliminarPerAutor()` (per a eliminació en cascada).
- `app/view/vista.header.php` — Menú desplegable amb opció "Usuaris" (condicional).

---

### 6. Eliminació en Cascada

**Problema que resol:**
Quan s'elimina un usuari del sistema, no podem deixar articles órfans (articles amb un `autor` que ja no existeix a la taula `usuaris`). Això violaria la integritat referencial de la base de dades i generaria dades inconsistents.

**Solució implementada:**
Quan un administrador elimina un usuari, el sistema executa els passos següents en ordre:

1. **Obtenció de dades de l'usuari:** Es recuperen totes les dades de l'usuari inclosa la ruta de la foto de perfil.
2. **Eliminació de fitxer de foto:** La foto de perfil es buida del disc dur (carpeta `uploads/img/fotos_perfil/`).
3. **Eliminació d'articles associats:** Tots els articles creats per aquest usuari es borren de la taula `articles` (mètode `eliminarPerAutor()`).
4. **Eliminació de l'usuari:** Finalment, el registre del usuari es borra de la taula `usuaris`.

**Per què és important:**
- **Integritat de dades:** Evita tenir articles sense autor.
- **Neteja del sistema:** No deixa arxius órfans al servidor.
- **Seguretat:** Elimina completament el registre de l'usuari i la seva informació personal (foto).

**Fitxers implicats:**
- `app/controller/usuari.php` — Lògica de eliminació amb neteja en cascada (línes 149-185).
- `app/model/model.articles.php` — Mètode `eliminarPerAutor()`.
- `app/model/model.usuari.php` — Mètode `eliminar()`.