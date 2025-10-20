<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Formulari</title>
    <link rel="stylesheet" href="../CSS/style_vista.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
</head>

<body>
    <h1>Pràctica 02 - Connexions PDO</h1>
    <h2>Afegir Article</h2>
     <form method="POST" action ="../controller/articles.php?action=afegir">
        <!-- Primer camp del formulari (DNI)-->
        <label for="dni">DNI: </label>
        <input type="text" placeholder="dni" name="dni" id="dni">

        <!-- Segon camp del formulari (nom)-->
        <label for="nom">Nom: </label> 
        <input type="text" id="nom" name="nom" >

        <!-- Tercer camp del formulari (cos)-->
        <label for="cos">Cos: </label> 
        <textarea name="cos" id="cos"   ></textarea>

        <!-- Quart camp del formulari (enviar cap a la base de dades)-->
        <input type="submit" name="btn-enviar" value="AFEGIR ARTICLE">

        <!-- Enllaç per tornar a la vista principal -->
        <a href="../index.php">TORNA ENRRERE</a>
    </form>
</body>
</html>
