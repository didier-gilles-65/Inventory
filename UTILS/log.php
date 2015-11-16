<?php
function ecrireLog($type,$level,$chaine)
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
