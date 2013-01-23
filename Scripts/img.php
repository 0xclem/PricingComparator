<?php


include('simplehtmldom_1_5/simple_html_dom.php');



// Palanquee
/*
$url = "http://www.palanquee.com/magasin-plongee-news/accueil/cagoule/cagoule-focea-comfort-3-beuchat";
$html = file_get_html($url);

echo $html->find('.single_product_image_cont img', 0)->src;*/


// Bubble-diving
/*
$url = "http://www.bubble-diving.com/plonge/nouveautes-accueil/monopiece-atlantis-7-mm-d.html";
$html = file_get_html($url);

echo $html->find('img#image', 0)->src;*/

// Vieux plongeur
/*
$url = "http://plongee.vieuxplongeur.com/fr/7641-detendeur-mk17-s600-modele-2012.html";
$html = file_get_html($url);

echo $html->find('#view_full_size img', 0)->src;*/

// Scubaland
/*
$url = "http://www.scubaland.fr/gilet-plongee-aqualung-pearl-pink-aqualung.html";
$html = file_get_html($url);

$link = $html->find('img[name=image_affichee]', 0)->src;
$finalLink = "http://www.scubaland.fr/".$link;
echo $finalLink;
*/

// Scubastore
/*
$url = "http://www.scubastore.com/plongee/beuchat-over-shorty-focea-comfort-3-lady-5mm/13557/p";
$html = file_get_html($url);

$link = $html->find('.jqzoom', 0)->href;
$finalLink = "http://www.scubastore.com".$link;
echo $finalLink;
*/

// Poussin_Store
/*
echo "va te faire enculer"; 
*/








?>