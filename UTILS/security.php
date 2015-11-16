<?php
function check_permanent_login()
{
    global $bdd;
	
	$duree_limite = "6";
	
	if (isset($_COOKIE['persistent_key']) )
	{
		$cle = $_COOKIE['persistent_key'];
//ecrireLog('APP','INFO','security.php|cookie persistent_key : '.$cle);
	}
	else
	{
		return false;
	}
	if (isset($_COOKIE['login']) )
	{
		$login = $_COOKIE['login'];
//ecrireLog('APP','INFO','security.php|cookie login : '.$login);
	}
	else
	{
		return false;
	}
	$requete = 'select * from comptes where persistent_key = \''.$cle.'\' and login = \''.$login.'\' and DATEDIFF(CURRENT_TIMESTAMP,persistent_date) < '.$duree_limite;
	$req = $bdd->prepare($requete);
    if (!$req->execute())
	{
//ecrireLog('APP','ERROR','security.php|requete ne fonctionnant pas : '.$requete);
		return false;
	}
	$user = $req->fetch();
//	print_r $user;
//	exit();
//ecrireLog('APP','INFO','security.php|user récupéré : '.$user['LOGIN']);
	if ($user)
	{
		$_SESSION['connect']=1; 
		$_SESSION['login']=$user['LOGIN']; 
		$_SESSION['gravatar_flag']=$user['GRAVATAR_FLAG']; 
		$_SESSION['email']=$user['EMAIL']; 
		$_SESSION['utilisateur']=$user['PRENOM'];
		$_SESSION['profile']=$user['PROFILE'];
		$req->closeCursor();
//ecrireLog('APP','INFO','security.php|Après et OK checkpermanent : '.print_r($_SESSION, true));
		return true;
	}
	$req->closeCursor();
//ecrireLog('APP','ERROR','security.php|probleme lors du fetch : '.$requete);
    return false;
}

function check_admin()
{
    global $bdd;
	
	$login = $_SESSION['login'];
	
	$requete = 'select * from comptes where  login = \''.$login.'\'';
	$req = $bdd->prepare($requete);
    if (!$req->execute())
	{
//ecrireLog('APP','ERROR','security.php|requete ne fonctionnant pas : '.$requete);
		return false;
	}
	$user = $req->fetch();
//	print_r $user;
//	exit();
//ecrireLog('APP','INFO','security.php|user récupéré : '.$user['LOGIN']);
	if ($user)
	{
		$profile = $user['PROFILE']; 
		$req->closeCursor();
		if ($profile <> 'ADMIN') return false;
		return true;
	}
	$req->closeCursor();
//ecrireLog('APP','ERROR','security.php|probleme lors du fetch : '.$requete);
    return false;
}
