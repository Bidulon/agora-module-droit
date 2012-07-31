<?php
////	INIT
define("GLOBAL_EXPRESS",1);
require_once "../includes/global.inc.php";


if($_SESSION["user"]["id_utilisateur"] > 0)
{
	////	ON MET À JOUR LA NOTIF DE PRESENCE DE L'UTILISATEUR
	$do_update = (db_valeur("SELECT count(*) FROM gt_utilisateur_livecounter WHERE id_utilisateur='".$_SESSION["user"]["id_utilisateur"]."'")>0)  ?  true  :  false;
	if($do_update==true)	db_query("UPDATE gt_utilisateur_livecounter SET date_verif='".time()."' WHERE id_utilisateur='".$_SESSION["user"]["id_utilisateur"]."'");
	else					db_query("INSERT INTO gt_utilisateur_livecounter SET id_utilisateur='".$_SESSION["user"]["id_utilisateur"]."', adresse_ip='".$_SERVER["REMOTE_ADDR"]."', date_verif='".time()."'");

	////	LIVECOUNTER AFFICHÉ EST DIFFÉRENT DU NOUVEAU : RELOAD !  (il y a eu de nouvelles connexions / déconnexions)
	if(isset($_SESSION["cfg"]["espace"]["users_connectes"]) && users_connectes()!=$_SESSION["cfg"]["espace"]["users_connectes"]){
		echo "maj_textes_livecounters();";
		db_query("DELETE FROM gt_utilisateur_livecounter WHERE date_verif < '".(time() - duree_livecounter)."'"); //Supprime les anciens livecounter
	}

	////	ON VÉRIFIE SI YA DE NOUVEAUX MESSAGES (SI MESSENGER A DEJA ÉTÉ AFFICHÉ)
	if(!isset($_SESSION["cfg"]["espace"]["messenger_dernier_affichage"]))	$_SESSION["cfg"]["espace"]["messenger_dernier_affichage"] = time();
	if(db_valeur("SELECT count(*) FROM gt_utilisateur_messenger WHERE id_utilisateur_destinataires LIKE '%@@".$_SESSION["user"]["id_utilisateur"]."@@%' AND date > '".$_SESSION["cfg"]["espace"]["messenger_dernier_affichage"]."'") > 0)
		$_SESSION["cfg"]["espace"]["messenger_alerte"] = 1;
	// Alerte d'un nouveau message (nouvelle ou ancienne!) : on lance la fonction
	if(@$_SESSION["cfg"]["espace"]["messenger_alerte"]==1)
		echo "messenger_nouveau_message();";

	////	DÉCONNEXION À LA BDD
	db_close();
}
?>