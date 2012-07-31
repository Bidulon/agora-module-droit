<?php
////	INIT
require "commun.inc.php";

////	ON DEPLACE PLUSIEURS ELEMENTS
foreach(request_elements($_POST["elements"],$objet["fichier"]) as $id_fichier)			{ deplacer_fichier($id_fichier, $_POST["id_dossier"]); }
foreach(request_elements($_POST["elements"],$objet["fichier_dossier"]) as $id_dossier)	{ deplacer_fichier_dossier($id_dossier, $_POST["id_dossier"]); }

////	DECONNEXION À LA BDD & FERMETURE DU POPUP
reload_close();
?>
