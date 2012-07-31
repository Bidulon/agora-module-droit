<?php
////	FICHIERS
$liste_fichiers = db_tableau("SELECT DISTINCT * FROM gt_fichier WHERE ".sql_affichage_objets_arbo($objet["fichier"])." ".sql_selection_plugins($objet["fichier"])."  ORDER BY date_crea desc ");
foreach($liste_fichiers as $fichier_tmp)
{
	$opener = ($cfg_plugin["mode"]=="recherche") ? ".opener" : "";
	$resultat_tmp = array("type"=>"elem", "module_dossier"=>$cfg_plugin["module_dossier"]);
	$resultat_tmp["lien_js_icone"] = "window".$opener.".location.replace('".ROOT_PATH.$cfg_plugin["module_dossier"]."/index.php?id_dossier=".$fichier_tmp["id_dossier"]."');";
	$resultat_tmp["lien_js_libelle"] = "window.location.replace('".ROOT_PATH.$cfg_plugin["module_dossier"]."/telecharger.php?id_fichier=".$fichier_tmp["id_fichier"]."');";
	$resultat_tmp["libelle"] = "<span ".infobulle(chemin($objet["fichier_dossier"],$fichier_tmp["id_dossier"],"url_virtuelle")."<br />".$trad["auteur"]." ".auteur($fichier_tmp["id_utilisateur"],$fichier_tmp["invite"])).">".nom_fichier_reduit($fichier_tmp["nom"],30)."</span>";
	$cfg_plugin["resultats"][] = $resultat_tmp;
}
////	DOSSIERS
$liste_dossiers	= db_tableau("SELECT * FROM gt_fichier_dossier WHERE 1 ".sql_affichage($objet["fichier_dossier"])." ".sql_selection_plugins($objet["fichier_dossier"])."  ORDER BY date_crea desc ");
foreach($liste_dossiers as $dossier_tmp)
{
	$resultat_tmp = array("type"=>"dossier", "module_dossier"=>$cfg_plugin["module_dossier"]);
	$resultat_tmp["lien_js_icone"] = "window.location.replace('".ROOT_PATH.$cfg_plugin["module_dossier"]."/index.php?id_dossier=".$dossier_tmp["id_dossier"]."');";
	$resultat_tmp["lien_js_libelle"] = $resultat_tmp["lien_js_icone"];
	$resultat_tmp["libelle"] = $dossier_tmp["nom"];
	$cfg_plugin["resultats"][] = $resultat_tmp;
}
?>
