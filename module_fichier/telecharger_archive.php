<?php
////	INIT
require "commun.inc.php";
require_once "../divers/ziplib.php";
modif_php_ini();


////	TELECHARGE TOUT UN DOSSIER (et sous dossiers..)
////
if(isset($_GET["id_dossier"]))
{
	// Controle la taille
	if(is_file(ROOT_PATH."host.inc.php"))	controle_download(PATH_MOD_FICHIER.chemin($objet["fichier_dossier"],$_GET["id_dossier"],"url"));
	// Init
	$_GET["elements"] = "";
	$dossiers_arborescence = arborescence($objet["fichier_dossier"], $_GET["id_dossier"]);
	// Ajoute les fichiers de chaque dossier de l'arborescence de départ
	foreach($dossiers_arborescence as $dossiers_tmp){
		$fichiers_dossier = db_tableau("SELECT * FROM gt_fichier WHERE id_dossier='".$dossiers_tmp["id_dossier"]."'");
		foreach($fichiers_dossier as $fichier_tmp)	{ $_GET["elements"] .= $objet["fichier"]["type_objet"]."-".$fichier_tmp["id_fichier"]."@@"; }
	}
	// Nom archive
	$id_dossier_nom_archive = $_GET["id_dossier"];
}


////	AJOUT DE CHAQUE FICHIER
////
$tab_fichiers = array();
foreach(request_elements($_GET["elements"],$objet["fichier"]) as $id_fichier)
{
	$fichier_tmp = objet_infos($objet["fichier"],$id_fichier);
	if(droit_acces($objet["fichier"],$fichier_tmp) > 0)
	{
		// Chemin réel
		$dernier_fichier = infos_version_fichier($id_fichier);
		$fichier_tmp["path_source"] = PATH_MOD_FICHIER.chemin($objet["fichier_dossier"],$fichier_tmp["id_dossier"],"url").$dernier_fichier["nom_reel"];
		// chemin du fichier dans le zip (on supprime l'arborescence "primaire" & le premier "\" ou "/")
		$chemin_zip = "";
		if(isset($_GET["id_dossier"]))
		{
			$chemin_zip_parent = chemin($objet["fichier_dossier"],$_GET["id_dossier"],"url_zip");
			$chemin_zip = chemin($objet["fichier_dossier"],$fichier_tmp["id_dossier"],"url_zip");
			$chemin_zip = substr($chemin_zip, strlen($chemin_zip_parent)-1);
			if(substr($chemin_zip,0,1)=="\\" || substr($chemin_zip,0,1)=="/")	$chemin_zip = substr($chemin_zip,1);
		}
		$fichier_tmp["path_zip"] = $chemin_zip.suppr_carac_spe($fichier_tmp["nom"],"fichier_win");
		// Nom de l'archive
		if(empty($id_dossier_nom_archive))	$id_dossier_nom_archive = $fichier_tmp["id_dossier"];
		// Ajoute le fichier
		$tab_fichiers[] = $fichier_tmp;
		//Logs
		add_logs("consult2", $objet["fichier"], $id_fichier);
	}
}


////	CREATION DE L'ARCHIVE
////
if($id_dossier_nom_archive>1)	{ $nom_archive = objet_infos($objet["fichier_dossier"],$id_dossier_nom_archive,"nom").".zip"; }
else							{ $nom_archive = "archive.zip"; }
creer_envoyer_archive($tab_fichiers,$nom_archive);
db_close();
?>