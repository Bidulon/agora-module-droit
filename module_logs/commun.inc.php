<?php
////	INIT
@define("MODULE_NOM","logs");
@define("MODULE_DOSSIER","module_logs");
require_once "../includes/global.inc.php";
controle_acces_admin("admin_general");

// COLONNES A AFFICHER
$liste_champs = array("L.date", "S.nom", "L.module", "L.ip", "U.identifiant", "L.action", "L.commentaire");
?>