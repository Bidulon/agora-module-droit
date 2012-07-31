<?php
////	INIT
require "commun.inc.php";
require_once PATH_INC."header.inc.php";


////	INFOS + DROIT ACCES + LOGS
////
$espace_tmp = (isset($_GET["id_espace"]))  ?  info_espace($_GET["id_espace"])  :  array("id_espace"=>"0");
if($_SESSION["espace"]["droit_acces"]<2)	exit;


////	VALIDATION DU FORMULAIRE
////
if(isset($_POST["id_espace"]))
{
	////	MODIF / AJOUT
	$corps_sql = "nom=".db_format($_POST["nom"]).", description=".db_format($_POST["description"]).", inscription_users=".db_format(@$_POST["inscription_users"]).", fond_ecran=".db_format(@$_POST["fond_ecran"]);
	if($_POST["id_espace"]>0)	{ db_query("UPDATE gt_espace SET ".$corps_sql." WHERE id_espace=".db_format($_POST["id_espace"])); }
	else						{ db_query("INSERT INTO gt_espace SET ".$corps_sql);   $_POST["id_espace"] = db_last_id(); }

	////	AFFECTATION DES UTILISATEURS
	if($_SESSION["user"]["admin_general"]==1)
	{
		// Réinitialisation
		db_query("DELETE FROM gt_jointure_espace_utilisateur WHERE id_espace=".db_format($_POST["id_espace"]));
		// Tous les utilisateurs
		if(isset($_POST["tous_box_1"]))		{ db_query("INSERT INTO gt_jointure_espace_utilisateur SET id_espace=".db_format($_POST["id_espace"]).", tous_utilisateurs=1, droit=1, envoi_invitation=".db_format(@$_POST["tous_box_1b"],"bool")); }
		// invites (+ password?)
		if(isset($_POST["box_invites"]))  {
			db_query("INSERT INTO gt_jointure_espace_utilisateur SET id_espace=".db_format($_POST["id_espace"]).", invites=1, droit=1 ");
			db_query("UPDATE gt_espace SET password=".db_format($_POST["password"])." WHERE id_espace=".db_format($_POST["id_espace"]));
		}
		// Chaque utilisateur
		if(isset($_POST["user"])) {
			foreach($_POST["user"] as $id_user => $droit)	{ db_query("INSERT INTO gt_jointure_espace_utilisateur SET id_espace=".db_format($_POST["id_espace"]).", id_utilisateur=".db_format($id_user).", droit=".db_format($droit).", envoi_invitation=".db_format(@$_POST["user_invit"][$id_user],"bool")); }
		}
	}

	////	MODULES DE L'ESPACE
	db_query("DELETE FROM gt_jointure_espace_module WHERE id_espace=".db_format($_POST["id_espace"]));
	foreach($_POST["liste_modules"] as $nom_module)
	{
		$options_module = "";
		if(isset($_POST["option_modules"][$nom_module]))	{  foreach($_POST["option_modules"][$nom_module] as $option_module)  { $options_module .= $option_module."@@"; }  }
		db_query("INSERT INTO gt_jointure_espace_module SET id_espace=".db_format($_POST["id_espace"]).", nom_module=".db_format($nom_module).", classement=".db_format($_POST["classement"][$nom_module]).", options=".db_format(trim($options_module),"@@"));
	}

	////	LOGS  +  FERME LE POPUP
	add_logs("modif", "", "", $trad["ESPACES_parametrage"]." : ".$_POST["nom"]);
	////	RELOAD L'ESPACE (+ REDIR VERS "/module_espaces/" SI BESOIN)  OU  RECHARGE JUSTE LA PAGE
	$page_redir = ($_POST["id_espace"]==$_SESSION["espace"]["id_espace"])  ?  "index.php?id_espace_acces=".$_POST["id_espace"]  :  "";
	if($page_redir!="" && preg_match("/".MODULE_DOSSIER."/i",$_POST["page_origine"]))	$page_redir .= "&redir_module_dossier=".MODULE_DOSSIER;
	reload_close($page_redir);
}
?>


<style type="text/css">
table		{ width:100%; margin-bottom:5px; font-weight:bold; }
.cols1		{ width:70px; text-align:center; }
.cols2		{ width:90px; text-align:center; }
.cols3		{ width:90px; text-align:center; }
</style>


<script type="text/javascript">
////	Redimensionne
resize_iframe_popup(600,700);

