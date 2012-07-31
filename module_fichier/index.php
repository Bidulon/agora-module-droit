<?php
////	INIT
define("IS_MAIN_PAGE",true);
require "commun.inc.php";
require PATH_INC."header_menu.inc.php";
init_id_dossier();
elements_width_height_type_affichage("small","100px","bloc");


////	DIVERS CONTROLES AUTO (ADMIN GE)
////
////	Dossier racine existe?
if($_SESSION["user"]["admin_general"]==1 && $_GET["id_dossier"]==1) {
	nettoyer_tmp();
	if(is_dir(PATH_MOD_FICHIER)==false)	{ mkdir(PATH_MOD_FICHIER); chmod(PATH_MOD_FICHIER,0775); }
}
////	Dossier accessible en écriture?
$droit_acces_dossier = droit_acces_controler($objet["fichier_dossier"], $_GET["id_dossier"], 1);
$chemin_dossier_courant = PATH_MOD_FICHIER.chemin($objet["fichier_dossier"],$_GET["id_dossier"],"url");
if(!is_writable(PATH_STOCK_FICHIERS) && $_SESSION["user"]["admin_general"]==1)		{ alert($trad["MSG_ALERTE_chmod_stock_fichiers"]); }
elseif(!is_writable($chemin_dossier_courant) &&  $droit_acces_dossier>1)			{ alert($trad["FICHIER_ajouter_fichier_alert"]." (id_dossier=".$_GET["id_dossier"].")"); }


////	LISTE DES FICHIERS + PREPARATION DU SCROLLER D'IMAGES ET VIDEOS
////
$liste_fichiers = db_tableau("SELECT * FROM gt_fichier WHERE id_dossier='".intval($_GET["id_dossier"])."'  ".sql_affichage($objet["fichier"],$_GET["id_dossier"])."  ".tri_sql($objet["fichier"]["tri"]));
$_SESSION["cfg"]["espace"]["scroller_images"] = array();
foreach($liste_fichiers as $fichier_tmp)	{  if(controle_fichier("image_browser",$fichier_tmp["nom"])==true)  $_SESSION["cfg"]["espace"]["scroller_images"][] = $fichier_tmp;  }
$_SESSION["cfg"]["espace"]["scroller_videos"] = array();
foreach($liste_fichiers as $fichier_tmp)	{  if(controle_fichier("video_browser",$fichier_tmp["nom"])==true)  $_SESSION["cfg"]["espace"]["scroller_videos"][] = $fichier_tmp;  }
?>


<script type="text/javascript">
////	Affiche les images dans une iframe
function afficher_images(id_fichier)
{
	iframe_page_fantome("images.php?id_dossier=<?php echo $_GET["id_dossier"]; ?>&id_fichier="+id_fichier, '100%');
}

////	Affiche les videos dans une iframe
function afficher_videos(id_fichier)
{
	iframe_page_fantome("video.php?id_dossier=<?php echo $_GET["id_dossier"]; ?>&id_fichier="+id_fichier, '100%');
}

////	Telechargement des fichiers
function telecharger_fichiers()
{
	if(nb_elements_select(false,"fichier-")==0)		selection_tous_elements(true, "fichier-");
	if(nb_elements_select(false,"fichier-")<10  || (nb_elements_select(false,"fichier-")>=10 && confirm("<?php echo $trad["FICHIER_telecharger_fichiers_confirm"]; ?>")==true))
		redir('telecharger_archive.php?elements='+elements_url());
}
</script>


