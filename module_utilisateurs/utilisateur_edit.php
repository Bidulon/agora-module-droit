<?php
////	INIT
require "commun.inc.php";
require_once PATH_INC."header.inc.php";
if(droit_modif_utilisateur(@$_REQUEST["id_utilisateur"])!="1" && $_SESSION["espace"]["droit_acces"]<2)  exit();
if(isset($_REQUEST["id_utilisateur"]))	$user_tmp = user_infos($_REQUEST["id_utilisateur"]);
else									nb_users_depasse();


////	SUPPRESSION OU AJOUT D'ADRESSE IP
////
if(isset($_GET["action"])){
	if($_GET["action"]=="suppr_adresse_ip")		{ db_query("DELETE FROM gt_utilisateur_adresse_ip WHERE adresse_ip=".db_format($_GET["adresse_ip"])." AND id_utilisateur=".db_format($_GET["id_utilisateur"])); }
	if($_POST["action"]=="ajout_adresse_ip")	{ db_query("INSERT INTO gt_utilisateur_adresse_ip SET adresse_ip=".db_format($_POST["adresse_ip"]).", id_utilisateur=".db_format($_POST["id_utilisateur"])); }
}


////	VALIDATION DU FORMULAIRE D'EDITION D'UTILISATEUR
////
if(@$_POST["action"]=="editer")
{
	////	MODIF / AJOUT
	$corps_sql = " civilite=".db_format($_POST["civilite"]).", nom=".db_format($_POST["nom"]).", prenom=".db_format($_POST["prenom"]).", identifiant=".db_format($_POST["identifiant"]).", adresse=".db_format($_POST["adresse"]).", codepostal=".db_format($_POST["codepostal"]).", ville=".db_format($_POST["ville"]).", pays=".db_format($_POST["pays"]).", telephone=".db_format($_POST["telephone"]).", telmobile=".db_format($_POST["telmobile"]).", fax=".db_format($_POST["fax"]).", mail=".db_format($_POST["mail"]).", siteweb=".db_format($_POST["siteweb"]).", competences=".db_format($_POST["competences"]).", hobbies=".db_format($_POST["hobbies"]).", fonction=".db_format($_POST["fonction"]).", societe_organisme=".db_format($_POST["societe_organisme"]).", commentaire=".db_format($_POST["commentaire"]).", langue=".db_format($_POST["langue"]).", espace_connexion=".db_format(@$_POST["espace_connexion"]);
	if($_POST["id_utilisateur"]>0 && $_POST["pass"]!=""){
		$corps_sql .= ", pass='".sha1_pass($_POST["pass"])."'";
		add_logs("modif", $objet["utilisateur"], $_POST["id_utilisateur"], auteur($_POST["id_utilisateur"]));
	}
	elseif($_POST["id_utilisateur"]<1){
		$_POST["id_utilisateur"] = creer_utilisateur(@$_POST["nom"],@$_POST["prenom"],$_POST["identifiant"],$_POST["pass"]);
		add_logs("ajout", $objet["utilisateur"], $_POST["id_utilisateur"], auteur($_POST["id_utilisateur"]));
	}

	////	CREATION VALIDEE
	if($_POST["id_utilisateur"]>0)
	{
		////	ENREGISTRE LES INFOS SUR L'USER
		db_query("UPDATE gt_utilisateur SET ".$corps_sql." WHERE id_utilisateur=".db_format($_POST["id_utilisateur"]));

		////	AGENDA DESACTIVE  /  ADMIN GENERAL  /  ESPACE DE CONNEXION  /  MAJ DES VALEURS DE SESSION
		if(isset($_POST["agenda_desactive"]) && $_SESSION["user"]["admin_general"]==1)	db_query("UPDATE gt_utilisateur SET agenda_desactive=".db_format($_POST["agenda_desactive"],"bool")." WHERE id_utilisateur=".db_format($_POST["id_utilisateur"]));
		if(isset($_POST["admin_general"]) && $_SESSION["user"]["admin_general"]==1)		db_query("UPDATE gt_utilisateur SET admin_general=".db_format($_POST["admin_general"],"bool")." WHERE id_utilisateur=".db_format($_POST["id_utilisateur"]));
		if(isset($_POST["espace_connexion"]) && ($_SESSION["user"]["admin_general"]==1 || $_SESSION["user"]["id_utilisateur"]==$_POST["id_utilisateur"]))		db_query("UPDATE gt_utilisateur SET espace_connexion=".db_format($_POST["espace_connexion"])." WHERE id_utilisateur=".db_format($_POST["id_utilisateur"]));
		if(is_auteur($_POST["id_utilisateur"])==true)	$_SESSION["user"] = user_infos($_POST["id_utilisateur"]);

		////	PHOTO
		if(preg_match("/supprimer|changer/i",$_POST["image"]))
		{
			// Supprime
			$nom_photo = user_infos($_POST["id_utilisateur"],"photo");
			if($nom_photo!=""){
				unlink(PATH_MOD_USER.$nom_photo);
				db_query("UPDATE gt_utilisateur SET photo=null WHERE id_utilisateur=".db_format($_POST["id_utilisateur"]));
			}
			// Ajoute / change
			if($_POST["image"]=="changer" && controle_fichier("image_gd",@$_FILES["fichier_image"]["name"])==true) {
				$nom_photo = $_POST["id_utilisateur"].extension($_FILES["fichier_image"]["name"]);
				$chemin_photo = PATH_MOD_USER.$nom_photo;
				move_uploaded_file($_FILES["fichier_image"]["tmp_name"], $chemin_photo);
				reduire_image($chemin_photo, $chemin_photo, 200, 200);
				db_query("UPDATE gt_utilisateur SET photo='".$nom_photo."' WHERE id_utilisateur=".db_format($_POST["id_utilisateur"]));
			}
		}

		/////	AFFECTATION AUX ESPACES (avec réinitialisation)
		if($_SESSION["user"]["admin_general"]==1)
		{
			db_query("DELETE FROM gt_jointure_espace_utilisateur WHERE id_utilisateur=".db_format($_POST["id_utilisateur"]));
			if(isset($_POST["espace"])) {
				foreach($_POST["espace"] as $id_espace => $droit)	{ db_query("INSERT INTO gt_jointure_espace_utilisateur SET id_espace=".db_format($id_espace).", id_utilisateur=".db_format($_POST["id_utilisateur"]).", droit=".db_format($droit).", envoi_invitation=".db_format(@$_POST["espace_invit"][$id_espace],"bool")); }
			}
		}

		////	AFFECTATION A L'ESPACE COURANT (S'IL N'EST PAS DEJA OUVERT A TOUS LES USERS DU SITE)
		if(isset($_POST["add_espace_courant"]) && db_valeur("SELECT count(*) FROM gt_jointure_espace_utilisateur WHERE id_espace='".$_SESSION["espace"]["id_espace"]."' AND tous_utilisateurs='1' ")==0) {
			db_query("INSERT INTO gt_jointure_espace_utilisateur SET id_espace='".$_SESSION["espace"]["id_espace"]."', id_utilisateur=".db_format($_POST["id_utilisateur"]).", droit='1'");
		}

		////	ENVOI DE NOTIFICATION PAR MAIL
		if(isset($_POST["notification"]) && $_POST["mail"])
		{
			$objet_mail = $trad["UTILISATEURS_mail_objet_nouvel_utilisateur"]."  ''".$_SESSION["agora"]["nom"]."''";
			$contenu_mail  = $trad["UTILISATEURS_mail_nouvel_utilisateur"]."  ''".$_SESSION["agora"]["nom"]."''";
			$contenu_mail .= "<br /><br />".$trad["UTILISATEURS_mail_infos_connexion"]." :";
			$contenu_mail .= "<br />".$trad["login"]." : <b>".$_POST["identifiant"]."</b>";
			$contenu_mail .= "<br />".$trad["pass"]." : <b>".$_POST["pass"]."</b>";
			$contenu_mail .= "<br /><br />".$trad["UTILISATEURS_mail_infos_connexion2"];
			envoi_mail($_POST["mail"], $objet_mail, magicquotes_strip($contenu_mail), array("notif"=>true));
		}
	}

	////	FERMETURE DU POPUP
	reload_close();
}
?>


