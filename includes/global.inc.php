<?php
////	CONSTANTES !
////

////	DIVERS
define("URL_AGORA_PROJECT","http://www.agora-project.net");
define("BG_DEFAULT","default@@"); //Pour distinguer les fonds d'écran par défaut
define("LARGEUR_MENU_GAUCHE",300);


////	PAR DEFAUT
if(!defined("ROOT_PATH"))				define("ROOT_PATH","../");
if(!defined("IS_MAIN_PAGE"))			define("IS_MAIN_PAGE",false);
if(!defined("CONTROLE_SESSION"))		define("CONTROLE_SESSION",true);
if(!defined("MODULE_CONTROL_ACCES"))	define("MODULE_CONTROL_ACCES",true);

////	CHEMIN DES PRINCIPAUX DOSSIERS
if(is_file(ROOT_PATH."host.inc.php"))	{  require_once(ROOT_PATH."host.inc.php");  host_constants();  }
else									{  define("PATH_STOCK_FICHIERS",ROOT_PATH."stock_fichiers/");  }
define("PATH_TPL", ROOT_PATH."templates/");
define("PATH_LANG", ROOT_PATH."traduction/");
define("PATH_INC", ROOT_PATH."includes/");
define("PATH_DIVERS", ROOT_PATH."divers/");
define("PATH_COMMUN", ROOT_PATH."commun/");
define("PATH_TMP", PATH_STOCK_FICHIERS."tmp/");
define("PATH_MOD_FICHIER", PATH_STOCK_FICHIERS."gestionnaire_fichiers/");
define("PATH_MOD_FICHIER2", PATH_STOCK_FICHIERS."gestionnaire_fichiers_vignettes/");
define("PATH_MOD_USER", PATH_STOCK_FICHIERS."photos_utilisateurs/");
define("PATH_MOD_CONTACT", PATH_STOCK_FICHIERS."photos_contact/");
define("PATH_WALLPAPER_USER", PATH_STOCK_FICHIERS."fond_ecran/");
define("PATH_WALLPAPER", PATH_TPL."fond_ecran/");
define("PATH_OBJECT_FILE", PATH_STOCK_FICHIERS."fichiers_objet/");

////	CONSTANTES DE CONFIG.  &  MAINTENANCE DE L'AGORA ?
require_once PATH_STOCK_FICHIERS."config.inc.php";
if(agora_maintenance==true)	 { @header("location:".PATH_DIVERS."maintenance.html");  exit; }

////	INIT LE TEMPS D'EXECUTION
$mtime = explode(" ",microtime());
$starttime = $mtime[1] + $mtime[0];


////	OUVERTURE SESSION
////
@ini_set("session.gc_probability",1);			// Initialise le garbage collector (rares bugs php)
@ini_set("session.gc_divisor",1000);			// Idem
@ini_set("session.gc_maxlifetime",3600);		// Augmente la durée de session à 1 heure
session_name("agora_project_".@HOST_DOMAINE);	// connexion sur plusieurs domaines du même serveur avec le même browser
session_start();


////	RECUPERATION DES FONCTIONS
////
require_once ROOT_PATH."fonctions/db.inc.php";			// Fonctions & Connexion à la bdd
require_once ROOT_PATH."fonctions/divers.inc.php";		// Fonctions diverses
require_once ROOT_PATH."fonctions/text.inc.php";		// Fonctions de traitement des chaines de caractère
require_once ROOT_PATH."fonctions/utilisateur.inc.php";	// Fonctions sur les utilisateurs
require_once ROOT_PATH."fonctions/menu.inc.php";		// Fonctions d'affichage des menus
require_once ROOT_PATH."fonctions/objet.inc.php";		// Fonctions sur les objets (éléments ou conteneurs)
require_once ROOT_PATH."fonctions/fichier.inc.php";		// Fonctions sur la gestion des fichiers & dossiers


