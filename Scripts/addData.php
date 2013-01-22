<?php

	function addData($idArticle, $db) {
		
		//$time = time() - (100000);
		
		//echo new MongoDate($time);
		$result = array();
		
		for($i=0;$i<500;$i++) {
			$time = time() - (200000 * $i);
			$price = rand(175,199);
			
			array_unshift($result, array("price" => $price, "time" => new MongoDate($time)));
		}
		$db->articles->update(array("_id" => new MongoId($idArticle)), array('$set' => array('prices'=> $result)));
	}
	
	$m = new Mongo(); // Connexion à Mongo établie.
	$db = $m->selectDB("comparator"); // Choix de la base de données.

	addData('50d7644cb7df62bcb49cd4b0', $db);
	addData('50d763d167577bbedeba2b70', $db);
	addData('50d76434b7df62bcb49cd4af', $db);
	

?>