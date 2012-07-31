<?php
////	INIT
require_once "../".$_GET["module_dossier"]."/commun.inc.php";
require_once PATH_INC."header.inc.php";
titre_popup($trad["deplacer_autre_dossier"]);
?>


<script type="text/javascript">
////	On redimensionne le popup
resize_iframe_popup(450,400);
////    On indique le dossier sélectionné dans le formulaire
function select_dossier_action(id_dossier)		{ set_value("id_dossier",id_dossier); }
</script>


<form action="<?php echo ROOT_PATH.$_GET["module_dossier"]."/deplacer.php"; ?>" method="post">
	<?php
	////	MENU D'ARBORESCENCE
	$cfg_menu_arbo = array("objet"=>$objet[$_GET["type_objet_dossier"]], "id_objet"=>$_GET["id_dossier_parent"]);
	require_once PATH_INC."menu_arborescence.inc.php";
	?>
	<div style="text-align:right;padding:10px;">
		<input type="hidden" name="id_dossier" id="id_dossier" value="<?php echo $_GET["id_dossier_parent"]; ?>">
		<input type="hidden" name="elements" value="<?php echo $_GET["elements"]; ?>" />
		<input type="submit" value="<?php echo $trad["modifier"]; ?>" class="button_big" />
	</div>
</form>


<?php require PATH_INC."footer.inc.php"; ?>