////	SI C'EST UN SCRIPT "EXPRESS" (ex:livecounter_verif.php) => ON SAUTE CETTE ETAPE !
////
if(defined("GLOBAL_EXPRESS")==false)
{
	////	DECONNEXION DE L'AGORA  (détruit variables session & cookie connexion auto)
	////
	if(isset($_GET["deconnexion"]) || @$_GET["msg_alerte"]=="identification")
	{
		add_logs("deconnexion");
		$_SESSION = array();
		session_destroy();
		setcookie("AGORAP_LOG", "", time()-3600);
		setcookie("AGORAP_PASS", "", time()-3600);
	}


	////	INFOS SUR LE SITE  &  MISE A JOUR DE L'AGORA (?)  &  INFOS STATS
	////
	if(!isset($_SESSION["agora"]))	$_SESSION["agora"] = db_ligne("SELECT * FROM gt_agora_info");
	require_once PATH_INC."mise_a_jour.inc.php";
	if(is_file(ROOT_PATH."host.inc.php"))  info_domaine_stats();


	////	VALIDATION D'INVIT OU REINIT. PASSWORD  =>  REDIR EN PAGE D'ACCUEIL SI BESOIN
	////
	if(CONTROLE_SESSION==true && (isset($_GET["id_newpassword"]) || isset($_GET["id_invitation"])))
		redir(ROOT_PATH."index.php?deconnexion=1&".$_SERVER["QUERY_STRING"]);


	////	ACCES INVITE  /  IDENTIFICATION UTILISATEUR
	////
	if(!isset($_SESSION["user"])  ||  (!empty($_POST["login"]) && !empty($_POST["password"])))
	{
		////	COMPTE INVITE PAR DEFAUT
		$_SESSION["user"] = array("id_utilisateur"=> 0,"admin_general"=> 0);

		////	CONNEXION DEMANDÉ OU AUTO ?
		if(!empty($_POST["login"]) && !empty($_POST["password"]))					{ $login=$_POST["login"];			$password=$_POST["password"]; }
		elseif(!empty($_COOKIE["AGORAP_LOG"]) && !empty($_COOKIE["AGORAP_PASS"]))	{ $login=$_COOKIE["AGORAP_LOG"];	$password=$_COOKIE["AGORAP_PASS"];	if(strlen($password)>=20) $password_deja_crypte=true; }

		////	CONNEXION DE L'UTILISATEUR
		if(isset($login) && isset($password))
		{
			// IDENTIFICATION
			$password_crypte = (@$password_deja_crypte==true)  ?  $password  :  sha1_pass($password);
			$sql_password = "AND pass='".$password_crypte."'";
			if(is_file(ROOT_PATH."host.inc.php"))	$sql_password = sql_password($password,$sql_password);//SHORT.C?
			$user_tmp = db_ligne("SELECT * FROM gt_utilisateur WHERE identifiant=".db_format($login)." ".$sql_password);
			if(count($user_tmp)==0)		{ redir(ROOT_PATH."index.php?msg_alerte=identification"); }
			else
			{
				// EFFACE LES ENTREES ERRONNEES DU LIVECOUNTER
				db_query("DELETE FROM gt_utilisateur_livecounter WHERE id_utilisateur='".$user_tmp["id_utilisateur"]."' AND (adresse_ip is null or adresse_ip='127.0.0.1')");
				// COMPTE RESTREINT A DES ADRESSES IP ?
				if(controle_ip==true  &&  db_valeur("SELECT count(*) FROM gt_utilisateur_adresse_ip WHERE id_utilisateur='".$user_tmp["id_utilisateur"]."' AND adresse_ip is not null")>0  &&  db_valeur("SELECT count(*) FROM gt_utilisateur_adresse_ip WHERE id_utilisateur='".$user_tmp["id_utilisateur"]."' AND adresse_ip='".$_SERVER["REMOTE_ADDR"]."'")==0)
					redir(ROOT_PATH."index.php?msg_alerte=adresseip");
				// COMPTE EN COURS D'UTILISATION SUR UN AUTRE POSTE ?  (Vérif si ya d'autres IP connectées dans les 20 dernières secondes. Attention! l'IP publique peut changer à la déconnexion, si l'user se trouve derrière des proxys !!)
				if(db_valeur("SELECT count(*) FROM gt_utilisateur_livecounter  WHERE  id_utilisateur='".$user_tmp["id_utilisateur"]."'  AND  date_verif > '".(time()-20)."'  AND  CHAR_LENGTH(adresse_ip) > 1  AND  adresse_ip not like '".$_SERVER["REMOTE_ADDR"]."'")>0)
					redir(ROOT_PATH."index.php?msg_alerte=dejapresent");
				// CONNEXION VALIDÉ  =>  REINITIALISE VALEURS DE SESSION
				define("USER_IDENTIFIE","1");
				$_SESSION = array();
				$precedente_connexion = ($user_tmp["derniere_connexion"]>0)  ?  $user_tmp["derniere_connexion"]  :  strtotime(strftime("%Y-%m-%d 00:00:00")); // Aujourd'hui si c'est la premiere connexion
				db_query("UPDATE gt_utilisateur SET derniere_connexion='".time()."', precedente_connexion='".$precedente_connexion."' WHERE id_utilisateur='".$user_tmp["id_utilisateur"]."'");
				$_SESSION["user"]  = db_ligne("SELECT * FROM gt_utilisateur WHERE id_utilisateur='".$user_tmp["id_utilisateur"]."'");
				$_SESSION["agora"] = db_ligne("SELECT * FROM gt_agora_info");
				add_logs("connexion");
				// ENREGISTRE LOGIN & PASSWORD POUR UNE CONNEXION AUTO ?
				if(isset($_REQUEST["connexion_auto"]))				{ setcookie("AGORAP_LOG", $login, time()+1296000);  setcookie("AGORAP_PASS", $password_crypte, time()+1296000); }  // 1296000s = 2 semaines
				elseif(isset($_REQUEST["memoriser_identifiant"]))	{ setcookie("AGORAP_LOG", $login, time()+1296000); }
			}
		}

		////	INITIALISE LA CONFIG DU NAVIGATEUR  (RESOLUTION D'ECRAN & LE NOM DU NAVIGATEUR)
		if(empty($_SESSION["cfg"]["navigateur"]))	cfg_navigateur();
	}


	////	FIN DE SESSION ?  (déconnexion auto au bout de 4 heures maxi)
	////
	if($_SESSION["user"]["id_utilisateur"]>0 && @$_SESSION["user"]["derniere_connexion"]>0 && (time()-@$_SESSION["user"]["derniere_connexion"])>14400)
		redir(ROOT_PATH."index.php?deconnexion=1&msg_alerte=temps_session");


	////	FUSEAU HORAIRE
	////
	if(version_compare(PHP_VERSION,'5.1.0','>='))	date_default_timezone_set(current_timezone());


	////	LANGUE
	////
	// Langue de l'utilisateur / de l'agora / par défaut  &  Récup du fichier de traduction
	if(@$_SESSION["user"]["langue"]!="")		{ $langue_select = $_SESSION["user"]["langue"]; }
	elseif(@$_SESSION["agora"]["langue"]!="")	{ $langue_select = $_SESSION["agora"]["langue"]; }
	else										{ $langue_select = "francais"; }
	require_once PATH_LANG.$langue_select.".php";


	////	SELECTION D'UN ESPACE  (changement d'espace / identification)
	////
	if(isset($_GET["id_espace_acces"]) || (!isset($_SESSION["espace"]) && defined("USER_IDENTIFIE")))
	{
		$liste_espaces = espaces_affectes_user();
		////	AUCUN ESPACE DISPO POUR L'UTILISATEUR
		if(count($liste_espaces)==0){
			redir(ROOT_PATH."index.php?msg_alerte=pasaccesite");
		}
		////	CONNEXION D'UN INVITE OU SWITCH D'UN UTILISATEUR SUR UN ESPACE DONNE
		elseif(isset($_GET["id_espace_acces"]))
		{
			foreach($liste_espaces as $espace){
				$invite_autorise = ($_SESSION["user"]["id_utilisateur"]<1 && ($espace["password"]==@$_GET["password"] || $espace["password"]==""))  ?  true  :  false;
				if($espace["id_espace"]==$_GET["id_espace_acces"] && ($_SESSION["user"]["id_utilisateur"]>0 || $invite_autorise==true))  { $_SESSION["espace"] = $espace;  break; }
			}
		}
		////	ESPACE DE CONNEXION DE L'UTILISATEUR / ESPACE PAR DEFAUT
		elseif($_SESSION["user"]["id_utilisateur"]>0 )
		{
			if($_SESSION["user"]["espace_connexion"]!=""){
				foreach($liste_espaces as $espace_tmp)	{ if($espace_tmp["id_espace"]==$_SESSION["user"]["espace_connexion"])	$_SESSION["espace"] = $espace_tmp; }
			}
			if(count(@$_SESSION["espace"])==0)	$_SESSION["espace"]=$liste_espaces[0];
		}

		////	ESPACE SELECTIONNE
		if(@$_SESSION["espace"]["id_espace"]>0)
		{
			// DROITS D'ACCES & MODULES & PUBLIC & CONFIG
			$_SESSION["espace"]["droit_acces"]	= droit_acces_espace($_SESSION["espace"]["id_espace"],$_SESSION["user"]);
			$_SESSION["espace"]["modules"]		= modules_espace($_SESSION["espace"]["id_espace"]);
			$_SESSION["espace"]["public"]		= db_valeur("SELECT count(*) FROM gt_jointure_espace_utilisateur WHERE invites=1 AND id_espace='".$_SESSION["espace"]["id_espace"]."'");
			$_SESSION["cfg"]["espace"] = array();
			// ENVOI D'INVITATION PAR MAIL
			$_SESSION["user"]["envoi_invitation"] = "0";
			$user_invit = db_valeur("SELECT MAX(envoi_invitation) FROM gt_jointure_espace_utilisateur WHERE id_espace='".$_SESSION["espace"]["id_espace"]."'  AND  (tous_utilisateurs > 0 OR (id_utilisateur=".$_SESSION["user"]["id_utilisateur"]." and id_utilisateur > 0)) ");
			if(($_SESSION["espace"]["droit_acces"]==2 || $user_invit > 0) && function_exists("mail") && $_SESSION["agora"]["adresse_web"]!="" && $_SESSION["user"]["id_utilisateur"]>0)   $_SESSION["user"]["envoi_invitation"] = "1";
			$_SESSION["espace"]["groupes_user_courant"] = groupes_users($_SESSION["espace"]["id_espace"],$_SESSION["user"]["id_utilisateur"]);
			// REDIRECTION VERS LE PREMIER MODULE
			redir_module_espace();
		}
	}
	////	FIN DE SESSION + DANS L'ESPACE => PAGE DE CONNEXION
	elseif(!isset($_SESSION["espace"]) && CONTROLE_SESSION==true)	{ redir(ROOT_PATH."index.php?deconnexion=1"); }


	////	ACCES AU MODULE COURANT ?  (VOIR AFFECTATIONS ESPACE-MODULES  &  PAS UN MODULE PARAMETRAGE OU AGENDA (modules pas affectés à l'espace))
	////
	if($_SESSION["user"]["admin_general"]!=1 && isset($_SESSION["espace"]["modules"]) && defined("MODULE_DOSSIER") && MODULE_CONTROL_ACCES==true)
	{
		foreach($_SESSION["espace"]["modules"] as $mod_tmp)   {  if($mod_tmp["module_dossier_fichier"]==MODULE_DOSSIER)  $acces_module = true;  }
		if(@$acces_module!=true)	redir_module_espace();
	}


	////	AFFICHAGE DES OBJETS : AUTEUR / TOUS / NORMAL ?
	////
	if(!isset($_SESSION["cfg"]["espace"]["affichage_objet"]))	$_SESSION["cfg"]["espace"]["affichage_objet"]="normal";
	if(isset($_REQUEST["affichage_objet"]))
	{
		if($_REQUEST["affichage_objet"]=="tout" && $_SESSION["espace"]["droit_acces"]==2)			$_SESSION["cfg"]["espace"]["affichage_objet"] = "tout";
		elseif($_REQUEST["affichage_objet"]=="auteur" && $_SESSION["user"]["id_utilisateur"] > 0)	$_SESSION["cfg"]["espace"]["affichage_objet"] = "auteur";
		else																						$_SESSION["cfg"]["espace"]["affichage_objet"] = "normal";
	}


	////	ENREGISTRE EN SESSION L'IMAGE DU FOND D'ECRAN
	////
	if(empty($_SESSION["cfg"]["espace"]["fond_ecran"]))
	{
		// FOND D'ECRAN =>  DE L'ESPACE  /  DU SITE
		if(@$_SESSION["espace"]["fond_ecran"]!=""){
			if(preg_match("/".BG_DEFAULT."/",$_SESSION["espace"]["fond_ecran"]))	$fond_ecran_tmp  = PATH_WALLPAPER.str_replace(BG_DEFAULT,"",$_SESSION["espace"]["fond_ecran"]);
			else																	$fond_ecran_tmp  = PATH_WALLPAPER_USER.$_SESSION["espace"]["fond_ecran"];
		}else{
			if(preg_match("/".BG_DEFAULT."/",$_SESSION["agora"]["fond_ecran"]))		$fond_ecran_tmp  = PATH_WALLPAPER.str_replace(BG_DEFAULT,"",$_SESSION["agora"]["fond_ecran"]);
			else																	$fond_ecran_tmp  = PATH_WALLPAPER_USER.$_SESSION["agora"]["fond_ecran"];
		}
		$_SESSION["cfg"]["espace"]["fond_ecran"] = (is_file($fond_ecran_tmp))  ?  $fond_ecran_tmp  :  PATH_WALLPAPER."1.jpg";
	}
}
?>