<table id="contenu_principal_table"><tr>
	<td id="menu_gauche_block_td">
		<div id="menu_gauche_block_flottant">
			<div class="menu_gauche_block content">
				<?php
				////	MENU D'ARBORESCENCE
				$cfg_menu_arbo = array("objet"=>$objet["fichier_dossier"], "id_objet"=>$_GET["id_dossier"], "ajouter_dossier"=>true, "droit_acces_dossier"=>$droit_acces_dossier);
				require_once PATH_INC."menu_arborescence.inc.php";
				?>
			</div>
			<div class="menu_gauche_block content">
				<?php
				////	AJOUTER FICHIER
				if($droit_acces_dossier>=1.5)	echo "<div class='menu_gauche_line lien' onclick=\"edit_iframe_popup('ajouter_fichiers.php?id_dossier=".$_GET["id_dossier"]."');\"><div class='menu_gauche_img'><img src=\"".PATH_TPL."divers/ajouter.png\" /></div><div class='menu_gauche_txt'>".$trad["FICHIER_ajouter_fichier"]."</div></div>";
				////	LANCER DIAPORAMA
				if(count($_SESSION["cfg"]["espace"]["scroller_images"])>1)	echo "<div class='menu_gauche_line lien' onclick=\"afficher_images();\"><div class='menu_gauche_img'><img src=\"".PATH_TPL."module_fichier/diaporama.png\" /></div><div class='menu_gauche_txt'>".$trad["FICHIER_voir_images"]."</div></div>";
				////	VOIR VIDEOS
				if(count($_SESSION["cfg"]["espace"]["scroller_videos"])>0)	echo "<div class='menu_gauche_line lien' onclick=\"afficher_videos(".$_SESSION["cfg"]["espace"]["scroller_videos"][0]["id_fichier"].");\"><div class='menu_gauche_img'><img src=\"".PATH_TPL."module_fichier/videorama.png\" /></div><div class='menu_gauche_txt'>".$trad["FICHIER_voir_videos"]."</div></div>";
				////	TELECHARGER LES FICHIERS
				if(count($liste_fichiers)>0)	echo "<div class='menu_gauche_line lien' onClick=\"telecharger_fichiers();\"><div class='menu_gauche_img'><img src=\"".PATH_TPL."divers/telecharger.png\" /></div><div class='menu_gauche_txt'>".$trad["FICHIER_telecharger_fichiers"]."</div></div>";
				echo "<hr />";
				////	MENU ELEMENTS
				$cfg_menu_elements = array("objet"=>$objet["fichier"], "objet_dossier"=>$objet["fichier_dossier"], "id_objet_dossier"=>$_GET["id_dossier"], "droit_acces_dossier"=>$droit_acces_dossier);
				require PATH_INC."elements_menu_selection.inc.php";
				////	MENU D'AFFICHAGE  &  DE TRI  &  CONTENU DU DOSSIER
				echo menu_type_affichage();
				echo menu_tri($objet["fichier"]["tri"]);
				echo contenu_dossier($objet["fichier_dossier"],$_GET["id_dossier"]);
				?>
			</div>
		</div>
	</td>
	<td>
		<?php
		////	MENU CHEMIN + OBJETS_DOSSIERS + TAILLE ICONES
		////
		echo menu_chemin($objet["fichier_dossier"], $_GET["id_dossier"]);
		$cfg_dossiers = array("objet"=>$objet["fichier_dossier"], "id_objet"=>$_GET["id_dossier"], "largeur_icone"=>"80px");
		require_once PATH_INC."dossiers.inc.php";
		$height_icone = str_replace("px","",$height_element)-5;
		$libelle_background = (@$_SESSION["agora"]["skin"]=="blanc")  ?  "background-image:url(".PATH_TPL."module_fichier/fond_libelle_fichier.png);"  :  "background-image:url(".PATH_TPL."module_fichier/fond_libelle_fichier_noir.png);";

		////	AFFICHAGE DES FICHIERS
		////
		foreach($liste_fichiers as $fichier_tmp)
		{
			////	INFOS / MODIF / SUPPR
			$nb_versions = db_valeur("SELECT count(*) FROM gt_fichier_version WHERE id_fichier='".$fichier_tmp["id_fichier"]."'");
			$cfg_menu_elem = array("objet"=>$objet["fichier"], "objet_infos"=>$fichier_tmp, "fichiers_joint"=>false);
			$fichier_tmp["droit_acces"] = ($_GET["id_dossier"]>1)  ?  $droit_acces_dossier  :  droit_acces($objet["fichier"],$fichier_tmp);
			if($fichier_tmp["droit_acces"]>=2) {
				$cfg_menu_elem["modif"] = "fichier_edit.php?id_fichier=".$fichier_tmp["id_fichier"];
				$cfg_menu_elem["deplacer"] = PATH_DIVERS."deplacer.php?module_dossier=".MODULE_DOSSIER."&type_objet_dossier=fichier_dossier&id_dossier_parent=".$_GET["id_dossier"]."&elements=fichier-".$fichier_tmp["id_fichier"];
				$cfg_menu_elem["suppr"] = "elements_suppr.php?id_fichier=".$fichier_tmp["id_fichier"]."&id_dossier_retour=".$_GET["id_dossier"];
				$cfg_menu_elem["options_divers"][] = array("icone_src"=>PATH_TPL."divers/ajouter.png", "text"=>$trad["FICHIER_ajouter_versions_fichier"], "action_js"=>"edit_iframe_popup('ajouter_fichiers.php?id_dossier=".$_GET["id_dossier"]."&id_fichier_version=".$fichier_tmp["id_fichier"]."');");
			}
			////	TAILLE OCTETS  +  FICHIER IMAGE
			$fichier_tmp["afficher_taille"] = afficher_taille($fichier_tmp["taille_octet"]);
			if(controle_fichier("image_browser",$fichier_tmp["nom"])){
				$infos_dernier_fichier = infos_version_fichier($fichier_tmp["id_fichier"]);
				list($width,$height) = @getimagesize($chemin_dossier_courant.$infos_dernier_fichier["nom_reel"]);
				$fichier_tmp["resolution"] = $width." x ".$height." ".$trad["FICHIER_pixels"];
			}
			////	INFOBULLES DETAILS DU FICHIER
			$txt_infobulle = "";
			if($fichier_tmp["description"]!="") 	$txt_infobulle .= "<div>".$fichier_tmp["description"]."</div>";
			if(isset($fichier_tmp["resolution"]))	$txt_infobulle .= "<div>".$fichier_tmp["resolution"]."</div>";
			$txt_infobulle .= "<div>".$fichier_tmp["afficher_taille"]."</div>";
			////	LIEN VERS LE TELECHARGEMENT
			$lien_telecharger = "<a onClick=\"redir('telecharger.php?id_fichier=".$fichier_tmp["id_fichier"]."');\" onMouseMove='pas_propager_click(this)' style=\"cursor:url('".PATH_TPL."divers/telecharger.png'),pointer;\"  ".infobulle("<div style='color:#f55;line-height:13px;'>".$trad["telecharger"]." <i><br />".$fichier_tmp["nom"]."</i></div>".$txt_infobulle)." >";
			////	ICONE DU FICHIER
			if($fichier_tmp["vignette"]!="")	$icone_lien_fichier = "<img src=\"".PATH_MOD_FICHIER2.$fichier_tmp["vignette"]."\" style='height:".$height_element.";max-width:".$width_element.";' />";
			else								$icone_lien_fichier = "<img src='".PATH_TPL."module_fichier/type_fichier/".image_fichier($fichier_tmp["nom"]).".png' style='max-height:".$height_icone."px;".($_REQUEST["type_affichage"]=="bloc"?"padding-top:7px;":"")."' />";
			// Ajoute le lien sur l'icone
			if($fichier_tmp["vignette"]!="" && controle_fichier("pdf",$fichier_tmp["nom"])==false)	$icone_lien_fichier = "<a onClick=\"afficher_images('".$fichier_tmp["id_fichier"]."');\" onMouseMove='pas_propager_click(this)' class='lien_loupe' ".infobulle("<div style='color:#f55;'>".$trad["FICHIER_apercu"]."</div>".$txt_infobulle).">".$icone_lien_fichier."</a>";
			elseif(controle_fichier("video_browser",$fichier_tmp["nom"]))							$icone_lien_fichier = "<a onClick=\"afficher_videos('".$fichier_tmp["id_fichier"]."');\" onMouseMove='pas_propager_click(this)' class='lien_loupe' ".infobulle("<div style='color:#f55;'>".$trad["FICHIER_regarder"]."</div>".$txt_infobulle).">".$icone_lien_fichier."</a>";
			elseif(controle_fichier("text",$fichier_tmp["nom"])  ||  controle_fichier("web",$fichier_tmp["nom"])  ||  (controle_fichier("pdf",$fichier_tmp["nom"]) && @$_SESSION["cfg"]["navigateur"]!="ie"))	$icone_lien_fichier = "<a onClick=\"popup('afficher_fichier.php?id_fichier=".$fichier_tmp["id_fichier"]."',null,500,600);\" onMouseMove='pas_propager_click(this)' class='lien_loupe' ".infobulle("<div style='color:#f55;'>".$trad["FICHIER_apercu"]."</div>".$txt_infobulle).">".$icone_lien_fichier."</a>";
			else																					$icone_lien_fichier = $lien_telecharger.$icone_lien_fichier."</a>";
			////	LECTEUR MP3 (fichier de moins de 15Mo)
			if(controle_fichier("mp3",$fichier_tmp["nom"])==true && $fichier_tmp["taille_octet"]<15360000)
				$fichier_tmp["lecteur_mp3"] = "<object type='application/x-shockwave-flash' data=\"".PATH_COMMUN."dewplayer-mini.swf?mp3=telecharger.php%3Fid_fichier%3D".$fichier_tmp["id_fichier"]."\" width='180px' height='18px'><param name='wmode' value='transparent' /><param name='movie' value=\"".PATH_COMMUN."dewplayer-mini.swf?mp3=telecharger.php%3Fid_fichier%3D".$fichier_tmp["id_fichier"]."\" /></object>";


			////	DIV SELECTIONNABLE + OPTIONS
			$cfg_menu_elem["id_div_element"] = div_element($objet["fichier"],$fichier_tmp["id_fichier"]);
			require PATH_INC."element_menu.inc.php";
				////	AFFICHAGE BLOCK
				$libelle_background_tmp = $versions_tmp = $lecteur_mp3 = "";
				if($_REQUEST["type_affichage"]=="bloc")
				{
					////	NB DE VERSIONS  +  BACKGROUND DU NOM DE FICHIER  +  ICONE PDF (SI VIGNETTE)  +  LECTEUR MP3
					if($nb_versions>1)																	$versions_tmp = "<img src=\"".PATH_TPL."module_fichier/versions.png\" onclick=\"popup('versions_fichier.php?id_fichier=".$fichier_tmp["id_fichier"]."');\" class='lien' ".infobulle($nb_versions." ".$trad["FICHIER_nb_versions_fichier"])." /> &nbsp;";
					if($fichier_tmp["vignette"]!="" || @$fichier_tmp["lecteur_mp3"]!="")				$libelle_background_tmp = $libelle_background;
					if($fichier_tmp["vignette"]!="" && controle_fichier("pdf",$fichier_tmp["nom"]))		$icone_lien_fichier = "<div style='text-align:center;'>".$icone_lien_fichier."<img src=\"".PATH_TPL."module_fichier/type_fichier/pdf2.png\" style='margin-left:-10px;margin-top:-2px;vertical-align:top;' /></div>";
					if(isset($fichier_tmp["lecteur_mp3"]))												$lecteur_mp3 = "<div style='position:absolute;margin-left:25px;'>".$fichier_tmp["lecteur_mp3"]."</div><br />";
					////	NOM DU FICHIER (placé en bas)  +  ICONE DU FICHIER
					echo "<table id='titre_fichier_".$fichier_tmp["id_fichier"]."' style='position:absolute;z-index:100;".$libelle_background_tmp."'><tr><td style='padding:0px;line-height:12px;text-align:center;'>".$versions_tmp.$lien_telecharger.nom_fichier_reduit($fichier_tmp["nom"])."</a></td></tr></table>".$lecteur_mp3;
					echo "<script>  element('titre_fichier_".$fichier_tmp["id_fichier"]."').style.width = element('div_elem_".$cpt_div_element."').clientWidth+'px';   replacer_bas_block('div_elem_".$cpt_div_element."','titre_fichier_".$fichier_tmp["id_fichier"]."');  </script>";
					echo "<div class='div_elem_contenu'><div style='text-align:".($fichier_tmp["vignette"]!=""?"right":"center").";'>".$icone_lien_fichier."</div></div>";
				}
				////	AFFICHAGE LISTE
				else
				{
					////	NB DE VERSIONS  +  LECTEUR MP3
					if($nb_versions>1)						$versions_tmp = "<a onClick=\"popup('versions_fichier.php?id_fichier=".$fichier_tmp["id_fichier"]."');\" onMouseMove='pas_propager_click(this)'>".$nb_versions." ".$trad["FICHIER_nb_versions_fichier"]." &nbsp; <img src=\"".PATH_TPL."module_fichier/versions.png\" /></a><img src=\"".PATH_TPL."divers/separateur.gif\" /> " ;
					if(isset($fichier_tmp["lecteur_mp3"]))	$lecteur_mp3 = $fichier_tmp["lecteur_mp3"]."<img src=\"".PATH_TPL."divers/separateur.gif\" />";
					////	ICONE FICHIER + NOM
					echo "<div class='div_elem_contenu' >";
						echo "<table class='div_elem_table'><tr>";
							echo "<td style='text-align:center;width:80px;'>".$icone_lien_fichier."</td>";
							echo "<td class='div_elem_td'>".$lien_telecharger.$fichier_tmp["nom"]."</a></td>";
						echo "<td class='div_elem_td div_elem_td_right'>".$lecteur_mp3.$versions_tmp.$fichier_tmp["afficher_taille"]." <img src=\"".PATH_TPL."divers/separateur.gif\" /> ".$cfg_menu_elem["auteur_tmp"]." <img src=\"".PATH_TPL."divers/separateur.gif\" /> ".temps($fichier_tmp["date_crea"],"date")."</td>";
						echo "</tr></table>";
					echo "</div>";
				}
			echo "</div>";
		}
		////	AUCUN FICHIER
		if(@$cpt_div_element<1)  echo "<div class='div_elem_aucun'>".$trad["FICHIER_aucun_fichier"]."</div>";
		?>
	</td>
</tr></table>


<?php require PATH_INC."footer.inc.php"; ?>