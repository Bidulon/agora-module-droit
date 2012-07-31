<?php
////	INIT
define("IS_MAIN_PAGE",true);
require "commun.inc.php";
require PATH_INC."header_menu.inc.php";
init_id_dossier();
elements_width_height_type_affichage("medium","63px","bloc");
$droit_acces_dossier = droit_acces_controler($objet["lien_dossier"], $_GET["id_dossier"], 1);
?>


<table id="contenu_principal_table"><tr>
	<td id="menu_gauche_block_td">
		<div id="menu_gauche_block_flottant">
			<div class="menu_gauche_block content">
				<?php
				////	MENU D'ARBORESCENCE
				$cfg_menu_arbo = array("objet"=>$objet["lien_dossier"], "id_objet"=>$_GET["id_dossier"], "ajouter_dossier"=>true, "droit_acces_dossier"=>$droit_acces_dossier);
				require_once PATH_INC."menu_arborescence.inc.php";
				?>
			</div>
			<div class="menu_gauche_block content">
				<?php
				////	AJOUTER LIEN
				if($droit_acces_dossier>=1.5)	echo "<div class='menu_gauche_ligne lien' onclick=\"edit_iframe_popup('lien_edit.php?id_dossier=".$_GET["id_dossier"]."');\"><div class='menu_gauche_img'><img src=\"".PATH_TPL."divers/ajouter.png\" /></div><div class='menu_gauche_txt'>".$trad["LIEN_ajouter_lien"]."</div></div><hr />";
				////	MENU ELEMENTS
				$cfg_menu_elements = array("objet"=>$objet["lien"], "objet_dossier"=>$objet["lien_dossier"], "id_objet_dossier"=>$_GET["id_dossier"], "droit_acces_dossier"=>$droit_acces_dossier);
				require PATH_INC."elements_menu_selection.inc.php";
				////	MENU D'AFFICHAGE  &  DE TRI  &  CONTENU DU DOSSIER
				echo menu_type_affichage();
				echo menu_tri($objet["lien"]["tri"]);
				echo contenu_dossier($objet["lien_dossier"],$_GET["id_dossier"]);
				?>
			</div>
		</div>
	</td>
	<td>
		<?php
		////	MENU CHEMIN + OBJETS_DOSSIERS
		////
		echo menu_chemin($objet["lien_dossier"], $_GET["id_dossier"]);
		$cfg_dossiers = array("objet"=>$objet["lien_dossier"], "id_objet"=>$_GET["id_dossier"]);
		require_once PATH_INC."dossiers.inc.php";

		////	SNAPSHOTS
		////
		$snapkeys	= array("i22BNc7FMUsL","EBo6HO9NQF8j","jA3x0kD0RBU9");
		$snapkey	= $snapkeys[mt_rand(0,2)];
		$masquer_websnapr = option_module("masquer_websnapr");
		if($masquer_websnapr!=true)  echo "<script type='text/javascript' src='http://www.websnapr.com/js/websnapr.js'></script>";

		////	LISTE DES LIENS
		////
		$liste_liens = db_tableau("SELECT * FROM gt_lien WHERE id_dossier='".intval($_GET["id_dossier"])."' ".sql_affichage($objet["lien"],$_GET["id_dossier"])." ".tri_sql($objet["lien"]["tri"]));
		foreach($liste_liens as $lien_tmp)
		{
			////	INFOS / MODIF / SUPPR
			$cfg_menu_elem = array("objet"=>$objet["lien"], "objet_infos"=>$lien_tmp);
			$lien_tmp["droit_acces"] = ($_GET["id_dossier"]>1)  ?  $droit_acces_dossier  :  droit_acces($objet["lien"],$lien_tmp);
			if($lien_tmp["droit_acces"]>=2)	{
				$cfg_menu_elem["modif"] = "lien_edit.php?id_lien=".$lien_tmp["id_lien"];
				$cfg_menu_elem["deplacer"] = PATH_DIVERS."deplacer.php?module_dossier=".MODULE_DOSSIER."&type_objet_dossier=lien_dossier&id_dossier_parent=".$_GET["id_dossier"]."&elements=lien-".$lien_tmp["id_lien"];
				$cfg_menu_elem["suppr"] = "elements_suppr.php?id_lien=".$lien_tmp["id_lien"]."&id_dossier_retour=".$_GET["id_dossier"];
			}
			////	LIEN / LIBELLE / SNAPSHOT
			$libelle = "<a href=\"".$lien_tmp["adresse"]."\" target='_blank' onClick=\"requete_ajax('GET','add_logs.php?id_lien=".$lien_tmp["id_lien"]."');\" onMouseMove='pas_propager_click(this);' ".infobulle($lien_tmp["adresse"])."><span style='padding:15px;padding-left:8px;padding-right:8px;'>".text_reduit(($lien_tmp["description"]!=""?$lien_tmp["description"]:$lien_tmp["adresse"]),60)."</span></a>";
			$snapshot = "<div style='position:absolute;width:90px;height:70px;' onClick=\"requete_ajax('GET','add_logs.php?id_lien=".$lien_tmp["id_lien"]."');popup('".$lien_tmp["adresse"]."',null,800,600);\" onMouseMove='pas_propager_click(this);' class='lien'>&nbsp;</div><script type='text/javascript'>wsr_snapshot('".$lien_tmp["adresse"]."','".$snapkey."','t');</script>";

			////	DIV SELECTIONNABLE + OPTIONS
			$cfg_menu_elem["id_div_element"] = div_element($objet["lien"], $lien_tmp["id_lien"]);
			require PATH_INC."element_menu.inc.php";
				////	AFFICHAGE BLOCK
				if($_REQUEST["type_affichage"]=="bloc")
				{
					echo "<div class='div_elem_contenu' style='background-image:url(".PATH_TPL."module_lien/fond_element.png);'>";
						echo "<table style='width:100%;height:".$height_element.";' cellpadding='0px' cellspacing='0px'><tr>";
							echo "<td class='div_elem_td' style='text-align:center;'>".$libelle."</td>";
							if($masquer_websnapr!=true)  echo "<td style='width:70px;'>".$snapshot."</td>";
						echo "</tr></table>";
					echo "</div>";
				}
				////	AFFICHAGE LISTE
				else
				{
					echo "<div class='div_elem_contenu' >";
						echo "<table class='div_elem_table'><tr>";
						if($masquer_websnapr!=true)  echo "<td class='div_elem_td' style='width:70px;'><div style='height:40px;overflow:hidden;'>".$snapshot."</div></td>";
						echo "<td class='div_elem_td'>".$libelle."</td>";
						echo "<td class='div_elem_td div_elem_td_right'>".$cfg_menu_elem["auteur_tmp"]." <img src=\"".PATH_TPL."divers/separateur.gif\" /> ".temps($lien_tmp["date_crea"],"date")."</td>";
						echo "</tr></table>";
					echo "</div>";
				}
			echo "</div>";
		}
		////	AUCUN LIEN
		if(@$cpt_div_element<1)  echo "<div class='div_elem_aucun'>".$trad["LIEN_aucun_lien"]."</div>";
		?>
	</td>
</tr></table>


<?php require PATH_INC."footer.inc.php"; ?>