<?php

include_once('UTILS/log.php');
include_once('UTILS/gestion_erreur.php');
include_once('MODELE/get_connexion.php');

header('content-type: application/json');

if (isset($_GET['id']) ) { $id = $_GET['id'];} else $id = '';

$req = $bdd->prepare('SELECT ID, CODE_BARRE, NOM, CATEGORIE, PRIX, DATE_FORMAT(DATE_ACHAT,\'%d/%m/%Y\') as MYDATE, DUREE_GARANTIE, DESCRIPTION, COMMENTAIRE FROM i_assets WHERE ID = \''.$id.'\'');

if (!$req->execute()) {retournerErreur( 403 , 03, 'ANDROID_INVENTAIRE_JSON_GET_ASSET.PHP| Erreur lors de l\'exécution de la requète de récupération de l\'objet'); }
$json = array();

while ($row = $req->fetch()) {
	$json[]='ID';
	$json[]=$row['ID'];
	$json[]='CODE_BARRE';
	$json[]=$row['CODE_BARRE'];
	$json[]='NOM';
	$json[]=$row['NOM'];
	$json[]='CATEGORIE';
	$json[]=$row['CATEGORIE'];
	$json[]='PRIX';
	$json[]=$row['PRIX'];
	$json[]='DATE_ACHAT';
	$json[]=$row['MYDATE'];
	$json[]='DUREE_GARANTIE';
	$json[]=$row['DUREE_GARANTIE'];
	$json[]='DESCRIPTION';
	$json[]=$row['DESCRIPTION'];
	$json[]='COMMENTAIRE';
	$json[]=$row['COMMENTAIRE'];
}
$req->closeCursor();
echo json_encode($json,JSON_UNESCAPED_SLASHES); 
exit();	
	