<?php require_once __DIR__ . '/config/basepath.php'; ?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error 404 - Pàgina no trobada</title>
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/resources/css/style.error404.css">
</head>
<body>
    <div class="error-container">
        <h1 class="error-code">404</h1>
        <h2 class="error-title">Pàgina no trobada</h2>
        <img src="<?php echo BASE_PATH; ?>/uploads/img/Formula1.webp" alt="F1 Logo" class="f1-logo">
        <p class="error-message">
            Ho sentim, la pàgina que estàs buscant no es pot trobar. 
            Pot ser que hagi estat moguda o que l'adreça sigui incorrecta.
        </p>
        <div class="actions">
            <a href="<?php echo BASE_PATH; ?>/index.php" class="btn btn-primary">Tornar a l'inici</a>
            <a href="<?php echo BASE_PATH; ?>/app/view/vista.articles.php" class="btn btn-secondary">Veure articles</a>
        </div>
    </div>
</body>
</html>
