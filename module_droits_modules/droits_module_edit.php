<?php
////	INIT
require "commun.inc.php";
require_once PATH_INC."header.inc.php";

if (isset($_POST['update_rights']) && $_POST['update_rights'] == 1) {
	$objet["module"] = objet_infos(array('table_objet' => 'gt_module', 'cle_id_objet' => 'id_module'), $_REQUEST["id_module"], "*");
	$objet["module"]["type_objet"] = "module";
	affecter_droits_acces($objet["module"], $_POST["id_module"]);

	////	FERMETURE DU POPUP
	reload_close();
}

// Configuration pour l'affichage des droits d'accès
$cfg_menu_edit["notif_mail"] = false;
$cfg_menu_edit["fichiers_joint"] = false;
$cfg_menu_edit["objet"]["cle_id_objet"] = "id_module";
$cfg_menu_edit["objet"]["table_objet"] = "gt_module";
$cfg_menu_edit["objet"]["type_objet"] = "module"; 
$cfg_menu_edit["id_objet"] = $_REQUEST["id_module"];
$cfg_menu_edit["hide_users"] = 1;
?>

<script type="text/javascript">
////	Redimensionne
resize_iframe_popup(770,750);
</script>

<form action="<?php echo php_self(); ?>" method="post">

	<?php include(PATH_INC."element_edit.inc.php"); ?>

	<div style="text-align:right;margin-top:20px;">
		<input type="hidden" name="update_rights" value="1" />
		<input type="hidden" name="id_module" value="<?php echo $_REQUEST['id_module']; ?>" />
		<input type="submit" value="<?php echo $trad["valider"]; ?>" class="button_big" />
	</div>
</form>
<?php require PATH_INC."footer.inc.php"; ?>