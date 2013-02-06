<?php

include('lib/ExcelExport.class.php5');

$m = new Mongo(); // Connexion à Mongo établie.
$db = $m->selectDB("comparator"); // Choix de la base de données.

$articles = $db->articles_base->find();

$columns = array(
    '1' => array('title' => 'Articles/Sites', 'type' => ExcelExport::STRING),
    '2' => array('title' => 'Palanquee', 'type' => ExcelExport::STRING),
    '3' => array('title' => 'VieuxPlongeur', 'type' => ExcelExport::STRING),
    '4' => array('title' => 'Bubble-Diving', 'type' => ExcelExport::STRING),
    '5' => array('title' => 'Scubastore', 'type' => ExcelExport::STRING),
    '6' => array('title' => 'Scubaland', 'type' => ExcelExport::STRING),
  );

$excel_export = new ExcelExport($columns);
$excel_export = new ExcelExport($columns, 'fr');

$fp = fopen ("export.xls", "w");

$contents = array();

foreach ($articles as $art) {
	$name = $art['name'];
	$palanquee = 'N/A';
	$vieuxplongeur = 'N/A';
	$bubble = 'N/A';
	$scubastore = 'N/A';
	$scubaland = 'N/A';
	
	foreach ($art['_articles'] as $match) {
		$matched = '';
		$price = '';
		
		$matched = $db->articles->findOne(array('_id' => new MongoId($match)));
		$price = end($matched['prices']);
		echo $price['price']."\n";
		
		if (strcmp($matched['_site'], "5102e6c63a81b414e086564e") == 0) {
			$palanquee = $price['price'];
		}
		elseif (strcmp($matched['_site'], "5102e6c73a81b414e086564f") == 0) {
			$vieuxplongeur = $price['price'];
		}
		elseif (strcmp($matched['_site'], "51033c533a81b414e0865654") == 0) {
			$bubble = $price['price'];
		}
		elseif (strcmp($matched['_site'], "5102e6c73a81b414e0865651") == 0) {
			$scubastore = $price['price'];
		}
		elseif (strcmp($matched['_site'], "5102e6c93a81b414e0865652") == 0) {
			$scubaland = $price['price'];
		}
	}
	array_push($contents, array('1' => $name, '2' => $palanquee, '3' => $vieuxplongeur, '4' => $bubble, '5' => $scubastore, '6' => $scubaland));
}

$excel_export->addContent($contents);
fwrite($fp, $excel_export->getExcelContents());
?>