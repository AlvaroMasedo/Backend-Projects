<?php 

try {
	//nou objecte PDO (connexió,base_de_dades,usuari,password);
	$connexio = new PDO('mysql:host=localhost;dbname=prova_dades', 'root', '');
	//si canviem el nom de la base de dades (dbname), o el password p.ex i executem el fitxer pdo.php ens donarà un error per pantalla de la variable $e
	echo "Connexio correcta!!" . "<br />";
	
	//dos formes d'agafar les dades: per QUERYS o amb PREPARED STATEMENTS >> fitxer pdo_pst.php
	//mètode Query està bé per a consultes senzilles però ens poden injectar codi des de la URL, per tant millor fer servir Prepared Statements
	$resultats = $connexio->query("SELECT * FROM usuaris WHERE id = 2"); //podriem fer també query(INSERT...) per exemple

	foreach($resultats as $fila){  //passem els $resultats i els obtenim per mitjà de $fila
		echo $fila['nom'] . "<br />";
	}

} catch(PDOException $e){ //
	// mostrarem els errors
	echo "Error: " . $e->getMessage();
}

?>
