<?php
////	INIT
require_once "../".$_REQUEST["module_dossier"]."/commun.inc.php";
require_once PATH_INC."header.inc.php";
droit_acces_controler($objet[$_REQUEST["type_objet"]], $_REQUEST["id_objet"], 3);
titre_popup($trad["historique_element"]);
?>

<style>
td			{ padding:5px; }
.log_action	{ min-width:90px; }
.log_auteur	{ min-width:90px; }
.log_date	{ width:140px; }
</style>

<script type="text/javascript">
////	Redimensionne
resize_iframe_popup(800,350);
</script>


<div style="padding:20px;margin-top:-40px;font-weight:bold;">
<?php
////	INIT
$champs_sql = "DISTINCT action, id_utilisateur, DATE_FORMAT(date,'%Y-%m-%d %H:%i') as date, commentaire";
$log_modif = db_tableau("SELECT ".$champs_sql." FROM gt_logs WHERE type_objet='".$_REQUEST["type_objet"]."' AND id_objet='".intval($_REQUEST["id_objet"])."' AND action='modif' ORDER BY date asc");
$log_acces = db_tableau("SELECT ".$champs_sql." FROM gt_logs WHERE type_objet='".$_REQUEST["type_objet"]."' AND id_objet='".intval($_REQUEST["id_objet"])."' AND action like '%consult%' ORDER BY date asc");
////	LOGS DE MODIF
if(count($log_modif)>0)
{
	echo "<h3>".$trad["LOGS_modif"]." <img src=\"".PATH_TPL."divers/tri_asc.png\" /></h3>";
	echo "<table>";
	foreach($log_modif as $cpt => $log)
	{
		echo "<tr><td class='log_action'>".($cpt+1).". ".$trad["LOGS_modif"]."</td>
		<td class='log_auteur'>".auteur($log["id_utilisateur"])."</td>
		<td class='log_date'>".temps($log["date"],"complet")."</td>
		<td>".$log["commentaire"]."</td></tr>";
	}
	echo "</table>";
}
////	LOGS D'ACCES
if(count($log_acces)>0)
{
	echo "<h3>".$trad["LOGS_consult"]." <img src=\"".PATH_TPL."divers/tri_desc.png\" /></h3>";
	echo "<table>";
	foreach($log_acces as $cpt => $log)
	{
		echo "<tr><td class='log_action'>".($cpt+1).". ".$trad["LOGS_".$log["action"]]."</td>
		<td class='log_auteur'>".auteur($log["id_utilisateur"])."</td>
		<td class='log_date'>".temps($log["date"],"complet")."</td></tr>";
	}
	echo "</table>";
}
?>
</div>


<?php require PATH_INC."footer.inc.php"; ?>