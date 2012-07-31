<?php
////	INIT
require_once "../includes/global.inc.php";
$_SESSION["cfg"]["espace"]["messenger_dernier_affichage"] = time();
$_SESSION["cfg"]["espace"]["messenger_alerte"] = 0;

/////   SUPPRESSION DES ANCIENS MESSAGES "PERIMÉS" DU MESSENGER
db_query("DELETE FROM gt_utilisateur_messenger WHERE date < '".(time() - duree_messages_messenger)."'");
?>


<table style="font-weight:bold;" cellpadding="3" cellspacing="0">
<?php
/////   LISTE DES MESSAGES
$liste_messages = db_tableau("SELECT DISTINCT  T1.*, T2.*  FROM  gt_utilisateur_messenger T1, gt_utilisateur T2  WHERE  T1.id_utilisateur_expediteur=T2.id_utilisateur  AND  T1.id_utilisateur_destinataires LIKE '%@@".$_SESSION["user"]["id_utilisateur"]."@@%'  ORDER BY T1.date asc");
foreach($liste_messages as $infos_elem)
{
	// HEURE & DESTINATAIRES DU MESSAGE
	$heure_destinataires = $trad["HEADER_MENU_envoye_a"]." ".strftime("%H:%M",$infos_elem["date"])." :";
	foreach(text2tab($infos_elem["id_utilisateur_destinataires"]) as $id_user){
		if($infos_elem["id_utilisateur"]!=$id_user)		$heure_destinataires .= "<div style='margin-top:5px;'>".auteur($id_user)."</div>";
	}
	// MESSAGE
	echo "<tr style=\"cursor:help;\" ".infobulle("<div style='float:right;margin:10px;'>".$heure_destinataires."</div>".photo_user($infos_elem,100,100)).">";
		echo "<td style=\"width:70px;text-align:right;\">".$infos_elem["prenom"]."</td>";
		echo "<td style=\"color:".$infos_elem["couleur"].";font-weight:bold;\">".$infos_elem["message"]."</td>";
	echo "</tr>";
}
?>
</table>
