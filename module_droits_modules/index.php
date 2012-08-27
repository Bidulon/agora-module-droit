<?php
////	INIT
define("IS_MAIN_PAGE",true);
require "commun.inc.php";
require PATH_INC."header_menu.inc.php";
elements_width_height_type_affichage("large","180px","bloc");

controle_acces_admin('admin_espace');

$liste_modules = modules_espace($_SESSION['espace']['id_espace']);
$liste_utilisateurs = users_espace($_SESSION['espace']['id_espace'], 'all' );
$liste_groupes = groupes_users($_SESSION['espace']['id_espace']);
// var_dump($liste_groupes);

// Configuration pour l'affichage des droits d'accès
$cfg_menu_edit["notif_mail"] = false;
$cfg_menu_edit["fichiers_joint"] = false;
$cfg_menu_edit["objet"]["cle_id_objet"] = "id_module";
$cfg_menu_edit["objet"]["table_objet"] = "gt_module";
?>

<style>
.titre_espace		{ font-weight:bold; margin:5px; margin-left:0px; font-size:15px; }
.titre_espace_bis	{ font-weight:bold; margin:5px; margin-left:0px; }
.affectation_espace	{ float:left; width:47%; height:22px; margin:1px; }
</style>

<table id="contenu_principal_table"><tr>
	<td id="menu_gauche_block_td">
		<div id="menu_gauche_block_flottant">
			<div id="menu_gauche_block_flottant" class="menu_gauche_block content">
				<div class="menu_gauche_ligne">
					<div class="menu_gauche_txt"><?php echo $_SESSION["espace"]["nom"]; ?></div>
				</div><hr />
				<div class="menu_gauche_ligne">
					<div class="menu_gauche_img"></div>
					<div class="menu_gauche_txt"><?php echo $trad["DROITS_MODULES_liste_modules"]; ?></div>
				</div>
				<?php foreach ($liste_modules as $module) : ?>
				<div class="menu_gauche_ligne">
					<div class="menu_gauche_img"><img src="<?php echo PATH_TPL.$module['module_dossier_fichier']; ?>/plugin.png"/></div>
					<div class="menu_gauche_txt lien" onclick="edit_iframe_popup('droits_module_edit.php?id_module=<?php echo $module["id_module"]; ?>');"><?php echo $trad[strtoupper($module["nom"])."_nom_module"]; ?></div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	</td>
	<td>
	<?php $indexModule = 0; ?>
	<table>
		<tr>
	<?php foreach ($liste_modules as $module) : ?>
		<?php
			$indexModule++;
			$cfg_menu_edit["objet"]["type_objet"] = "module"; 
			$cfg_menu_edit["objet"]["id_objet"] = $module["id_module"];

			$droits_module = objet_affectations($cfg_menu_edit["objet"], $module["id_module"], "groupes");

			$lecteurs = array();
			$contributeurs = array();

			foreach ($droits_module as $droit) {
				switch ($droit['droit']) {
					case '1':
						$lecteurs[] = $droit['titre'];
						break;
					
					case '2':
						$contributeurs[] = $droit['titre'];
						break;
				}
			}

			sort($lecteurs);
			sort($contributeurs);

		?>
			<td>
				<table class="div_elem_deselect div_elem_contenu div_elem_table" style="<?php echo "width:".$width_element.";height:".$height_element.";"; ?>">
					<tr>
						<td>
							<div>
								<div class="titre_espace"><?php echo $trad[strtoupper($module["nom"])."_nom_module"]; ?></div><hr />
								<div>
								<div class="titre_espace_bis"><i>Groupes avec accès en lecture :</i></div>
								<div><?php echo (count($lecteurs) > 0) ? implode(', ', $lecteurs) : $trad['Aucun']; ?></div>
								</div>
								<div class="titre_espace_bis"><i>Groupes avec accès en écriture :</i></div>
								<div><?php echo (count($contributeurs) > 0) ? implode(', ', $contributeurs) : $trad['Aucun']; ?></div>
								</div>
							</div>
						</td>
					</tr>
				</table>
			</td>
		<?php if (($indexModule % 2) == 0) : ?>
		</tr><tr>
		<?php endif; ?>
	<?php endforeach; ?>
		</tr>
	</table>
	</td>
</table>

<?php require PATH_INC."footer.inc.php"; ?>