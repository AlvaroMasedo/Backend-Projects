#  PrjF1 - Validació d'usuaris i registre de sessions

Projecte educatiu de gestió d'usuaris i articles per a DAW.

---

##  Qué té el projecte?

 **Sistema d'Autenticació**
- Login i registre local (email + contrasenya)
- Login amb Google (OAuth2)
- Recuperar contrasenya per email
- "Recorda'm" (estàs loguejat 30 dies)

**Articles** 
- Crear, editar, eliminar articles
- Paginació (8 articles per pàgina)
- Búsqueda per título
- Permisos: l'autor (o admin) pot editar/eliminar

 **Perfil d'Usuari**
- Ver perfil
- Editar nickname, nom, cognom
- Pujar foto de perfil

 **Admin**
- Veure llista d'usuaris
- Eliminar usuaris (elimina articles automàticament)

---

##  Estructura (MVC)

```
app/
├── model/          → Connecció BD (queries, prepared statements)
├── controller/     → Lògica negoci (fluxes, validacions)
└── view/           → Formularis i vistes (HTML)

config/            → Configuració BD + .env secrets
includes/          → session_check.php (manages sessions)
lib/               → OAuth, reCAPTCHA, email (PHPMailer)
resources/css/     → Estils
uploads/           → Fotos d'usuaris
```

---

## ⚙️ Per qué aquesta estructura?

**Model separada de Controller:**
- Reutilitzar queries als models
- Facilitar canvis BD sense editar controllers
- Més fàcil de testear

**PDO Prepared Statements (no SQL directe):**
```php
// Insegur - SQL Injection
SELECT * FROM usuaris WHERE email = '$email'

//  Segur
SELECT * FROM usuaris WHERE email = :email
```

**Remember-Me (token persistent):**
- Sessió normal: expira tancar navegador (seguretat)
- Remember-token: recordar 30 dies (comoditat)

**Pàgina de confirmació al registre:**
- Usuari revisa dades antes de guardar
- Evita errors

**reCAPTCHA solo après 3 intents fallits:**
- UX millor (usuaris normals no veuen CAPTCHA)
- Seguretat contra força bruta

---

##  Instal·lació

### 1. Base de Dades
```
- Obrir http://localhost/phpmyadmin
- Crear BD: articles_f1  
- Import: config/Pt04_Alvaro_Masedo.sql
```

### 2. Configurar BD
Editar `config/db_connection.php`:
```php
define('DB_HOST', 'localhost:3366');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'articles_f1');
```

### 3. Google OAuth (opcional)
Crear `.env` a arrel projecte:
```ini
GOOGLE_CLIENT_ID=vostre_id
GOOGLE_CLIENT_SECRET=vostre_secret
GOOGLE_OAUTH_EMAIL=vostre_email@gmail.com
GOOGLE_OAUTH_PASSWORD=app_password
```

### 4. Probar
`http://localhost/[carpeta-projecte]` → Register → Login → Create Article

---

##  Fluxes Principals

### Login Local
```
1. Email + contrasenya
2. Búsqueda BD
3. Si 3+ intents fallits → mostrar reCAPTCHA
4. Si correcte → $_SESSION['usuari'] = datos
5. Si "Recorda'm" → guardar token 30 dies
```

### Registre
```
1. Nickname, email, contrasenya
2. Validar força contrasenya
3. Mostra confirmació (revision)
4. Usuari confirma → guardar BD + login automàtic
```

### OAuth Google
```
1. Clic "Login amb Google"
2. Tot redirects a Google
3. Usuari autoritza
4. Google retorna email
5. Si existeix en BD → login
6. Si NO → crear compte automàticament + login
```

### Recuperar Contrasenya
```
1. Entrar email
2. Rebre email amb link 24h
3. Obrir link → formulari nova contrasenya
4. Guardar nova contrasenya
```

### CRUD Articles
```
Crear:     Formulari → Guardar BD
Veure:     Llista cón paginació
Editar:    Solo si eres autor o admin
Eliminar:  Solo si eres autor o admin
```

---

##  Schema BD (Simplificat)

```sql
-- Usuaris
CREATE TABLE usuaris (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nickname VARCHAR(50) UNIQUE,
    email VARCHAR(255) UNIQUE,
    contrasenya VARCHAR(255),
    nom VARCHAR(100),
    cognom VARCHAR(100),
    imatge_perfil VARCHAR(255),
    administrador TINYINT(1) DEFAULT 0,   -- 1=admin, 0=normal
    oauth_provider VARCHAR(50),             -- google o NULL
    oauth_id VARCHAR(255),
    remember_token VARCHAR(64),             -- token 30 dies
    remember_expires DATETIME
);

-- Articles
CREATE TABLE articles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(255),
    cos TEXT,
    autor_id INT,
    data_creacio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_ultima_edicio DATETIME,
    FOREIGN KEY (autor_id) REFERENCES usuaris(id)
);
```

---

## 🛡️ Seguretat

**Actualment (OK per pràctica educativa):**
-  PDO Prepared Statements (prevé SQL injection)
-  Sessions segures (HttpOnly + SameSite)
-  reCAPTCHA (atacs força bruta)
-  SHA-256 per contrasenya (no és ideal)

**En PRODUCCIÓ hauríem de:**
```php
//  Actual (no és prou)
$hash = sha1($contrasenya);

//  Producció (millor)
$hash = password_hash($contrasenya, PASSWORD_ARGON2ID);
if (password_verify($contrasenya, $hash)) { OK }
```

**Altres millores per producció:**
-  HTTPS obligatori
-  NO commitar `.env` (include al .gitignore)
-  XSS prevention: `htmlspecialchars()` al output
-  Rate limiting en login

---

## Funcionalitats Noves

### 1. Remember-Me (Recorda'm)
- Checkbox "Recorda'm" al login
- Llei token 30 dies si l'usuari ho marca
- Sessió es restaura automàticament

### 2. Modificar Perfil
- Editar nickname, nom, cognom
- Pujar foto (JPEG/PNG/GIF/WebP, máx 5MB)

### 3. Búsqueda Articles
- Barra a la navegació
- Búsqueda case-insensitive per título

### 4. Filtrat Articles
- Per autor
- Usuaris normals: veuen solo els seus
- Admin: veu tots

### 5. Admin Panel
- Veure llista d'usuaris
- Eliminar usuaris
- Quando s'elimina usuario → elimina articles automàticament (eliminacio en cascada)

---

## Probes Ràpides

```
1. Registrar usuari novo
2. Login | Logout
3. Marcar "Recorda'm" → tancar navegador → reobrir → loguejat?
4. Crear article
5. Editar article (otro user NO pot)
6. Eliminar article
7. Com admin: veure secció "Usuaris"
8. Com admin: eliminar usuari (articles elimats automàticament)
```

---


## Recursos

- [OWASP](https://owasp.org/)
- [PHP Password](https://www.php.net/manual/es/function.password-hash.php)
- [PDO](https://www.php.net/manual/es/book.pdo.php)
- [Google OAuth](https://developers.google.com/identity/protocols/oauth2)
- [PHPMailer](https://github.com/PHPMailer/PHPMailer)

---

**Álvaro Masedo Pérez** | DAW | Març 2026
