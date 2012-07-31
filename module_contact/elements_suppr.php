<?php
////	INITIALISATION
require "commun.inc.php";


////	SUPPRESSION DE DOSSIER / CONTACT / ELEMENTS
if(isset($_GET["id_dossier"]))		{ suppr_contact_dossier($_GET["id_dossier"]); }
elseif(isset($_GET["id_contact"]))	{ suppr_contact($_GET["id_contact"]); }
elseif(isset($_GET["elements"]))
{
	foreach(request_elements($_GET["elements"],$objet["contact"]) as $id_contact)			{ suppr_contact($id_contact); }
	foreach(request_elements($_GET["elements"],$objet["contact_dossier"]) as $id_dossier)	{ suppr_contact_dossier($id_dossier); }
}

////	Redirection
redir("index.php?id_dossier=".$_GET["id_dossier_retour"]);
?>