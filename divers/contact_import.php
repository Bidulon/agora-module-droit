<?php
////	INIT
if($_REQUEST["type_import"]=="utilisateurs") {
	include "../module_utilisateurs/commun.inc.php";
	controle_acces_admin("admin_espace");
	nb_users_depasse();
}
else {
	include "../module_contact/commun.inc.php";
	droit_acces_controler($objet["contact_dossier"], $_REQUEST["id_dossier"], 1);
}


////	HEADER & TITRE DU POPUP
////
require_once PATH_INC."header.inc.php";
$titre_popup = (($_REQUEST["type_import"]=="utilisateurs")  ?  $trad["users_importer"]  :  $trad["contact_importer"])." (".$trad["contact_import_formats"].")";
titre_popup($titre_popup);


////	IMPORTE LES CONTACTS / UTILISATEURS SELECTIONNES
////
if(isset($_POST["champs_contact"]) && $_POST["contact_import"])
{
	////	Créé le tableau des contacts à importer à partir du tableau général des contacts
	$contacts_import = array();
	foreach($_POST["champs_contact"] as $contact_cpt => $contact)
	{
		// Si le contact a été sélectionné, on l'ajoute au tableau de sortie
		if(in_array($contact_cpt,$_POST["contact_import"])) {
			$contact_tmp = array();
			foreach($_POST["champs_agora"] as $champ_cpt => $champ)		{ if($champ!="")	$contact_tmp[$champ] = $contact[$champ_cpt]; }
			$contacts_import[] = $contact_tmp;
		}
	}

	////	On créé le contact / l'utilisateur
	foreach($contacts_import as $contact_tmp)
	{
		// Init
		$corps_sql = " civilite=".db_format(@$contact_tmp["civilite"],"slash").", adresse=".db_format(@$contact_tmp["adresse"],"slash").", codepostal=".db_format(@$contact_tmp["codepostal"],"slash").", ville=".db_format(@$contact_tmp["ville"],"slash").", pays=".db_format(@$contact_tmp["pays"],"slash").", telephone=".db_format(@$contact_tmp["telephone"],"slash").", telmobile=".db_format(@$contact_tmp["telmobile"],"slash").", fax=".db_format(@$contact_tmp["fax"],"slash").", mail=".db_format(@$contact_tmp["mail"],"slash").", siteweb=".db_format(@$contact_tmp["siteweb"],"slash").", fonction=".db_format(@$contact_tmp["fonction"],"slash").", societe_organisme=".db_format(@$contact_tmp["societe_organisme"],"slash").", competences=".db_format(@$contact_tmp["competences"],"slash").", hobbies=".db_format(@$contact_tmp["hobbies"],"slash").", commentaire=".db_format(@$contact_tmp["commentaire"],"slash");
		// CONTACT
		if($_REQUEST["type_import"]=="contact")
		{
			// Création du contact & affectation en lecture à l'espace courant
			$_POST["lecture_espaces"][0] = $_SESSION["espace"]["id_espace"];
			db_query("INSERT INTO gt_contact SET id_dossier='".intval($_POST["id_dossier"])."', nom=".db_format(@$contact_tmp["nom"],"slash").", prenom=".db_format(@$contact_tmp["prenom"],"slash").", ".$corps_sql.", date_crea='".db_insert_date()."', id_utilisateur='".$_SESSION["user"]["id_utilisateur"]."'");
			affecter_droits_acces($objet["contact"],db_last_id());
		}
		// UTILISATEUR
		elseif($_REQUEST["type_import"]=="utilisateurs")
		{
			// Identifiant (non spécifié => Email OU "jdupon" (pour "Jean dupond"))
			$identifiant = (@$contact_tmp["identifiant"]!="")  ?  $contact_tmp["identifiant"]  :  @$contact_tmp["mail"];
			if($identifiant=="")	$identifiant = strtolower(substr(@$contact_tmp["prenom"],0,1).substr(@$contact_tmp["nom"],0,5));
			$identifiant = str_replace(" ", "", suppr_carac_spe($identifiant,"faible"));
			// Password (non spécifié => nouveau)
			$password = (@$contact_tmp["pass"]!="")  ?  $contact_tmp["pass"]  :  mt_rand(100000,999999);
			$password = str_replace(" ", "", $password);
			// Création de l'user
			$id_user_tmp = creer_utilisateur(@$contact_tmp["nom"], @$contact_tmp["prenom"], $identifiant, $password, null, @$contact_tmp["mail"]);
			if($id_user_tmp==false)		continue;
			// Ajout des infos sur l'utilisateur et affectation aux espaces
			db_query("UPDATE gt_utilisateur SET ".$corps_sql." WHERE id_utilisateur='".intval($id_user_tmp)."'");
			if(count(@$_POST["espaces_affectation"])>0)
			{
				foreach($_POST["espaces_affectation"] as $espace_tmp){
					db_query("INSERT INTO gt_jointure_espace_utilisateur SET id_espace=".db_format($espace_tmp).", id_utilisateur='".intval($id_user_tmp)."', droit='1'");
				}
			}
		}
	}
	////	FERMETURE DU POPUP
	reload_close();
}
?>


