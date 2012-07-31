<?php
////	INIT
require "commun.inc.php";

////	ON DEPLACE PLUSIEURS ELEMENTS
foreach(request_elements($_POST["elements"],$objet["lien"]) as $id_lien)			{ deplacer_lien($id_lien, $_POST["id_dossier"]); }
foreach(request_elements($_POST["elements"],$objet["lien_dossier"]) as $id_dossier)	{ deplacer_lien_dossier($id_dossier, $_POST["id_dossier"]); }

////		DECONNEXION À LA BDD & FERMETURE DU POPUP
reload_close();
?>