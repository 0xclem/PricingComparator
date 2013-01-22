<?php





/*Algorithme de matching:



- Chaque libellé = une chaîne de caractère qui sera divisée en mots dans un tableau à l'aide de la fonction explode().
    Exemple: "coucou ça va toi" deviendra "coucou, ça, va, toi"

- La fonction de matching prendra en paramètre deux strings: celui du libellé courant, c'est à dire celui provenant
    d'un article du site référent et le deuxième provenant d'un des autres sites, mais aussi le prix des deux articles.
        Exemple: match($lib1, $lib2, $prix1, $prix2) avec $prix1 le prix de l'article dont le libellé est $lib1..etc.

- La fonction renverra "true" ou "false" selon si oui ou non, il y a un match entre deux articles.

- Principe de l'algorithme: 
    
    - Si on a au moins  2 intersections (éléments communs dans les chaînes) ainsi qu'une correspondance sur les prix
    (+-25%) alors on valide le matching. On va donc traiter ici que les chaînes de caractères identiques. Il suffira
    d'une différence de lettre pour que l'intersection entre deux mots ne soit pas valable.

    - Autrement on essaye de trouver une correspondance avec l'aide de "similar_text". Cette fonction renvoie un 
    pourcentage de matching entre chaînes, pouvant ainsi déterminer les chaînes quasi-identiques, qui varient à 
    quelques lettre près.
    
    Si on a au moins deux correspondances à plus de 85% ainsi qu'une correspondance sur le prix, alors on valide.

    
    - Si pas de correspondance avec l'algo 1 et l'algo 2 alors on rejette le matching.

Fin.



 */


//Exemple d'articles à matcher
//$libelle = "Montre ordinateurs D6i Métal SUUNTO";
//$libelle2 = "Ordinateur D6i bracelet metal + interface incluse";
//$libelle3 = "Suunto D6i Elastomer White + Free Transmitter";
//$libelle4 = "Montre D6i Suunto bracelet acier avec Interface USB - Suunto";
//$libelle5 = "MONTRE D6I ALL BLACK - SUUNTO Aqualung";
//
//$prix = 779.0;
//$prix2 = 790.0;
//$prix3 = 799.02;
//$prix4 = 899.0;
//$prix5 = 719.0;
//
//$categorie = "ordi";
//$categorie2 = "ordi";
//$categorie3 = "ordi";
//$categorie4 = "ordi";
//$categorie5 = "ordi";


//Fonction principale pour matcher deux articles
function match($lib1, $lib2, $prix1, $prix2)
{
    $bool = false;

    //On transforme les deux libellés en tableaux de mots
    $mots1 = explode(" ", minusculesSansAccents($lib1));
    $mots2 = explode(" ", minusculesSansAccents($lib2));

    //Algo 1 => correspodance parfaite entre les mots
    if(count(array_intersect($mots1, $mots2)) >=2 && ($prix1 >= $prix2 - $prix2*0.25 && $prix1 <= $prix2 + $prix2*0.25))
    {
        //echo "algo 1";
        $bool = true;
    }

    //Algo 2 => pourcentage de correspondance
    elseif (arrayCompare($mots1, $mots2) >= 2 && ($prix1 >= $prix2 - $prix2*0.25 && $prix1 <= $prix2 + $prix2*0.25)) 
    {
        //echo "algo 2";
        $bool = true;
    }
    
    return $bool;
}


/*Fonction utilisée pour l'algo 2. Elle croise chaque élément des deux tableaux passés
    en paramètres afin de déterminé un % de matching entre ces mots. Deux mots sont considérés comme identiques
    si il y a un % d'au moins 85%. Renvoie le nombre de correspondances.*/
function arrayCompare($a1, $a2)
{
    $nb = 0;

    foreach ($a1 as $m1) 
    {
        foreach ($a2 as $m2) 
        {
           similar_text($m1, $m2, $percent);
           if($percent >= 85)
           {
                $nb++;
           }
           
        }
    }
    return $nb;
}


/*Fonction utilisée pour mettre tous les caractères des libellés sans accents et en minuscule.
    Ne pas toucher cette fonction. */
function minusculesSansAccents($texte)
{
    $texte = mb_strtolower($texte, 'UTF-8');
    $texte = str_replace(
        array(
            'à', 'â', 'ä', 'á', 'ã', 'å',
            'î', 'ï', 'ì', 'í', 
            'ô', 'ö', 'ò', 'ó', 'õ', 'ø', 
            'ù', 'û', 'ü', 'ú', 
            'é', 'è', 'ê', 'ë', 
            'ç', 'ÿ', 'ñ', 
        ),
        array(
            'a', 'a', 'a', 'a', 'a', 'a', 
            'i', 'i', 'i', 'i', 
            'o', 'o', 'o', 'o', 'o', 'o', 
            'u', 'u', 'u', 'u', 
            'e', 'e', 'e', 'e', 
            'c', 'y', 'n', 
        ),
        $texte
    );
 
    return $texte;        
}


$m = new Mongo(); // Connexion à Mongo établie.
$db = $m->selectDB("comparator"); // Choix de la base de données.

$articles = $db->articles->find(array('_site' => new MongoId("50d762f667577bbedeba2b6c"))); // On trouve le libellé du site grâce à son id
$articlesComp = $db->articles->find();

foreach ($articles as $art)
{
	foreach ($articlesComp as $artComp)
	{
		if (strcmp($artComp['_site'], "50d762f667577bbedeba2b6c") != 0) {
			
			if(match($art['name'], $artComp['name'], $art['prices']['price'], $artComp['prices']['price'])) {
				$match = $db->articles->findOne(array('_id' => new MongoId($art['_id'])));
				if (!array_search($artComp['_id'], $match['match'])) {
					$db->articles->update(array("_id" => new MongoId($art['_id'])), array('$push' => array('match'=> new MongoId($artComp['_id']))));
				}
			}
		}
	}
}
?>