////    CONTROLE DE SAISIE DU FORMULAIRE
////
function controle_formulaire()
{
	// Vérif des modules cochés
	var nb_modules = 0;
	tab_modules = document.getElementsByName("liste_modules[]");
	for(i=0; i<tab_modules.length; i++)		{  if(tab_modules[i].checked==true)  nb_modules++; }
	if(nb_modules==0)			{ alert("<?php echo $trad["ESPACES_selectionner_module"]; ?>"); return false; }
	if(get_value("nom")=="")	{ alert("<?php echo $trad["specifier_nom"]; ?>");  return false; }
}


////	SELECTION DE "ESPACE PUBLIC"
////
function selection_espace_public(id_elem)
{
	// Sélectionne / désélectionne
	check_txt_box(id_elem,'invites','lien_select2');
	// Affiche le password?
	afficher('password_invites',is_checked('box_invites'));
	if(is_checked('txt_invites'))	element('password').focus();
}
</script>


<form action="<?php echo php_self(); ?>" method="post" OnSubmit="return controle_formulaire();" style="padding:10px;font-weight:bold;">
	
	<fieldset>
		<table cellpadding="5">
			<tr>
				<td style="width:80px;"><?php echo $trad["nom"]; ?></td>
				<td><input type="text" name="nom" id="nom" value="<?php echo @$espace_tmp["nom"]; ?>" style="width:50%" /></td>
			</tr>
			<tr>
				<td><?php echo $trad["description"]; ?></td>
				<td><input type="text" name="description" id="description" value="<?php echo @$espace_tmp["description"]; ?>" style="width:95%" /></td>
			</tr>
			<tr>
				<td><?php echo $trad["fond_ecran"]; ?></td>
				<td><?php echo menu_fonds_ecran(@$espace_tmp["fond_ecran"]); ?></td>
			</tr>
			<tr>
				<td colspan="2" <?php echo infobulle($trad["inscription_users_option_espace_info"]); ?>>
					<?php
					////	INSCRIPTION DES INVITES
					echo "<span id='txt_inscription_users' class='".(@$espace_tmp["inscription_users"]>0?"lien_select2":"lien")." pas_selection' onClick=\"check_txt_box(this.id,'inscription_users','lien_select2');\"><img src=\"".PATH_TPL."divers/crayon.png\" /> &nbsp; ".$trad["inscription_users_option_espace"]."</span>";
					echo "<input type='checkbox' name='inscription_users' value=\"1\" id='box_inscription_users' onClick=\"check_txt_box(this.id,'inscription_users','lien_select2');\" ".(@$espace_tmp["inscription_users"]>0?"checked":"")." />";
					?>
				</td>
			</tr>
			<tr class="tr_survol" <?php echo infobulle($trad["ESPACES_invites_infos"]); ?> >
				<td colspan="2">
					<?php
					////	ESPACE PUBLIC
					$is_espace_public = db_valeur("SELECT count(*) FROM gt_jointure_espace_utilisateur WHERE id_espace='".$espace_tmp["id_espace"]."' AND invites=1 AND droit=1");
					echo "<span  id='txt_invites' onClick=\"selection_espace_public(this.id);\"  class='".($is_espace_public>0?"lien_select2":"lien")."'><img src=\"".PATH_TPL."divers/planete.png\" /> &nbsp; ".$trad["ESPACES_espace_public"]."</span>";
					echo "<input type='checkbox' name='box_invites' value='1'  id='box_invites' onClick=\"selection_espace_public(this.id);\"  ".($is_espace_public>0?"checked":"")." />";
					echo "<span id='password_invites' style='margin-left:20px;".($is_espace_public==0?"display:none;":"")."'><i>".$trad["pass"]."</i> <input type='text' name='password' value=\"".@$espace_tmp["password"]."\" style='width:60px;' /></span>";
					?>
				</td>
			</tr>
		</table>
	</fieldset>

	<?php
	////	AFFECTATION DES UTILISATEURS (ADMIN GENERAL)
	////
	if($_SESSION["user"]["admin_general"]==1)
	{
		////	ACCES À TOUS LES UTILISATEURS
		$acces_tous       = db_valeur("SELECT count(*) FROM gt_jointure_espace_utilisateur WHERE id_espace='".$espace_tmp["id_espace"]."' AND tous_utilisateurs=1 AND droit=1 ");
		$acces_tous_invit = db_valeur("SELECT count(*) FROM gt_jointure_espace_utilisateur WHERE id_espace='".$espace_tmp["id_espace"]."' AND tous_utilisateurs=1 AND envoi_invitation=1 ");
	?>

		<fieldset style="margin-top:40px">
			<legend><?php echo $trad["ESPACES_gestion_acces"]; ?></legend>
			<table>
				<tr>
					<td>&nbsp;</td>
					<td class="cols1" title="<?php echo $trad["ESPACES_utilisation_info"]; ?>" ><img src="<?php echo PATH_TPL; ?>divers/acces_utilisateur.png" /><br /><?php echo $trad["ESPACES_utilisation"]; ?></td>
					<td class="cols2" title="<?php echo $trad["ESPACES_invitation_info"]; ?>" ><img src="<?php echo PATH_TPL; ?>divers/acces_utilisateur_plus.png" /><br /><?php echo $trad["ESPACES_invitation"]; ?></td>
					<td class="cols3" title="<?php echo $trad["ESPACES_administration_info"]; ?>" ><img src="<?php echo PATH_TPL; ?>divers/acces_admin_espace.png" /><br /><?php echo $trad["ESPACES_administration"]; ?></td>
				</tr>
				<tr class="tr_survol">
					<td class="<?php echo ($acces_tous>0)?"txt_acces_user":"lien"; ?>" onClick="affect_users_espaces(this,'tous');"><i><?php echo majuscule($trad["ESPACES_tous_utilisateurs"]); ?></i> &nbsp; <img src="<?php echo PATH_TPL; ?>divers/utilisateurs_small.png" /></td>
					<td class="cols1"><input type="checkbox" name="tous_box_1"  value="1" onClick="affect_users_espaces(this,'tous');" title="<?php echo $trad["ESPACES_utilisation_info"]; ?>"  <?php if($acces_tous>0) echo "checked"; ?> /></td>
					<td class="cols2"><input type="checkbox" name="tous_box_1b" value="1" onClick="affect_users_espaces(this,'tous');" title="<?php echo $trad["ESPACES_invitation_info"]; ?>"  <?php if($acces_tous_invit>0) echo "checked"; ?> /></td>
					<td class="cols3">&nbsp;</td>
				</tr>
				<?php
				////	UTILISATEURS DU SITE
				$users_site = db_tableau("SELECT * FROM gt_utilisateur ORDER BY ".$_SESSION["agora"]["tri_personnes"]);
				foreach($users_site as $compteur => $infos_users)
				{
					$class_txt = "lien";
					$checked1 = $checked1b = $checked2 = "";
					$sql_tmp = "SELECT count(*) FROM gt_jointure_espace_utilisateur WHERE id_espace='".$espace_tmp["id_espace"]."' AND id_utilisateur='".$infos_users["id_utilisateur"]."' ";
					if(db_valeur($sql_tmp." AND droit=1")>0)				{ $checked1  = "checked";	$class_txt = "txt_acces_user"; }
					if(db_valeur($sql_tmp." AND envoi_invitation=1")>0)		{ $checked1b = "checked"; }
					if(db_valeur($sql_tmp." AND droit=2")>0)				{ $checked2  = "checked";	$class_txt = "txt_acces_admin"; }
					echo "<tr class='tr_survol'>";
						echo "<td class='".$class_txt." pas_selection' id='user".$compteur."_txt' onClick=\"affect_users_espaces(this,'user".$compteur."');\">".$infos_users["nom"]." ".$infos_users["prenom"]."</td>";
						echo "<td class='cols1'><input type='checkbox' name='user[".$infos_users["id_utilisateur"]."]'			value='1' id='user".$compteur."_box_1'	onClick=\"affect_users_espaces(this,'user".$compteur."');\" title=\"".$trad["ESPACES_utilisation_info"]."\" ".$checked1." /></td>";
						echo "<td class='cols2'><input type='checkbox' name='user_invit[".$infos_users["id_utilisateur"]."]'	value='1' id='user".$compteur."_box_1b'	onClick=\"affect_users_espaces(this,'user".$compteur."');\" title=\"".$trad["ESPACES_invitation_info"]."\" ".$checked1b." /></td>";
						echo "<td class='cols3'><input type='checkbox' name='user[".$infos_users["id_utilisateur"]."]'			value='2' id='user".$compteur."_box_2'	onClick=\"affect_users_espaces(this,'user".$compteur."');\" title=\"".$trad["ESPACES_administration_info"]."\" ".$checked2." /></td>";
					echo "</tr>";
				}
				?>
			</table>
		</fieldset>
	<?php } ?>


	<fieldset style="margin-top:40px">
		<legend><?php echo $trad["ESPACES_modules_espace"]; ?></legend>
		
		<table style="width:100%;">
		<?php
		////	LISTE DES MODULES
		////
		$modules_espace = db_tableau("SELECT  T1.*  FROM  gt_module T1, gt_jointure_espace_module T2  WHERE  T1.nom=T2.nom_module  AND  T2.id_espace='".$espace_tmp["id_espace"]."'  ORDER BY T2.classement asc");
		$autres_modules = db_tableau("SELECT  *  FROM  gt_module  WHERE  nom  NOT IN (SELECT DISTINCT nom_module FROM gt_jointure_espace_module WHERE id_espace='".$espace_tmp["id_espace"]."') ");
		$modules_site = array_merge($modules_espace, $autres_modules);
		$compteur = 1;
		foreach($modules_site as $module_tmp)
		{
			////	Module affecté à l'espace ?
			$module_check = db_valeur("SELECT count(*) FROM gt_jointure_espace_module WHERE id_espace='".$espace_tmp["id_espace"]."' AND nom_module='".$module_tmp["nom"]."'");
			$id_tmp = "module_".$compteur;
			echo "<tr class='tr_survol'>";
			echo "<td class='".($module_check>0?"lien_select2":"lien")." pas_selection' id='txt_".$id_tmp."' onClick=\"check_txt_box(this.id,'".$id_tmp."','lien_select2');\">".$trad[strtoupper($module_tmp["nom"])."_nom_module"]."</td>";
			echo "<td style='width:30px;'><input type='checkbox' name='liste_modules[]' value=\"".$module_tmp["nom"]."\" id='box_".$id_tmp."'  onClick=\"check_txt_box(this.id,'".$id_tmp."','lien_select2');\" ".($module_check>0?"checked":"")." /></td>";
			echo "<td style='width:30px;'><input type='text' name=\"classement[".$module_tmp["nom"]."]\" value=\"".$compteur."\" ".infobulle($trad["ESPACES_modules_classement"])." style='width:20px' /></td>";
			echo "</tr>";
			////	Options du module (s'il y en a)
			require_once ROOT_PATH.$module_tmp["module_dossier_fichier"]."/commun.inc.php";
			if(isset($config["module_espace_options"][$module_tmp["nom"]]))
			{
				foreach($config["module_espace_options"][$module_tmp["nom"]] as $module_option)
				{
					$module_option_check = db_valeur("SELECT count(*) FROM gt_jointure_espace_module WHERE id_espace='".$espace_tmp["id_espace"]."' AND nom_module='".$module_tmp["nom"]."' AND options LIKE '%".$module_option."%'");
					echo "<tr class='tr_survol'>";
						echo "<td class='".($module_option_check>0?"lien_select2":"lien")." pas_selection' id='txt_".$module_option."' onClick=\"check_txt_box(this.id,'".$module_option."','lien_select2');\"> <img src=\"".PATH_TPL."divers/dependance_dossier.png\" style=\"opacity:0.5;filter:alpha(opacity=50);height:15px;\" /> ".$trad[strtoupper($module_tmp["nom"])."_".$module_option]."</td>";
						echo "<td style='width:30px;'><input type='checkbox' name=\"option_modules[".$module_tmp["nom"]."][]\" value=\"".$module_option."\" id='box_".$module_option."' onClick=\"check_txt_box(this.id,'".$module_option."','lien_select2');\" ".($module_option_check>0?"checked":"")." /></td>";
						echo "<td style='width:30px;'>&nbsp;</td>";
					echo "</tr>";
				}
			}
			////	Incrémente le compteur
			$compteur++;
		}
		?>
		</table>
	</fieldset>

	<div style="margin-top:20px;text-align:right;">
		<input type="hidden" name="id_espace" value="<?php echo $espace_tmp["id_espace"]; ?>" />
		<input type="hidden" name="page_origine" value="<?php echo $_SERVER["HTTP_REFERER"]; ?>" />
		<input type="submit" value="<?php echo $trad["valider"]; ?>" class="button_big" />
	</div>

</form>


<?php require PATH_INC."footer.inc.php"; ?>