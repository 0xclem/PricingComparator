<?php
include('simplehtmldom_1_5/simple_html_dom.php');

	function createUpdateArray($idArticle, $price, $db)
	/* Ajoute le prix de l'article avec la date de l'exécution du script.
	   args : $idArticle -> id de l'article à mettre à jour.
	   		  $price -> prix courant de l'article.
	   		  $db -> Base de données utilisée.
	*/
	{
		$array_price = array("price" => $price, "time" => new MongoDate());
		$db->articles->update(array("_id" => new MongoId($idArticle)), array('$push' => array('prices'=> $array_price)));
	}

if ($argc != 4) {
	echo "Erreur : Nombre d'arguments incorrect. \n\n";
	echo "usage: crawlerArticle [idSite] [idArticle] [urlArticle]";
	echo "      Mise à jour du prix de l'article \n";
	exit;
}
if ( !preg_match("/^[a-z0-9]{24}$/", $argv[1]) ) {
	echo "Erreur : idSite non valide. \n\n";
	echo "usage: crawlerArticle [idSite] [idArticle] [urlArticle]";
	echo "      Mise à jour du prix de l'article \n";
	exit;
}
if ( !preg_match("/^[a-z0-9]{24}$/", $argv[2]) ) {
	echo "Erreur : idArticle non valide. \n\n";
	echo "usage: crawlerArticle [idSite] [idArticle] [urlArticle]";
	echo "      Mise à jour du prix de l'article \n";
	exit;
}

$m = new Mongo(); // Connexion à Mongo établie.
$db = $m->selectDB("comparator"); // Choix de la base de données.

$site = $db->sites->findOne(array('_id' => new MongoId($argv[1]))); // On trouve le libellé du site grâce à son id 
$idArticle = $argv[2];
$url = $argv[3];

$html = file_get_html($url);

if(strcmp($site['name'], 'Scubastore') == 0) // Parsing du prix de l'article pour le site Scubastore
{
	$prixArticleStr = $html->find('#total_dinamic', 0)->plaintext;
	$prixArticle =  floatval(str_replace(',','.',$prixArticleStr));
	createUpdateArray($idArticle, $prixArticle, $db);
}
elseif (strcmp($site['name'], 'Palanquee') == 0) // Parsing du prix de l'article pour le site Palanquee
{
	$prixArticleStr = $html->find('.productPrice', 0)->plaintext;
	$prixArticle =  floatval(str_replace(',','.',$prixArticleStr));
	createUpdateArray($idArticle, $prixArticle, $db);
}
elseif (strcmp($site['name'], 'VieuxPlongeur') == 0)// Parsing du prix de l'article pour le site VieuxPlongeur
{
	$prixArticleStr = $html->find('.our_price_display', 0)->plaintext;
	$prixArticle =  floatval(str_replace(',','.',$prixArticleStr));
	createUpdateArray($idArticle, $prixArticle, $db);
}
elseif (strcmp($site['name'], 'Bubble-Diving') == 0)// Parsing du prix de l'article pour le site Bubble-Diving
{
	$prixArticleStr = $html->find('.regular-price', 0)->plaintext;
	$prixArticle =  floatval(str_replace(',','.',$prixArticleStr));
	createUpdateArray($idArticle, $prixArticle, $db);
}
elseif (strcmp($site['name'], 'Scubaland') == 0) // Parsing du prix de l'article pour le site Scubaland
{
	$row = $html->find('#table3 tr', 0);
	$i = 0;
	$column = $row->children(0);
	$a = $column->plaintext;

	while (strcmp($a, "Tarif ") != 0) {
		$i++;
		$column = $row->children($i);
		$a = $column->plaintext;
	}
	
	$row = $html->find('#table3 tr', 0)->next_sibling()->next_sibling();
	if ( $i==1 ) {
		$prixArticleStr = $row->find('td',0)->next_sibling()->plaintext;
	}
	elseif ( $i == 2 ) {
		$prixArticleStr = $row->find('td',0)->next_sibling()->next_sibling()->plaintext;
	}
	elseif ( $i == 3 ) {
		$prixArticleStr = $row->find('td',0)->next_sibling()->next_sibling()->next_sibling()->plaintext;
	}
	
	$prixArticle =  floatval(str_replace(',','.',$prixArticleStr));
	createUpdateArray($idArticle, $prixArticle, $db);
}

$m->close();// Fermeture de la connexion à Mongo


?>