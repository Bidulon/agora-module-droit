<?php
////	INITIALISATION
require "commun.inc.php";

////	ON DEPLACE PLUSIEURS ELEMENTS
foreach(request_elements($_POST["elements"],$objet["contact"]) as $id_contact)			{ deplacer_contact($id_contact, $_POST["id_dossier"]); }
foreach(request_elements($_POST["elements"],$objet["contact_dossier"]) as $id_dossier)	{ deplacer_contact_dossier($id_dossier, $_POST["id_dossier"]); }

////	DECONNEXION À LA BDD & FERMETURE DU POPUP
reload_close();
?>
