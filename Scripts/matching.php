<?php


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

    $nbMatch = 0;
    $compteur = 4;

    while($nbMatch==0 && $compteur > 1)
    {   
        if (arrayCompare($mots1, $mots2) >= $compteur && ($prix1 >= $prix2 - $prix2*0.25 && $prix1 <= $prix2 + $prix2*0.25)) 
        {
            $nbMatch++;
        }
        else
        {
            $compteur--;
        }        
    }

    if ($nbMatch == 0) 
    {
        $bool = false;
    }
    else
    {
        $bool = true;
    }

    return $bool;
}


/*Fonction qui croise chaque élément des deux tableaux passés
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
           if($percent >= 95)
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




// 50d762f667577bbedeba2b6c
if(isset($argv[1]))
{
    $m = new Mongo(); // Connexion à Mongo établie.
    $db = $m->selectDB("comparator"); // Choix de la base de données.

    $articles = $db->articles->find(array('_site' => new MongoId($argv[1]))); // On trouve le libellé du site grâce à son id
    $articlesComp = $db->articles->find();

    foreach ($articles as $art)
    {
        foreach ($articlesComp as $artComp)
        {
            if (strcmp($artComp['_site'], $argv[1]) != 0) 
            {
            
                if(match($art['name'], $artComp['name'], $art['prices']['price'], $artComp['prices']['price'])) 
                {
                    $match = $db->articles->findOne(array('_id' => new MongoId($art['_id'])));
                    if (!array_search($artComp['_id'], $match['match'])) 
                    {
                        $db->articles->update(array("_id" => new MongoId($art['_id'])), array('$push' => array('match'=> new MongoId($artComp['_id']))));
                        //echo $art['name']." ===> Site: ".$artComp['_site']." ==> ".$artComp['name'];                       
                    }
                }
            }
        }
    }
}
else
{
    echo "Argument du site référent manquant";
}

?>