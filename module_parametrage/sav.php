<?php
////	INIT
require "commun.inc.php";
modif_php_ini();
nettoyer_tmp();
if(!isset($_GET["savbdd"]) && is_file(ROOT_PATH."host.inc.php"))	controle_download(PATH_STOCK_FICHIERS,100);


////	BACKUP BDD  /  BACKUP BDD + STOCK_FICHIERS
////
$fichier_dump = db_sauvegarde();
$tableau_fichier = array();
if(isset($_GET["savbdd"]))
{
	$tableau_fichier[] = array("path_source"=>$fichier_dump, "path_zip"=>str_replace(PATH_STOCK_FICHIERS,"",$fichier_dump));
	creer_envoyer_archive($tableau_fichier, "BackupAgoraBdd_".strftime("%d%m%Y").".zip");
}
else
{
	$nom_archive = "BackupAgora_".strftime("%d%m%Y");
	// Sauvegarde en ligne de commande  (100*1048576 = 100Mo minimum)
	if(function_exists("shell_exec") && is_file(ROOT_PATH."host.inc.php") && dossier_taille(PATH_STOCK_FICHIERS) > (100*1048576))
	{
		$nom_archive_tar = $nom_archive.".tar";
		$chemin_archive = PATH_STOCK_FICHIERS.$nom_archive_tar;
		shell_exec("cd ".PATH_STOCK_FICHIERS."; tar -cf ".$nom_archive_tar." *");  // -c=creation -f=precise le nom du fichier
		telecharger($nom_archive_tar, $chemin_archive, false, false);
		$is_archive_cmd = is_file($chemin_archive);
		if($is_archive_cmd==true)	unlink($chemin_archive);
	}
	// Sinon sauvegarde via PHP
	if(!isset($is_archive_cmd) || $is_archive_cmd==false)
	{
		foreach(dossier_fichiers(PATH_STOCK_FICHIERS) as $chemin_elem)
		{
			$fichier_tmp["path_source"] = $chemin_elem;
			if($chemin_elem==$fichier_dump)		$chemin_elem = str_replace(PATH_STOCK_FICHIERS, "", $chemin_elem);  // Place le fichier dump SQl à la racine de l'archive
			$fichier_tmp["path_zip"]  = str_replace(ROOT_PATH, "", $chemin_elem);
			$tableau_fichier[] = $fichier_tmp;
		}
		creer_envoyer_archive($tableau_fichier, $nom_archive.".zip");
	}
}


////	SUPPR DES FICHIERS TMP & FERME MYSL
////
unlink($fichier_dump);
db_close();
?>