<?php 
//segona FORMA DE CONNEXIO A UNA BDD, MITJANÇANT PDO. MÉS SEGUR I MÉS FÀCIL

//afegim l'estrutura try-catch que mostrarà error en cas de que el trobi.
try {
	//crearem un nou objecte PDO (connexió,base_de_dades,usuari,password);
	//amb connexions PDO, podem afegir un altra connexió que no sigui mysql, a diferència de mysql_connect que només serà mysql		
	//dintre del try anirem mostrant les dades de la bdd. Ho podriem fer fora del try-catch però llavors no comprovariem si hi ha errors
	$connexio = new PDO('mysql:host=localhost;dbname=prova_dades', 'root', '');	
	echo "Connexio correcta!!" . "<br />";
	
	//en PDO hi ha dos formes d'agafar les dades: per QUERYS o amb PREPARED STATEMENTS
	//mètode Query està bé per a consultes senzilles però ens poden injectar codi des de la URL, per tant millor fer servir Prepared Statements
	$resultats = $connexio->query("SELECT * FROM usuaris WHERE id = 2"); //podriem fer també query(INSERT...) per exemple

	foreach($resultats as $fila){  //passem els $resultats i els obtenim per mitjà de $fila
		echo $fila['nom'] . "<br />";
	}

} catch(PDOException $e){ //
	// mostrarem els errors
	echo "Error: " . $e->getMessage();
	//** altra forma si volem personalitzar la sortida per pantalla del nostre error */
	//**    echo "Error al conectar-se a la base de dades."; */
  	//**    die(); */

}

?>
