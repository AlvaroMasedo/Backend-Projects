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
    <h2>Modificar Article</h2>
    <div class="separador"></div>
    <form method="POST" action ="../controller/articles.php?action=modificar">
        <!-- Camp ID per buscar l'entrada que modificar-->
        <label for="id">Escriu l'ID de la entrada que vols modificar</label>
        <input type="text" placeholder="id" name="id" id="id" value="<?php echo htmlspecialchars($id ?? ''); ?>">
        <?php echo $errorId ?? ''; ?>

        <!-- Primer camp del formulari (DNI)-->
        <label for="dni">Nou DNI: </label>
        <input type="text" placeholder="dni" name="dni" id="dni" value="<?php echo htmlspecialchars($dni ?? ''); ?>">
        <?php echo $errorDni ?? ''; ?>

        <!-- Segon camp del formulari (nom)-->
        <label for="nom">Nou Nom: </label>
        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($nom ?? ''); ?>">
        <?php echo $errorNom ?? ''; ?>

        <!-- Tercer camp del formulari (cos)-->
        <label for="cos">Nou Cos: </label>
        <textarea name="cos" id="cos"><?php echo htmlspecialchars($cos ?? ''); ?></textarea>

        <!-- Missatge d'èxit o error -->
        <?php echo $enviatMissatge ?? ''; ?>

        <!-- Quart camp del formulari (enviar cap a la base de dades)-->
        <input type="submit" name="btn-enviar" value="MODIFICAR ARTICLE">

        <!-- Enllaç per tornar a la vista principal -->
        <a href="../../index.php">TORNA ENRRERE</a>
    </form>
</body>
</html>