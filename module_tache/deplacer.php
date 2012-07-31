<?php
////	INIT
require "commun.inc.php";

////	ON DEPLACE PLUSIEURS ELEMENTS
foreach(request_elements($_POST["elements"],$objet["tache"]) as $id_tache)				{ deplacer_tache($id_tache, $_POST["id_dossier"]); }
foreach(request_elements($_POST["elements"],$objet["tache_dossier"]) as $id_dossier)	{ deplacer_tache_dossier($id_dossier, $_POST["id_dossier"]); }

////	DECONNEXION À LA BDD & FERMETURE DU POPUP
reload_close();
?>