<?php
include('simplehtmldom_1_5/simple_html_dom.php');

	function createArticle($name, $prixArticle, $cat, $idSite, $urlArticle, $img, $db)
	/* Ajoute le prix de l'article avec la date de l'exécution du script.
	   args : $idArticle -> id de l'article à mettre à jour.
	   		  $price -> prix courant de l'article.
	   		  $db -> Base de données utilisée.
	*/
	{
		$array_article = array("url" => $urlArticle, "_site" => new MongoId($idSite), "category" => $cat, "name" => str_replace("\t", "", $name), "img" => $img, "match" => array(), "prices" => array(array("price" => (float) $prixArticle, "time" => new MongoDate())));
		$db->articles->insert($array_article);
	}

if ($argc != 4) {
	echo "Erreur : Nombre d'arguments incorrect. \n\n";
	echo "usage: ./crawlerCateg [idSite] [nomCategorie] [urlCategorie]";
	echo "      Ajout des articles de la catégorie en base.\n";
	exit;
}
if ( !preg_match("/^[a-z0-9]{24}$/", $argv[1]) ) {
	echo "Erreur : idSite non valide. \n\n";
	echo "usage: Crawler [idSite] [nomCategorie] [urlCategorie]";
	echo "      Ajout des articles de la catégorie en base.\n";
	exit;
}

$m = new Mongo(); // Connexion à Mongo établie.
$db = $m->selectDB("comparator"); // Choix de la base de données.

$site = $db->sites->findOne(array('_id' => new MongoId($argv[1]))); // On trouve le libellé du site grâce à son id
$idSite = $argv[1];
$cat = $argv[2];
$url = $argv[3];

