<?php
error_reporting(E_ALL);
include_once('UTILS/log.php');
include_once('UTILS/gestion_erreur.php');
include_once('MODELE/get_connexion.php');
include_once('UTILS/security.php'); // utils for permanent login checking

/*
Usage:

android_inventaire_image_upload.php?mode=<facture|asset>&login=<login>&password=<password>&id=<id_asset>

*/
ecrireLog('SQL', 'INFO', 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| EXECUTION');
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');
/*
$target_path  = "./";

$target_path = $target_path . basename( $_FILES['uploadedfile']['name']);

$file = basename( $_FILES['uploadedfile']['name']);

move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)
*/

function imageCreateFromAny($filepath) { 

    list($w, $h, $t, $attr) = getimagesize($filepath); // [] if you don't have exif you could use getImageSize() 
    $allowedTypes = array( 
        IMG_GIF,  // [] gif 
        IMG_JPG,  // [] jpg 
        IMG_PNG,  // [] png 
        IMG_WBMP   // [] bmp 
    ); 
    if (!in_array($t, $allowedTypes)) { 
        return false; 
    } 
    switch ($t) { 
        case IMG_GIF : 
            $im = imageCreateFromGif($filepath); 
        break; 
        case IMG_JPG : 
            $im = imageCreateFromJpeg($filepath); 
        break; 
        case IMG_PNG : 
            $im = imageCreateFromPng($filepath); 
        break; 
        case IMG_WBMP : 
            $im = imageCreateFromBmp($filepath); 
        break; 
    }    
    return $im;  
}

if (isset($_GET['mode']) ) { 
	if ($_GET['mode'] == 'facture' ) {
		$table='i_photos_facture';
	}
	else {
		$table='i_photos_asset';
	}
}
else {
	$table='i_photos_asset';
}

if (isset($_GET['login']) ) { $login = $_GET['login']; ecrireLog('APP', 'INFO', 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| login :'.$login); } else $login = '';
if (isset($_GET['password']) ) { $password = $_GET['password']; ecrireLog('APP', 'INFO', 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| password :'.$password); } else $password = '';

	$logintrouve = false;

	$sql = "SELECT * FROM comptes where LOGIN = '$login'";
	$req = $bdd->prepare($sql);
	$req->execute();

	while ($line = $req->fetch())
	{
		if ($login == $line['LOGIN']) // Si le nom d'utilisateur est trouvé, on vérifie le mdp 
		{
			$s=$line['SALT'];
			$hash = hash('sha256', $password);
			$pwd = hash('sha256', $s . $hash);
			if ($pwd != $line['PASSWORD'])
			{
				retournerErreur( 401 , 05, 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| mot de passe incorrect pour ce login');
				exit();
			}
			$logintrouve=true;
		}
	}
	if (!$logintrouve)
	{
		retournerErreur( 401 , 06, 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| login inconnu');
		exit();
	}


if(isset($_GET['id'])) { $id = $_GET['id']; ecrireLog('APP', 'INFO', 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| asset :'.$id);} else { 
	ecrireLog('APP', 'ERROR', 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| GET : '.print_r($_GET, true));
	ecrireLog('APP', 'ERROR', 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| Pas de référence de asset dans les données en POST');
	retournerErreur( 400 , 01, 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| Pas de référence de asset dans les données en POST'); 
}
$requete='SELECT MAX(INDICE)+1 AS NEXT_INDICE FROM '.$table.' WHERE ID = '.$id;
ecrireLog('APP', 'INFO', 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| requète : '.$requete);

$req = $bdd->prepare($requete);
if (!$req->execute()){ 
	ecrireLog('APP', 'ERROR', 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| Erreur sur l\'exécution de requète de récupération de l\'indice d\'image pour la référence de asset donné'); 
	retournerErreur( 403 , 02, 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| Erreur sur l\'exécution de requète de récupération de l\'indice d\'image pour la référence de asset donné');
}
$new_indice = $req->fetch();
$req->closeCursor();
if (!$new_indice) { 
	ecrireLog('APP', 'ERROR', 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| Erreur sur la lecture de l\'exécution de requète de récupération de l\'indice d\'image pour la référence de asset donné'); 
	retournerErreur( 403 , 03, 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| Erreur sur la lecture de l\'exécution de requète de récupération de l\'indice d\'image pour la référence de asset donné');
}

$next_indice = 1;
$next_indice = $new_indice['NEXT_INDICE']; 
if(!isset($next_indice) || !($next_indice > 0)) {$next_indice = 1;}

$uploaddir = 'IMAGES/ASSETS/';
//$uploadfile = $id.'-'.$next_indice.'-'.basename($_FILES['uploadedfile']['name']);
 
if ( ($_FILES['uploadedfile']['name']) == 'inventaire_photo_facture.jpg') {
	$uploadfile = $id.'-facture.jpg';
}
else {
	$uploadfile = $id.'-asset.jpg';
}

//ecrireLog('APP', 'INFO', 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| FICHIER CIBLE = '.$uploaddir.$uploadfile);
ecrireLog('APP', 'INFO', 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| contenu $_FILE : '.print_r($_FILES, true)); 
if (!move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $uploaddir.$uploadfile)) {
	ecrireLog('APP', 'ERROR', 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| Problème sur le déplacement du fichier uploadé'); 
	retournerErreur( 403 , 04, 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| Problème sur le déplacement du fichier uploadé');
}

// Calcul des nouvelles dimensions
list($width, $height) = getimagesize($uploaddir.$uploadfile);
$newwidth = $width * 60 / $height;
$newheight = 60;

// Chargement
$thumb = imagecreatetruecolor($newwidth, $newheight);
$source = imageCreateFromAny($uploaddir.$uploadfile);

// Redimensionnement
imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

imagejpeg($thumb, $uploaddir.'SMALL/'.$uploadfile);
imagedestroy($thumb);
imagedestroy($source);


$requete='INSERT INTO '.$table.' ( ID, INDICE, FICHIER, CHEMIN ) values ( '.$id.', '.$next_indice.', \''.$uploadfile.'\', \''.$uploaddir.'\' )';
ecrireLog('SQL', 'INFO', 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| REQUETE = '.$requete);
$req = $bdd->prepare($requete);
if (!$req->execute()){
	ecrireLog('APP', 'ERROR', 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| Erreur à l\insertion de l\'enregistrement nouvelle image'); 
	retournerErreur( 403 , 05, 'ANDROID_INVENTAIRE_IMAGE_UPLOAD.PHP| Erreur à l\insertion de l\'enregistrement nouvelle image');
}
exit();


?>