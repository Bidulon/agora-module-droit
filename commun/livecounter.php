<?php
////	INIT
require_once "../includes/global.inc.php";
$_SESSION["cfg"]["espace"]["users_connectes"] = users_connectes();


////	LIVECOUNTER PRINCIPAL
////
if($_GET["type"]=="principal")
{
	if(count($_SESSION["cfg"]["espace"]["users_connectes"])>0)
	{
		//Pour comparer à $cpt_users qui commence à 0..
		$nb_users = count($_SESSION["cfg"]["espace"]["users_connectes"]) - 1;
		//Affiche le titre et chaque user
		echo $trad["HEADER_MENU_en_ligne"]." &nbsp;";
		foreach($_SESSION["cfg"]["espace"]["users_connectes"] as $cpt_users => $user_tmp){
			echo "<span class='lien' style=\"color:#fc0\" onClick=\"check_txt_box(this.id,'users_messenger".$cpt_users."');afficher('calque_messenger',true);\" ".infobulle($user_tmp["civilite"]." ".$user_tmp["nom"]." ".$user_tmp["prenom"]." ".$trad["HEADER_MENU_connecte_a"]." ".strftime("%H:%M",$user_tmp["derniere_connexion"])).">".$user_tmp["prenom"].(($cpt_users<$nb_users)?", ":"")."</span>";
		}
	}
}


////	LIVECOUNTER DU MESSENGER
////
if($_GET["type"]=="messenger")
{
	// TITRE "SEUL SUR LE SITE"
	if(count($_SESSION["cfg"]["espace"]["users_connectes"])==0)	{ echo "<span style=\"color:#005;\">".$trad["HEADER_MENU_seul_utilisateur_connecte"]."</span>"; }
	// LISTE UTILISATEURS
	else
	{
		echo "<table>";
		foreach($_SESSION["cfg"]["espace"]["users_connectes"] as $user_cpt => $user_tmp)
		{
			$id_tmp = "users_messenger".$user_cpt;
			//  On re-check utilisateurs sélectionnés au dernier message
			if(isset($_SESSION["users_consult_messenger"]) && in_array($user_tmp["id_utilisateur"],$_SESSION["users_consult_messenger"]))	{ $style = "lien_select";	$checked = "checked"; }
			else																															{ $style = "lien";			$checked = ""; }
			// Affiche des checkbox
			echo "<tr>
					<td style=\"width:28px;\">".photo_user($user_tmp,28,28,true)."<input type=\"checkbox\" name=\"tab_users_messenger[]\" value=\"".$user_tmp["id_utilisateur"]."\" id=\"box_".$id_tmp."\" ".$checked." onClick=\"check_txt_box(this.id,'".$id_tmp."');\" style=\"display:none;\" /></td>
					<td style=\"vertical-align:middle;\"><span class=\"".$style."\" id=\"txt_".$id_tmp."\" onClick=\"check_txt_box(this.id,'".$id_tmp."');\" ".infobulle($user_tmp["civilite"]." ".$user_tmp["nom"]." ".$user_tmp["prenom"]).">".$user_tmp["prenom"]."</span></td>
				</tr>";
		}
		echo "</table>";
	}
}
?>
