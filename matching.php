<?php


$libelle = "Montre ordinateurs D6i Métal SUUNTO";
$libelle2 = "Ordinateur D6i bracelet metal + interface incluse";
$libelle3 = "Suunto D6i Elastomer White + Free Transmitter";
$libelle4 = "Montre D6i Suunto bracelet acier avec Interface USB - Suunto";
$libelle5 = "MONTRE D6I ALL BLACK - SUUNTO Aqualung";

$prix = 779.0;
$prix2 = 79.0;
$prix3 = 799.02;
$prix4 = 899.0;
$prix5 = 719.0;

$categorie = "ordi";
$categorie2 = "ordi";
$categorie3 = "ordi";
$categorie4 = "ordi";
$categorie5 = "ordi";





/*Algorithme de matching:



- Chaque libellé = une chaîne de caractère qui sera divisée en mots dans un tableau à l'aide de la fonction explode().

- La fonction de matching prendra en paramètre deux tableaux: celui du libellé courant, c'est à dire celui provenant
    d'un article du site référent et le deuxième provenant d'un des autres sites.s, mais aussi le prix des deux articles.

- La fonction renverra "true" ou "false" selon si oui ou non, il y a un match entre deux articles.

- Exemple d'utilisation : "if(match($libelle1, $libelle2, prix1, prix2){blablabla...}"





- Si on a au moins  2 intersections (éléments communs dans les chaînes) ainsi qu'une correspondance sur les prix
    alors on valide le matching.

- Autrement on essaye de trouver une correspondance avec l'aide de "similar_text". Si au moins deux correspondances à 
    plus de 80% ainsi que matching de prix, alors on valide.

- Sinon on ne valide pas.



 */

function match($lib1, $lib2, $prix1, $prix2)
{
    $bool = false;
    $mots1 = explode(" ", minusculesSansAccents($lib1));
    $mots2 = explode(" ", minusculesSansAccents($lib2));

    $result = array_intersect($mots1, $mots2);
    $c = count($result);

    $cp = 0;

    if(count(array_intersect($mots1, $mots2)) && ($prix1 >= $prix2 - $prix2*0.25 && $prix1 <= $prix2 + $prix2*0.25))
    {
        $bool = true;
    }
    else
    {
       
    }
    foreach ($mots1 as $m1) 
    {
        foreach ($mots2 as $m2) 
        {
           similar_text($m1, $m2, $percent);
           if($percent >= 85)
           {
                $cp++;
           }
           
        }
    }

    if()
    {
        echo "oui";
    }


    print_r($c);
    echo "<br>";
    echo $cp;
    echo "<br>";


}






/*print_r($result);
echo $mots[4];
print_r(count($mots));

similar_text($libelle, $libelle2, $percent);
echo $percent;*/



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


if(match($libelle, $libelle2, $prix, $prix2))
{
    echo "vrai";
}
else echo "faux";















?>