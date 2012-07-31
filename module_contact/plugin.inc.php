<?php
////	CONTACTS
$liste_contacts = db_tableau("SELECT DISTINCT * FROM gt_contact WHERE ".sql_affichage_objets_arbo($objet["contact"])." ".sql_selection_plugins($objet["contact"])."  ORDER BY date_crea desc ");
foreach($liste_contacts as $contact_tmp)
{
	$opener = ($cfg_plugin["mode"]=="recherche") ? ".opener" : "";
	$resultat_tmp = array("type"=>"elem", "module_dossier"=>$cfg_plugin["module_dossier"]);
	$resultat_tmp["lien_js_icone"] = "window".$opener.".location.replace('".ROOT_PATH.$cfg_plugin["module_dossier"]."/index.php?id_dossier=".$contact_tmp["id_dossier"]."');";
	$resultat_tmp["lien_js_libelle"] = "popup('".ROOT_PATH.$cfg_plugin["module_dossier"]."/contact.php?id_contact=".$contact_tmp["id_contact"]."','contact".$contact_tmp["id_contact"]."');";
	$resultat_tmp["libelle"] = "<span ".infobulle(chemin($objet["contact_dossier"],$contact_tmp["id_dossier"],"url_virtuelle")."<br />".$trad["auteur"]." ".auteur($contact_tmp["id_utilisateur"],$contact_tmp["invite"])).">".$contact_tmp["civilite"]." ".$contact_tmp["nom"]." ".$contact_tmp["prenom"]."</span>";
	$cfg_plugin["resultats"][] = $resultat_tmp;
}
////	DOSSIERS
$liste_dossiers	= db_tableau("SELECT * FROM gt_contact_dossier WHERE 1 ".sql_affichage($objet["contact_dossier"])." ".sql_selection_plugins($objet["contact_dossier"])."  ORDER BY date_crea desc ");
foreach($liste_dossiers as $dossier_tmp)
{
	$resultat_tmp = array("type"=>"dossier", "module_dossier"=>$cfg_plugin["module_dossier"]);
	$resultat_tmp["lien_js_icone"] = "window.location.replace('".ROOT_PATH.$cfg_plugin["module_dossier"]."/index.php?id_dossier=".$dossier_tmp["id_dossier"]."');";
	$resultat_tmp["lien_js_libelle"] = $resultat_tmp["lien_js_icone"];
	$resultat_tmp["libelle"] = $dossier_tmp["nom"];
	$cfg_plugin["resultats"][] = $resultat_tmp;
}
?>
