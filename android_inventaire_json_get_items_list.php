<?php

include_once('UTILS/log.php');
include_once('UTILS/gestion_erreur.php');
include_once('MODELE/get_connexion.php');

header('content-type: application/json');

$req = $bdd->prepare('SELECT DISTINCT ID, NOM FROM i_assets ORDER BY ID');

if (!$req->execute()) {retournerErreur( 403 , 03, 'ANDROID_INVENTAIRE_JSON_GET_ASSETS.PHP| Erreur lors de l\'exécution de la requète de récupération des objets'); }
$json = array();

while ($row = $req->fetch()) {
	$json[]=$row['ID'];
	$json[]=$row['NOM'];
}
$req->closeCursor();
echo json_encode($json,JSON_UNESCAPED_SLASHES); 
exit();	
	