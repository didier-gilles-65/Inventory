<?php

function retournerErreur($httpcode, $number, $message) { 
	header('charset=utf-8');
	http_response_code($httpcode);
    echo json_encode(array('status' => 'error','errcode' => $number,'message'=> $message));
 //   echo json_encode(array('error' => $message));
	exit();
    return 0;  
}
function retournerErreurBrute($httpcode, $number, $message) { 
	http_response_code($httpcode);
//    echo json_encode(array('status' => 'error','errcode' => $number,'message'=> $message,'post_content'=> json_encode($_POST)));
	exit();
    return 0;  
}
function ecrireLogErreur($type,$level,$chaine)
{
	switch($type){
		// TRACE DES REQUETES SQL
		case 'SQL':
			$filename = 'billes-requetes.log';
			break;
        
        // TRACES APP
        case 'APP':
			$filename = 'billes-appli.log';
			break;
        case 'SYS':
			$filename = 'billes-systeme.log';
			break;
        default:
			$filename = 'billes-defaut.log';
			break;
	}

	$fp = fopen($filename, "a");
	if($fp==false) die("ouverture log impossible");
	date_default_timezone_set('Europe/Paris');
	fputs($fp, date("d-m-Y H:i:s e").'|'.$type.'|'.$level.'|'.$chaine.'
');
	return;
}

function lireMessageErreur($errcode)
{
	$erreur['2000']='Bille inconnue';
	$erreur['2010']='Marque inconnue';
	$erreur['2030']='Droits insuffisants';
	$erreur['9000']='Comment diable êtes vous arrivé là?';
	$erreur['2040']='Problême lors de la création de nouvelle instance de marque/bille';
	$erreur['4010']='Blog inconnu';
	$erreur['4020']='Commentaire absent';
	$erreur['4030']='USer inconnu';
	$erreur['4040']='Problême lors de l\'accès au post';
	$erreur['4050']='Problême lors de l\'accès au user';
	$erreur['5010']='Problême lors de la création du compte';
	$erreur['6010']='Problême avec les informations de contact';
	$erreur['6110']='Problême avec le fichier joint';
	$erreur['6120']='Fichier joint trop grand';
	$erreur['6130']='Problême sur l\'upload du fichier joint';
	$erreur['6140']='Problême sur le traitement du contact';
	$erreur['6210']='Problême avec la mise à jour du mot de passe';
	$erreur['6310']='Problême avec la préparation de la mise à jour des informations sur la collection de bille';
	$erreur['6320']='Problême à la mise à jour des informations sur la collection de bille';
	$erreur['6410']='Problême à la mise à jour des informations sur la bille';
	
	if (isset($erreur[$errcode])) { return $erreur[$errcode]; } else { return 'Erreur inconnue'; }
}

function bille_error_handler($no, $str, $file, $line){
    switch($no){
        // Erreur fatale
        case E_USER_ERROR:
            echo '<p><strong>Erreur fatale</strong> : '.$str.'</p>';
			ecrireLogErreur('APP','ERROR','fichier:'.$file.'| ligne:'.$line.'| err:'.$no.'|'.$str);
            exit;//on arrete le script
            break;
        
        // Avertissement
        case E_USER_WARNING:
            echo '<p><strong>Avertissement</strong> : '.$str.'</p>';
			ecrireLogErreur('APP','WARN','fichier:'.$file.'| ligne:'.$line.'| warn:'.$no.'|'.$str);
            break;
        
        // Note
        case E_USER_NOTICE:
            echo '<p><strong>Note</strong> : '.$str.'</p>';
			ecrireLogErreur('APP','INFO','fichier:'.$file.'| ligne:'.$line.'| info:'.$no.'|'.$str);
            break;
        
        // Erreur générée par PHP
        default:
            echo '<p><strong>Erreur inconnue</strong> ['.$no.'] : '.$str.'<br/>';
            echo 'Dans le fichier : "'.$file.'", à la ligne '.$line.'.</p>';
			ecrireLogErreur('SYS','DEFAUT','fichier:'.$file.'| ligne:'.$line.'| no:'.$no.'|'.$str);
            break;
    }
}
set_error_handler('bille_error_handler');
?>
