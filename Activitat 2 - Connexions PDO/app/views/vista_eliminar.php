    <!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Formulari</title>
    <link rel="stylesheet" href="../../resources/CSS/style_vista.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
</head>

<body>
    <h1>Pràctica 02 - Connexions PDO</h1>
    <h2>Eliminar Article</h2>
    <div class="separador"></div>
    <form method="POST" action ="../controller/articles.php?action=eliminar">
        <!-- Camp per buscar la entrada que eliminar-->
        <label for="id">Escriu l'ID de la entrada que vols eliminar</label>
        <input type="text" placeholder="id" name="id" id="id" value="<?php echo htmlspecialchars($id ?? ''); ?>">
        <?php echo $errorId ?? ''; ?>

        <!-- Missatge d'èxit o error -->
        <?php echo $enviatMissatge ?? ''; ?>

        <!-- Quart camp del formulari (enviar cap a la base de dades)-->
        <input type="submit" name="btn-enviar" value="ELIMINAR ARTICLE">

        <!-- Enllaç per tornar a la vista principal -->
        <a href="../../index.php">TORNA ENRRERE</a>    </form>
</body>
</html>

