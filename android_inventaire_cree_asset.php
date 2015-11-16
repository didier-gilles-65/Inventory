<?php
session_start();
include_once('UTILS/log.php');
include_once('UTILS/gestion_erreur.php');
include_once('MODELE/get_connexion.php');
include_once('UTILS/security.php'); // utils for permanent login checking

	$json = array();
    //Données passées en POST : S'il s'agit de l'envoi de formulaire, suite au scan fait par l'IPAD
    if (isset($_POST['nom']) ) { $nom = $_POST['nom'];} else $nom = '';
    if (isset($_POST['categorie']) ) { $categorie = $_POST['categorie'];} else $categorie = '';
    if (isset($_POST['prix']) ) { $prix = $_POST['prix'];} else $prix = 0;
    if (isset($_POST['achat']) ) { $achat = $_POST['achat'];} else $achat = '';
    if (isset($_POST['garantie']) ) { $garantie = $_POST['garantie'];} else $garantie = 0;
    if (isset($_POST['description']) ) { $description = $_POST['description'];} else $description = '';
    if (isset($_POST['commentaire']) ) { $commentaire = $_POST['commentaire'];} else $commentaire = '';
    if (isset($_POST['codebarre']) ) { $codebarre = $_POST['codebarre'];} else $codebarre = '';
    if (isset($_POST['login']) ) { $login = $_POST['login']; ecrireLog('APP', 'INFO', 'login :'.$login); } else $login = '';
    if (isset($_POST['password']) ) { $password = $_POST['password']; ecrireLog('APP', 'INFO', 'password :'.$password); } else $password = '';

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
				retournerErreur( 401 , 05, 'ANDROID_INVENTAIRE_CREE_ASSET.PHP| mot de passe incorrect pour ce login');
				exit();
			}
			$logintrouve=true;
		}
	}
	if (!$logintrouve)
	{
		retournerErreur( 401 , 06, 'ANDROID_INVENTAIRE_CREE_ASSET.PHP| login inconnu');
		exit();
	}

	$requete='SELECT MAX(ID)+1 AS NEXT_INDICE FROM i_assets';
	ecrireLog('APP', 'INFO', 'ANDROID_INVENTAIRE_CREE_ASSET.PHP| requète : '.$requete);
	
	$req = $bdd->prepare($requete);
	if (!$req->execute()){ 
		ecrireLog('APP', 'ERROR', 'ANDROID_INVENTAIRE_CREE_ASSET.PHP| Erreur sur l\'exécution de requète de récupération de l\'indice d\'asset'); 
		retournerErreur( 403 , 02, 'ANDROID_INVENTAIRE_CREE_ASSET.PHP| Erreur sur l\'exécution de requète de récupération de l\'indice d\'asset');
	}
	$new_indice = $req->fetch();
	$req->closeCursor();
	if (!$new_indice) { 
		ecrireLog('APP', 'ERROR', 'ANDROID_INVENTAIRE_CREE_ASSET.PHP| Erreur sur la lecture de l\'exécution de requète de récupération de l\'indice d\'image pour la référence de asset donné'); 
		retournerErreur( 403 , 03, 'ANDROID_INVENTAIRE_CREE_ASSET.PHP| Erreur sur la lecture de l\'exécution de requète de récupération de l\'indice d\'image pour la référence de asset donné');
	}

	$next_indice = 1;
	$next_indice = $new_indice['NEXT_INDICE']; 
	

	$sql = 'INSERT INTO i_assets ( ID, NOM, CATEGORIE, PRIX, DATE_ACHAT, DUREE_GARANTIE, DESCRIPTION, CODE_BARRE, COMMENTAIRE  ) VALUES ( '.$next_indice.', \''.$nom.'\',\''.$categorie.'\','.$prix.',STR_TO_DATE(\''.$achat.'\', \'%d/%m/%Y\'),'.$garantie.',\''.$description.'\',\''.$codebarre.'\',\''.$commentaire.'\');';
	ecrireLog('SQL', 'INFO', 'ANDROID_INVENTAIRE_CREE_ASSET.PHP| REQUETE INSERT = '.$sql);
	$req = $bdd->prepare($sql); //CREER La LIGNE
	if ($req->execute()) {
		$json[]=$next_indice;
	}
	else { 
		$json[]="0";
		retournerErreur( 409 , 04, 'ANDROID_INVENTAIRE_CREE_ASSET.PHP| Erreur sur l\'exécution de requète d\'insertion de sachet scanné'); ecrireLog('APP', 'INFO', 'ERREUR POUR INSERER LA MAJ!'); 
	}
	echo json_encode($json,JSON_UNESCAPED_SLASHES); 
	$req->closeCursor();
?>