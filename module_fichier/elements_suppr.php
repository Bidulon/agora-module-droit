<?php
////	INIT
require "commun.inc.php";
modif_php_ini();

////	SUPPRESSION DE CHAQUE FICHIER ET/OU DOSSIER
if(isset($_GET["id_dossier"]))		{ suppr_fichier_dossier($_GET["id_dossier"]); }
elseif(isset($_GET["id_fichier"]))	{ suppr_fichier($_GET["id_fichier"]); }
elseif(isset($_GET["elements"]))
{
	foreach(request_elements($_GET["elements"],$objet["fichier"]) as $id_fichier)				{ suppr_fichier($id_fichier); }
	foreach(request_elements($_GET["elements"],$objet["fichier_dossier"]) as $id_dossier)		{ suppr_fichier_dossier($id_dossier); }
}

////	Redirection
redir("index.php?id_dossier=".$_GET["id_dossier_retour"]);
?>
