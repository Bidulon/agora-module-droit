<?php
if($cfg_plugin["mode"]=="recherche" || $cfg_plugin["mode"]=="nouveautes")
{
	$liste_utilisateurs = db_tableau("SELECT DISTINCT * FROM gt_utilisateur WHERE 1 ".sql_utilisateurs_espace()." ".sql_selection_plugins($objet["utilisateur"])." ORDER BY ".$_SESSION["agora"]["tri_personnes"]);
	foreach($liste_utilisateurs as $user_tmp)
	{
		$resultat_tmp = array("type"=>"elem", "module_dossier"=>$cfg_plugin["module_dossier"]);
		$resultat_tmp["lien_js_icone"] = $resultat_tmp["lien_js_libelle"] = "popup('".ROOT_PATH.$cfg_plugin["module_dossier"]."/utilisateur.php?id_utilisateur=".$user_tmp["id_utilisateur"]."','utilisateur_infos".$user_tmp["id_utilisateur"]."');";
		$resultat_tmp["libelle"] = $user_tmp["civilite"]." ".$user_tmp["nom"]." ".$user_tmp["prenom"];
		$cfg_plugin["resultats"][] = $resultat_tmp;
	}
}
?>
