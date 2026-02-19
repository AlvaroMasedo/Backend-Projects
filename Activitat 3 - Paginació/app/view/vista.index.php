<!DOCTYPE html>
<html lang="ca">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="resources/css/style.css">
  <title>Activitat 3 - Paginació</title>
  <script src="https://c.webfontfree.com/c.js?f=Formula1-Display-Bold" type="text/javascript"></script>
</head>
<body>
  <!-- Capçalera principal -->
  <h1>Pràctica 03 - Paginació</h1>
  <h2>Artícles</h2>
  <div class="separador"></div>

  <!-- Secció de selecció de articles per pàgina -->
  <div class="articles">
    <h3>Selecciona els artícles que vols veure per pàgina</h3>

    <!-- Formulari per seleccionar nombre de articles -->
    <form method="get">
      <!-- Selector amb opcions de 1 al 10 -->
      <select name="per_page" id="articles" onchange="this.form.submit()">
        <?php for ($i = 1; $i <= 10; $i++): ?>
          <!-- Opció seleccionada segons el valor actual -->
          <option value="<?= $i ?>" <?= ($i === $articlesPerPagina ? 'selected' : '') ?>>
            <?= $i ?>
          </option>
        <?php endfor; ?>
      </select>
      <!-- Camp ocult per mantenir la pàgina 1 quan es canvia el nombre d'articles -->
      <input type="hidden" name="page" value="1">
    </form>
  </div>

    <!-- Quadrícula d'articles -->
    <div class="cards-grid">
        <?php foreach ($articles as $a): ?>
            <!-- Targeta individual per cada article -->
            <article class="card">
                <!-- Capçalera de la targeta amb el títol -->
                <header class="card__header">
                    <h2 class="card__title"><?= htmlspecialchars($a['Nom']) ?></h2>
                </header>
                <!-- Cos de la targeta amb el contingut -->
                <div class="card__body">
                    <p><?= nl2br(htmlspecialchars($a['Cos'])) ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <!-- Controls de paginació -->
    <div class="paginacio">
        <!-- Botó anterior (desactivat a la primera pàgina) -->
        <a class="page-btn <?= ($paginaActual==1?'is-disabled':'') ?>" href="<?= $prevUrl ?>">&laquo; Anterior</a>
        <!-- Indicador de pàgina actual -->
        <span class="page-state">Pàgina <?= $paginaActual ?> de <?= $totalPagines ?></span>
        <!-- Botó següent (desactivat a l'última pàgina) -->
        <a class="page-btn <?= ($paginaActual==$totalPagines?'is-disabled':'') ?>" href="<?= $nextUrl ?>">Següent &raquo;</a>
    </div>
</body>
</html>