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
    <h2>Consultar Article</h2>
    <div class="separador"></div>
    <form method="POST" action ="../controller/articles.php?action=consultar">
        <!-- Camp per buscar la entrada que eliminar-->
        <label for="dni">Escriu el DNI de la entrada que vols cercar</label>
        <input type="text" placeholder="dni" name="dni" id="dni" value="<?php echo htmlspecialchars($dni ?? ''); ?>">
        <?php echo $errorDni ?? ''; ?>


        <!-- Quart camp del formulari (enviar cap a la base de dades)-->
        <input type="submit" name="btn-enviar" value="CONSULTAR ARTICLE">

        <!-- Enllaç per tornar a la vista principal -->
        <a href="../../index.php">TORNA ENRRERE</a>
    </form>
    <!-- Panell on mostra les entrades consultades-->
        <div>
            <?php if (!empty($resultats)): ?>
                <table>
                    <thead>
                        <tr>
                            <?php foreach (array_keys($resultats[0]) as $col): ?>
                                <th><?php echo htmlspecialchars($col); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultats as $row): ?>
                            <tr>
                                <?php foreach ($row as $cell): ?>
                                    <td><?php echo nl2br(htmlspecialchars((string)$cell)); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
</body>
</html>

