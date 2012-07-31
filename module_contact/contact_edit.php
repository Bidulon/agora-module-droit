<?php
////	INIT
require "commun.inc.php";
require_once PATH_INC."header.inc.php";
if(@$_REQUEST["id_contact"]>0)	{ $contact_tmp = objet_infos($objet["contact"],$_REQUEST["id_contact"]);   droit_acces_controler($objet["contact"], $contact_tmp, 1.5); }
else							{ $contact_tmp["id_dossier"] = $_REQUEST["id_dossier"]; }


////	VALIDATION DU FORMULAIRE
////
if(isset($_POST["id_contact"]))
{
	////	MODIF / AJOUT
	$corps_sql = " civilite=".db_format($_POST["civilite"]).", nom=".db_format($_POST["nom"]).", prenom=".db_format($_POST["prenom"]).", adresse=".db_format($_POST["adresse"]).", codepostal=".db_format($_POST["codepostal"]).", ville=".db_format($_POST["ville"]).", pays=".db_format($_POST["pays"]).", telephone=".db_format($_POST["telephone"]).", telmobile=".db_format($_POST["telmobile"]).", fax=".db_format($_POST["fax"]).", mail=".db_format($_POST["mail"]).", siteweb=".db_format($_POST["siteweb"]).", competences=".db_format($_POST["competences"]).", hobbies=".db_format($_POST["hobbies"]).", fonction=".db_format($_POST["fonction"]).", societe_organisme=".db_format($_POST["societe_organisme"]).", commentaire=".db_format($_POST["commentaire"]).", raccourci=".db_format(@$_POST["raccourci"],"bool")." ";
	if($_POST["id_contact"] > 0){
		db_query("UPDATE gt_contact SET ".$corps_sql." WHERE id_contact=".$_POST["id_contact"]." ");
		add_logs("modif", $objet["contact"], $_POST["id_contact"]);
	}
	else{
		db_query("INSERT INTO gt_contact SET id_dossier=".db_format($_POST["id_dossier"]).", date_crea='".db_insert_date()."', id_utilisateur='".$_SESSION["user"]["id_utilisateur"]."', invite=".db_format(@$_POST["invite"]).", ".$corps_sql);
		$_POST["id_contact"] = db_last_id();
		add_logs("ajout", $objet["contact"], $_POST["id_contact"]);
	}

	////	AFFECTATION DES DROITS D'ACCÈS  +  AJOUT DE FICHIERS JOINTS
	affecter_droits_acces($objet["contact"],$_POST["id_contact"]);
	ajouter_fichiers_joint($objet["contact"],$_POST["id_contact"]);

	////	PHOTO
	if(preg_match("/supprimer|changer/i",$_POST["image"]))
	{
		// Supprime
		if(@$contact_tmp["photo"]!=""){
			unlink(PATH_MOD_CONTACT.@$contact_tmp["photo"]);
			db_query("UPDATE gt_contact SET photo=null WHERE id_contact=".db_format($_POST["id_contact"]));
		}
		// Ajoute / change
		if($_POST["image"]=="changer" && controle_fichier("image_gd",@$_FILES["fichier_image"]["name"])==true) {
			$nom_photo = $_POST["id_contact"].extension($_FILES["fichier_image"]["name"]);
			$chemin_photo = PATH_MOD_CONTACT.$nom_photo;
			move_uploaded_file($_FILES["fichier_image"]["tmp_name"], $chemin_photo);
			reduire_image($chemin_photo, $chemin_photo, 200, 200);
			db_query("UPDATE gt_contact SET photo=".db_format($nom_photo)." WHERE id_contact=".db_format($_POST["id_contact"]));
		}
	}

	////	ENVOI DE NOTIFICATION PAR MAIL
	if(isset($_POST["notification"]))
	{
		$liste_id_destinataires = users_affectes($objet["contact"], $_POST["id_contact"]);
		$objet_mail = $trad["CONTACT_mail_nouveau_contact_cree"]." ".$_SESSION["user"]["nom"]." ".$_SESSION["user"]["prenom"];
		$contenu_mail = $_POST["civilite"]." ".$_POST["nom"]." ".$_POST["prenom"];
		envoi_mail($liste_id_destinataires, $objet_mail, magicquotes_strip($contenu_mail), array("notif"=>true));
	}

	////	FERMETURE DU POPUP
	reload_close();
}
?>


<script type="text/javascript">
////	Redimensionne
resize_iframe_popup(580,600);

////	CONTROLE VALIDATION FINALE
function controle_formulaire()
{
	// Vérification du mail
	if(get_value("mail")!="" && controle_mail(get_value("mail"))==false){
		alert("<?php echo $trad["mail_pas_valide"]; ?>");
		return false;
	}
	// Controle le nombre de groupes et d'utilisateurs
	if(Controle_Menu_Objet()==false)	return false;
}
</script>


<?php
////	FORMULAIRE PRINCIPAL
////
echo "<form action=\"".php_self()."\" method=\"post\" enctype=\"multipart/form-data\" style=\"padding:10px\" OnSubmit=\"return controle_formulaire();\">";

	////	INFOS PRINCIPALES
	////
	echo "<fieldset style=\"margin-top:10px\"><table style=\"width:100%;\" cellpadding=\"3px\" cellspacing=\"0px\">";
		aff_champ(@$contact_tmp, "civilite");
		aff_champ(@$contact_tmp, "nom");
		aff_champ(@$contact_tmp, "prenom");
		aff_champ(@$contact_tmp, "mail");
		aff_champ(@$contact_tmp, "telephone");
		aff_champ(@$contact_tmp, "telmobile");
		aff_champ(@$contact_tmp, "fax");
		aff_champ(@$contact_tmp, "adresse");
		aff_champ(@$contact_tmp, "codepostal");
		aff_champ(@$contact_tmp, "ville");
		aff_champ(@$contact_tmp, "pays");
		aff_champ(@$contact_tmp, "siteweb");
		aff_champ(@$contact_tmp, "competences");
		aff_champ(@$contact_tmp, "hobbies");
		aff_champ(@$contact_tmp, "fonction");
		aff_champ(@$contact_tmp, "societe_organisme");
		echo "<tr>";
			echo "<td class=\"form_libelle\">".$trad["commentaire"]."</td>";
			echo "<td><textarea name=\"commentaire\" style=\"width:100%;height:30px;\">".@$contact_tmp["commentaire"]."</textarea></td>";
		echo "</tr>";
		echo "<tr>";
			$src_photo = (@$contact_tmp["photo"]=="")  ?  PATH_TPL."divers/inconnu.png"  :  PATH_MOD_CONTACT.$contact_tmp["photo"];
			echo "<td style=\"text-align:center;\"><img src=\"".$src_photo."\" style=\"max-width:130px;max-height:130px;\" /></td>";
			echo "<td><br />".menu_photo(@$contact_tmp["photo"])."</td>";
		echo "</tr>";
	echo "</table></fieldset>";

	
	////	DROITS D'ACCES ET OPTIONS
	////
	$cfg_menu_edit = array("objet"=>$objet["contact"], "id_objet"=>@$contact_tmp["id_contact"]);
	require_once PATH_INC."element_edit.inc.php";
	?>

	<div style="text-align:right;margin-top:20px;">
		<input type="hidden" name="id_contact" value="<?php echo @$contact_tmp["id_contact"]; ?>" />
		<input type="hidden" name="id_dossier" value="<?php echo $contact_tmp["id_dossier"]; ?>" />
		<input type="submit" value="<?php echo $trad["valider"]; ?>" class="button_big" />
	</div>
</form>


<?php require PATH_INC."footer.inc.php"; ?>