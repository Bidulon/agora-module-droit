<?php
////	INIT
require "commun.inc.php";

////	TABLEAU DES LOGS
$contenu_export = "";
$liste_logs = db_tableau("SELECT ".implode(", ",$liste_champs)."  FROM  gt_logs L  LEFT JOIN gt_utilisateur U ON U.id_utilisateur=L.id_utilisateur  LEFT JOIN gt_espace S ON S.id_espace=L.id_espace");
foreach($liste_logs as $log_tmp)
{
	foreach($log_tmp as $log_champ)  { $contenu_export .= "\"".$log_champ."\";"; }
	$contenu_export .= "\n";
}

////	ENVOI DES LOGS
telecharger($_SESSION["agora"]["nom"]." - LOGS.csv", false, $contenu_export);
?>