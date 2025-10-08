<?php 

try {
	//crearem nou objecte PDO (connexió,base_de_dades,usuari,password);
	$connexio = new PDO('mysql:host=localhost;dbname=prova_dades', 'root', '');
	echo "Connexio correcta!!" . "<br />";
	
	//2a forma: amb PREPARED STATEMENTS
    //preparem la consulta i la guardem a la variable $statement (p.ex).
    $statement = $connexio->prepare('SELECT * FROM usuaris WHERE id = :id or nom = :nom');
    //o també si volem inserir:
    $statement = $connexio->prepare('INSERT INTO usuaris (id, nom) VALUES (null, "Josep")');
    //:id i :nom són placeholder (valors temporals) que posem als valors que vull inserir
    //executem la consulta i per tant si ho fem per un valor concret fariem:
    $statement->execute( 
        array(':id' => 5)  //això seria hardcodejar!!
    );
    //però si ho volem fer sobre el valor d'una variable seria per exemple crear la variable $id i aquesta variable $id pren el valor des del GET
    //és a dir: localhost/PractiquesUF1/Practica03/Exemple01/pdo_pst.php?id=5

    //i a l'inici del codi posariem $id = $_GET['id'];
    $statement->execute( 
        array(
        ':id' => $id, 
        ':nom' => $nom)
    );
    //en tot cas, per defecte hauriem de fer el següent, just abans del fetchAll:
    $statement->execute();
    //si a la funció execute volem cridar només un valor, hauriem de fer fetch(), fetchAll() retorna tots els valors
    $resultats = $statement->fetchAll();
    foreach ($resultats as $usuari){
        print_r($usuari);
        //altra forma de printar: echo $usuari['nom'] . '<br>';
    }    

} catch(PDOException $e){ //
	// mostrarem els errors
	echo "Error: " . $e->getMessage();
}

?>