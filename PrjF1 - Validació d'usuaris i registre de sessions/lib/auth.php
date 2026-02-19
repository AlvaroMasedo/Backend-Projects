<?php
declare(strict_types=1);
//Álvaro Masedo Pérez
// Auth helpers centralitzats

// No iniciem sessió aquí: s'assumeix que `includes/session_check.php` ho fa
if (session_status() === PHP_SESSION_NONE) {
    
}

/** Retorna l'usuari actual o null */
function usuari_actual(): ?array {
    return $_SESSION['usuari'] ?? null;
}

/** Retorna el nickname de l'usuari actual o null */
function nickname_usuari_actual(): ?string {
    $u = usuari_actual();
    return $u['nickname'] ?? null;
}

/** Retorna true si l'usuari actual és administrador */
function usuari_es_admin(): bool {
    $u = usuari_actual();
    return ((int)($u['administrador'] ?? 0)) === 1;
}

/** Redirigeix a login si no hi ha sessió */
function requerir_inici_sessio_o_redirigir(string $redirect = '../view/vista.login.php'): void {
    if (nickname_usuari_actual() === null) {
        header('Location: ' . $redirect);
        exit;
    }
}

/** Retorna true si l'usuari pot gestionar (editar/eliminar) l'article */
function pot_usuari_gestionar_article(array $article): bool {
    $u = usuari_actual();
    if (! $u) {
        return false;
    }
    if (usuari_es_admin()) {
        return true;
    }
    $nickname = $u['nickname'] ?? null;
    return isset($article['autor']) && $article['autor'] === $nickname;
}
