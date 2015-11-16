<?php

include_once('UTILS/log.php');
include_once('UTILS/gestion_erreur.php');
include_once('MODELE/get_connexion.php');

header('content-type: application/json');

$req = $bdd->prepare('SELECT DISTINCT CATEGORIE, ID FROM i_categories ORDER BY ID');

if (!$req->execute()) {retournerErreur( 403 , 03, 'ANDROID_INVENTAIRE_JSON_GET_CATEGORIES.PHP| Erreur lors de l\'exécution de la requète de récupération des catégories'); }
$json = array();

while ($row = $req->fetch()) {
	$json[]=$row['CATEGORIE'];
}
$req->closeCursor();
echo json_encode($json,JSON_UNESCAPED_SLASHES); 
exit();	
	