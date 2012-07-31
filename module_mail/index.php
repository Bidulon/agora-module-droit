<?php
////	INIT
define("IS_MAIN_PAGE",true);
require "commun.inc.php";
require PATH_INC."header_menu.inc.php";
init_editeur_tinymce();


////	ENVOIE DU MAIL & INSERTION DANS L'HISTORIQUE
////
if(isset($_POST["titre"]))
{
	// Envoi le mail
	modif_php_ini();
	$options["envoi_fichiers"] = true;
	if(@$_POST["no_header_footer"]=="1")		$options["header_footer"] = false;
	if(@$_POST["afficher_dest_message"]=="1")	$options["afficher_dest_message"] = true;
	if(@$_POST["accuse_reception"]=="1")		$options["accuse_reception"] = true;
	$envoi_mail = envoi_mail($_POST["mails"], magicquotes_strip($_POST["titre"]), magicquotes_strip($_POST["description"]), $options);
	// Ajoute dans l'historique
	$destinataires = "";
	foreach($_POST["mails"] as $mail)	{ $destinataires .= $mail.", "; }
	if($envoi_mail==true)	db_query("INSERT INTO gt_historique_mails SET destinataires=".db_format(substr($destinataires,0,-2)).", titre=".db_format($_POST["titre"]).", description=".db_format($_POST["description"]).", date_crea='".db_insert_date()."', id_utilisateur='".$_SESSION["user"]["id_utilisateur"]."'");
}
?>


<script type="text/javascript">
////	SELECTIONNER / DESELECTIONNER TOUS LES UTILISATEURS OU CONTACT
function select_ensemble_users(id_espace_dossier)
{
	for(var i=0; i < users_ensembles[id_espace_dossier].length; i++)
	{
		id_tmp = users_ensembles[id_espace_dossier][i];
		if(element("box_"+id_tmp).checked==false)	{ element("box_"+id_tmp).checked = true;	element("txt_"+id_tmp).className = "lien_select"; }
		else										{ element("box_"+id_tmp).checked = false;	element("txt_"+id_tmp).className = "lien"; }
	}
}

////    On contrôle les champs
function controle_formulaire()
{
	// Au moins un mails sélectionné!
	var nb_mails_coches = 0;
	inputs_mail = document.getElementsByName("mails[]");
	for(i=0; i<inputs_mail.length; i++){
		if(inputs_mail[i].checked==true)  nb_mails_coches++;
	}
	if(nb_mails_coches==0)	{ alert("<?php echo $trad["MAIL_specifier_mail"]; ?>");  return false; }
	// Il doit y avoir un titre
	if (get_value("titre").length==0 || get_value("titre")=="<?php echo $trad["MAIL_titre"]; ?>")	{ alert("<?php echo $trad["specifier_titre"]; ?>"); return false; }
}
</script>