<style type="text/css">  body { background-image:url('<?php echo PATH_TPL; ?>module_utilisateurs/fond_popup.png'); font-weight:bold; }  </style>


<script type="text/javascript">
////	Redimensionne la page
<?php echo (count($_POST)==0) ? "resize_iframe_popup(500,250);" : "resize_iframe_popup(950,550);"; ?>

////	Contrôle du formulaire du fichier
function controle_formulaire()
{
	// Il doit y avoir un fichier
	if(get_value("import_fichier")=="")					{ alert("<?php echo $trad["specifier_fichier"]; ?>"); return false; }
	// Le fichier doit être au format csv
	if(extension(get_value("import_fichier"))!="csv")	{ alert("<?php echo $trad["extension_fichier"]; ?> CSV"); return false; }
}

////	Contrôle du formulaire des contacts
function controle_contacts()
{
	// Le champ Agora "nom" et "prenom" doivent être sélectionné
	var select_nom = select_prenom = false;
	for(champ_cpt=0; champ_cpt < get_value("nb_champs"); champ_cpt++)
	{
		if(get_value("champs_agora["+champ_cpt+"]")=="nom")			{ select_nom = true; }
		else if(get_value("champs_agora["+champ_cpt+"]")=="prenom")	{ select_prenom = true; }
	}
	if(select_nom==false || select_prenom==false)	{ alert("<?php echo $trad["import_alert"]; ?>"); return false; }
	// Au moins un contact doit être sélectionné
	var nb_contacts_select = 0;
	for(contact_cpt=0; contact_cpt < get_value("nb_contacts"); contact_cpt++)		{ if(element("contact_import["+contact_cpt+"]").checked==true)	nb_contacts_select ++; }
	if(nb_contacts_select==0)	{ alert("<?php echo $trad["import_alert2"]; ?>"); return false; }
}

////	Selectionne ligne
function select_ligne(contact_cpt)
{
	element("ligne_"+contact_cpt).style.backgroundColor = (element("contact_import["+contact_cpt+"]").checked)  ?  "<?php echo STYLE_TR_SELECT; ?>"  :  "<?php echo STYLE_TR_DESELECT; ?>";
}

////	On coche/décoche tout
function selection_import()
{
	for(contact_cpt=0; contact_cpt<get_value("nb_contacts"); contact_cpt++)
	{
		contact = element("contact_import["+contact_cpt+"]");
		if(contact.checked==true)	{ contact.checked = false; select_ligne(contact_cpt); }
		else						{ contact.checked = true;  select_ligne(contact_cpt); }
	}
}