<script type="text/javascript">
////	Redimensionne
resize_iframe_popup(520,600);

////	On contrôle les champs principaux
function controle_formulaire()
{
	// Certains champs sont obligatoire
	if(get_value("nom")=="")			{ alert("<?php echo $trad["UTILISATEURS_specifier_nom"]; ?>");			return false; }
	if(get_value("prenom")=="")			{ alert("<?php echo $trad["UTILISATEURS_specifier_prenom"]; ?>");		return false; }
	if(get_value("identifiant")=="")	{ alert("<?php echo $trad["UTILISATEURS_specifier_identifiant"]; ?>");	return false; }
	// Vérif du password (new user / modif de password)
	if(get_value("pass")=="" && '<?php echo @$_REQUEST["id_utilisateur"]; ?>' < 1)	{ alert("<?php echo $trad["UTILISATEURS_specifier_pass"]; ?>");	return false; }
	if(get_value("pass")!=get_value("pass2"))	{  alert("<?php echo $trad["password_verif_alert"]; ?>");  return false;  }
	// Vérif de l'email (si spécifié)
	if(get_value("mail")!="" && controle_mail(get_value("mail"))==false)	{ alert("<?php echo $trad["mail_pas_valide"]; ?>");  return false; }
	// controle existance identifiant
	requete_ajax("GET", "identifiant_verif.php?identifiant="+get_value("identifiant")+"&id_utilisateur="+get_value("id_utilisateur"));
	if(trouver("oui",Http_Request_Result))	{ alert("<?php echo $trad["UTILISATEURS_identifiant_deja_present"]; ?>"); return false; }
}

