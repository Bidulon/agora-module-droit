<?php
////	INIT
if($_REQUEST["type_export"]=="utilisateurs")	{ require "../module_utilisateurs/commun.inc.php"; }
else{
	require "../module_contact/commun.inc.php";
	droit_acces_controler($objet["contact_dossier"], $_REQUEST["id_dossier"], 1);
}


////	EXPORTE LES CONTACTS / UTILISATEUR
////
if(isset($_REQUEST["type_export"]) && isset($_POST["export_format"]))
{
	require "contact_import_export.inc.php";
	if($_REQUEST["type_export"]=="utilisateurs")	{ $liste_contacts = db_tableau("SELECT * FROM gt_utilisateur WHERE 1 ".sql_utilisateurs_espace()); }
	else											{ $liste_contacts = db_tableau("SELECT * FROM gt_contact WHERE id_dossier='".intval($_REQUEST["id_dossier"])."' ".sql_affichage($objet["contact"],$_REQUEST["id_dossier"])); }
	export_contact($liste_contacts);
}


////	HEADER & TITRE DU POPUP
////
require_once PATH_INC."header.inc.php";
$titre_popup = ( ($_REQUEST["type_export"]=="utilisateurs")  ?  $trad["users_exporter"]  :  $trad["contact_exporter"] )." (".$trad["contact_export_formats"].")";
titre_popup($titre_popup);
?>


<script type="text/javascript"> resize_iframe_popup(400,250); </script>
<style type="text/css">
body { background-image:url('<?php echo PATH_TPL; ?>module_utilisateurs/fond_popup.png'); font-weight:bold; }
</style>


<form action="<?php echo php_self(); ?>" method="post" style="text-align:center;margin-top:10px;">
	<?php echo $trad["contact_format"]; ?>
	<select name="export_format" onchange="if(this.value=='csv_agora')  afficher('div_csv_agora',true);  else  afficher('div_csv_agora',false);">
		<option value="csv_agora">CSV</option>
		<option value="csv_outlook">CSV OUTLOOK</option>
		<option value="csv_yahoo">CSV YAHOO</option>
		<option value="csv_thunderbird">CSV THUNDERBIRD</option>
		<option value="csv_hotmail">CSV HOTMAIL</option>
		<option value="csv_windowsmail">CSV WINDOWS MAIL</option>
		<option value="ldif">LDIF</option>
	</select> &nbsp; 
	<input type="submit" value="<?php echo $trad["valider"]; ?>" class="button" />
	<input type="hidden" name="type_export" value="<?php echo $_REQUEST["type_export"]; ?>" />
	<input type="hidden" name="id_dossier" value="<?php echo @$_REQUEST["id_dossier"]; ?>" />
	<div id="div_csv_agora" style="display:none;">
		<br /><br />
		<?php echo $trad["contact_separateur"]; ?> &nbsp;<input type="text" name="separateur" value=";" style="width:10px;" /> &nbsp;  &nbsp; 
		<?php echo $trad["contact_delimiteur"]; ?> &nbsp;<input type="text" name="delimiteur" value='"' style="width:10px;" />
	</div>
</form>


<?php require PATH_INC."footer.inc.php"; ?>