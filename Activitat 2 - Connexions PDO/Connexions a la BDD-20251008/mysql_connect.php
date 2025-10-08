<?php 
//PRIMERA FORMA DE CONNEXIO A UNA BDD
//procedim a comprovar com es connecta PHP amb MySQL.

//creem una connexió a la BDD
//podem posar localhost o 127.0.0.1
//mysql_connect('servidor', 'usr', 'psw')
//posem la connexió dins d'una variable que direm $connexio
//si la connexió no és correcta, evitem que l'usuari pugui continuar navegant amb DIE
$connexio = mysql_connect('127.0.0.1', 'root', '') or die('No s´ha pogut connectar a la BDD');

mysql_select_db('nom_bdd', $connexio);

//següent pas: executar una consulta SQL
// Podem executar qualsevol consulta SQL, per exemple per a ingressar usuaris en una base de dades.
//ficarem els resultats en $resultats
$resultats = mysql_query('SELECT * FROM usuaris');
//si ara fem un echo, només ens dirà que tenim un recurs id
//mysql_query s'encarrega d'executar qualsevol consulta mysql, per tant també podrieu fer:
//$resultats = mysql_query('INSERT INTO usuaris values ("Sara","Jaume")');

/*
per mostrar-lo per pantalla, farem:
amb mysql_fetch_object recuperem un valor de la fila i avança el punter fins el següent element. Tractarem les files com un objecte:
per exemple:
while($fila = mysql_fetch_object($resultats)){  //retorna el primer valor i posarà el punter apuntant al següent valor de la llista
	echo $fila->norm;
	//o també
	echo $fila->ID;
}
*/
//però si volem accedir a totes les files tractant-les com un array:
while($fila = mysql_fetch_array($resultats)){  //mysql_fetch_array converteix els resultats per a poder-los mostrar per pantalla
	echo $fila['nom'];
	echo "<br />";
}

?>