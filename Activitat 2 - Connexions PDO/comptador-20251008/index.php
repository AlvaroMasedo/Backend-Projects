<?php 
function comptar_usuaris(){
	$fitxer = 'comptador.txt';

	if ( file_exists($fitxer) ) {
		$comptats = file_get_contents($fitxer) + 1;
		file_put_contents($fitxer, $comptats);

		return $comptats;
	} else {
		file_put_contents($fitxer, 1);
		return 1;
	}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Comptador de Visites</title>
	<link rel="stylesheet" href="estils.css">
	<link href='https://fonts.googleapis.com/css?family=Oswald:700,400,300' rel='stylesheet' type='text/css'>
</head>
<body>
	<h1>Comptador de Visites</h1>
	<div class="visitants">
		<p class="numero"><?php echo comptar_usuaris(); ?></p>
		<p class="text">Visites</p>
	</div>
</body>
</html>