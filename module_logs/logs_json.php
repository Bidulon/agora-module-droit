<?php
////	INIT
require "commun.inc.php";


////	PREPARE LA RECUPERATION DES RESULTATS DU TABLEAU
////

////	TRI DES RESULTATS
if(isset($_GET["iSortCol_0"]))
{
	$sql_order = "ORDER BY ";
	for($i=0; $i<intval($_GET["iSortingCols"]); $i++){
		if($_GET["bSortable_".intval($_GET["iSortCol_".$i])]=="true")	$sql_order .= $liste_champs[intval($_GET["iSortCol_".$i])]." ".mysql_real_escape_string($_GET["sSortDir_".$i]).", ";
	}
	$sql_order = ($sql_order!="ORDER BY ")  ?  trim($sql_order,", ")  :  "";
}

////	FILTRER LES RESULTATS VIA LA RECHERCHE TEXTUELLE (sur tous les champs..)
$sql_where = "";
if($_GET["sSearch"]!="")
{
	$sql_where = "WHERE (";
	foreach($liste_champs as $champ_tmp)	{ $sql_where .= $champ_tmp." LIKE '%".mysql_real_escape_string($_GET["sSearch"])."%' OR "; }
	$sql_where = trim($sql_where," OR ").")";
	if($_SESSION["user"]["admin_general"]!=1)	$sql_where .= " AND id_espace=".$_SESSION["espace"]["id_espace"];
}


////	FILTRE LES RESULTATS PAR CHAMP
foreach($liste_champs as $key => $champ_tmp)
{
	if($_GET["bSearchable_".$key]=="true" && $_GET["sSearch_".$key]!="")
	{
		if($sql_where=="")	$sql_where  = "WHERE ";
		else				$sql_where .= " AND ";
		// filtre par menu déroulant : comparaison exacte
		if($key==1 || $key==2 || $key==5)	$sql_where .= $champ_tmp." LIKE '".mysql_real_escape_string($_GET["sSearch_".$key])."' ";
		else								$sql_where .= $champ_tmp." LIKE '%".mysql_real_escape_string($_GET["sSearch_".$key])."%' ";
	}
}


////	RECUPERATION DES LOGS
////
$sql_limit = (isset($_GET["iDisplayStart"]) && $_GET["iDisplayLength"]!="-1")  ?  "LIMIT ".mysql_real_escape_string($_GET["iDisplayStart"]).", ".mysql_real_escape_string($_GET["iDisplayLength"])  :  "";
$corps_resultats = " gt_logs L  LEFT JOIN gt_utilisateur U ON U.id_utilisateur=L.id_utilisateur  LEFT JOIN gt_espace S ON S.id_espace=L.id_espace ".$sql_where;
$tab_resultats = db_tableau("SELECT ".implode(", ",$liste_champs)." FROM ".$corps_resultats." ".$sql_order." ".$sql_limit);
$nb_resultats_total = db_valeur("SELECT count(*) FROM ".$corps_resultats);


////	TABLEAU DE SORTIE JSON
////
$output = array("sEcho"=>intval($_GET["sEcho"]), "iTotalRecords"=>$nb_resultats_total, "iTotalDisplayRecords"=>$nb_resultats_total, "aaData"=>array());
foreach($tab_resultats as $result)
{
	$row = array();
	foreach($liste_champs as $champ_tmp)
	{
		$valeur_tmp = $result[str_replace(array("L.","U.","S."),"",$champ_tmp)];
		if(isset($trad[strtoupper($valeur_tmp)."_nom_module"]))	$row[] = $trad[strtoupper($valeur_tmp)."_nom_module"];
		elseif(isset($trad["LOGS_".$valeur_tmp]))				$row[] = $trad["LOGS_".$valeur_tmp];
		elseif($champ_tmp=="L.date")							$row[] = strftime("%d/%m/%Y - %H:%M",strtotime($valeur_tmp));
		else													$row[] = $valeur_tmp;
	}
	$output["aaData"][] = $row;
}

////	AFFICHE LA SORTIE JSON  (via PHP ou une librairie)
////
if(version_compare(PHP_VERSION,'5.2','>='))	{ echo json_encode($output); }
else
{
	require("JSON.php");
	$json = new Services_JSON();
	$json_output = $json->encode($output);
	print($json_output);
}
?>