////	On vérifie que le champ agora n'est pas sélectionné 2 fois
function controle_champ(selection)
{
	for(champ_cpt=0; champ_cpt < get_value("nb_champs"); champ_cpt++)
	{
		if(selection!=champ_cpt && get_value("champs_agora["+champ_cpt+"]")==get_value("champs_agora["+selection+"]") && get_value("champs_agora["+selection+"]")!="") {
			alert("<?php echo $trad["import_alert3"]; ?>");
			set_value("champs_agora["+selection+"]","");
			return false;
		}
	}
}
</script>


<?php
////	SELECTIONNE LE FICHIER CSV
////
if(!isset($_FILES["import_fichier"]) && !isset($_POST["champs_contact"])) {
?>
	<form action="<?php echo php_self(); ?>" method="post" style="text-align:center;margin-top:10px;" enctype="multipart/form-data" OnSubmit="return controle_formulaire();">
		<input type="file" name="import_fichier" />
		<br /><br /><br />
		<input type="hidden" name="type_import" value="<?php echo $_REQUEST["type_import"]; ?>" />
		<input type="hidden" name="id_dossier" value="<?php echo @$_REQUEST["id_dossier"]; ?>" />
		<input type="submit" value="<?php echo $trad["valider"]; ?>" class="button" />
	</form>
<?php
}
////	AFFICHE LES CONTACTS DU FICHIER
////
elseif(isset($_FILES["import_fichier"]))
{
	////	Init
	$import_contacts = array();
	include "contact_import_export.inc.php";

	////	RECUPERE LES VALEURS DU CSV  +  SEPARATEUR
	$csv_import_entete = file($_FILES["import_fichier"]["tmp_name"]);
	$csv_import_entete = str_replace(array("\r","\n"), "", $csv_import_entete[0]);
	$separateur_cpt = 0;
	if(substr_count($csv_import_entete,";") > $separateur_cpt)		{ $separateur = ";";	$separateur_cpt = substr_count($csv_import_entete,";"); }
	if(substr_count($csv_import_entete,",") > $separateur_cpt)		{ $separateur = ",";	$separateur_cpt = substr_count($csv_import_entete,","); }
	if(substr_count($csv_import_entete,"	") > $separateur_cpt)	{ $separateur = "	";	$separateur_cpt = substr_count($csv_import_entete,"	"); }
	$type_csv = type_csv(explode($separateur,$csv_import_entete));

	////	ON PARCOUR CHAQUE LIGNE DU CSV
	$handle = fopen($_FILES["import_fichier"]["tmp_name"],"r");
	while(($data = fgetcsv($handle,10000,$separateur))!==false)		{ $import_contacts[] = $data; }
	$import_nb_champs = count($import_contacts[0]);

	////	INFOS SUR L'IMPORTATION
	if($_REQUEST["type_import"]=="contact" && @$_REQUEST["id_dossier"]==1)		{ $droit_acces_contact = "<hr width='50%'>".$trad["import_infos_contact"]; }
	elseif($_REQUEST["type_import"]=="utilisateurs")							{ $droit_acces_contact = "<hr width='50%'>".$trad["import_infos_user"]; }
	echo "<div class=\"div_infos\" style=\"margin:30px;margin-top:0px;padding:5px;line-height:16px;\">".$trad["import_infos"].@$droit_acces_contact."</div>";

	////	DEBUT DU FIELDSET
	echo "<form action=\"".php_self()."\" method=\"post\" OnSubmit=\"return controle_contacts();\">";
		echo "<fieldset>";
			echo "<table style=\"font-size:9px;\">";

			////	ENTETE DU TABLEAU
			echo "<tr>";
			echo "<td class='lien' onClick=\"selection_import();\" ".infobulle($trad["inverser_selection"])."><img src=\"".PATH_TPL."divers/tri.png\" style=\"margin-left:5px;margin-right:10px;\" /></td>";
			// SELECTION DU CHAMP D'AGORA
			for($champ_cpt=0; $champ_cpt < $import_nb_champs; $champ_cpt++)
			{
				echo "<td><select name=\"champs_agora[".$champ_cpt."]\" style=\"font-size:8px;font-weight:bold;width:100px;\" onChange=\"controle_champ(".$champ_cpt.");\"><option></option>";
				// Sélectionne si le champ agora affiché a bien été répertorié sur cette colonne (avec $type_csv)
				foreach($tab_csv_principal["csv_agora"]["champs"] as $champ_agora_cle => $champ_agora_libelle){
					$selected = (isset($type_csv["tab_cle_agora_position_csv"][$champ_agora_cle]) && $type_csv["tab_cle_agora_position_csv"][$champ_agora_cle]===$champ_cpt)  ?  "selected"  :  "";
					echo "<option value=\"".$champ_agora_cle."\" ".$selected.">".$trad[$champ_agora_cle]."</option>";
				}
				if($_REQUEST["type_import"]=="utilisateurs"){
					echo "<option value=\"identifiant\">".$trad["login"]."</option>";
					echo "<option value=\"pass\">".$trad["pass"]."</option>";
				}
				echo "</select></td>";
			}
			echo "</tr>";

			////	AFFICHE CHAQUE CONTACT
			foreach($import_contacts as $contact_cpt => $contact)
			{
				echo "<tr id=\"ligne_".$contact_cpt."\">";
				echo "<td><input type=\"checkbox\" name=\"contact_import[".$contact_cpt."]\" value=\"".$contact_cpt."\" onClick=\"select_ligne('".$contact_cpt."');\" ".($contact_cpt==0?"":"checked")." /></td>";
				foreach($contact as $champ_cpt => $champ_tmp)	{ echo "<td>".convert_utf8($champ_tmp)."<input type=\"hidden\" name=\"champs_contact[".$contact_cpt."][".$champ_cpt."]\" value=\"".convert_utf8($champ_tmp)."\" /></td>"; }
				echo "</tr>";
				echo "<script> select_ligne('".$contact_cpt."'); </script>";
			}
			echo "</table>";
		echo "</fieldset>";

		////	LISTE LES ESPACES POUR LES AFFECTATIONS
		if($_REQUEST["type_import"]=="utilisateurs")
		{
			echo "<fieldset style=\"width:300px;margin-left:auto;margin-right:auto;\">";
			echo "&nbsp; ".$trad["ESPACES_description_module"]." :";
			foreach(espaces_affectes_user() as $espace_tmp)
			{
				if(droit_acces_espace($espace_tmp["id_espace"],$_SESSION["user"])==2){
					$checked = ($_SESSION["espace"]["id_espace"]==$espace_tmp["id_espace"])  ?  "checked"  :  "";
					echo "<div style=\"margin-left:20px;\"><input type=\"checkbox\" name=\"espaces_affectation[]\" value=\"".$espace_tmp["id_espace"]."\" ".$checked." /> ".$espace_tmp["nom"]."</div>";
				}
			}
			echo "</fieldset>";
		}

		////	INFOS SUR LE CSV + VALIDATION
		echo "<div style=\"text-align:center;margin:20px;\">";
			echo "<input type=\"hidden\" name=\"nb_champs\" value=\"".$import_nb_champs."\" />";
			echo "<input type=\"hidden\" name=\"nb_contacts\" value=\"".count($import_contacts)."\" />";
			echo "<input type=\"hidden\" name=\"type_import\" value=\"".$_REQUEST["type_import"]."\" />";
			echo "<input type=\"hidden\" name=\"id_dossier\" value=\"".@$_REQUEST["id_dossier"]."\" />";
			echo "<input type=\"submit\" value=\"".$trad["valider"]."\" class=\"button_big\" />";
		echo "</div>";
	echo "</form>";
}

require PATH_INC."footer.inc.php";
?>