////	Confirmation de la suppression d'une adresse IP
function controle_suppr_ip(adresse_ip)
{
	if (confirm(adresse_ip+"\n<?php echo $trad["confirmer_suppr"]; ?>"))	redir("utilisateur_edit.php?id_utilisateur=<?php echo @$user_tmp["id_utilisateur"]; ?>&action=suppr_adresse_ip&adresse_ip="+adresse_ip);
}

////	Controle l'ajout d'une adresse IP
function controle_adresse_ip()
{
	if(get_value("adresse_ip")=="")				{ alert("<?php echo $trad["UTILISATEURS_specifier_adr_ip"]; ?>");  return false;  }
	var reg = /([0-9]{1,3}\.){3}[0-9]{1,3}/;  //Adresse ip => 1 à 3 chiffres suivit d'un point, répété 3 fois, puis 1 à 3 chiffres
	if(!reg.test(get_value("adresse_ip")))		{ alert("adresse ip invalide !");  return false; }
}
</script>


<?php
////	FORMULAIRE PRINCIPAL
////
echo "<form action=\"".php_self()."\" enctype='multipart/form-data' method='post' style='padding:10px' OnSubmit='return controle_formulaire();'>";

	////	INFOS PRINCIPALES
	////
	echo "<fieldset style='margin-top:10px'><table style='width:100%;' cellpadding='3px' cellspacing='0px'>";
		aff_champ(@$user_tmp, "civilite");
		aff_champ(@$user_tmp, "nom", "obligatoire");
		aff_champ(@$user_tmp, "prenom", "obligatoire");
		aff_champ(@$user_tmp, "identifiant", "obligatoire");
		$pass_obligatoire = (@$_REQUEST["id_utilisateur"]<1)  ?  "obligatoire"  :  "";
		aff_champ(@$user_tmp, "pass", $pass_obligatoire);
		aff_champ(@$user_tmp, "pass2", $pass_obligatoire);
		aff_champ(@$user_tmp, "mail");
		aff_champ(@$user_tmp, "telephone");
		aff_champ(@$user_tmp, "telmobile");
		aff_champ(@$user_tmp, "fax");
		aff_champ(@$user_tmp, "adresse");
		aff_champ(@$user_tmp, "codepostal");
		aff_champ(@$user_tmp, "ville");
		aff_champ(@$user_tmp, "pays");
		aff_champ(@$user_tmp, "siteweb");
		aff_champ(@$user_tmp, "competences");
		aff_champ(@$user_tmp, "hobbies");
		aff_champ(@$user_tmp, "fonction");
		aff_champ(@$user_tmp, "societe_organisme");
		echo "<tr>";
			echo "<td class='form_libelle'>".$trad["commentaire"]."</td>";
			echo "<td><textarea name='commentaire' style='width:100%;height:30px;'>".@$user_tmp["commentaire"]."</textarea></td>";
		echo "</tr>";
		echo "<tr>";
			$src_photo = (!isset($user_tmp["photo"]) || $user_tmp["photo"]=="")  ?  PATH_TPL."divers/inconnu.png"  :  PATH_MOD_USER.$user_tmp["photo"];
			echo "<td style='text-align:center;'><img src=\"".$src_photo."\" style='max-width:130px;max-height:130px;' /></td>";
			echo "<td><br />".menu_photo(@$user_tmp["photo"])."</td>";
		echo "</tr>";
	echo "</table></fieldset>";


	////	INFOS SECONDAIRES
	////
	echo "<fieldset style='margin-top:40px'><legend>".$trad["divers"]."</legend>";
	echo "<table style='width:100%;' cellpadding='5px' cellspacing='0px'>";
		////	ADMIN GENERAL ?
		if($_SESSION["user"]["admin_general"]==1 && $_SESSION["user"]["id_utilisateur"]!=@$user_tmp["id_utilisateur"])
		{
			echo "<tr>";
				echo "<td class='form_libelle'><img src=\"".PATH_TPL."divers/acces_admin_general.png\" width='16px' /> &nbsp; ".$trad["UTILISATEURS_admin_general"]."</td>";
				echo "<td class='form_libelle'>";
					echo "<select name='admin_general' onChange=\"style_select(this.name);\">";
						echo "<option value='0' ".(@$user_tmp["admin_general"]!="1"?"selected":"").">".$trad["non"]."</option>";
						echo "<option value='1' ".(@$user_tmp["admin_general"]=="1"?"selected":"")." style='font-weight:bold;color:#900;'>".$trad["oui"]."</option>";
					echo "</select>";
					echo "<script> style_select('admin_general'); </script>";
				echo "</td>";
			echo "</tr>";
		}
		////	ESPACE CONNEXION AGORA
		$espaces_user = espaces_affectes_user(@$user_tmp,true);
		if(@$user_tmp["id_utilisateur"]>0 && count($espaces_user)>0)
		{
			foreach($espaces_user as $espace_tmp)	{ @$liste_espaces_consult .= "<option value=\"".$espace_tmp["id_espace"]."\" ". (($user_tmp["espace_connexion"]==$espace_tmp["id_espace"])?"selected":"") ."> ".$espace_tmp["nom"]."</option>";  }
			echo "<tr>";
				echo "<td class='form_libelle'><img src=\"".PATH_TPL."module_utilisateurs/user_connexion.png\" /> &nbsp; ".$trad["UTILISATEURS_espace_connexion"]."</td>";
				echo "<td><select name='espace_connexion'>".@$liste_espaces_consult."</select></td>";
			echo "</tr>";
		}
		////	LANGUE
		echo "<tr>";
			echo "<td class='form_libelle'><img src=\"".PATH_TPL."module_utilisateurs/user_pays.png\" /> &nbsp; ".$trad["UTILISATEURS_langues"]."</td>";
			echo "<td>".liste_langues(@$user_tmp["langue"],"user")."</td>";
		echo "</tr>";
		////	AGENDA ACTIVE
		if($_SESSION["user"]["admin_general"]==1)
		{
			echo "<tr ".infobulle($trad["UTILISATEURS_agenda_perso_active_infos"])." >";
				echo "<td class='form_libelle'><img src=\"".PATH_TPL."module_utilisateurs/user_agenda.png\" /> &nbsp; <acronym>".$trad["UTILISATEURS_agenda_perso_active"]."</acronym></td>";
				echo "<td class='form_libelle'>";
					echo "<select name='agenda_desactive' onChange=\"style_select(this.name);\">";
						echo "<option value='0' ".(@$user_tmp["agenda_desactive"]!="1"?"selected":"")." style='color:#090;font-weight:bold;'>".$trad["oui"]."</option>";
						echo "<option value='1' ".(@$user_tmp["agenda_desactive"]=="1"?"selected":"")." style='color:#900;font-weight:bold;'>".$trad["non"]."</option>";
					echo "</select>";
					echo "<script>style_select('agenda_desactive');</script>";
				echo "</td>";
			echo "</tr>";
		}
		////	NOTIFICATION PAR MAIL
		if(@$user_tmp["id_utilisateur"]==0 && function_exists("mail")==true)
		{
			echo "<tr>";
				echo "<td colspan='2' onClick='if(get_value('mail')=='' && is_checked('notification')) alert('".addslashes($trad["UTILISATEURS_alert_notification_mail"])."');'>";
					echo "<span onClick=\"set_check('notification','bascule');\" class='lien'><img src=\"".PATH_TPL."module_utilisateurs/user_mail.png\" /> &nbsp; ".$trad["UTILISATEURS_notification_mail"]."</span>";
					echo "<input type='checkbox' name='notification' value='1' />";
				echo "</td>";
			echo "</tr>";
		}
	echo "</table></fieldset>";


	////	ESPACES OU L'UTILISATEUR EST AFFECTE
	////
	if($_SESSION["user"]["admin_general"]==1)
	{
		echo "<fieldset style='margin-top:40px'><legend>".$trad["UTILISATEURS_liste_espaces"]."</legend>";
		echo "<table>";
			echo "<tr>";
				echo "<td>&nbsp;</td>";
				echo "<td class='cols1' title=\"".$trad["ESPACES_utilisation_info"]."\"><img src=\"".PATH_TPL."divers/acces_utilisateur.png\" /><br />".$trad["ESPACES_utilisation"]."</td>";
				echo "<td class='cols2' title=\"".$trad["ESPACES_invitation_info"]."\"><img src=\"".PATH_TPL."divers/acces_utilisateur_plus.png\" /><br />".$trad["ESPACES_invitation"]."</td>";
				echo "<td class='cols3' title=\"".$trad["ESPACES_administration_info"]."\"><img src=\"".PATH_TPL."divers/acces_admin_espace.png\" /><br />".$trad["ESPACES_administration"]."</td>";
			echo "</tr>";
			foreach(db_tableau("SELECT * FROM gt_espace") as $compteur => $espace_tmp)
			{
				$class_txt = "lien";
				$checked1 = $checked1b = $checked2 = $tous_users_info = "";
				$sql_tmp = "SELECT count(*) FROM gt_jointure_espace_utilisateur WHERE id_espace='".$espace_tmp["id_espace"]."' AND id_utilisateur='".@$user_tmp["id_utilisateur"]."'";
				if(db_valeur($sql_tmp." AND droit=1")>0)				{ $checked1  = "checked";	$class_txt = "txt_acces_user"; }
				if(db_valeur($sql_tmp." AND envoi_invitation=1")>0)		{ $checked1b = "checked"; }
				if(db_valeur($sql_tmp." AND droit=2")>0)				{ $checked2  = "checked";	$class_txt = "txt_acces_admin"; }
				$sql_tmp2 = "SELECT count(*) FROM gt_jointure_espace_utilisateur WHERE id_espace='".$espace_tmp["id_espace"]."' AND tous_utilisateurs='1' ";
				if(db_valeur($sql_tmp2." AND droit=1")>0)				{ $checked1  .= "checked disabled";		$tous_users_info = infobulle($trad["UTILISATEURS_tous_users_affectes"]);		$class_txt = "txt_acces_user"; }
				if(db_valeur($sql_tmp2." AND envoi_invitation=1")>0)	{ $checked1b .= "checked disabled";		$tous_users_info = infobulle($trad["UTILISATEURS_tous_users_affectes"]); }
				echo "<tr class='tr_survol' ".$tous_users_info.">";
					echo "<td class='".$class_txt." pas_selection' id='espace".$compteur."_txt' onClick=\"affect_users_espaces(this,'espace".$compteur."');\">".$espace_tmp["nom"]."</td>";
					echo "<td class='cols1'><input type='checkbox' name='espace[".$espace_tmp["id_espace"]."]'		 value='1' id='espace".$compteur."_box_1'  onClick=\"affect_users_espaces(this,'espace".$compteur."');\" title=\"".$trad["ESPACES_utilisation_info"]."\" ".$checked1." /></td>";
					echo "<td class='cols2'><input type='checkbox' name='espace_invit[".$espace_tmp["id_espace"]."]' value='1' id='espace".$compteur."_box_1b' onClick=\"affect_users_espaces(this,'espace".$compteur."');\" title=\"".$trad["ESPACES_invitation_info"]."\" ".$checked1b." /></td>";
					echo "<td class='cols3'><input type='checkbox' name='espace[".$espace_tmp["id_espace"]."]'		 value='2' id='espace".$compteur."_box_2'  onClick=\"affect_users_espaces(this,'espace".$compteur."');\" title=\"".$trad["ESPACES_administration_info"]."\" ".$checked2." /></td>";
				echo "</tr>";
			}
		echo "</table></fieldset>";
		echo "<style>  .cols1{width:70px;text-align:center;} .cols2{width:90px;text-align:center;} .cols3{width:90px;text-align:center;}  </style>";
	}


	////	VALIDATION FINALE
	////
	echo "<div style='text-align:center;margin-top:30px;'>";
		if(isset($_GET["add_espace_courant"]))  echo "<input type='hidden' name='add_espace_courant' value='1' />";
		echo "<input type='hidden' name='id_utilisateur' value='".@$user_tmp["id_utilisateur"]."' />";
		echo "<input type='hidden' name='action' value='editer' />";
		echo "<input type='submit' value=\"".$trad["valider"]."\" class='button_big' />";
	echo "</div>";