if(strcmp($site['name'], 'Scubastore') == 0) // Parsing du prix de l'article pour le site Scubastore
{	
	$nbPages = 1;
	for($i=1; $i <= $nbPages; $i++) {		
		$url2 = substr($url, 0, stripos($argv[3], 'page=')+5).$i.substr($url, stripos($argv[3], 'page=')+6);
		$html = file_get_html($url2);
		$categ = $html->find('.prodSub', 0);
		
		$suiv = $html->find('a.ultPag', 0);
		if (!is_null($suiv)) {
			$nbPages++;
		}
		foreach ($categ->find('.singleBoxMarca') as $a)
		{
			$b = $a->find('a', 0);
			$urlArticle = "http://www.scubastore.com".$b->href;
			$html2 = file_get_html($urlArticle);
			
			if ($html2 && !$db->articles->findOne(array('url' => $urlArticle))) {
				$name = $html2->find('.title', 0)->plaintext;
				$prixArticleStr = $html2->find('#total_dinamic', 0)->plaintext;
				$prixArticle =  floatval(str_replace(',','.',$prixArticleStr));
				$img = '';
				$imgLink = $html2->find('.jqzoom', 0)->href;
				$img = "http://www.scubastore.com".$imgLink;
				createArticle($name, $prixArticle, $cat, $idSite, $urlArticle, $img, $db);
				
				$html2->clear();
				unset($html2);
			}
		}
		$html->clear();
		unset($html);
	}
}
elseif (strcmp($site['name'], 'Palanquee') == 0) // Parsing du prix de l'article pour le site Palanquee
{
	$html = file_get_html($url);
	$categ = $html->find('.category_child_listing h3.bp_product_name');
	if (empty($categ)) {
		foreach ($html->find('.h3.bp_product_name') as $a1) 
		{
			foreach ($a1->find('a') as $b1)
			{
				$urlArticle = 'http://www.palanquee.com'.$b1->href;
				$html3 = file_get_html($urlArticle);

				if ($html3 && !$db->articles->findOne(array('url' => $urlArticle))) {
					$name = $html3->find('.p_page_head_bar', 0)->plaintext;
					$prixArticleStr = $html3->find('.productPrice', 0)->plaintext;
					$prixArticle =  floatval(str_replace(',','.',$prixArticleStr));
					$img = '';
					if(isset($html3->find('img[alt=image produit]', 0)->src)) {
						$img = "http://www.palanquee.com".$html3->find('img[alt=image produit]', 0)->src;
					}
					elseif(isset($html3->find('img[class=browseProductImage]', 0)->src)) {
						$img = $html3->find('img[class=browseProductImage]', 0)->src;
					}
					//echo $urlArticle."\n".$img."\n\n";
					createArticle($name, $prixArticle, $cat, $idSite, $urlArticle, $img, $db);
				}
				$html3->clear();
				unset($html3);
			}
		}
		$html->clear();
		unset($html);
	}
	else {
		foreach ($categ as $a) {
			foreach ($a->find('a') as $b) {
				$urlSousCat = 'http://www.palanquee.com'.$b->href;
				$html2 = file_get_html($urlSousCat);
		
				foreach ($html2->find('.h3.bp_product_name') as $a1) {
					$urlArticle = 'http://www.palanquee.com'.$a1->find('a', 0)->href;
					$html3 = file_get_html($urlArticle);

					if (isset($html3) && !$db->articles->findOne(array('url' => $urlArticle))) {
						$name = $html3->find('.p_page_head_bar', 0)->plaintext;
						$prixArticleStr = $html3->find('.productPrice', 0)->plaintext;
						$prixArticle =  floatval(str_replace(',','.',$prixArticleStr));
						$img = '';
						if(isset($html3->find('img[alt=image produit]', 0)->src)) {
							$img = "http://www.palanquee.com".$html3->find('img[alt=image produit]', 0)->src;
						}
						elseif(isset($html3->find('img[class=browseProductImage]', 0)->src)) {
							$img = $html3->find('img[class=browseProductImage]', 0)->src;
						}
						createArticle($name, $prixArticle, $cat, $idSite, $urlArticle, $img, $db);
					}
					$html3->clear();
					unset($html3);
				}
				$html2->clear();
				unset($html2);
			}
		}
	}
}
elseif (strcmp($site['name'], 'VieuxPlongeur') == 0)// Parsing du prix de l'article pour le site VieuxPlongeur
{
	$html = file_get_html($url);
	
	$categ = $html->find('#pagination', 0);
	$nbPages = count($categ->find('a'));
	
	for($i=1; $i <= $nbPages; $i++) {
		$url2 = $url.'?p='.$i;
		$html = file_get_html($url2);
	
		foreach ($html->find('#product_list') as $a)
		{
			foreach ($a->find('.center_block') as $b)
			{
				$c = $b->find('a', 0);
				$urlArticle = $c->href;
				$html2 = file_get_html($urlArticle);
				
				if ($html2 && !$db->articles->findOne(array('url' => $urlArticle))) {
					$name = str_replace("\t", '', $html2->find('#pb-left-column h1', 0)->plaintext);
					$prixArticleStr = $html2->find('.our_price_display', 0)->plaintext;
					$prixArticle =  floatval(str_replace(',','.',$prixArticleStr));
					$img = '';
					$img = $html2->find('#view_full_size img', 0)->src;
					createArticle($name, $prixArticle, $cat, $idSite, $urlArticle, $img, $db);
					
					$html2->clear();
					unset($html2);
				}
			}
		}
		$html->clear();
		unset($html);
	}
}
elseif (strcmp($site['name'], 'Bubble-Diving') == 0)// Parsing du prix de l'article pour le site Bubble-Diving
{
	$html = file_get_html($url);
	if (strcmp($cat, 'Ordinateurs') == 0) {
		$categ = array($html->find('li.nav-1', 0));
	}
	elseif (strcmp($cat, 'Palmes Masques Tubas') == 0) {
		$categ = array($html->find('li.nav-21', 0), $html->find('li.nav-22', 0), $html->find('li.nav-23', 0));
	}
	elseif (strcmp($cat, 'Montres') == 0) {
		$categ = array($html->find('li.nav-2', 0));
	}
	elseif (strcmp($cat, 'Bagagerie') == 0) {
		$categ = array($html->find('li.nav-11', 0));
	}
	elseif (strcmp($cat, 'Bouteilles') == 0) {
		$categ = array($html->find('li.nav-27', 0));
	}
	elseif (strcmp($cat, 'Combinaisons') == 0) {
		$categ = array($html->find('li.nav-12', 0), $html->find('li.nav-13', 0), $html->find('li.nav-14', 0), $html->find('li.nav-15', 0));
	}
	elseif (strcmp($cat, 'Eclairages') == 0) {
		$categ = array($html->find('li.nav-5', 0));
	}
	elseif (strcmp($cat, 'Fusils') == 0) {
		$categ = array($html->find('li.nav-32', 0));
	}
	elseif (strcmp($cat, 'Lestage') == 0) {
		$categ = array($html->find('li.nav-28', 0));
	}
	elseif (strcmp($cat, 'Chaussons') == 0) {
		$categ = array($html->find('li.nav-19', 0));
	}
	elseif (strcmp($cat, 'Gants') == 0) {
		$categ = array($html->find('li.nav-20', 0));
	}
	elseif (strcmp($cat, 'Gilets') == 0) {
		$categ = array($html->find('li.nav-9', 0));
	}

	foreach ($categ as $sousCateg) { 
		foreach ($sousCateg->find('a') as $sousCat) {
			$url = $sousCat->href;
			$html = file_get_html($url);
	
			$categ = $html->find('.pages', 0);
			if ($categ) {
				$nbPages = count($categ->find('a'));
			}
			else {
				$nbPages = 1;
			}
			
			for($i=1; $i <= $nbPages; $i++) {
				$url2 = $url.'?p='.$i;
				$html2 = file_get_html($url2);
				
				foreach ($html2->find('.products-grid') as $a) {
					foreach ($a->find('.item') as $b) {
						$c = $b->find('a', 0);
						
						$urlArticle = $c->href;
						$html3 = file_get_html($urlArticle);
						
						if ($html3 && !$db->articles->findOne(array('url' => $urlArticle))) {
							$name = $html3->find('.product-name h1', 0)->plaintext;
							$prixArticleStr = $html3->find('.regular-price', 0)->plaintext;
							$prixArticle =  floatval(str_replace(',','.',$prixArticleStr));
							$img = '';
							$img = $html3->find('img#image', 0)->src;
							createArticle($name, $prixArticle, $cat, $idSite, $urlArticle, $img, $db);
							
							$html3->clear();
							unset($html3);
						}
					}
				}
				$html2->clear();
				unset($html2);
			}
			$html->clear();
			unset($html);
		}
	}
}
elseif (strcmp($site['name'], 'Scubaland') == 0) // Parsing du prix de l'article pour le site Scubaland
{
	$html = file_get_html($url);
	
	$str = $html->find('.marquedsdivnormal', 0)->next_sibling()->plaintext;
	$i = intval(substr($str, 7,3));
	
	for($j=1; $j<=$i; $j++) {
		$url2 = substr($url, 0, -5).'_page'.$j.".html";
		$html = file_get_html($url2);
		$tabGeneral = $html->find('table#corpsdusite', 0);
		foreach ($tabGeneral->find('a.petit_titreproduit') as $a) {
			$urlArticle = 'http://www.scubaland.fr/'.$a->href;
			$html2 = file_get_html($urlArticle);
			
			if ($html2 && !$db->articles->findOne(array('url' => $urlArticle))) {
			
				$row = $html2->find('#table3 tr', 0);
				$k = 0;
				$column = $row->children();
				$a = $column[0]->plaintext;
				$nbCol = count($column);
			
				if ($nbCol > 1) {
					while (strcmp($a, "Tarif ") != 0 && $k < $nbCol) {
						$k++;
						$a = $column[$k]->plaintext;
					}
					
					$row = $html2->find('#table3 tr', 0)->next_sibling()->next_sibling();
					if ( $k==1 ) {
						$prixArticleStr = $row->find('td',0)->next_sibling()->plaintext;
					}
					elseif ( $k == 2 ) {
						$prixArticleStr = $row->find('td',0)->next_sibling()->next_sibling()->plaintext;
					}
					elseif ( $k == 3 ) {
						$prixArticleStr = $row->find('td',0)->next_sibling()->next_sibling()->next_sibling()->plaintext;
					}
				}
				else {
					$prixArticleStr = $html2->find('.prixkit', 0)->plaintext;
				}
				
				$name = $html2->find('.nom_model', 0)->plaintext;
				$prixArticle =  floatval(str_replace(',','.',$prixArticleStr));
				$img = '';
				if ($html2->find('img[name=image_affichee]', 0)) {
					$imgLink = $html2->find('img[name=image_affichee]', 0)->src;
					$img = "http://www.scubaland.fr/".$imgLink;
				}
				createArticle($name, $prixArticle, $cat, $idSite, $urlArticle, $img, $db);
			}
			$html2->clear();
			unset($html2);
		}
		$html->clear();
		unset($html);
	}
}

$m->close();// Fermeture de la connexion à Mongo
?>