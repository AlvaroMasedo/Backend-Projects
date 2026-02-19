<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Formulari</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
</head>

<body>
    <h1>FORMULARI</h1>
    <div class="separador"></div>
    <form method="POST">
        <!-- Primer camp del formulari (nom)-->
        <label for="nom">Nom </label> <?php echo $errorNom; ?>
        <input type="text" placeholder="Nom" name="nom" id="nom" value="<?php echo htmlspecialchars($nom); ?>">

        <!-- Segon camp del formulari (email)-->
        <label for="email">Email </label> <?php echo $errorEmail; ?>
        <input type="email" id="email" name="email" placeholder="correu@exemple.com" value="<?php echo htmlspecialchars($email); ?>">

        <!-- Tercer camp del formulari (missatge)-->
        <label for="missatge">Missatge </label> <?php echo $errorMissatge; ?>
        <textarea name="missatge" id="missatge"><?php echo htmlspecialchars($missatge); ?></textarea>

        <!-- Quart camp del formulari (enviar)-->
        <input type="submit" name="btn-enviar" value="ENVIAR">
        <?php echo isset($enviatMissatge) ? $enviatMissatge : ''; ?>
    </form>
</body>
</html>