echo "</form>";



////	CONTROLE ADRESSE IP
////
if($_SESSION["user"]["admin_general"]==1 && controle_ip==true && @$user_tmp["id_utilisateur"]>0)
{
	echo "<fieldset title=\"".$trad["UTILISATEURS_info_adresse_ip"]."\" style='margin-top:60px;margin-bottom:10px;border:1px solid #f00;text-align:center;font-weight:bold;'>";
		////	Affichage des addresse IP
		$liste_adresses_ip = db_tableau("SELECT * FROM gt_utilisateur_adresse_ip WHERE id_utilisateur='".$user_tmp["id_utilisateur"]."'");
		if(count($liste_adresses_ip)>0)
		{
			echo $trad["UTILISATEURS_liste_adresses_ip"]." : &nbsp; ";
			foreach($liste_adresses_ip as $infos_ip)	{ echo $infos_ip["adresse_ip"]." <a href=\"javascript:controle_suppr_ip('".$infos_ip["adresse_ip"]."');\" title=\"".$trad["UTILISATEURS_suppr_adresses_ip"]."\" ><img src=\"".PATH_TPL."divers/supprimer.png\" style='height:15px;' /></a> &nbsp; "; }
			echo "<br /><br />";
		}
		////	Menu d'ajout d'adresse IP
		echo "<form action=\"".php_self()."\" method='post' OnSubmit=\"return controle_adresse_ip();\" id='ajouter_ip' style='display:none;margin-bottom:10px'>";
			echo $trad["UTILISATEURS_ajouter_adr_ip"]." : <input type='text' name='adresse_ip' style='width:100px;' /> &nbsp; &nbsp; ";
			echo "<input type='hidden' name='id_utilisateur' value=\"".$user_tmp["id_utilisateur"]."\" /><input type='hidden' name='action' value='ajout_adresse_ip' />";
			echo "<input type='submit' value='".$trad["ajouter"]."' class='button' />";
		echo "</form>";
		echo "<span class='lien' onClick=\"afficher('ajouter_ip',null,'block');redir('#bottom_page');this.style.display='none';\"><img src=\"".PATH_TPL."divers/info_small.png\" /> ".$trad["UTILISATEURS_ajouter_adr_ip"]."</span>";
	echo "</fieldset><a name='bottom_page'></a>";
}


////	Affichage du bottom + Footer
if(preg_match("/adresse_ip/i",@$_REQUEST["action"]))	echo "<script> redir('#bottom_page'); </script>";
require PATH_INC."footer.inc.php";
?>