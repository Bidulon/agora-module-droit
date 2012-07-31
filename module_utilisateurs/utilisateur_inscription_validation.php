<?php
////	INIT
require "commun.inc.php";
require_once PATH_INC."header.inc.php";
if($_SESSION["espace"]["droit_acces"]<2)	exit;


////	VALIDATION D'INSCRIPTION D'UTILISATEURS
////
if(isset($_POST["inscription_users"]))
{
	foreach($_POST["inscription_users"] as $id_inscription)
	{
		// Init
		$inscrip_infos = db_ligne("SELECT * FROM gt_utilisateur_inscription WHERE id_inscription='".$id_inscription."'");
		// VALIDATION
		if($_POST["action"]=="valider_inscriptions")
		{
			// Controle le nombre max d'users
			if(nb_users_depasse(true,false))	continue;
			// Ajoute l'utilisateur
			$id_utilisateur = creer_utilisateur(addslashes(@$inscrip_infos["nom"]), addslashes(@$inscrip_infos["prenom"]), addslashes($inscrip_infos["identifiant"]), addslashes($inscrip_infos["pass"]), addslashes($inscrip_infos["id_espace"]), addslashes($inscrip_infos["mail"]));
			// Envoi du mail : "Votre compte a bien été validé sur ''Mon Espace''"
			$objet_mail = $contenu_mail = $trad["inscription_users_valider_mail"]." ''".$_SESSION["espace"]["nom"]."''";
			$contenu_mail  = "<b>".$contenu_mail." :</b><br /><br />";
			$contenu_mail .= $trad["UTILISATEURS_mail_infos_connexion"]." :<br />";
			$contenu_mail .= $trad["login"]." : &nbsp; <b>".$inscrip_infos["mail"]."</b><br />";
			$contenu_mail .= $trad["pass"]." : &nbsp; <b>".$inscrip_infos["pass"]."</b><br /><br />";
			$contenu_mail .= $trad["UTILISATEURS_mail_infos_connexion2"];
			$envoi_mail = envoi_mail($inscrip_infos["mail"], $objet_mail, magicquotes_strip($contenu_mail), array("message_alert"=>false));
			// Logs
			add_logs("ajout", $objet["utilisateur"], $id_utilisateur, auteur($id_utilisateur));
		}
		// INVALIDATION
		elseif($_POST["action"]=="invalider_inscriptions")
		{
			// Envoi du mail : "Votre compte n'a pas été validé sur Mon_Espace"
			$objet_mail = $contenu_mail = $trad["inscription_users_invalider_mail"]." ''".$_SESSION["espace"]["nom"]."'";
			$contenu_mail  = "<b>".$contenu_mail." :</b>";
			$envoi_mail = envoi_mail($inscrip_infos["mail"], $objet_mail, magicquotes_strip($contenu_mail), array("message_alert"=>false));
		}
		// Supprime l'inscription
		db_query("DELETE FROM gt_utilisateur_inscription WHERE id_inscription='".$id_inscription."'");
	}
	reload_close();
}
?>


<script type="text/javascript">
////	Redimensionne
resize_iframe_popup(400,500);
</script>


<form action="<?php echo php_self(); ?>" method="post">
	<fieldset>
		<legend><?php echo $trad["inscription_users_validation"]; ?></legend>
		<?php
		////	USERS A CONFIRMER
		foreach(db_tableau("SELECT * FROM  gt_utilisateur_inscription WHERE id_espace='".$_SESSION["espace"]["id_espace"]."'") as $user_tmp)
		{
			$id_txt_box = "inscription_user".$user_tmp["id_inscription"];
			echo "<div>
					<input type='checkbox' name='inscription_users[]' value='".$user_tmp["id_inscription"]."' id='box_".$id_txt_box."' onClick=\"check_txt_box(this.id,'$id_txt_box');\" />
					<span id='txt_".$id_txt_box."' onClick=\"check_txt_box(this.id,'$id_txt_box');\" class='lien' ".infobulle(temps($user_tmp["date"])."<br>".$user_tmp["message"])." >".$user_tmp["nom"]." ".$user_tmp["prenom"]." &nbsp;(".$user_tmp["mail"].")</span>
				  </div>";
		}
		?>
		<br />
		<button type="submit" name="action" value="valider_inscriptions" class="button" style="float:left;width:150px;"><?php echo $trad["inscription_users_valider"]; ?></button> &nbsp; &nbsp;
		<button type="submit" name="action" value="invalider_inscriptions" class="button" style="float:right;"><?php echo $trad["inscription_users_invalider"]; ?></button>
	</fieldset>
</form>


<?php require PATH_INC."footer.inc.php"; ?>