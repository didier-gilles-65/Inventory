<?php
error_reporting(E_ALL);
include_once('UTILS/log.php');
include_once('UTILS/gestion_erreur.php');
include_once('MODELE/get_connexion.php');
include_once('UTILS/security.php'); // utils for permanent login checking

header('content-type: application/json');

$json = array();

if (isset($_GET['id']) ) { $id = $_GET['id'];} else $id = '';

$sql = 'DELETE FROM i_assets WHERE ID = \''.$id.'\'';
$req = $bdd->prepare($sql);

if (!$req->execute()) {retournerErreur( 403 , 03, 'ANDROID_INVENTAIRE_JSON_DELETE_ASSET.PHP| Erreur lors de l\'exécution de la requète de suppression de l\'objet'); }

array_map('unlink', glob('IMAGES/ASSETS/'.$id.'-asset*.jpg'));
array_map('unlink', glob('IMAGES/ASSETS/'.$id.'-facture*.jpg'));
array_map('unlink', glob('IMAGES/ASSETS/SMALL/'.$id.'-asset*.jpg'));
array_map('unlink', glob('IMAGES/ASSETS/SMALL/'.$id.'-asset*.jpg'));

$json[]='OK';

exit();	
	