<form action="<?php echo php_self(); ?>" method="POST" id="form_mail" enctype="multipart/form-data" OnSubmit="return controle_formulaire();" class="contenu_principal_centre">
	<table id="contenu_principal_table"><tr>
		<td id="menu_gauche_block_td">
			<div class="menu_gauche_block content">
				<img src="<?php echo PATH_TPL; ?>module_utilisateurs/menu.png" style="margin-top:-10px;margin-right:-10px;float:right;opacity:0.4;filter:alpha(opacity=40);" />
				<?php
				////	UTILISATEURS DE CHAQUE ESPACE
				////
				foreach(espaces_affectes_user(null,true) as $espace_tmp)
				{
					////	ESPACE (+ tableau pour les affectations auto)
					$id_espace_tmp = "E".$espace_tmp["id_espace"];
					echo "<img src=\"".PATH_TPL."divers/espaces.png\" align='absmiddle' />&nbsp; <span class='lien' onClick=\"afficher_dynamic('".$id_espace_tmp."');\" ".infobulle($espace_tmp["description"]).">".$espace_tmp["nom"]."</span> &nbsp;";
					echo "<img src=\"".PATH_TPL."divers/check_inverser.png\" class='lien' onclick=\"select_ensemble_users('".$id_espace_tmp."');afficher_dynamic('".$id_espace_tmp."',null,true);\" ".infobulle($trad["inverser_selection"])." />";
					echo "<div id=\"".$id_espace_tmp."\" style='display:".($_SESSION["espace"]["id_espace"]==$espace_tmp["id_espace"]?"block":"none")."'>";
						////	GROUPES D'UTILISATEURS (AFFECTATIONS EXPRESS)
						foreach(groupes_users($espace_tmp["id_espace"]) as $groupe_tmp)
						{
							// Init
							foreach($groupe_tmp["users_tab"] as $id_user)	{ @$groupe_tmp["users_tab_bis"][] = "'E".$espace_tmp["id_espace"]."_U".$id_user."'"; }
							$groupe_tmp["id_groupe_bis"] = "E".$espace_tmp["id_espace"]."_G".$groupe_tmp["id_groupe"];
							// Groupe  +  tableau des utilisateurs
							echo "<div style='padding-left:15px;' title=\"".$groupe_tmp["users_title"]."\">";
								echo "<img src=\"".PATH_TPL."divers/utilisateurs_groupe.png\" class='icone_groupe' /> <span id='txt_".$groupe_tmp["id_groupe_bis"]."' class='lien' onClick=\"check_txt_box(this.id,'".$groupe_tmp["id_groupe_bis"]."');selection_groupe('".$groupe_tmp["id_groupe_bis"]."');\" class='lien'>".$groupe_tmp["titre"]."</span>";
								echo "<input type='checkbox' id='box_".$groupe_tmp["id_groupe_bis"]."' style='visibility:hidden;' />";
								echo "<script>  users_ensembles['".$groupe_tmp["id_groupe_bis"]."'] = Array(".implode(",",$groupe_tmp["users_tab_bis"])."); </script>";
							echo "</div>";
						}
						////	USERS
						$tab_id_utilisateur_tmp = array();
						foreach(users_espace($espace_tmp["id_espace"],"tableau") as $user_tmp)
						{
							if($user_tmp["mail"]!="")
							{
								$id_utilisateur_tmp = "E".$espace_tmp["id_espace"]."_U".$user_tmp["id_utilisateur"];
								$tab_id_utilisateur_tmp[] = "'".$id_utilisateur_tmp."'";
								echo "<div style='padding-left:15px;' ".infobulle($user_tmp["mail"]."<br />".$user_tmp["fonction"]."<br />".$user_tmp["societe_organisme"])." >";
									echo "<input type='checkbox' name='mails[]' value=\"".$user_tmp["mail"]."\" id=\"box_".$id_utilisateur_tmp."\" onClick=\"check_txt_box(this.id,'".$id_utilisateur_tmp."');\" />";
									echo "<span class='lien' id=\"txt_".$id_utilisateur_tmp."\" onClick=\"check_txt_box(this.id,'".$id_utilisateur_tmp."');\">".$user_tmp["prenom"]." ".$user_tmp["nom"]."</span>";
								echo "</div>";
							}
						}
						////	TAB JS DES USERS  &  FIN DE LA LISTE DES USERS
						echo "<script>  users_ensembles['".$id_espace_tmp."'] = Array(".implode(",",$tab_id_utilisateur_tmp)."); </script>";
					echo "</div><br />";
				}
				?>
			</div>
			<div id="liste_contacts" class="menu_gauche_block content">
				<img src="<?php echo PATH_TPL; ?>module_contact/menu.png" style="margin-top:-10px;margin-right:-10px;float:right;opacity:0.4;filter:alpha(opacity=40);" />
				<?php
				////	CONTACT DE CHAQUE DOSSIER DE CONTACT
				////
				$afficher_contacts = false;
				foreach(arborescence($objet["contact_dossier"]) as $dossier_tmp)
				{
					$liste_contacts	= db_tableau("SELECT * FROM gt_contact WHERE id_dossier='".$dossier_tmp["id_dossier"]."' AND mail!='' ".sql_affichage($objet["contact"],$dossier_tmp["id_dossier"])." ORDER BY ".$_SESSION["agora"]["tri_personnes"]);
					if(count($liste_contacts) > 0)
					{
						////	DOSSIER
						$afficher_contacts = true;
						$id_dossier_tmp = "D".$dossier_tmp["id_dossier"];
						echo "<div style='margin-bottom:7px;margin-left:".($dossier_tmp["niveau"]*20)."px;'>";
					 	echo "<img src=\"".PATH_TPL."divers/dossier_arborescence.png\" align='absmiddle' />&nbsp; <span class='lien' onClick=\"afficher_dynamic('".$id_dossier_tmp."');\" ".infobulle(ucfirst($dossier_tmp["description"])).">".ucfirst($dossier_tmp["nom"])."</span> &nbsp;";
					 	echo "<img src=\"".PATH_TPL."divers/check_inverser.png\" class='lien' onclick=\"select_ensemble_users('".$id_dossier_tmp."');afficher_dynamic('".$id_dossier_tmp."',null,true);\" ".infobulle($trad["inverser_selection"])." />";
					 	echo "<div id=\"".$id_dossier_tmp."\" ".($dossier_tmp["id_dossier"]>1?"style='display:none;'":"").">";
							////	CONTACTS
							$tab_id_contact_tmp = array();
							foreach($liste_contacts as $contact_tmp)
							{
								$id_contact_tmp = "D".$dossier_tmp["id_dossier"]."_C".$contact_tmp["id_contact"];
								$tab_id_contact_tmp[] = "'".$id_contact_tmp."'";;
								echo "<div style='padding-left:15px;' ".infobulle($contact_tmp["mail"]."<br />".$contact_tmp["fonction"]."<br />".$contact_tmp["societe_organisme"])." >";
									echo "<input type='checkbox' name='mails[]' value=\"".$contact_tmp["mail"]."\" id=\"box_".$id_contact_tmp."\" onClick=\"check_txt_box(this.id,'".$id_contact_tmp."');\" />";
									echo "<span class='lien' id=\"txt_".$id_contact_tmp."\" onClick=\"check_txt_box(this.id,'".$id_contact_tmp."');\">".$contact_tmp["prenom"]." ".$contact_tmp["nom"]."</span>";
								echo "</div>";
							}
							////	TAB JS DES CONTACTS  &  FIN DE LA LISTE DES CONTACTS
							echo "<script>  users_ensembles['".$id_dossier_tmp."'] = Array(".implode(",",$tab_id_contact_tmp)."); </script>";
							echo "</div>";
						echo "</div>";
					}
				}
				if($afficher_contacts==false)	echo "<script> afficher('liste_contacts',false); </script>";
				?>
			</div>
			<div class="menu_gauche_block content lien" onclick="popup('historique_mail.php?','historique_mail');">
				<img src="<?php echo PATH_TPL; ?>module_mail/historique_mail.png" /> <?php echo $trad["MAIL_historique_mail"]; ?>
			</div>
		</td>
		<td>
			<div class="content">
				<input type="text" name="titre" value="<?php echo $trad["MAIL_titre"]; ?>" id="titre" style="width:90%;margin-bottom:10px;" onFocus="if(this.value=='<?php echo addslashes($trad["MAIL_titre"]); ?>') this.value='';" />
				<div class="form_libelle"><?php echo $trad["description"]; ?><textarea name="description" id="description" style="width:100%;height:300px;"></textarea></div>
				<table style="width:100%;margin-top:10px;">
					<tr>
						<td>
							<?php
							////	PAS DE HEADER-FOOTER / AFFICHER LES DESTINATAIRES DANS LE MESSAGE / ACCUSE DE RECEPTION
							echo "<div ".infobulle($trad["MAIL_no_header_footer_infos"])."><input type='checkbox' name='no_header_footer' value='1' style='vertical-align:bottom;' />".$trad["MAIL_no_header_footer"]."</div>";
							echo "<div ".infobulle($trad["MAIL_afficher_destinataires_message_infos"])."><input type='checkbox' name='afficher_dest_message' value='1' style='vertical-align:bottom;' />".$trad["MAIL_afficher_destinataires_message"]."</div>";
							if($_SESSION["user"]["mail"]!="")	echo "<div ".infobulle($trad["MAIL_accuse_reception_infos"])."><input type='checkbox' name='accuse_reception' value='1' />".$trad["MAIL_accuse_reception"]."</div>";
							?>
						</td>
						<td style="text-align:right;">
							<?php
							for($i=1; $i<=10; $i++){
								echo "<div id='div_fichier".$i."' ".($i>1?"class='cacher'":"")." >".$trad["MAIL_fichier_joint"]." &nbsp;<input type='file' name='fichier".$i."' onChange=\"if(this.value!='') afficher_dynamic('div_fichier".($i+1)."','show',true);\" /></div>";
							}
							?>
						</td>
					</tr>
					<tr><td colspan="2" style="text-align:center;margin-top:20px;">
						<input type="submit" value="<?php echo $trad["envoyer"]; ?>" class="button_big" style="width:200px;" />
					</td></tr>
				</table>
			</div>
		</td>
	</tr></table>
</form>


<?php require PATH_INC."footer.inc.php"; ?>